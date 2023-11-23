<!DOCTYPE html>
<html>
<head>
    <title>Event Registration Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .invoice-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        h1, h2 {
            color: #EF233C;
            text-align: center;
        }
        p {
            margin: 5px 0;
        }
        strong {
            font-weight: bold;
        }
        .thank-you {
            text-align: center;
            margin-top: 20px;
        }
        .ttd {
            text-align: right;
            margin-top: 40px;
        }
        h3 {
            font-weight: 500;
            color: #EF233C;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <h1>Event Registration Ticket</h1>
        <p>Thank you for registering for the event. Here are the details:</p>
        <p><strong>Event Name:</strong> {{ $event->name }}</p>
        <p><strong>Event Title:</strong> {{ $event->title }}</p>
        <p><strong>Event Description:</strong> {{ $event->description }}</p>
        <p><strong>Event Date:</strong> {{ $event->date }}</p>
        <p><strong>Event Time:</strong> {{ $event->time }}</p>
        <p><strong>Event Location:</strong> {{ $event->location }}</p>
        <div class="thank-you">
            <p style="color: #EF233C">Thank you for your registration!</p>
        </div>
        <!-- Anda dapat menambahkan informasi lainnya sesuai kebutuhan -->
        <div class="ttd">
            <h3>Belanja.id</h3>
            <p>{{ $event->name }}</p>
        </div>
    </div>
</body>
</html>