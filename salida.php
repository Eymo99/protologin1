<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <script>
        setTimeout(function() {
            window.location.href = "logout.php";
        }, 6000);
    </script>
</head>
<body>
<div class="page-header">
    <h1>Hola, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Gracias por su visita.</h1>
</div>
</body>
</html>
