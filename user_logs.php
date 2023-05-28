<?php
require_once "config.php";
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$link) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

$sql = "SELECT * FROM users";
$result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registros de Usuarios</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            color: #333;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<h2>Registros de Usuarios</h2>
<a href="login.php" class="btn">Volver al inicio de sesión</a>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Nombre de Usuario</th>
        <th>Inicio de Sesion</th>
        <th>Direccion IP</th>
        <th>Sistema Operativo</th>
        <th>Navegador</th>
        <th>Tiempo de bloqueo</th>
        <th>Rol</th>

    </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['last_login']; ?></td>
            <td><?php echo $row['ip_address']; ?></td>
            <td><?php echo $row['operating_system']; ?></td>
            <td><?php echo $row['browser']; ?></td>
            <td><?php echo $row['blocked_until']; ?></td>
            <td><?php echo $row['role']; ?></td>

        </tr>
    <?php } ?>
    </tbody>
</table>

<?php mysqli_close($link); ?>
</body>
</html>
