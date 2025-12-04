<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quote Request</title>
</head>
<body>
    @if(session('status'))
        <p>{{ session('status') }}</p>
    @endif
    <form method="POST" action="/lead">
        @csrf
        <label>Name <input type="text" name="name" required></label><br>
        <label>Company <input type="text" name="company_name"></label><br>
        <label>Email <input type="email" name="email"></label><br>
        <label>Phone <input type="text" name="phone"></label><br>
        <label>Origin <input type="text" name="origin"></label><br>
        <label>Destination <input type="text" name="destination"></label><br>
        <label>Freight Details <input type="text" name="freight_details"></label><br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
