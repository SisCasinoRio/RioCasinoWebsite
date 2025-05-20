<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../Loginadmin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px;
        }
        .sidebar a:hover, .sidebar .active {
            background-color: #495057;
        }
        .content-frame {
            width: 100%;
            height: calc(100vh - 56px); /* ajusta por navbar */
            border: none;
        }

        .dropdown-toggle {
    padding: 15px;
    display: block;
    color: white;
    text-decoration: none;
}
.dropdown-menu .dropdown-item:hover {
    background-color: #6c757d;
}

    </style>
</head>
<body>

<!-- Barra superior -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
        <a href="../../logout.php" class="btn btn-outline-light">Cerrar sesión</a>
    </div>
</nav>

<!-- Contenedor principal -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-flex flex-column p-0">
            <a href="#" class="active" onclick="cargarContenido('general.php')">General</a>
            <div class="dropdown">
    <a class="dropdown-toggle" href="#" id="dropdownInventario" data-bs-toggle="dropdown" aria-expanded="false">
        Inventario
    </a>
    <ul class="dropdown-menu bg-dark border-0" aria-labelledby="dropdownInventario">
        <li><a class="dropdown-item text-white" href="#" onclick="cargarContenido('Inventario/Inventario.php')">Ver Inventario</a></li>
        <li><a class="dropdown-item text-white" href="#" onclick="cargarContenido('Inventario/Categorias.php')">Categorías</a></li>
    </ul>
</div>

            <a href="#" onclick="cargarContenido('SistemaTickets/SistemaTickets.php')">Sistema de Tickets</a>
            <a href="#" onclick="cargarContenido('Usuarios/Usuarios.php')">Usuarios</a>
        </div>

        <!-- Área de contenido -->
        <div class="col-md-10 p-0">
            <iframe src="general.php" name="contenido" class="content-frame" id="iframeContenido"></iframe>
        </div>
    </div>
</div>

<script>
    function cargarContenido(url) {
        document.getElementById('iframeContenido').src = url;
        let links = document.querySelectorAll('.sidebar a');
        links.forEach(link => link.classList.remove('active'));
        event.target.classList.add('active');
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
