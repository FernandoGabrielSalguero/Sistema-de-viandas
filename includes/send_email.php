<?php
function sendEmail($to, $subject, $message) {
    $headers = "From: no-reply@viandas.ilmanagastronomia.com\r\n";
    $headers .= "Reply-To: no-reply@viandas.ilmanagastronomia.com\r\n";
    $headers .= "Content-type: text/html\r\n";

    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}