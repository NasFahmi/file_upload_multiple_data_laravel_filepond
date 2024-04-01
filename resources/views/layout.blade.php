<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    @vite('resources/css/app.css')
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
        rel="stylesheet" />

    @vite('resources/css/app.css')
    <title>@yield('title')</title>
</head>
<body>
    <div class="container mx-auto max-w-screen-lg">
        @yield('content')
    </div>

</body>
</html>