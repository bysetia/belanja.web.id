@component('mail::message')
<!--# Halo! {{ $user->name }}-->

Kami memiliki promo baru untuk Anda:

*Produk:* {{ $promo->product->name }}
*Diskon:* {{ $promo->persen_promo }}%
*Harga Sebelum Promo:* Rp {{ number_format($promo->product->price, 0, ',', '.') }}
*Harga Setelah Promo:* Rp {{ number_format($promo->after_promo, 0, ',', '.') }}

![Gambar Produk]({{ $promo->product->picturePath }})

*Promo berlaku dari {{ $promo->start_date }} hingga {{ $promo->end_date }}*

@component('mail::button', ['url' => url('/promos/' . $promo->id)])
Lihat Promo
@endcomponent

Terima kasih telah menggunakan website kami!

Thanks,<br>
{{ config('app.name') }}
@endcomponent