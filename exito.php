<?php
// ============================================================
// ARCHIVO: exito.php
// DESCRIPCIÓN: Página de confirmación de reserva
// ============================================================

include "conexion.php";

$reserva_id = isset($_GET['reserva_id']) ? $_GET['reserva_id'] : 0;

if ($reserva_id == 0) {
    die("ERROR: ID de reserva no válido.");
}

// Obtener datos de la reserva
$sql = "SELECT 
            c.nombre AS cliente,
            c.email,
            r.fecha,
            r.horario,
            GROUP_CONCAT(s.nombre SEPARATOR ', ') AS servicios,
            SUM(dr.precio_unitario) AS total
        FROM reservas r
        INNER JOIN clientes c ON r.cliente_id = c.id
        LEFT JOIN detalle_reservas dr ON r.id = dr.reserva_id
        LEFT JOIN servicios s ON dr.servicio_id = s.id
        WHERE r.id = '$reserva_id'
        GROUP BY r.id";

$resultado = mysqli_query($conexion, $sql);
$reserva = mysqli_fetch_assoc($resultado);

if (!$reserva) {
    die("ERROR: Reserva no encontrada.");
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Reserva Confirmada! - Sarah Wendy</title>
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
            padding: 3rem 2.5rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(233, 30, 140, 0.15);
        }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
        h1 { 
            font-family: 'Playfair Display', serif;
            color: #e91e8c;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        p { color: #c9b8e8; line-height: 1.8; margin-bottom: 0.5rem; }
        .detalle {
            background: #252545;
            border-radius: 12px;
            padding: 1.2rem;
            margin: 1.5rem 0;
            text-align: left;
            font-size: 0.9rem;
        }
        .detalle strong { color: #e91e8c; }
        .detalle .fila {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid rgba(233, 30, 140, 0.05);
        }
        .detalle .fila:last-child { border-bottom: none; }
        .total {
            font-size: 1.2rem;
            color: #e91e8c;
            font-weight: 700;
            padding-top: 0.5rem;
            border-top: 2px solid rgba(233, 30, 140, 0.2);
            margin-top: 0.5rem;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #e91e8c, #c2185b);
            color: #fff;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            margin: 0.5rem;
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
        .acciones {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        @media (max-width: 480px) {
            .container { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🎉</div>
        <h1>¡Reserva Confirmada!</h1>
        <p><strong><?php echo htmlspecialchars($reserva['cliente']); ?></strong>, tu reserva ha sido registrada exitosamente.</p>
        
        <div class="detalle">
            <div class="fila">
                <span>📅 Fecha</span>
                <span><?php echo date('d/m/Y', strtotime($reserva['fecha'])); ?></span>
            </div>
            <div class="fila">
                <span>🕐 Horario</span>
                <span><?php echo $reserva['horario']; ?> hs</span>
            </div>
            <div class="fila">
                <span>📋 Servicios</span>
                <span><?php echo htmlspecialchars($reserva['servicios']); ?></span>
            </div>
            <div class="total">
                Total: $<?php echo number_format($reserva['total'], 0, ',', '.'); ?>
            </div>
        </div>
        
        <p>Te enviaremos los detalles a <strong><?php echo htmlspecialchars($reserva['email']); ?></strong></p>
        
        <div class="acciones">
            <a href="index.php" class="btn">📝 Nueva Reserva</a>
            <a href="listar_reservas.php" class="btn btn-secondary">📋 Ver Mis Reservas</a>
        </div>
    </div>
</body>
</html>