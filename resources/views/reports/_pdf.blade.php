<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Report')</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; margin: 20px; color: #222; }
        h1 { font-size: 18pt; margin-bottom: 4px; }
        h2 { font-size: 14pt; margin-top: 18pt; margin-bottom: 6px; }
        .meta { color: #666; font-size: 10pt; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #999; padding: 5px 7px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
        .num { text-align: right; }
        .pass { color: #198754; font-weight: bold; }
        .fail { color: #dc3545; font-weight: bold; }
        .badge-pass { background: #198754; color: white; padding: 2px 8px; border-radius: 4px; }
        .badge-fail { background: #dc3545; color: white; padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
