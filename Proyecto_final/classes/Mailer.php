<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private function crearMailer()
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->Port = MAIL_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        if (defined('MAIL_SMTP_SECURE') && MAIL_SMTP_SECURE === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (defined('MAIL_SMTP_SECURE') && MAIL_SMTP_SECURE === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->isHTML(true);

        return $mail;
    }

    private function armarCuerpo($cuerpoHtml)
    {
        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
            body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #0d6efd, #0056b3); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .body { padding: 30px; }
            .body p { line-height: 1.6; color: #333; }
            .info { background: #f8f9fa; border-left: 4px solid #0d6efd; padding: 15px; margin: 15px 0; border-radius: 4px; }
            .footer { background: #212529; color: #adb5bd; text-align: center; padding: 20px; font-size: 12px; }
            .btn { display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 8px; margin: 10px 0; }
        </style></head><body>
            <div class="container">
                <div class="header"><h1>' . SITE_NAME . '</h1></div>
                <div class="body">' . $cuerpoHtml . '</div>
                <div class="footer">&copy; ' . date('Y') . ' ' . SITE_NAME . ' - Todos los derechos reservados</div>
            </div>
        </body></html>';
    }

    public function enviar($para, $asunto, $cuerpoHtml)
    {
        try {
            $mail = $this->crearMailer();
            $mail->addAddress($para);
            $mail->Subject = $asunto;
            $mail->Body = $this->armarCuerpo($cuerpoHtml);
            $mail->AltBody = strip_tags($cuerpoHtml);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer: " . $mail->ErrorInfo ?? $e->getMessage());
            return false;
        }
    }

    public function bienvenida($nombre, $email)
    {
        $asunto = '¡Bienvenido a ' . SITE_NAME . '!';
        $cuerpo = '<h2>¡Hola ' . htmlspecialchars($nombre) . '!</h2>
            <p>Gracias por registrarte en <strong>' . SITE_NAME . '</strong>.</p>
            <p>Ahora puedes explorar nuestras canchas y reservar tu horario favorito.</p>
            <div class="info">
                <p><strong>Tu correo:</strong> ' . htmlspecialchars($email) . '</p>
                <p><strong>Fecha de registro:</strong> ' . date('d/m/Y H:i') . '</p>
            </div>
            <p style="text-align:center;"><a href="' . SITE_URL . '/pages/canchas.php" class="btn">Ver Canchas</a></p>
            <p>Si tienes dudas, contáctanos.</p>
            <p>¡Disfruta del deporte!</p>';
        return $this->enviar($email, $asunto, $cuerpo);
    }

    public function confirmacionReservacion($nombre, $email, $cancha, $fecha, $horaInicio, $horaFin, $total, $referencia)
    {
        $asunto = 'Reservación Confirmada - ' . SITE_NAME;
        $cuerpo = '<h2>¡Reservación Confirmada, ' . htmlspecialchars($nombre) . '!</h2>
            <p>Tu reservación ha sido confirmada exitosamente.</p>
            <div class="info">
                <p><strong>Cancha:</strong> ' . htmlspecialchars($cancha) . '</p>
                <p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($fecha)) . '</p>
                <p><strong>Horario:</strong> ' . substr($horaInicio, 0, 5) . ' - ' . substr($horaFin, 0, 5) . '</p>
                <p><strong>Total pagado:</strong> $' . number_format($total, 2) . '</p>
                <p><strong>Referencia:</strong> ' . htmlspecialchars($referencia) . '</p>
            </div>
            <p style="text-align:center;"><a href="' . SITE_URL . '/pages/mis_reservaciones.php" class="btn">Mis Reservaciones</a></p>
            <p>Te esperamos. ¡Disfruta tu partido!</p>';
        return $this->enviar($email, $asunto, $cuerpo);
    }

    public function nuevaReservacionAdmin($adminEmail, $usuarioNombre, $cancha, $fecha, $horaInicio, $horaFin)
    {
        $asunto = 'Nueva Reservación - ' . SITE_NAME;
        $cuerpo = '<h2>Nueva Reservación Realizada</h2>
            <div class="info">
                <p><strong>Usuario:</strong> ' . htmlspecialchars($usuarioNombre) . '</p>
                <p><strong>Cancha:</strong> ' . htmlspecialchars($cancha) . '</p>
                <p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($fecha)) . '</p>
                <p><strong>Horario:</strong> ' . substr($horaInicio, 0, 5) . ' - ' . substr($horaFin, 0, 5) . '</p>
            </div>
            <p style="text-align:center;"><a href="' . SITE_URL . '/admin/reservaciones.php" class="btn">Ver en Admin</a></p>';
        return $this->enviar($adminEmail, $asunto, $cuerpo);
    }

    public function cancelacionReservacion($nombre, $email, $cancha, $fecha, $horaInicio, $horaFin)
    {
        $asunto = 'Reservación Cancelada - ' . SITE_NAME;
        $cuerpo = '<h2>Reservación Cancelada, ' . htmlspecialchars($nombre) . '</h2>
            <p>Tu reservación ha sido cancelada.</p>
            <div class="info">
                <p><strong>Cancha:</strong> ' . htmlspecialchars($cancha) . '</p>
                <p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($fecha)) . '</p>
                <p><strong>Horario:</strong> ' . substr($horaInicio, 0, 5) . ' - ' . substr($horaFin, 0, 5) . '</p>
            </div>
            <p>Si requieres más información, contáctanos.</p>
            <p style="text-align:center;"><a href="' . SITE_URL . '/pages/mis_reservaciones.php" class="btn">Mis Reservaciones</a></p>';
        return $this->enviar($email, $asunto, $cuerpo);
    }

    public function recuperacionPassword($nombre, $email, $token)
    {
        $asunto = 'Recuperación de Contraseña - ' . SITE_NAME;
        $enlace = SITE_URL . '/pages/restablecer.php?token=' . urlencode($token);
        $cuerpo = '<h2>Hola ' . htmlspecialchars($nombre) . '</h2>
            <p>Recibimos una solicitud para restablecer tu contraseña en <strong>' . SITE_NAME . '</strong>.</p>
            <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
            <p style="text-align:center;"><a href="' . $enlace . '" class="btn">Restablecer Contraseña</a></p>
            <p>Si no solicitaste esto, ignora este mensaje.</p>
            <div class="info">
                <p><strong>Este enlace expira en 1 hora.</strong></p>
            </div>
            <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
            <p style="font-size:12px; word-break:break-all;">' . htmlspecialchars($enlace) . '</p>';
        return $this->enviar($email, $asunto, $cuerpo);
    }

    public function recordatorioReservacion($nombre, $email, $cancha, $fecha, $horaInicio, $horaFin)
    {
        $asunto = 'Recordatorio: Tienes una reservación mañana - ' . SITE_NAME;
        $cuerpo = '<h2>¡Recordatorio, ' . htmlspecialchars($nombre) . '!</h2>
            <p>Te recordamos que tienes una reservación mañana en <strong>' . SITE_NAME . '</strong>.</p>
            <div class="info">
                <p><strong>Cancha:</strong> ' . htmlspecialchars($cancha) . '</p>
                <p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($fecha)) . '</p>
                <p><strong>Horario:</strong> ' . substr($horaInicio, 0, 5) . ' - ' . substr($horaFin, 0, 5) . '</p>
            </div>
            <p>¡Te esperamos!</p>
            <p style="text-align:center;"><a href="' . SITE_URL . '/pages/mis_reservaciones.php" class="btn">Mis Reservaciones</a></p>';
        return $this->enviar($email, $asunto, $cuerpo);
    }
}
