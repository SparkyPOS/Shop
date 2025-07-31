<!DOCTYPE html>
<html lang="en">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>{{getAppName()}}</title>
</head>
<body>
    @if($token)
        <form method="post" action="{{ url('sso-login') }}" id="ssoForm">
            {{ csrf_field() }}
            <input type='hidden' name="token" value="{{ $token }}" />
        </form>
    @endif
<script>
    const token = '{{ $token ?? null }}';
    const redirectTo = '{{ $redirectTo ?? null }}';
    document.addEventListener('DOMContentLoaded', function() {
        if (token) {
            document.getElementById('ssoForm').submit();
        } else {
            document.location = redirectTo;
        }
    })
</script>
</body>