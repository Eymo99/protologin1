<?php

session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: salida.php");
    exit;
}
require_once "config.php";

$username = $password = "";
$username_err = $password_err = "";
$recaptcha_err = "";


function getOperatingSystem($user_agent)
{
    $operating_systems = array(
        '/windows nt 10.0/i'    => 'Windows 11',
        '/windows nt 10/i'      => 'Windows 10',
        '/windows nt 6.3/i'     => 'Windows 8.1',
        '/windows nt 6.2/i'     => 'Windows 8',
        '/windows nt 6.1/i'     => 'Windows 7',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i'        => 'Mac OS 9',
        '/linux/i'              => 'Linux',
        '/ubuntu/i'             => 'Ubuntu',
        '/iphone/i'             => 'iPhone',
        '/ipod/i'               => 'iPod',
        '/ipad/i'               => 'iPad',
        '/android/i'            => 'Android',
        '/webos/i'              => 'Mobile',
    );

    foreach ($operating_systems as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            return $value;
        }
    }

    return 'Unknown';
}
function getBrowser($user_agent) {
    $browser = "Unknown";

    if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser = 'Mozilla Firefox';
    } elseif (preg_match('/Chrome/i', $user_agent)) {
        $browser = 'Google Chrome';
    } elseif (preg_match('/Safari/i', $user_agent)) {
        $browser = 'Apple Safari';
    } elseif (preg_match('/Opera/i', $user_agent)) {
        $browser = 'Opera';
    }

    return $browser;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor ingrese su usuario.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password, blocked_until, login_attempts, role FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $blocked_until, $login_attempts, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        if ($blocked_until && $blocked_until > date('Y-m-d H:i:s')) {
                            header("Location: bloqueado.php");
                            exit;
                        }
                        if (password_verify($password, $hashed_password)) {
                            $recaptcha_secret = "6Lca-CkmAAAAACZ91Vr5CqXMF1F024wrxvLuKedX";
                            $recaptcha_response = $_POST['g-recaptcha-response'];
                            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
                            $recaptcha_data = array(
                                'secret' => $recaptcha_secret,
                                'response' => $recaptcha_response
                            );
                            $recaptcha_options = array(
                                'http' => array(
                                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                    'method' => 'POST',
                                    'content' => http_build_query($recaptcha_data)
                                )
                            );
                            $recaptcha_context = stream_context_create($recaptcha_options);
                            $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
                            $recaptcha_json = json_decode($recaptcha_result);
                            if ($recaptcha_json->success) {
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["role"] = $role;
                                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                                $operating_system = getOperatingSystem($user_agent);
                                $current_datetime = date('Y-m-d H:i:s');

                                $sql = "UPDATE users SET last_login = ?, operating_system = ? WHERE id = ?";
                                if ($stmt = mysqli_prepare($link, $sql)) {
                                    mysqli_stmt_bind_param($stmt, "ssi", $current_datetime, $operating_system, $id);
                                    mysqli_stmt_execute($stmt);
                                    mysqli_stmt_close($stmt);
                                }
                                $sql = "UPDATE users SET last_login = NOW(), ip_address = ?, browser = ? WHERE username = ?";
                                if ($stmt = mysqli_prepare($link, $sql)) {
                                    mysqli_stmt_bind_param($stmt, "sss", $ip_address, $browser, $username);
                                    $ip_address = $_SERVER['REMOTE_ADDR'];
                                    $browser = getBrowser($_SERVER['HTTP_USER_AGENT']);
                                    mysqli_stmt_execute($stmt);
                                    mysqli_stmt_close($stmt);
                                }

                                if ($role === 'admin') {
                                    header("location: admin.php");
                                    exit;
                                } elseif ($role === 'user') {

                                    header("location: user.php");
                                    exit;
                                }
                            } else {
                                $recaptcha_err = "Por favor, completa la validación de reCAPTCHA.";
                            }
                        } else {

                            $password_err = "La contraseña que has ingresado no es válida.";
                            $login_attempts++;
                            if ($login_attempts >= 3) {
                                $blocked_until = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                                mysqli_stmt_close($stmt);
                                $sql = "UPDATE users SET blocked_until = ?, login_attempts = 0 WHERE username = ?";
                                if ($stmt = mysqli_prepare($link, $sql)) {
                                    mysqli_stmt_bind_param($stmt, "ss", $blocked_until, $username);
                                    mysqli_stmt_execute($stmt);
                                    mysqli_stmt_close($stmt);
                                }
                                $_SESSION["blocked_until"] = $blocked_until;
                            } else {
                                mysqli_stmt_close($stmt);
                                $sql = "UPDATE users SET login_attempts = ? WHERE username = ?";
                                if ($stmt = mysqli_prepare($link, $sql)) {
                                    mysqli_stmt_bind_param($stmt, "is", $login_attempts, $username);
                                    mysqli_stmt_execute($stmt);
                                    mysqli_stmt_close($stmt);
                                    $_SESSION["blocked_until"] = $blocked_until;
                                }
                            }
                        }
                    }
                } else {
                    $username_err = "No existe cuenta registrada con ese nombre de usuario.";
                }
            } else {
                echo "Algo salió mal, por favor vuelve a intentarlo.";
            }
        }
    }
    mysqli_close($link);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="wrapper">
        <div class="login-image">
            <img src="images/computer.jpg" alt="Imagen de inicio de sesión">
        </div>
        <h2>Inicio de Sesion</h2>
        <p>Por favor, ingrese sus datos</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Usuario</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <div class="g-recaptcha" data-sitekey="6Lca-CkmAAAAAEjhZKEIaczPqk4rRw5qqEyevjgf"></div> <!-- Reemplazar con tu propia clave del sitio -->
                <span class="recaptcha-error"><?php echo $recaptcha_err; ?></span> <!-- Nuevo: Mensaje de error de reCAPTCHA -->
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Ingresar">
            </div>
            <p>¿No tienes una cuenta? <a href="register.php">Regístrate ahora</a>.</p>
        </form>
    </div>    
</body>
</html>
