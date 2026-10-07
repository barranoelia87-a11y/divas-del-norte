<?php
// ============================================================
// ARCHIVO: listar_reservas.php
// DESCRIPCIÓN: Muestra todas las reservas registradas
// ============================================================

include "conexion.php";

// ── CONSULTA PARA OBTENER RESERVAS ─────────────────────────
$sql = "SELECT 
            r.id AS reserva_id,
            c.nombre AS cliente,
            c.email,
            c.telefono,
            r.fecha,
            r.horario,
            r.estado,
            r.created_at,
            GROUP_CONCAT(s.nombre SEPARATOR ', ') AS servicios,
            SUM(dr.precio_unitario) AS total
        FROM reservas r
        INNER JOIN clientes c ON r.cliente_id = c.id
        LEFT JOIN detalle_reservas dr ON r.id = dr.reserva_id
        LEFT JOIN servicios s ON dr.servicio_id = s.id
        GROUP BY r.id
        ORDER BY r.fecha DESC, r.horario DESC";

$resultado = mysqli_query($conexion, $sql);
$total_reservas = mysqli_num_rows($resultado);
?>

<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Reservas - Sarah Wendy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            background: #1a1a2e;
            color: #f0e6ff;
            padding: 2rem;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #e91e8c;
            margin-bottom: 0.5rem;
        }
        .subtitle { color: #c9b8e8; margin-bottom: 2rem; }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #e91e8c, #c2185b);
            color: #fff;
            padding: 10px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(233, 30, 140, 0.4);
        }
        .btn-secondary {
            background: transparent;
            border: 2px solid rgba(233, 30, 140, 0.4);
            color: #c9b8e8;
        }
        .btn-secondary:hover {
            border-color: #e91e8c;
            color: #e91e8c;
        }
        .estadisticas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .estadistica-card {
            background: #1e1e3a;
            border: 1px solid rgba(233, 30, 140, 0.15);
            border-radius: 12px;
            padding: 1.2rem;
            text-align: center;
        }
        .estadistica-card .numero {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 900;
            color: #e91e8c;
        }
        .estadistica-card .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #c9b8e8;
        }
        .table-wrapper {
            overflow-x: auto;
            background: #1e1e3a;
            border-radius: 16px;
            border: 1px solid rgba(233, 30, 140, 0.15);
            padding: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        th {
            text-align: left;
            padding: 12px 10px;
            color: #e91e8c;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            border-bottom: 2px solid rgba(233, 30, 140, 0.2);
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(233, 30, 140, 0.08);
            color: #c9b8e8;
        }
        tr:hover td { background: rgba(233, 30, 140, 0.05); }
        .estado {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .estado-pendiente { background: #f39c12; color: #fff; }
        .estado-confirmada { background: #2ecc71; color: #fff; }
        .estado-cancelada { background: #e53935; color: #fff; }
        .estado-completada { background: #3498db; color: #fff; }
        .servicios-list { font-size: 0.8rem; color: #c9b8e8; }
        .vacio {
            text-align: center;
            padding: 3rem;
            color: #c9b8e8;
        }
        .vacio .icon { font-size: 3rem; display: block; margin-bottom: 1rem; }
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .header-actions { flex-direction: column; align-items: stretch; }
            .btn { text-align: center; }
            table { font-size: 0.75rem; }
            th, td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-actions">
            <div>
                <h1>📋 Lista de Reservas</h1>
                <p class="subtitle">Total: <?php echo $total_reservas; ?> reservas registradas</p>
            </div>
            <div>
                <a href="index.php" class="btn">📝 Nueva Reserva</a>
                <a href="index.php#inicio" class="btn btn-secondary">🏠 Inicio</a>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="estadisticas">
            <?php
            $sql_estados = "SELECT estado, COUNT(*) as total FROM reservas GROUP BY estado";
            $result_estados = mysqli_query($conexion, $sql_estados);
            $estados = [];
            while ($row = mysqli_fetch_assoc($result_estados)) {
                $estados[$row['estado']] = $row['total'];
            }
            ?>
            <div class="estadistica-card">
                <div class="numero"><?php echo isset($estados['pendiente']) ? $estados['pendiente'] : 0; ?></div>
                <div class="label">⏳ Pendientes</div>
            </div>
            <div class="estadistica-card">
                <div class="numero"><?php echo isset($estados['confirmada']) ? $estados['confirmada'] : 0; ?></div>
                <div class="label">✅ Confirmadas</div>
            </div>
            <div class="estadistica-card">
                <div class="numero"><?php echo isset($estados['completada']) ? $estados['completada'] : 0; ?></div>
                <div class="label">✨ Completadas</div>
            </div>
            <div class="estadistica-card">
                <div class="numero"><?php echo isset($estados['cancelada']) ? $estados['cancelada'] : 0; ?></div>
                <div class="label">❌ Canceladas</div>
            </div>
        </div>

        <!-- Tabla de reservas -->
        <div class="table-wrapper">
            <?php if ($total_reservas > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Servicios</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $contador = 1;
                    while ($row = mysqli_fetch_assoc($resultado)): 
                        $estado_class = 'estado-' . $row['estado'];
                    ?>
                    <tr>
                        <td><?php echo $contador++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['cliente']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($row['telefono']); ?><br>
                            <small style="color:#6b3d5a;"><?php echo htmlspecialchars($row['email']); ?></small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                        <td><?php echo $row['horario']; ?></td>
                        <td class="servicios-list"><?php echo htmlspecialchars($row['servicios']); ?></td>
                        <td><strong style="color:#e91e8c;">$<?php echo number_format($row['total'], 0, ',', '.'); ?></strong></td>
                        <td><span class="estado <?php echo $estado_class; ?>"><?php echo $row['estado']; ?></span></td>
                        <td>
                            <a href="detalle_reserva.php?id=<?php echo $row['reserva_id']; ?>" style="color:#e91e8c;text-decoration:none;">🔍</a>
                            <a href="cambiar_estado.php?id=<?php echo $row['reserva_id']; ?>&estado=confirmada" style="color:#2ecc71;text-decoration:none;margin-left:5px;" onclick="return confirm('¿Confirmar esta reserva?')">✅</a>
                            <a href="cambiar_estado.php?id=<?php echo $row['reserva_id']; ?>&estado=cancelada" style="color:#e53935;text-decoration:none;margin-left:5px;" onclick="return confirm('¿Cancelar esta reserva?')">❌</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="vacio">
                <span class="icon">📭</span>
                <p>No hay reservas registradas aún.</p>
                <a href="index.php#reservas" class="btn" style="margin-top:1rem;display:inline-block;">Crear primera reserva</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conexion); ?>