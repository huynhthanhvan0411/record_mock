<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <title>{{ $notification->subject }}</title> --}}
    <title>Amela</title>
</head>
<body>
    <h1>{{ $notification->subject }}</h1>
    <p>{{ $notification->message }}</p>
</body>
</html>
