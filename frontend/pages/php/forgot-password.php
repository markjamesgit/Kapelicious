<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>

<body>
    <h1>Forgot Password</h1>

    <form action="/Kapelicious/backend/functions/send-password-reset.php" method="post">
        <label for="email">Email</label>
        <input type="email" name="email" id="email">
        <button>Send</button>
    </form>
</body>

</html>