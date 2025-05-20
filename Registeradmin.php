<?php
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Encriptar contraseña
    $contrasenaHash = password_hash($contrasena, PASSWORD_BCRYPT);

    // Conexión a la base de datos
    $conn = new mysqli("localhost", "root", "", "rcdb0");

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Insertar datos
    $sql = "INSERT INTO admin (usuario, correo, contrasena) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $usuario, $correo, $contrasenaHash);

    if ($stmt->execute()) {
        $mensaje = "Usuario creado con éxito. Redirigiendo...";
        header("refresh:2;url=Loginadmin.php");
    } else {
        $mensaje = "Error al crear el usuario: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de SuperAdmin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card p-4 shadow-lg w-100" style="max-width: 400px;">
        <h4 class="text-center mb-3">Registrar SuperAdmin</h4>
        <?php if ($mensaje): ?>
            <div class="alert alert-info text-center"><?= $mensaje ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" name="correo" id="correo" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="contrasena" id="contrasena" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrar</button>
        </form>
    </div>
</div>

</body>
</html>
