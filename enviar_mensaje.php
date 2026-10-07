<?php
// ============================================================
// ARCHIVO: enviar_mensaje.php
// DESCRIPCIÓN: Envía mensaje de contacto por email
// ============================================================

// ── RECIBIR DATOS ────────────────────────────────────────────
$nombre = isset($_POST['nombre_contacto']) ? $_POST['nombre_contacto'] : '';
$email = isset($_POST['email_contacto']) ? $_POST['email_contacto'] : '';
$mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : '';

// ── VALIDAR ──────────────────────────────────────────────────
if (empty($nombre) || empty($email) || empty($mensaje)) {
    die("ERROR: Todos los campos son obligatorios.");
}

// ── ENVIAR EMAIL ────────────────────────────────────────────
$to = "contacto@sarahwendy.com";
$subject = "Nuevo mensaje de contacto - Sarah Wendy";
$body = "Nombre: $nombre\n";
$body .= "Email: $email\n\n";
$body .= "Mensaje:\n$mensaje";

$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";

if (mail($to, $subject, $body, $headers)) {
    header("Location: index.php?mensaje=enviado");
    exit();
} else {
    echo "ERROR al enviar el mensaje. Por favor, intenta de nuevo.";
}
?>