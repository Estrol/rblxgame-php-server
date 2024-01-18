<?php
$redirect = "https://www.roblox.com";

header("location: $redirect");

?>

<!DOCTYPE html>
<html>

<head>
    <title>Redirecting...</title>
    <meta http-equiv="refresh" content="0; URL=<?php echo $redirect; ?>">
</head>

<body>
    <p>If you are not redirected automatically, follow this <a href='<?php echo $redirect; ?>'>link</a>.</p>

    <script>
        window.location.href = "<?php echo $redirect; ?>"
    </script>
</body>

</html>