<?php
require_once "conexion.php"; // Incluye la conexión a la base de datos

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];
    $confirmar = $_POST["confirmar_contrasena"];

    if ($contrasena !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        // Verificar si ya existe un SuperAdmin
        $query_superadmin = "SELECT Usuario_id FROM usuarios WHERE rol = 'SuperAdmin' LIMIT 1";
        $result = $conn->query($query_superadmin);

        if ($result && $result->num_rows > 0) {
            // Ya existe un SuperAdmin, asignar rol Usuario
            $rol = "Usuario";
        } else {
            // No existe SuperAdmin, asignar este usuario como SuperAdmin
            $rol = "SuperAdmin";
        }

        // Verifica si el correo ya está registrado
        $stmt = $conn->prepare("SELECT Usuario_id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensaje = "Este correo ya está registrado.";
        } else {
            // Inserta nuevo usuario
            $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, correo, contrasena, rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $usuario, $correo, $hashed_password, $rol);

            if ($stmt->execute()) {
                header("Location: Login.php");
                exit;
            } else {
                $mensaje = "Error al registrar. Intenta más tarde.";
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap 5 -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-card {
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
            color: white;
        }
        .login-link {
            display: block;
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
        .login-link a {
            color: #33A63B;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="register-card w-100" style="max-width: 400px;">
        <div class="text-center">
            <img src="images/Logo.png" alt="Logo" class="logo" />
            <h4 class="mb-3">Registro</h4>
        </div>
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info text-center"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" required />
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" name="correo" id="correo" required />
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input
                  type="password"
                  class="form-control"
                  name="contrasena"
                  id="contrasena"
                  required
                  minlength="6"
                />
            </div>
            <div class="mb-3">
                <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                <input
                  type="password"
                  class="form-control"
                  name="confirmar_contrasena"
                  id="confirmar_contrasena"
                  required
                  minlength="6"
                />
            </div>

            <!-- No hay opción para elegir rol, se asigna automáticamente -->

            <button type="submit" class="btn btn-verde w-100">Registrar</button>
        </form>
        <div class="login-link">
            ¿Ya tienes una cuenta? <a href="Login.php">Iniciar sesión</a>
        </div>
    </div>
</div>
</body>
</html>
