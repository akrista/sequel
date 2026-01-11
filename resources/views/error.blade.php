<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
  <link rel="shortcut icon" href="{{ asset('vendor/sequel/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('vendor/sequel/app.css') }}">

  <title>Error in Sequel</title>
</head>

<body style="background-color: #edf1f3;">

  <div id="sequel"></div>

  <script>
    // Pass initial data to JavaScript
    window.Prequel = {};
    window.Prequel.error = {
      error: true,
      detailed: @json($error_detailed),
      code: @json($http_code)
    };
    window.Prequel.data = {};
    window.Prequel.flat = [];
    window.Prequel.env = @json($env);
    window.Prequel.i18n = @json($lang);

  </script>
  <script src="{{ asset('/vendor/sequel/app.js') }}"></script>
</body>

</html>