<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'SuperAdmin') {
    header("Location: ../../login.php");
    exit();
}

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "rcdb";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

function limpiar($dato) {
    return htmlspecialchars(trim($dato));
}

$mensaje = "";

// Manejar eliminar usuario
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $idEliminar = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE Usuario_id = ?");
    $stmt->bind_param("i", $idEliminar);
    if ($stmt->execute()) {
        $mensaje = "Usuario eliminado correctamente.";
    } else {
        $mensaje = "Error al eliminar usuario.";
    }
    $stmt->close();
}

// Manejar actualizar usuario desde formulario modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    $id_editar = intval($_POST['Usuario_id']);
    $usuario = limpiar($_POST['usuario']);
    $correo = limpiar($_POST['correo']);
    $rol = limpiar($_POST['rol']);

    $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, correo = ?, rol = ? WHERE Usuario_id = ?");
    $stmt->bind_param("sssi", $usuario, $correo, $rol, $id_editar);

    if ($stmt->execute()) {
        $mensaje = "Usuario actualizado correctamente.";
        header("Location: Usuarios.php?mensaje=" . urlencode($mensaje));
        exit();
    } else {
        $mensaje = "Error al actualizar usuario.";
    }
    $stmt->close();
}

// Obtener todos los usuarios para mostrar tabla y pasar datos al JS
$resultado = $conn->query("SELECT Usuario_id, usuario, correo, rol FROM usuarios ORDER BY Usuario_id DESC");
$usuarios = [];
if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Usuarios Registrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">

    <h2 class="mb-4">Usuarios Registrados</h2>

    <?php if (!empty($_GET['mensaje'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['mensaje']) ?></div>
    <?php elseif ($mensaje): ?>
        <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($usuarios) > 0): ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td class="text-center"><?= htmlspecialchars($usuario['Usuario_id']) ?></td>
                            <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                            <td><?= htmlspecialchars($usuario['correo']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($usuario['rol']) ?></td>
                            <td class="text-center" style="white-space: nowrap;">
                                <button class="btn btn-info btn-sm me-1 btn-ver" 
                                    data-bs-toggle="modal" data-bs-target="#modalVer"
                                    data-id="<?= htmlspecialchars($usuario['Usuario_id']) ?>"
                                    data-usuario="<?= htmlspecialchars($usuario['usuario']) ?>"
                                    data-correo="<?= htmlspecialchars($usuario['correo']) ?>"
                                    data-rol="<?= htmlspecialchars($usuario['rol']) ?>"
                                >Ver</button>

                                <button class="btn btn-warning btn-sm me-1 btn-editar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditar"
                                    data-id="<?= htmlspecialchars($usuario['Usuario_id']) ?>"
                                    data-usuario="<?= htmlspecialchars($usuario['usuario']) ?>"
                                    data-correo="<?= htmlspecialchars($usuario['correo']) ?>"
                                    data-rol="<?= htmlspecialchars($usuario['rol']) ?>"
                                >Editar</button>

                                <a href="Usuarios.php?accion=eliminar&id=<?= urlencode($usuario['Usuario_id']) ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?');"
                                >Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ver -->
<div class="modal fade" id="modalVer" tabindex="-1" aria-labelledby="modalVerLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalVerLabel">Detalle del Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p><strong>ID:</strong> <span id="ver-id"></span></p>
        <p><strong>Usuario:</strong> <span id="ver-usuario"></span></p>
        <p><strong>Correo:</strong> <span id="ver-correo"></span></p>
        <p><strong>Rol:</strong> <span id="ver-rol"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="Usuarios.php" class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="modalEditarLabel">Editar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="Usuario_id" id="editar-id" />
        <div class="mb-3">
            <label for="editar-usuario" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="editar-usuario" name="usuario" required />
        </div>
        <div class="mb-3">
            <label for="editar-correo" class="form-label">Correo</label>
            <input type="email" class="form-control" id="editar-correo" name="correo" required />
        </div>
        <div class="mb-3">
            <label for="editar-rol" class="form-label">Rol</label>
            <select class="form-select" id="editar-rol" name="rol" required>
                <option value="SuperAdmin">SuperAdmin</option>
                <option value="Admin">Admin</option>
                <option value="Usuario">Usuario</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="editar_usuario" class="btn btn-primary">Guardar Cambios</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Rellenar modal Ver con datos del usuario
document.querySelectorAll('.btn-ver').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('ver-id').textContent = btn.getAttribute('data-id');
        document.getElementById('ver-usuario').textContent = btn.getAttribute('data-usuario');
        document.getElementById('ver-correo').textContent = btn.getAttribute('data-correo');
        document.getElementById('ver-rol').textContent = btn.getAttribute('data-rol');
    });
});

// Rellenar modal Editar con datos del usuario
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('editar-id').value = btn.getAttribute('data-id');
        document.getElementById('editar-usuario').value = btn.getAttribute('data-usuario');
        document.getElementById('editar-correo').value = btn.getAttribute('data-correo');
        document.getElementById('editar-rol').value = btn.getAttribute('data-rol');
    });
});
</script>

</body>
</html>
