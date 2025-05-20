<?php
session_start();
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Conexión a la base de datos
    $conn = new mysqli("localhost", "root", "", "rcdb0");

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Buscar usuario
    $sql = "SELECT * FROM admin WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $row = $resultado->fetch_assoc();
        if (password_verify($contrasena, $row['contrasena'])) {
            $_SESSION['usuario'] = $row['usuario'];
            header("Location: Dashboard/Admins/Dashboard_admin.php");
            exit();
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "Usuario no encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login SuperAdmin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-secondary text-white">

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card p-4 shadow-lg w-100" style="max-width: 400px;">
        <h4 class="text-center mb-3">Iniciar Sesión</h4>
        <?php if ($mensaje): ?>
            <div class="alert alert-danger text-center"><?= $mensaje ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="contrasena" id="contrasena" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Ingresar</button>
        </form>
    </div>
</div>

</body>
</html>
