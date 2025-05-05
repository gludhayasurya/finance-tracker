<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Mail</title>
</head>
<body>
    <h1>Hello {{ $details['name'] ?? 'User' }}</h1>
    <p>This is a <strong>test email</strong> sent from your Laravel application.</p>
    <p>If you received this, everything is working fine.</p>
    <br>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
