<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F3F4F6;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .reset-password-container {
            max-width: 400px;
            padding: 20px;
            background-color: #FFFFFF;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #EF233C;
            margin-top: 0;
        }
        label {
            font-weight: bold;
            color: #4A4A4A;
        }
        input[type="password"] {
            width: 93%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button[type="submit"] {
            background-color: cornflowerblue;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #EF233C;
        }
    </style>
</head>
<body>
      <div class="reset-password-container">
        <h1>Reset Password</h1>
        <form id="resetPasswordForm" method="POST" action="{{ route('dashboard.user.reset-password', $user) }}">
            @csrf
            @method('post')
            <label for="password">New Password</label>
            <input type="password" name="password" id="password" required>
            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
            <button type="button" id="resetButton">Reset Password</button>
        </form>        
    </div>
    
    <script>
        document.getElementById('resetButton').addEventListener('click', function() {
            var confirmed = confirm('Are you sure you want to reset your password?');
            if (confirmed) {
                document.getElementById('resetPasswordForm').submit();
            }
        });
    </script>
</body>
</html>