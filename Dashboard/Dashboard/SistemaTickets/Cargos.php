<?php
$mysqli = new mysqli("localhost", "root", "", "rcdb");
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$msg = "";

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"];

    if ($accion === "agregar") {
        $nombre = trim($_POST["nombre"]);
        if (!empty($nombre)) {
            $stmt = $mysqli->prepare("INSERT INTO cargos (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            $msg = $stmt->execute() ? "Cargo agregado correctamente." : "Error al agregar cargo.";
            $stmt->close();
        } else {
            $msg = "El nombre del cargo no puede estar vacío.";
        }
    }

    if ($accion === "editar") {
        $id = intval($_POST["Cargo_id"]);
        $nombre = trim($_POST["nombre"]);
        if (!empty($nombre)) {
            $stmt = $mysqli->prepare("UPDATE cargos SET nombre = ? WHERE Cargo_id = ?");
            $stmt->bind_param("si", $nombre, $id);
            $msg = $stmt->execute() ? "Cargo actualizado correctamente." : "Error al actualizar el cargo.";
            $stmt->close();
        } else {
            $msg = "El nombre del cargo no puede estar vacío.";
        }
    }
}

// Eliminar
if (isset($_GET["accion"], $_GET["Cargo_id"]) && $_GET["accion"] === "eliminar") {
    $id = intval($_GET["Cargo_id"]);
    $stmt = $mysqli->prepare("DELETE FROM cargos WHERE Cargo_id = ?");
    $stmt->bind_param("i", $id);
    $msg = $stmt->execute() ? "Cargo eliminado correctamente." : "Error al eliminar el cargo.";
    $stmt->close();
}

// Obtener todos los cargos
$cargos = $mysqli->query("SELECT * FROM cargos ORDER BY Cargo_id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Cargos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Gestión de Cargos</h2>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar Cargo</button>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($cargo = $cargos->fetch_assoc()): ?>
            <tr>
                <td><?= $cargo['Cargo_id'] ?></td>
                <td><?= htmlspecialchars($cargo['nombre']) ?></td>
                <td>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalVer<?= $cargo['Cargo_id'] ?>">Ver</button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $cargo['Cargo_id'] ?>">Editar</button>
                    <a href="?accion=eliminar&Cargo_id=<?= $cargo['Cargo_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este cargo?')">Eliminar</a>
                </td>
            </tr>

            <!-- Modal Ver -->
            <div class="modal fade" id="modalVer<?= $cargo['Cargo_id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Ver Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>ID:</strong> <?= $cargo['Cargo_id'] ?></p>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($cargo['nombre']) ?></p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Editar -->
            <div class="modal fade" id="modalEditar<?= $cargo['Cargo_id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form method="post" class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Editar Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="Cargo_id" value="<?= $cargo['Cargo_id'] ?>">
                    <div class="mb-3">
                        <label>Nombre del cargo:</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($cargo['nombre']) ?>" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                  </div>
                </form>
              </div>
            </div>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Agregar Cargo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="accion" value="agregar">
        <div class="mb-3">
            <label>Nombre del cargo:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Agregar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
