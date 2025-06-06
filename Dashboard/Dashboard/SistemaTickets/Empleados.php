<?php
include("../../../conexion.php");
header('Content-Type: text/html; charset=utf-8');

$msg = "";

// Obtener cargos y departamentos
$cargos = $conn->query("SELECT cargo_id AS id, nombre FROM cargos ORDER BY nombre ASC");
$departamentos = $conn->query("SELECT departamento_id AS id, nombre FROM departamentos ORDER BY nombre ASC");

// Función para limpiar texto
function clean($str) {
    return htmlspecialchars($str, ENT_QUOTES);
}

// Eliminar empleado
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM empleados WHERE empleado_id = ?");
    $stmt->bind_param("i", $id);
    $msg = $stmt->execute() ? "Empleado eliminado correctamente." : "Error al eliminar empleado: " . $conn->error;
    $stmt->close();
}

// Agregar o actualizar empleado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $cargo_id = intval($_POST["cargo"]);
    $departamento_id = intval($_POST["departamento_id"]);
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;

    if ($nombre !== "" && $cargo_id > 0 && $departamento_id > 0) {
        if ($edit_id > 0) {
            $stmt = $conn->prepare("UPDATE empleados SET nombre = ?, cargoID = ?, departamentoID = ? WHERE empleado_id = ?");
            $stmt->bind_param("siii", $nombre, $cargo_id, $departamento_id, $edit_id);
            $msg = $stmt->execute() ? "Empleado actualizado correctamente." : "Error al actualizar empleado: " . $conn->error;
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO empleados (nombre, cargoID, departamentoID) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $nombre, $cargo_id, $departamento_id);
            $msg = $stmt->execute() ? "Empleado agregado correctamente." : "Error al agregar empleado: " . $conn->error;
            $stmt->close();
        }
    } else {
        $msg = "Por favor, complete todos los campos correctamente.";
    }
}

// Consultar empleados
$sql = "SELECT e.empleado_id, e.nombre, c.nombre AS cargo, d.nombre AS departamento
        FROM empleados e
        LEFT JOIN cargos c ON e.cargoID = c.cargo_id
        LEFT JOIN departamentos d ON e.departamentoID = d.departamento_id
        ORDER BY e.empleado_id ASC";
$empleados = $conn->query($sql);

// AJAX: obtener un empleado
if (isset($_GET['ajax']) && $_GET['ajax'] === 'getEmpleado' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT e.empleado_id AS id, e.nombre, e.cargoID AS cargo_id, e.departamentoID AS departamento_id, c.nombre AS cargo, d.nombre AS departamento 
                            FROM empleados e 
                            LEFT JOIN cargos c ON e.cargoID = c.cargo_id 
                            LEFT JOIN departamentos d ON e.departamentoID = d.departamento_id 
                            WHERE e.empleado_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Gestión de Empleados</h2>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= clean($msg) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#modalEditar" onclick="abrirAgregar()">Agregar Empleado</button>

    <h3>Lista de Empleados</h3>
    <table class="table table-striped table-bordered bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($empleados->num_rows === 0): ?>
                <tr><td colspan="5" class="text-center">No hay empleados registrados.</td></tr>
            <?php else: ?>
                <?php while ($emp = $empleados->fetch_assoc()): ?>
                    <tr>
                        <td><?= $emp['empleado_id'] ?></td>
                        <td><?= clean($emp['nombre']) ?></td>
                        <td><?= clean($emp['cargo']) ?></td>
                        <td><?= clean($emp['departamento']) ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="verEmpleado(<?= $emp['empleado_id'] ?>)">Ver</button>
                            <button class="btn btn-warning btn-sm" onclick="editarEmpleado(<?= $emp['empleado_id'] ?>)">Editar</button>
                            <a href="?action=delete&id=<?= $emp['empleado_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este empleado?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ver -->
<div class="modal fade" id="modalVer" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalles del Empleado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p><strong>ID:</strong> <span id="ver-id"></span></p>
        <p><strong>Nombre:</strong> <span id="ver-nombre"></span></p>
        <p><strong>Cargo:</strong> <span id="ver-cargo"></span></p>
        <p><strong>Departamento:</strong> <span id="ver-departamento"></span></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar/Agregar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" id="formEmpleado">
      <div class="modal-header">
        <h5 class="modal-title">Empleado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="edit_id" id="edit_id" value="0">
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre</label>
          <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>
        <div class="mb-3">
          <label for="cargo" class="form-label">Cargo</label>
          <select name="cargo" id="cargo" class="form-select" required>
            <option value="">-- Seleccione un cargo --</option>
            <?php $cargos->data_seek(0); while ($car = $cargos->fetch_assoc()): ?>
                <option value="<?= $car['id'] ?>"><?= clean($car['nombre']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="departamento_id" class="form-label">Departamento</label>
          <select name="departamento_id" id="departamento_id" class="form-select" required>
            <option value="">-- Seleccione un departamento --</option>
            <?php $departamentos->data_seek(0); while ($dep = $departamentos->fetch_assoc()): ?>
                <option value="<?= $dep['id'] ?>"><?= clean($dep['nombre']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Guardar</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modalVer = new bootstrap.Modal(document.getElementById('modalVer'));
const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));

function verEmpleado(id) {
    fetch(`?ajax=getEmpleado&id=${id}`)
    .then(res => res.json())
    .then(data => {
        document.getElementById('ver-id').textContent = data.id || '';
        document.getElementById('ver-nombre').textContent = data.nombre || '';
        document.getElementById('ver-cargo').textContent = data.cargo || '';
        document.getElementById('ver-departamento').textContent = data.departamento || '';
        modalVer.show();
    });
}

function editarEmpleado(id) {
    fetch(`?ajax=getEmpleado&id=${id}`)
    .then(res => res.json())
    .then(data => {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('nombre').value = data.nombre;
        document.getElementById('cargo').value = data.cargo_id;
        document.getElementById('departamento_id').value = data.departamento_id;
        modalEditar.show();
    });
}

function abrirAgregar() {
    document.getElementById('formEmpleado').reset();
    document.getElementById('edit_id').value = "0";
}
</script>
</body>
</html>
