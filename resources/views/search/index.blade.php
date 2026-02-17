<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web-Search.org</title>
</head>
<body>
    Web-Search.org
        <form action="{{ route('search.show') }}" method="GET">
            <input type="text" name="q" placeholder="Search the web..." autofocus autocomplete="off">
            <button type="submit">Search</button>
        </form>
    </div>
</body>
</html>