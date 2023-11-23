<!DOCTYPE html>
<html>
<head>
    <title>Belanja.id</title>
    <style>
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

        .salam{
            font-weight: bold;
            color: black;
            font-size: 20px;
        }
        .title{
            font-weight: bold;
            color: black;
            font-size: 15px;
        }
        a{
            color: red;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Menerima inputan dari Postman
        $user_id = $_POST["user_id"];

        // Koneksi ke database
        $servername = "localhost";
        $username = "bimbeln1_belanja";
        $password = "alhamdulillah123";
        $dbname = "bimbeln1_belanja";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Periksa koneksi
        if ($conn->connect_error) {
            die("Koneksi gagal: " . $conn->connect_error);
        }

        // Mendapatkan data pengguna berdasarkan user_id dari database
        $sql = "SELECT name FROM users WHERE id = '$user_id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Ambil nama pengguna dari hasil query
            $row = $result->fetch_assoc();
            $nama_pengguna = $row["name"];

            // Tampilkan nama pengguna dalam HTML
            echo "<h1>Kepada, " . $nama_pengguna . "</h1>";
        } else {
            echo "<h1>Data pengguna tidak ditemukan</h1>";
        }

        // $url = "http://localhost:8000/api/email/verify/" . $user_id;

        // Tutup koneksi ke database
        $conn->close();
    }
    
    ?>

    <p>Terima kasih telah mendaftar di platform kami. Untuk menyelesaikan pengaturan akun Anda dan memastikan keamanan informasi Anda, kami mohon Anda melakukan verifikasi alamat email Anda dengan mengikuti petunjuk di bawah ini:</p>
    <h3>
        Silahkan verifikasi email kamu terlebih dahulu dengan mengklik link berikut:
    </h3>
    
    <a href="{{ $verification_url }}" target="_blank" onclick="verifyEmail(event)">Klik disini</a>

    <p>Terima kasih telah memilih platform kami. Kami berharap dapat melayani Anda dengan baik!</p>
    <p class="salam">Salam hangat,</p>
    <p class="title">Belanja.id</p>
    <p class="footer">Email ini dikirim secara otomatis. Mohon jangan membalas email ini.</p>

<script>
    function verifyEmail(event) {
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