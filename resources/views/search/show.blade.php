<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <a href="{{ route('search.index') }}">Web-Search.org</a>
    <form action="{{ route('search.show') }}" method="GET">
        <input type="text" name="q" value="{{ $query }}" autocomplete="off">
        <button type="submit">Search</button>
    </form>

    @if($count > 0)
        <h5>About {{ $count }} results</h5>
        
        @foreach($results as $result)
                {{ $result['domain'] }}
                <a href="{{ $result['url'] }}" target="_blank">
                    {{ $result['title'] }}
                </a>
                <p>{{ $result['description'] }}</p>
        @endforeach
    @else
        <h2>No results found for "{{ $query }}"</h2>
        <p>Try different keywords or check your spelling.</p>
    @endif
</body>
</html>