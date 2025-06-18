<?php
include("../../../conexion.php");

$msg = "";

// Agregar, editar o eliminar departamento
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === "add") {
        $nombre = trim($_POST["nombre"]);
        if (!empty($nombre)) {
            $stmt = $conn->prepare("INSERT INTO departamentos (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            $msg = $stmt->execute() ? "Departamento agregado correctamente." : "Error al agregar: " . $conn->error;
            $stmt->close();
        } else {
            $msg = "El nombre no puede estar vacío.";
        }
    }

    if ($_POST['action'] === "edit") {
        $id = intval($_POST["departamento_id"]);
        $nombre = trim($_POST["nombre"]);
        if ($id > 0 && !empty($nombre)) {
            $stmt = $conn->prepare("UPDATE departamentos SET nombre = ? WHERE departamento_id = ?");
            $stmt->bind_param("si", $nombre, $id);
            $msg = $stmt->execute() ? "Departamento actualizado correctamente." : "Error al actualizar: " . $conn->error;
            $stmt->close();
        } else {
            $msg = "Datos inválidos para actualizar.";
        }
    }

    if ($_POST['action'] === "delete") {
        $id = intval($_POST["departamento_id"]);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM departamentos WHERE departamento_id = ?");
            $stmt->bind_param("i", $id);
            $msg = $stmt->execute() ? "Departamento eliminado correctamente." : "Error al eliminar: " . $conn->error;
            $stmt->close();
        } else {
            $msg = "ID inválido para eliminar.";
        }
    }
}

$result = $conn->query("SELECT * FROM departamentos ORDER BY departamento_id ASC");
$departamentos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departamentos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Departamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
    table td, table th {
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
        vertical-align: middle;
    }

    .table td .btn {
        margin: 1px 0;
        padding: 0.25rem 0.5rem;
        font-size: 0.85rem;
    }

    h2.mb-4 {
        margin-bottom: 1rem !important;
    }

    .btn {
        font-size: 0.9rem;
    }
</style>

</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Gestión de Departamentos</h2>

    <?php if ($msg): ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar Departamento</button>

    <table class="table table-sm table-striped table-bordered bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($departamentos)): ?>
                <tr><td colspan="3" class="text-center">No hay departamentos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($departamentos as $dep): ?>
                <tr>
                    <td><?= $dep['departamento_id'] ?></td>
                    <td><?= htmlspecialchars($dep['nombre']) ?></td>
                    <td>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalVer" 
                            data-id="<?= $dep['departamento_id'] ?>" data-nombre="<?= htmlspecialchars($dep['nombre'], ENT_QUOTES) ?>">Ver</button>

                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar" 
                            data-id="<?= $dep['departamento_id'] ?>" data-nombre="<?= htmlspecialchars($dep['nombre'], ENT_QUOTES) ?>">Editar</button>

                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este departamento?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="departamento_id" value="<?= $dep['departamento_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
            <h5 class="modal-title" id="modalAgregarLabel">Agregar Departamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="nombreAgregar" class="form-label">Nombre del Departamento</label>
                <input type="text" class="form-control" id="nombreAgregar" name="nombre" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success">Agregar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
    </form>
  </div>
</div>

<!-- Modal Ver -->
<div class="modal fade" id="modalVer" tabindex="-1" aria-labelledby="modalVerLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalVerLabel">Ver Departamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <p><strong>ID:</strong> <span id="verId"></span></p>
            <p><strong>Nombre:</strong> <span id="verNombre"></span></p>
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
    <form method="POST" class="modal-content">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="editarId" name="departamento_id" value="">
        <div class="modal-header">
            <h5 class="modal-title" id="modalEditarLabel">Editar Departamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="nombreEditar" class="form-label">Nombre del Departamento</label>
                <input type="text" class="form-control" id="nombreEditar" name="nombre" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Modal Ver
    document.getElementById('modalVer').addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('verId').textContent = button.getAttribute('data-id');
        document.getElementById('verNombre').textContent = button.getAttribute('data-nombre');
    });

    // Modal Editar
    document.getElementById('modalEditar').addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('editarId').value = button.getAttribute('data-id');
        document.getElementById('nombreEditar').value = button.getAttribute('data-nombre');
    });
</script>
</body>
</html>
