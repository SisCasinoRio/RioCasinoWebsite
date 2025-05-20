<?php
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST['usuario'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Encriptar la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Simulación de guardado (puedes cambiar esto a una base de datos real)
    // Aquí simplemente se crea un archivo por usuario (solo para prueba)
    $archivo = fopen("usuarios/$usuario.txt", "w");
    fwrite($archivo, "Usuario: $usuario\nCorreo: $correo\nContraseña: $passwordHash");
    fclose($archivo);

    $mensaje = "✅ Usuario creado con éxito.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f0f2f5;
    }
    .card {
      max-width: 400px;
      margin: auto;
      margin-top: 80px;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .btn-custom {
      background-color: #ff6600;
      color: white;
    }
  </style>
</head>
<body>

<div class="card">
  <h4 class="text-center mb-4">Registro de Usuario</h4>

  <?php if (!empty($mensaje)): ?>
    <div class="alert alert-success text-center"><?= $mensaje ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label for="usuario" class="form-label">Usuario</label>
      <input type="text" class="form-control" id="usuario" name="usuario" required>
    </div>
    <div class="mb-3">
      <label for="correo" class="form-label">Correo electrónico</label>
      <input type="email" class="form-control" id="correo" name="correo" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-custom w-100">Registrar</button>
  </form>
</div>

</body>
</html>
