<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
</head>
<body>
<script>
    (function() {
        @if($data)
        let data = @json($data);

        try {
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        } catch (e) {
            window.opener.postMessage(JSON.stringify(data), '*');
        }
        @endif

        setTimeout(function () {
            window.close();
        }, 100)
    })()
</script>
</body>
</html>
