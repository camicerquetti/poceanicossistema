<?php
ob_start();
// Incluir la clase Cliente y la conexión
include('config.php');
include('clase/Clienteventas.php'); // Asegúrate de que la clase Cliente esté disponible

// Crear una instancia de la clase Cliente
$cliente = new Cliente($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';
$estado_filtro = isset($_POST['estado']) ? $_POST['estado'] : '%%';

// Obtener el número de registros por página
$registros_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $registros_por_pagina;

// Obtener los clientes con saldos de deuda filtrados y paginación
$clientes = $cliente->obtenerClientesConDeudas($filtro, $estado_filtro, $registros_por_pagina, $offset);

// Obtener el total de clientes para la paginación
$total_clientes = $cliente->contarClientesConDeudas($filtro, $estado_filtro);
$total_paginas = ceil($total_clientes / $registros_por_pagina);
?>

<style>
    .pagination {
        width: 100%;
        justify-content: center; /* Centra la paginación */
    }
    .container.mt-4 {
        width: 1200px;  
        margin-left: 220px;
        padding: 70px;
    }
    .estado-vencido {
        color: red;
    }
    .estado-pendiente {
        color: yellow;
    }
</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deudas de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Deudas de Clientes</h2>
        
        <a id="exportExcelBtn" class="btn btn-success mb-3" href="exportar_deudas_excel.php">Exportar a Excel</a>

        <!-- Formulario de filtro -->
        <form method="POST">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar por cliente" value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
            
            <!-- Filtro de estado -->
            <select name="estado" class="form-control mt-2">
                <option value="%%" <?php echo $estado_filtro == '%%' ? 'selected' : ''; ?>>Todos los estados</option>
                <option value="vencido" <?php echo $estado_filtro == 'vencido' ? 'selected' : ''; ?>>Vencidos</option>
                <option value="pendiente" <?php echo $estado_filtro == 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
            </select>

            <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
        </form>

        <!-- Tabla de deudas -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Saldo de Deuda</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $clientes->fetch_assoc()): ?>
                    <tr>
                        <!-- Mostrar el nombre completo del cliente -->
                        <td><?php echo $row['nombre'] . ' ' . $row['razon_social']; ?></td>
                        <!-- Mostrar el saldo de deuda -->
                        <td><?php echo '$' . number_format($row['saldo_deuda'] ?? 0, 2); ?></td>
                        <!-- Mostrar el estado con color -->
                        <td class="<?php echo 'estado-' . ($row['estado'] ? strtolower($row['estado']) : 'pendiente'); ?>">
                            <?php echo ucfirst($row['estado'] ?: 'Pendiente'); ?>
                        </td>
                        <td>
                            <!-- Enlace para ver más detalles o realizar pagos -->
                            <a href="detalles_cliente.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Ver Detalles</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <nav aria-label="Paginación">
            <ul class="pagination mt-3">
                <li class="page-item <?php echo $paginacion <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $paginacion - 1; ?>">Anterior</a>
                </li>

                <?php
                // Mostrar las páginas
                $rango = 2;
                $inicio = max(1, $paginacion - $rango);
                $fin = min($total_paginas, $paginacion + $rango);
                for ($i = $inicio; $i <= $fin; $i++) :
                ?>
                    <li class="page-item <?php echo $i == $paginacion ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $paginacion >= $total_paginas ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $paginacion + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
