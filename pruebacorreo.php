<?php
if (mail('fernandosalguero685@gmail.com', 'Prueba de correo', 'Este es un mensaje de prueba')) {
    echo 'Correo enviado exitosamente.';
} else {
    echo 'Error al enviar el correo.';
}
