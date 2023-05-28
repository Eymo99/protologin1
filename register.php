<?php

require_once "config.php";

$username = $password = $confirm_password = $admin_code = "";
$username_err = $password_err = $confirm_password_err = $admin_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["username"]))){
        $username_err = "Por favor ingrese un usuario.";
    } else{
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "Este usuario ya fue tomado.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Al parecer algo salió mal.";
            }
        }
        mysqli_stmt_close($stmt);
    }

    if(empty(trim($_POST["password"]))){
        $password_err = "Por favor ingresa una contraseña.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "La contraseña al menos debe tener 6 caracteres.";
    } else{
        $password = trim($_POST["password"]);
    }

    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Confirma tu contraseña.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "No coincide la contraseña.";
        }
    }

    if(isset($_POST["role"]) && $_POST["role"] === "admin"){
        if(empty(trim($_POST["admin_code"]))){
            $admin_err = "Por favor ingresa el código de administrador.";
        } else{
            $admin_code = trim($_POST["admin_code"]);
            // Verificar el código de administrador aquí
            if($admin_code !== "admin123"){
                $admin_err = "Código de administrador incorrecto.";
            }
        }
    }

    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($admin_err)){
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password, $param_role);
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_role = ($_POST["role"] === "admin") ? "admin" : "user"; // Assign role based on the selected option
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
            } else{
                echo "Algo salió mal, por favor inténtalo de nuevo.";
            }
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="styles.css" type="text/css">

    <style>
        .hidden {
            display: none;
        }
    </style>
<body>
<div class="wrapper">
    <h2>Registro</h2>
    <p>Por favor complete este formulario para crear una cuenta.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
            <label>Usuario</label>
            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
            <span class="help-block"><?php echo $username_err; ?></span>
        </div>
        <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
            <label>Contraseña</label>
            <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
            <span class="help-block"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
            <label>Confirmar Contraseña</label>
            <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
            <span class="help-block"><?php echo $confirm_password_err; ?></span>
        </div>
        <div class="form-group">
            <label>Rol:</label>
            <select name="role" class="form-control" id="role-select">
                <option value="user">Usuario</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <div class="form-group <?php echo ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["role"]) && $_POST["role"] === "admin" && !empty($admin_err)) ? 'has-error' : 'hidden'; ?>" id="admin-code-group">
            <label>Código de Administrador:</label>
            <input type="password" name="admin_code" class="form-control">
            <span class="help-block"><?php echo $admin_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Ingresar">
            <input type="reset" class="btn btn-default" value="Borrar">
        </div>
        <p>¿Ya tienes una cuenta? <a href="login.php">Ingresa aquí</a>.</p>
    </form>
</div>

<script>
    var roleSelect = document.getElementById('role-select');
    var adminCodeGroup = document.getElementById('admin-code-group');
    function toggleAdminCodeGroup() {
        if (roleSelect.value === 'admin') {
            adminCodeGroup.classList.remove('hidden');
        } else {
            adminCodeGroup.classList.add('hidden');
        }
    }
    roleSelect.addEventListener('change', toggleAdminCodeGroup);
    toggleAdminCodeGroup();
</script>
</body>
</html>


