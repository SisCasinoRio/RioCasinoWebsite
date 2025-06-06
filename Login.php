<?php
session_start();
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];

    $stmt = $conn->prepare("SELECT Usuario_id, usuario, contrasena, rol FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($contrasena, $user["contrasena"])) {
            $_SESSION["usuario_id"] = $user["Usuario_id"];
            $_SESSION["usuario"] = $user["usuario"];
            $_SESSION["rol"] = $user["rol"];

            if ($user["rol"] === "SuperAdmin") {
                header("Location:Dashboard/Dashboard/Dashboard_sadmin.php");
            } elseif ($user["rol"] === "Admin") {
                header("Location:Dashboard/Dashboard_Admin/Dashboard_admin.php");
            } else {
                header("Location: Dashboard/Dashboard_Usuario/Dashboard_usuario.php");
            }
            exit;
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .login-card {
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 40px 30px;
        }
        .logo {
            width: 120px;
            margin-bottom: 20px;
        }
        .btn-verde {
            background-color: #33A63B;
            color: white;
        }
        .btn-verde:hover {
            background-color: #2b8d33;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="login-card w-100" style="max-width: 400px;">
        <div class="text-center">
            <img src="images/Logo.png" alt="Logo" class="logo">
            <h4 class="mb-3">Iniciar Sesión</h4>
        </div>
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <form method="POST" action="Login.php">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" name="correo" id="correo" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="contrasena" id="contrasena" required>
            </div>
            <button type="submit" class="btn btn-verde w-100">Entrar</button>
        </form>
        <div class="text-center mt-3">
            ¿No tienes cuenta? <a href="Register.php" style="color:#33A63B;">Regístrate</a>
        </div>
    </div>
</div>

</body>
</html>
