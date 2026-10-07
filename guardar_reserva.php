<?php
include "conexion.php";

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$horario = isset($_POST['horario']) ? $_POST['horario'] : '';
$servicios = isset($_POST['servicios']) ? $_POST['servicios'] : array();

if (empty($nombre) || empty($email) || empty($telefono) || empty($fecha) || empty($horario)) {
    die("ERROR: Todos los campos obligatorios deben estar llenos.");
}

if (empty($servicios)) {
    die("ERROR: Debes seleccionar al menos un servicio.");
}

mysqli_begin_transaction($conexion);

try {
    // ── 1. BUSCAR O CREAR CLIENTE ──────────────────────
    $sql_cliente = "SELECT id FROM clientes WHERE email = '$email'";
    $resultado = mysqli_query($conexion, $sql_cliente);

    if (mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $cliente_id = $fila['id'];
    } else {
        $sql_insertar_cliente = "INSERT INTO clientes (nombre, email, telefono) 
                                 VALUES ('$nombre', '$email', '$telefono')";
        if (!mysqli_query($conexion, $sql_insertar_cliente)) {
            throw new Exception("Error al insertar cliente: " . mysqli_error($conexion));
        }
        $cliente_id = mysqli_insert_id($conexion);
    }

    // ── 2. INSERTAR RESERVA ────────────────────────────
    $sql_reserva = "INSERT INTO reservas (cliente_id, fecha, horario, estado) 
                    VALUES ('$cliente_id', '$fecha', '$horario', 'pendiente')";
    if (!mysqli_query($conexion, $sql_reserva)) {
        throw new Exception("Error al insertar reserva: " . mysqli_error($conexion));
    }
    $reserva_id = mysqli_insert_id($conexion);

    // ── 3. INSERTAR SERVICIOS ──────────────────────────
    foreach ($servicios as $servicio_id) {
        $sql_precio = "SELECT precio FROM servicios WHERE id = '$servicio_id'";
        $resultado_precio = mysqli_query($conexion, $sql_precio);
        if (!$resultado_precio) {
            throw new Exception("Error al obtener precio: " . mysqli_error($conexion));
        }
        $fila_precio = mysqli_fetch_assoc($resultado_precio);
        $precio = $fila_precio['precio'];

        $sql_detalle = "INSERT INTO detalle_reservas (reserva_id, servicio_id, precio_unitario) 
                        VALUES ('$reserva_id', '$servicio_id', '$precio')";
        if (!mysqli_query($conexion, $sql_detalle)) {
            throw new Exception("Error al insertar detalle: " . mysqli_error($conexion));
        }
    }

    // ── 4. CONFIRMAR TODO ──────────────────────────────
    mysqli_commit($conexion);
    
    // ── 5. REDIRIGIR AL ÉXITO ──────────────────────────
    header("Location: exito.php?reserva_id=" . $reserva_id);
    exit();

} catch (Exception $e) {
    // ── SI HAY ERROR, DESHACER TODO ────────────────────
    mysqli_rollback($conexion);
    echo "❌ ERROR al guardar la reserva: " . $e->getMessage();
}

mysqli_close($conexion);
?>