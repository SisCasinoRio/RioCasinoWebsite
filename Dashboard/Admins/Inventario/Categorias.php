<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "rcdb0");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = "";

// Crear nueva categoría
if (isset($_POST["crear_categoria"])) {
    $nombre = trim($_POST["nombre_categoria"]);
    if (!empty($nombre)) {
        // Validar si ya existe
        $check = $conn->prepare("SELECT id FROM categorias WHERE nombre = ?");
        $check->bind_param("s", $nombre);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje = "<div class='alert alert-warning'>La categoría ya existe.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            if ($stmt->execute()) {
                $mensaje = "<div class='alert alert-success'>Categoría creada correctamente.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al crear la categoría.</div>";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>El nombre no puede estar vacío.</div>";
    }
}

// Editar categoría
if (isset($_POST["editar_categoria"])) {
    $idEditar = intval($_POST["editar_id"]);
    $nuevoNombre = trim($_POST["nuevo_nombre"]);

    if (!empty($nuevoNombre)) {
        // Validar duplicado
        $check = $conn->prepare("SELECT id FROM categorias WHERE nombre = ? AND id != ?");
        $check->bind_param("si", $nuevoNombre, $idEditar);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje = "<div class='alert alert-warning'>Otra categoría ya tiene ese nombre.</div>";
        } else {
            $stmt = $conn->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
            $stmt->bind_param("si", $nuevoNombre, $idEditar);
            if ($stmt->execute()) {
                $mensaje = "<div class='alert alert-success'>Categoría actualizada.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al actualizar.</div>";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>El nuevo nombre no puede estar vacío.</div>";
    }
}

// Eliminar categoría
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM categorias WHERE id = $id");
    header("Location: Categorias.php");
    exit();
}

// Obtener todas las categorías
$result = $conn->query("SELECT * FROM categorias ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3 class="mb-3">Categorías de Inventario</h3>
    <?= $mensaje ?>

    <!-- Formulario nueva categoría -->
    <form method="POST" class="row g-3 mb-4">
        <input type="hidden" name="crear_categoria" value="1">
        <div class="col-md-6">
            <input type="text" name="nombre_categoria" class="form-control" placeholder="Nueva categoría" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Crear</button>
        </div>
    </form>

    <!-- Tabla -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha de creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php if (isset($_GET['editar']) && $_GET['editar'] == $row['id']): ?>
                        <!-- Fila de edición -->
                        <tr>
                            <form method="POST">
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <input type="text" name="nuevo_nombre" class="form-control" value="<?= htmlspecialchars($row['nombre']) ?>" required>
                                    <input type="hidden" name="editar_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="editar_categoria" value="1">
                                </td>
                                <td><?= $row['fecha_creacion'] ?></td>
                                <td>
                                    <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                                    <a href="Categorias.php" class="btn btn-secondary btn-sm">Cancelar</a>
                                </td>
                            </form>
                        </tr>
                    <?php else: ?>
                        <!-- Fila normal -->
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= $row['fecha_creacion'] ?></td>
                            <td>
                                <a href="Categorias.php?editar=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="Categorias.php?eliminar=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas eliminar esta categoría?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
