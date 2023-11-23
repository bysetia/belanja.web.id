<!DOCTYPE html>
<html>
<head>
    <title>Belanja.id - Lupa Password</title>
    <style>
        /* Gaya-gayaan CSS Anda di sini */

        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        h1 {
            color: #333333;
        }

        ol {
            margin-left: 20px;
        }

        p {
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 20px;
            color: #999999;
            font-size: 12px;
        }

        .salam {
            font-weight: bold;
            color: black;
            font-size: 20px;
        }

        .title {
            font-weight: bold;
            color: black;
            font-size: 15px;
        }

        a {
            color: red;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <h1>Belanja.id - Lupa Password</h1>
    <p>Terima kasih telah menggunakan platform kami. Jika Anda telah meminta untuk mereset password, kami telah mengirimkan email verifikasi untuk reset password Anda.</p>
    <p>Jika Anda tidak merasa melakukan permintaan ini, Anda bisa mengabaikan email ini.</p>
    <p>Untuk melanjutkan proses reset password, silakan klik tautan di bawah ini:</p>
    <a href="{{ $resetPasswordUrl }}" target="_blank" onclick="resetPassword(event)">Reset Password</a>
    <p>Jika tautan di atas tidak berfungsi, salin dan tempelkan tautan di bawah ini ke peramban web Anda:</p>
    <p>{{ $resetPasswordUrl }}</p>
    <p>Terima kasih telah memilih platform kami. Kami berharap dapat melayani Anda dengan baik!</p>
    <p class="salam">Salam hangat,</p>
    <p class="title">Belanja.id</p>
    <p class="footer">Email ini dikirim secara otomatis. Mohon jangan membalas email ini.</p>

    <script>
        function resetPassword(event) {
            event.preventDefault();

            // Mengirim permintaan POST menggunakan Fetch API
            post(event.target.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Tambahkan ini jika menggunakan Laravel Form
                },
                body: JSON.stringify({}) // Kosongkan body karena ID diambil dari URL
            })
            .then(response => {
                // Tanggapan dari server
                console.log(response);
                // Lakukan tindakan setelah permintaan berhasil dikirim
            })
            .catch(error => {
                // Tanggapan error
                console.error(error);
                // Lakukan tindakan jika terjadi kesalahan
            });
        }
    </script>
</body>
</html>