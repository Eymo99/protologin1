<?php
session_start();

if (!isset($_SESSION["blocked_until"])) {
    header("Location: login.php");
    exit;
}

$blocked_until = $_SESSION["blocked_until"];
$current_datetime = date('Y-m-d H:i:s');
$seconds_remaining = strtotime($blocked_until) - strtotime($current_datetime);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cuenta bloqueada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }

        h1 {
            color: red;
        }

        #countdown {
            font-size: 36px;
            font-weight: bold;
            margin-top: 50px;
        }
    </style>
 <script>
        function countdown() {
            var countDownDate = new Date(new Date().getTime() + (5 * 60 * 1000)).getTime();

            var x = setInterval(function() {
                var now = new Date().getTime();
                var distance = countDownDate - now;

                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("countdown").innerHTML = minutes + "m " + seconds + "s ";

                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("countdown").innerHTML = "¡Desbloqueado!";
                    window.location.href = "login.php";
                }
            }, 1000);
        }

        window.onload = function() {
            countdown();
        };
    </script>
</head>
<body>
    <h1>Cuenta bloqueada</h1>
    <p>Tu cuenta ha sido bloqueada debido a demasiados intentos fallidos de inicio de sesión.</p>
    <p>Por favor, espera el siguiente tiempo para desbloquear tu cuenta:</p>
    <div id="countdown"></div>
</body>
</html>
