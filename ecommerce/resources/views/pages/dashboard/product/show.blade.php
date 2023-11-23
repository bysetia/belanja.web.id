<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Product &raquo; #{{ $product->id }} {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="font-semibold text-lg text-gray-800 leading-tight mb-5">Product Details</h2>

            <div class="bg-white overflow-hidden shadow sm:rounded-lg mb-10">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="table-auto w-full">
                        <tbody>
                            <tr>
                                <th class="border px-6 py-4 text-left">Store Name</th>
                                <td class="border px-6 py-4">{{ $product->store->name }}</td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Seller Emails</th>
                                <td class="border px-6 py-4">{{ $product->user->email }}</td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Seller Address</th>
                                <td class="border px-6 py-4">{{ $product->user->address_one }}</td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Product name</th>
                                <td class="border px-6 py-4">{{ $product->name }}</td>
                            </tr>
                            
                             <tr>
                                <th class="border px-6 py-4 text-left">Product Images</th>
                                <td class="border px-6 py-4"> <img src="{{ $product->picturePath }}" alt="Gambar Produk" width="100"></td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Description</th>
                                <td class="border px-6 py-4">{{ $product->description }}</td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Product Stock</th>
                                <td class="border px-6 py-4">{{ $product->quantity }}</td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Sold Products</th>
                                <td class="border px-6 py-4">{{ $product->sold_quantity }}</td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Price</th>
                                <td class="border px-6 py-4">{{ number_format($product->price) }}</td>
                            </tr>
                            <!--<tr>-->
                            <!--    <th class="border px-6 py-4 text-left">Shipping Price</th>-->
                            <!--    <td class="border px-6 py-4">{{ number_format($product->shipping_cost) }}</td>-->
                            <!--</tr>-->
                            <tr>
                                <th class="border px-6 py-4 text-left">Ratings</th>
                                <td class="border px-6 py-4">{{ $product->rate }}</td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Product Category</th>
                                <td class="border px-6 py-4">{{ $product->category->name }}</td>
                            </tr>
                               <tr>
                                <th class="border px-6 py-4 text-left">Weight</th>
                                <td class="border px-6 py-4">{{ $product->weight }}gram</td>
                            </tr>
                            <tr>
                                <th class="border px-6 py-4 text-left">Created_at</th>
                                <td class="border px-6 py-4">{{ $product->created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

         
        </div>
    </div>
</x-app-layout>
