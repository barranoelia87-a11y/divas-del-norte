<?php
// ============================================================
// ARCHIVO: detalle_reserva.php
// DESCRIPCIÓN: Muestra el detalle de una reserva específica
// ============================================================

include "conexion.php";

// ── RECIBIR ID ──────────────────────────────────────────────
$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id == 0) {
    die("ERROR: ID de reserva no válido.");
}

// ── CONSULTAR DETALLE ──────────────────────────────────────
$sql = "SELECT 
            r.id AS reserva_id,
            c.nombre AS cliente,
            c.email,
            c.telefono,
            r.fecha,
            r.horario,
            r.estado,
            r.observaciones,
            r.created_at
        FROM reservas r
        INNER JOIN clientes c ON r.cliente_id = c.id
        WHERE r.id = '$id'";

$resultado = mysqli_query($conexion, $sql);

if (mysqli_num_rows($resultado) == 0) {
    die("ERROR: Reserva no encontrada.");
}

$reserva = mysqli_fetch_assoc($resultado);

// ── CONSULTAR SERVICIOS ─────────────────────────────────────
$sql_servicios = "SELECT s.nombre, s.precio, s.duracion, s.icono 
                  FROM servicios s
                  INNER JOIN detalle_reservas dr ON s.id = dr.servicio_id
                  WHERE dr.reserva_id = '$id'";
$resultado_servicios = mysqli_query($conexion, $sql_servicios);
$total = 0;
$servicios_list = [];
while ($row = mysqli_fetch_assoc($resultado_servicios)) {
    $servicios_list[] = $row;
    $total += $row['precio'];
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Reserva - Sarah Wendy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            background: #1a1a2e;
            color: #f0e6ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: #1e1e3a;
            border: 1px solid rgba(233, 30, 140, 0.25);
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(233, 30, 140, 0.15);
        }
        h1 {
            font-family: 'Playfair Display', serif;
            color: #e91e8c;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .subtitle { color: #c9b8e8; margin-bottom: 2rem; }
        .detalle-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(233, 30, 140, 0.08);
        }
        .detalle-item .label { color: #c9b8e8; font-weight: 600; }
        .detalle-item .value { color: #f0e6ff; font-weight: 600; }
        .estado {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .estado-pendiente { background: #f39c12; color: #fff; }
        .estado-confirmada { background: #2ecc71; color: #fff; }
        .estado-cancelada { background: #e53935; color: #fff; }
        .estado-completada { background: #3498db; color: #fff; }
        .servicio-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(233, 30, 140, 0.05);
            color: #c9b8e8;
        }
        .total {
            font-size: 1.3rem;
            color: #e91e8c;
            font-weight: 700;
            padding-top: 1rem;
            border-top: 2px solid rgba(233, 30, 140, 0.2);
            margin-top: 0.5rem;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #e91e8c, #c2185b);
            color: #fff;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            margin-top: 1.5rem;
            margin-right: 0.5rem;
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
        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        @media (max-width: 480px) {
            .container { padding: 1.5rem; }
            .detalle-item { flex-direction: column; gap: 4px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Detalle de Reserva</h1>
        <p class="subtitle">Reserva #<?php echo $reserva['reserva_id']; ?></p>

        <div class="detalle-item">
            <span class="label">👤 Cliente</span>
            <span class="value"><?php echo htmlspecialchars($reserva['cliente']); ?></span>
        </div>
        <div class="detalle-item">
            <span class="label">📧 Email</span>
            <span class="value"><?php echo htmlspecialchars($reserva['email']); ?></span>
        </div>
        <div class="detalle-item">
            <span class="label">📱 Teléfono</span>
            <span class="value"><?php echo htmlspecialchars($reserva['telefono']); ?></span>
        </div>
        <div class="detalle-item">
            <span class="label">📅 Fecha</span>
            <span class="value"><?php echo date('d/m/Y', strtotime($reserva['fecha'])); ?></span>
        </div>
        <div class="detalle-item">
            <span class="label">🕐 Horario</span>
            <span class="value"><?php echo $reserva['horario']; ?> hs</span>
        </div>
        <div class="detalle-item">
            <span class="label">📌 Estado</span>
            <span class="value"><span class="estado estado-<?php echo $reserva['estado']; ?>"><?php echo $reserva['estado']; ?></span></span>
        </div>
        <?php if (!empty($reserva['observaciones'])): ?>
        <div class="detalle-item">
            <span class="label">📝 Observaciones</span>
            <span class="value"><?php echo htmlspecialchars($reserva['observaciones']); ?></span>
        </div>
        <?php endif; ?>

        <h3 style="margin: 1.5rem 0 0.5rem; color: #e91e8c;">📋 Servicios</h3>
        <?php foreach ($servicios_list as $servicio): ?>
        <div class="servicio-item">
            <span><?php echo $servicio['icono']; ?> <?php echo htmlspecialchars($servicio['nombre']); ?></span>
            <span>$<?php echo number_format($servicio['precio'], 0, ',', '.'); ?></span>
        </div>
        <?php endforeach; ?>

        <div class="total">
            Total: $<?php echo number_format($total, 0, ',', '.'); ?>
        </div>

        <div class="acciones">
            <a href="listar_reservas.php" class="btn btn-secondary">← Volver</a>
            <a href="cambiar_estado.php?id=<?php echo $reserva['reserva_id']; ?>&estado=confirmada" class="btn" onclick="return confirm('¿Confirmar esta reserva?')">✅ Confirmar</a>
            <a href="cambiar_estado.php?id=<?php echo $reserva['reserva_id']; ?>&estado=cancelada" class="btn" style="background:#e53935;" onclick="return confirm('¿Cancelar esta reserva?')">❌ Cancelar</a>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conexion); ?>