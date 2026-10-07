<?php
// ============================================================
// ARCHIVO: funciones.php
// DESCRIPCIÓN: Funciones reutilizables para el sistema
// ============================================================

// ── INCLUIR CONEXIÓN ────────────────────────────────────────
include "conexion.php";

// ── FUNCIÓN: Obtener todos los servicios ────────────────────
function obtenerServicios($conexion) {
    $sql = "SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre";
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

// ── FUNCIÓN: Obtener servicio por ID ────────────────────────
function obtenerServicioPorId($conexion, $id) {
    $sql = "SELECT * FROM servicios WHERE id = '$id'";
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

// ── FUNCIÓN: Obtener cliente por email ──────────────────────
function obtenerClientePorEmail($conexion, $email) {
    $sql = "SELECT * FROM clientes WHERE email = '$email'";
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

// ── FUNCIÓN: Crear nuevo cliente ────────────────────────────
function crearCliente($conexion, $nombre, $email, $telefono) {
    $sql = "INSERT INTO clientes (nombre, email, telefono) VALUES ('$nombre', '$email', '$telefono')";
    if (mysqli_query($conexion, $sql)) {
        return mysqli_insert_id($conexion);
    }
    return false;
}

// ── FUNCIÓN: Crear nueva reserva ────────────────────────────
function crearReserva($conexion, $cliente_id, $fecha, $horario, $servicios) {
    // Iniciar transacción
    mysqli_begin_transaction($conexion);
    
    try {
        // Insertar reserva
        $sql = "INSERT INTO reservas (cliente_id, fecha, horario, estado) 
                VALUES ('$cliente_id', '$fecha', '$horario', 'pendiente')";
        mysqli_query($conexion, $sql);
        $reserva_id = mysqli_insert_id($conexion);
        
        // Insertar detalles de servicios
        foreach ($servicios as $servicio_id) {
            $sql_precio = "SELECT precio FROM servicios WHERE id = '$servicio_id'";
            $resultado = mysqli_query($conexion, $sql_precio);
            $fila = mysqli_fetch_assoc($resultado);
            $precio = $fila['precio'];
            
            $sql_detalle = "INSERT INTO detalle_reservas (reserva_id, servicio_id, precio_unitario) 
                           VALUES ('$reserva_id', '$servicio_id', '$precio')";
            mysqli_query($conexion, $sql_detalle);
        }
        
        // Confirmar transacción
        mysqli_commit($conexion);
        return $reserva_id;
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conexion);
        return false;
    }
}

// ── FUNCIÓN: Obtener reservas por cliente ──────────────────
function obtenerReservasPorCliente($conexion, $cliente_id) {
    $sql = "SELECT 
                r.id,
                r.fecha,
                r.horario,
                r.estado,
                GROUP_CONCAT(s.nombre SEPARATOR ', ') AS servicios,
                SUM(dr.precio_unitario) AS total
            FROM reservas r
            LEFT JOIN detalle_reservas dr ON r.id = dr.reserva_id
            LEFT JOIN servicios s ON dr.servicio_id = s.id
            WHERE r.cliente_id = '$cliente_id'
            GROUP BY r.id
            ORDER BY r.fecha DESC, r.horario DESC";
    
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

// ── FUNCIÓN: Obtener reservas por fecha ─────────────────────
function obtenerReservasPorFecha($conexion, $fecha) {
    $sql = "SELECT 
                r.id,
                c.nombre AS cliente,
                r.horario,
                r.estado,
                GROUP_CONCAT(s.nombre SEPARATOR ', ') AS servicios
            FROM reservas r
            INNER JOIN clientes c ON r.cliente_id = c.id
            LEFT JOIN detalle_reservas dr ON r.id = dr.reserva_id
            LEFT JOIN servicios s ON dr.servicio_id = s.id
            WHERE r.fecha = '$fecha'
            GROUP BY r.id
            ORDER BY r.horario";
    
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

// ── FUNCIÓN: Verificar disponibilidad de horario ────────────
function verificarDisponibilidad($conexion, $fecha, $horario) {
    $sql = "SELECT COUNT(*) as total FROM reservas 
            WHERE fecha = '$fecha' AND horario = '$horario' 
            AND estado IN ('pendiente', 'confirmada')";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return $fila['total'] == 0;
}

// ── FUNCIÓN: Cambiar estado de reserva ──────────────────────
function cambiarEstadoReserva($conexion, $reserva_id, $nuevo_estado) {
    $estados_validos = ['pendiente', 'confirmada', 'cancelada', 'completada'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        return false;
    }
    
    $sql = "UPDATE reservas SET estado = '$nuevo_estado' WHERE id = '$reserva_id'";
    return mysqli_query($conexion, $sql);
}

// ── FUNCIÓN: Obtener estadísticas ───────────────────────────
function obtenerEstadisticas($conexion) {
    $estadisticas = [];
    
    // Total de reservas
    $sql = "SELECT COUNT(*) as total FROM reservas";
    $resultado = mysqli_query($conexion, $sql);
    $estadisticas['total'] = mysqli_fetch_assoc($resultado)['total'];
    
    // Reservas por estado
    $sql = "SELECT estado, COUNT(*) as total FROM reservas GROUP BY estado";
    $resultado = mysqli_query($conexion, $sql);
    while ($row = mysqli_fetch_assoc($resultado)) {
        $estadisticas[$row['estado']] = $row['total'];
    }
    
    // Total de clientes
    $sql = "SELECT COUNT(*) as total FROM clientes";
    $resultado = mysqli_query($conexion, $sql);
    $estadisticas['clientes'] = mysqli_fetch_assoc($resultado)['total'];
    
    return $estadisticas;
}

// ── FUNCIÓN: Sanitizar datos ────────────────────────────────
function sanitizar($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// ── FUNCIÓN: Validar email ──────────────────────────────────
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ── FUNCIÓN: Validar teléfono boliviano ─────────────────────
function validarTelefonoBolivia($telefono) {
    // Formato: +591 70000000 (11 dígitos incluyendo +591)
    $digitos = preg_replace('/[^0-9]/', '', $telefono);
    return strlen($digitos) === 11 && strpos($telefono, '+591') === 0;
}

// ── FUNCIÓN: Formatear precio ───────────────────────────────
function formatearPrecio($precio) {
    return '$' . number_format($precio, 0, ',', '.');
}

// ── FUNCIÓN: Formatear fecha ────────────────────────────────
function formatearFecha($fecha) {
    return date('d/m/Y', strtotime($fecha));
}

// ── FUNCIÓN: Obtener clase de estado ────────────────────────
function getEstadoClass($estado) {
    $clases = [
        'pendiente' => 'estado-pendiente',
        'confirmada' => 'estado-confirmada',
        'cancelada' => 'estado-cancelada',
        'completada' => 'estado-completada'
    ];
    return isset($clases[$estado]) ? $clases[$estado] : '';
}

// ── FUNCIÓN: Obtener horarios disponibles ───────────────────
function obtenerHorariosDisponibles($conexion, $fecha) {
    $horarios = [];
    $hora_inicio = strtotime('09:00');
    $hora_fin = strtotime('20:00');
    $intervalo = 60; // minutos
    
    // Obtener reservas del día
    $sql = "SELECT horario FROM reservas WHERE fecha = '$fecha' AND estado IN ('pendiente', 'confirmada')";
    $resultado = mysqli_query($conexion, $sql);
    $reservados = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $reservados[] = $row['horario'];
    }
    
    // Generar horarios disponibles
    for ($h = $hora_inicio; $h < $hora_fin; $h += $intervalo * 60) {
        $horario = date('H:i', $h);
        if (!in_array($horario, $reservados)) {
            $horarios[] = $horario;
        }
    }
    
    return $horarios;
}

?>