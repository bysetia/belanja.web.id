<?php

namespace App\Http\Controllers\API;

use Exception;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Store;
use App\Models\Courier;
use Midtrans\CoreApi;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionItem;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Midtrans\Notification;
use App\Models\UserAddress;


class TransactionController extends Controller
{

    public function all(Request $request)
    {
        $id = $request->input('id');
        $product_id = $request->input('products_id');
        $status = $request->input('status');
        $store_id = $request->input('store_id');
        $user_id = $request->input('user_id'); // Ambil user_id dari permintaan

        $transactions = Transaction::with(['product', 'user', 'user.user_addresses' => function ($query) {
            $query->where('is_primary', 1)->limit(1);
        }, 'courier', 'product.store']); // Include the 'store' relationship on the 'product'

        if ($id) {
            $transactions->where('id', $id);
        }

        if ($user_id) {
            $transactions->where('users_id', $user_id);
        }

        if ($store_id) {
            $transactions->whereHas('product', function ($query) use ($store_id) {
                $query->where('store_id', $store_id);
            });
        }

        if ($product_id) {
            $transactions->where('products_id', $product_id);
        }

        if ($status) {
            $transactions->where('status', $status);
        }

        $transactions->latest();
        $transactions = $transactions->get();

        $formattedTransactions = $transactions->map(function ($transaction) {
            $transaction['created_at'] = Carbon::parse($transaction['created_at'])->format('Y-m-d H:i');

            if ($transaction->user_address_id) {
                $userAddress = UserAddress::where('id', $transaction->user_address_id)->first();
                $transaction['user_address'] = $userAddress;
            } else {
                $transaction['user_address'] = null;
            }

            return $transaction;
        });

        return ResponseFormatter::success(
            $formattedTransactions,
            'Transaction data successfully retrieved'
        );
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            // 'product_name' => $product->name, 
            // 'product_price' => $product->price, 
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:1',
            'total' => 'required|integer|min:0',
            'status' => 'required|in:PENDING,SUCCESS,PROCESSED,CANCELLED,FAILED,SHIPPING,SHIPPED',
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|exists:cities,id',
            'weight' => 'required|array|min:1',
            'weight.*' => 'required|numeric|min:1',
            'courier' => 'required|array|min:1',
            'courier.*' => 'required|in:jne,pos,tiki',
        ]);

        $user = Auth::user();
        $userAddress = $user->user_addresses->where('is_primary', 1)->first();

        if (!$userAddress) {
            return ResponseFormatter::error('User does not have a primary address', 400);
        }

        $transactions = [];
        $total = $request->total; // Use the provided total from the request

        foreach ($request->product_id as $index => $product_id) {
            $product = Product::find($product_id);

            if ($product->quantity <= 0) {
                return ResponseFormatter::error('Product with ID ' . $product_id . ' is out of stock.', 400);
            }

            if ($request->quantity[$index] > $product->quantity) {
                return ResponseFormatter::error('Product with ID ' . $product_id . ' does not have enough stock.', 400);
            }

            $shippingCost = $this->calculateShippingCost(
                $request->origin_city_id,
                $request->destination_city_id,
                $request->weight[$index],
                $request->courier[$index]
            );

            if ($shippingCost === false) {
                return ResponseFormatter::error('Failed to get shipping cost information', 500);
            }

            Cart::where('users_id', Auth::user()->id)
                ->where('products_id', $product_id)
                ->delete();

            $selectedCourier = $request->courier[$index];

            $newTransaction = Transaction::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product_id,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'quantity' => $request->quantity[$index],
                'status' => $request->status,
                'payment_url' => '',
                'shipping_cost' => $shippingCost,
                'courier' => $selectedCourier,
                'user_address_id' => $userAddress->id,
            ]);

            $product->increment('sold_quantity', $request->quantity[$index]);
            $product->decrement('quantity', $request->quantity[$index]);

            $transactionData = [
                'id' => $newTransaction->id,
                'users_id' => $newTransaction->users_id,
                'quantity' => $newTransaction->quantity,
                'user_address' => $userAddress,
            ];

            $transactionData['product'] = [
                'id' => $product->id,
                'price' => $product->price,
                'quantity' => $request->quantity[$index],
                'name' => $product->name,
                'product_name' => $product->name,
                'product_price' => $product->price,
            ];

            $store = Store::find($product->store_id);

            $transactionData['shipping_cost'] = $shippingCost;

            $transactions[] = $transactionData;

            Transaction::where('id', $transactions[$index]['id'])->update(['shipping_cost' => $shippingCost]);
        }

        // Calculate total saldo to be added to store
        $totalStoreSaldo = $total;

        // Update store's saldo
        if ($store) {
            $store->saldo += $totalStoreSaldo;
            $store->save();
        }

        foreach ($transactions as $index => $transaction) {
            Transaction::where('id', $transaction['id'])->update(['total' => $total]);
        }

        $firstTransactionId = $transactions[0]['id'];
        $firstTransactionObj = Transaction::find($firstTransactionId);

        $paymentUrl = $this->generatePaymentUrl($firstTransactionObj);

        if (!$paymentUrl) {
            return ResponseFormatter::error('Failed to get payment URL', 500);
        }

        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        $transactionData = [
            'transaction_details' => [
                'order_id' => $firstTransactionObj->id,
                'gross_amount' => (int) $firstTransactionObj->total,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'phone' => Auth::user()->phone,
                'billing_address' => [
                    'first_name' => $userAddress->first_name,
                    'last_name' => $userAddress->last_name,
                    'address' => $userAddress->address,
                    'city' => $userAddress->city,
                    'postal_code' => $userAddress->postal_code,
                    'phone' => $userAddress->phone,
                    'country_code' => $userAddress->country_code,
                ],
            ],
            'item_details' => [],
        ];

        foreach ($transactions as $transaction) {
            $product = Product::find($transaction['product']['id']);
            $user = User::find($transaction['users_id']);

            if (is_object($product) && property_exists($product, 'store_id')) {
                $store = Store::find($product->store_id);
            } else {
                $store = null;
            }

            $userDetails = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];

            $productTotal = $product['price'] * $product['quantity'];
            $totalWithShipping = $productTotal + $transaction['shipping_cost'];
            $courier = Courier::find($selectedCourier);


            $itemDetail = [
                'id' => $product['id'],
                'price' => $product['price'],
                'quantity' => $product['quantity'],
                'name' => $product['name'],
                'product_name' => $product['name'],
                'product_price' => $product['price'],
                'total' => $productTotal,
                'shipping_cost' => $transaction['shipping_cost'],
                'total_with_shipping' => $totalWithShipping,
                'user_details' => $userDetails,
                'courier' => $courier ? [
                    'courier' => $courier->code,
                    'service' => $courier->title,
                    'cost' => $transaction['shipping_cost'],
                    'etd' => $courier->etd,
                    'logo' => $courier->logo,
                ] : null,
            ];

            $itemDetail['product_total'] = $productTotal;
            $transactionData['item_details'][] = $itemDetail;
        }

        try {
            $snapToken = Snap::getSnapToken($transactionData);
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to get Snap token', 500);
        }

        foreach ($transactions as $index => $transaction) {
            Transaction::where('id', $transaction['id'])->update([
                'payment_url' => $paymentUrl,
                'snap_token' => $snapToken,
                'status' => 'PENDING',
            ]);
        }

        // Handle Midtrans callback
        // $this->handleMidtransCallback($request, $transactions);

        $response = [
            'data' => $transactions,
            'payment_url' => $paymentUrl,
            'snap_token' => $snapToken,
            'total_and_status' => [
                'total' => $total,
                'status' => 'PENDING',
            ],
        ];

        $firstTransactionObj->save();

        // Return the response
        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Transaction successful',
            ],
            'data' => $response,
        ]);
    }

    public function callback(Request $request)
    {
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        $notification = new Notification();

        // Mengambil data notifikasi dari $request
        $status = $notification->transaction_status;
        $payment_type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Cari transaksi berdasarkan order_id
        $transaction = Transaction::findOrFail($order_id);

        if ($status == 'capture') {
            if ($payment_type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $transaction->status = 'PENDING';
                } else {
                    $transaction->status = 'PROCESSED';
                }
            }
        } elseif ($status == 'settlement') {
            if ($transaction->status == 'SHIPPED') {
                $transaction->status = 'SHIPPED';
            } else {
                $transaction->status = 'PROCESSED';
            }

            // Check if the settlement date is more than 3 days ago
            $settlementTime = Carbon::parse($transaction->settlement_time); // Convert to Carbon instance
            $deadline = Carbon::now()->subDays(3); // Calculate the deadline (3 days ago)

            if ($settlementTime->lt($deadline)) {
                $transaction->status = 'CANCELLED';
            }
        } elseif ($status == 'pending') {
            $transaction->status = 'PENDING';
        } elseif ($status == 'deny') {
            $transaction->status = 'CANCELLED';
        } elseif ($status == 'expire') {
            $transaction->status = 'CANCELLED';
        } elseif ($status == 'cancel') {
            $transaction->status = 'CANCELLED';
        }

        $transaction->save();

        // Kirim respons JSON sesuai kebutuhan
        return response()->json(['message' => 'Callback diterima dan diproses']);
    }


    public function calculateShippingCost($originCityId, $destinationCityId, $weight, $courier)
    {
        // Panggil API pengiriman eksternal atau layanan lainnya untuk mendapatkan informasi ongkos kirim
        // Misalnya, menggunakan RajaOngkir
        $apiKey = '8d23f3e38ff8ee5e759188be37791b9c'; // Ganti dengan API key Anda

        $response = Http::asForm()->withHeaders([
            'key' => $apiKey
        ])->post('https://api.rajaongkir.com/starter/cost', [
            'origin' => $originCityId,
            'destination' => $destinationCityId,
            'weight' => $weight,
            'courier' => $courier
        ]);

        if ($response->failed()) {
            return false; // Gagal mendapatkan informasi ongkos kirim
        }

        $result = $response->json();

        if ($result['rajaongkir']['status']['code'] !== 200) {
            return false; // Gagal mendapatkan informasi ongkos kirim
        }

        // Ambil data ongkos kirim dari response
        $shippingCosts = $result['rajaongkir']['results'][0]['costs'];

        // Periksa apakah terdapat opsi pengiriman yang tersedia
        if (empty($shippingCosts)) {
            return false; // Tidak ada opsi pengiriman yang tersedia
        }

        // Ambil biaya ongkos kirim terendah (misalnya yang pertama)
        $cheapestShippingCost = $shippingCosts[0]['cost'][0]['value'];

        return $cheapestShippingCost;
    }

    private function generatePaymentUrl($transaction)
    {
        $user = $transaction->user;

        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        $midtrans = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'enabled_payments' => [
                'bank_transfer', 'cstore',
                'bca_klikbca', 'permata_va', 'bca_va', 'bni_va',
                'alfamart', 'indomaret', 'indomaret_phg', 'indomaret_point',
                'shopeepay',
            ],
            'vtweb' => [],
        ];

        try {
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            return $paymentUrl;
        } catch (Exception $e) {
            return null;
        }
    }


    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->all());

        return ResponseFormatter::success($transaction, 'Transaction updated successfully');
    }

    public function acceptOrder($transactionId)
    {
        // Cari transaksi berdasarkan ID dan sertakan relasi product
        $transaction = Transaction::with('product')->find($transactionId);

        // Pastikan transaksi ditemukan
        if (!$transaction) {
            return ResponseFormatter::error('Transaction not found.', 404);
        }

        // Pastikan status transaksi adalah 'PENDING'
        if ($transaction->status !== 'PROCESSED') {
            return ResponseFormatter::error('Transaction cannot be accepted. Status is not PROCESSED.', 400);
        }

        // Ubah status transaksi menjadi 'diproses'
        $transaction->status = 'SHIPPED';
        $transaction->save();

        // Ambil data produk terkait dengan transaksi
        $product = $transaction->product;

        // Ambil data user terkait dengan transaksi
        $user = $transaction->user;

        // Buat data respons dengan format yang diinginkan
        $response = [
            'id' => $transaction->id,
            'users_id' => $transaction->users_id,
            'products_id' => $transaction->products_id,
            'quantity' => $transaction->quantity,
            'total' => $transaction->total,
            'status' => $transaction->status,
            'payment_url' => $transaction->payment_url,
            'deleted_at' => $transaction->deleted_at,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
            'shipping_cost' => $transaction->shipping_cost,
            'snap_token' => $transaction->snap_token,
            'product' => $product, // Isi data produk jika diperlukan
            'user' => $user, // Isi data user terkait dengan transaksi
        ];

        return ResponseFormatter::success($response, 'Transaction has been accepted and status is now SHIPPED.');
    }


    public function markAsShipped($transactionId)
    {
        // Cari transaksi berdasarkan ID dan sertakan relasi product
        $transaction = Transaction::with('product')->find($transactionId);

        // Pastikan transaksi ditemukan
        if (!$transaction) {
            return ResponseFormatter::error('Transaction not found.', 404);
        }

        // Pastikan status transaksi adalah 'PROCESSED'
        if ($transaction->status !== 'PROCESSED') {
            return ResponseFormatter::error('Transaction cannot be marked as shipped. Status is not PROCESSED.', 400);
        }

        // Ubah status transaksi menjadi 'SHIPPED'
        $transaction->status = 'SHIPPED';
        $transaction->save();

        // Ambil data produk terkait dengan transaksi
        $product = $transaction->product;
        // $product->increment('sold_quantity', $transaction->quantity);

        // Ambil data user terkait dengan transaksi
        $user = $transaction->user;

        // Buat data respons dengan format yang diinginkan
        $response = [
            'id' => $transaction->id,
            'users_id' => $transaction->users_id,
            'products_id' => $transaction->products_id,
            'quantity' => $transaction->quantity,
            'total' => $transaction->total,
            'status' => $transaction->status,
            'payment_url' => $transaction->payment_url,
            'deleted_at' => $transaction->deleted_at,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
            'shipping_cost' => $transaction->shipping_cost,
            'snap_token' => $transaction->snap_token,
            'product' => $product, // Isi data produk jika diperlukan
            'user' => $user, // Isi data user terkait dengan transaksi
        ];

        return ResponseFormatter::success($response, 'Transaction has been marked as SHIPPED.');
    }

    public function markAsFinished($transactionId)
    {
        // Find the transaction by ID and include the product relation
        $transaction = Transaction::with('product')->find($transactionId);

        // Make sure the transaction is found
        if (!$transaction) {
            return ResponseFormatter::error('Transaction not found.', 404);
        }

        // Make sure the transaction status is 'SHIPPED'
        if ($transaction->status !== 'SHIPPED') {
            return ResponseFormatter::error('Transaction cannot be marked as finished. Status is not SHIPPED.', 400);
        }

        // Update the transaction status to 'FINISHED'
        $transaction->status = 'FINISHED';
        $transaction->save();

        // Get the product data associated with the transaction
        $product = $transaction->product;

        // Get the user data associated with the transaction
        $user = $transaction->user;

        // Create the response data in the desired format
        $response = [
            'id' => $transaction->id,
            'users_id' => $transaction->users_id,
            'products_id' => $transaction->products_id,
            'quantity' => $transaction->quantity,
            'total' => $transaction->total,
            'status' => $transaction->status,
            'payment_url' => $transaction->payment_url,
            'deleted_at' => $transaction->deleted_at,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
            'shipping_cost' => $transaction->shipping_cost,
            'snap_token' => $transaction->snap_token,
            'product' => $product, // Include product data if needed
            'user' => $user, // Include user data associated with the transaction
        ];

        return ResponseFormatter::success($response, 'Transaction has been marked as FINISHED.');
    }

    public function checkPaymentStatus($orderId)
    {
        $baseUrl = config('app.env') === 'production'
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        $authString = base64_encode("SB-Mid-server-ZYcsAfAMrYk0m5KMhgZqBcYl" . ":");

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => "Basic {$authString}",
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)
            ->get("{$baseUrl}/v2/{$orderId}/status");

        $responseData = $response->json();

        // Process and format the response data as needed
        // You can use $responseData to display the payment details

        return response()->json([
            'meta' => [
                'code' => $response->status(),
                'status' => $response->status() == 200 ? 'success' : 'error',
                'message' => $response->status() == 200 ? 'Payment status retrieved successfully' : 'Failed to retrieve payment status',
            ],
            'data' => $responseData,
        ]);
    }
}
