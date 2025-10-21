<?php
require_once './conn/loader.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['vendedor']) || empty($_SESSION['vendedor'])) {
    header('Location: ./login.php');
    exit;
}

require_once './libs/PHPMailer/src/PHPMailer.php';
require_once './libs/PHPMailer/src/SMTP.php';
require_once './libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


$mensaje = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Gracias por tu reserva</title>
  </head>
  <body style="font-family: Arial, sans-serif; font-size: 14px; color: #333333; background-color: #f6f6f6; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f6f6f6">
      <tr>
        <td align="center">
          <table width="600" cellpadding="20" cellspacing="0" border="0" bgcolor="#ffffff" style="border: 1px solid #dddddd;">
            <tr>
              <td>
                <p style="font-size: 16px; margin-top: 0;">Hola,</p>

                <p style="font-size: 14px; line-height: 1.5;">
                  ¡Gracias por realizar tu reserva! Queremos contarte que ya estamos trabajando en el armado de tu presupuesto de forma personalizada, para brindarte la mejor opción posible según tus necesidades.
                </p>

                <p style="font-size: 14px; line-height: 1.5;">
                  En breve nos pondremos en contacto con vos con todos los detalles. Si tenés alguna consulta o querés sumar información adicional, no dudes en escribirnos respondiendo a este correo o escribirnos al WhatsApp <strong><a href="https://wa.me/+5491121670916" target="_blank">+54 9 11 2167-0916</a></strong>.
                </p>

                <p style="font-size: 14px; line-height: 1.5;">
                  Agradecemos tu confianza y estamos a tu disposición para lo que necesites.
                </p>

                <p style="font-size: 14px; margin-bottom: 0;">
                  Saludos cordiales,<br>
                  <strong>Tienda Jean Cartier Hogar - Paraná</strong>
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>

';

try {
    //Server settings
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host       = 'mail.tiendajeancartierhogar.com.ar';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'presupuesto@tiendajeancartierhogar.com.ar';
    $mail->Password   = 'FaruSae0ujoh';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // o 'ssl'
    $mail->Port       = 465; 

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ];
  
    $mail->Port       = 465;                                   
    //Recipients
    //$mail->addReplyTo('marcos.kukuchka@gmail.com');
    $mail->setFrom('presupuesto@tiendajeancartierhogar.com.ar', 'Reserva Web');

    //$mail->addAddress('contactoweb@alavsrl.com.ar');
    $mail->addAddress($_SESSION['vendedor']['Email']);
    //$mail->addAddress('marcos.kukuchka@gmail.com');

    $idPresupuesto = $_SESSION['idPresupuesto'] ?? '';

    $mail->isHTML(true);
    $mail->Subject = 'Reserva Web' . ' ' . $idPresupuesto;
    $mail->Body    = $mensaje;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $emailEnviado = $mail->send();
} catch (Exception $e) {
    //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once './includes/head.php' ?>
    <title>Pedido Confirmado - Jean Cartier Paraná</title>
    <style>
        .success-animation {
            animation: scaleIn 0.5s ease-in-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .check-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        .info-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <?php require_once './includes/header.php' ?>
    
    <main>
        <div class="container my-5">
            <!-- Mensaje de éxito principal -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 success-animation">
                        <div class="card-body text-center py-5">
                            <!-- Icono de éxito -->
                            <div class="check-icon success-animation">
                                <i class="fas fa-check fa-3x text-white"></i>
                            </div>

                            <!-- Título principal -->
                            <h1 class="display-4 fw-bold text-success mb-3">
                                ¡Pedido Realizado!
                            </h1>
                            <p class="lead text-muted mb-4">
                                Tu pedido ha sido registrado exitosamente
                            </p>

                            <!-- Línea divisoria -->
                            <hr class="my-4">

                            <!-- Información del pedido -->
                            <div class="row text-start mt-4">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Usuario</small>
                                            <strong><?= htmlspecialchars($_SESSION['vendedor']['Email'] ?? 'No disponible') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-store fa-2x text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Sucursal</small>
                                            <strong>Paraná</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-alt fa-2x text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Fecha y hora</small>
                                            <strong><?= date('d/m/Y H:i') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle fa-2x text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Estado</small>
                                            <span class="badge bg-success fs-6">Procesado</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="row justify-content-center mt-4">
                <div class="col-lg-8">
                    <div class="row g-3">
                        <!-- Tarjeta: ¿Qué sigue? -->
                        <div class="col-md-6">
                            <div class="card h-100 info-card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="fas fa-clock fa-2x text-primary"></i>
                                        </div>
                                        <h5 class="card-title mb-0">¿Qué sigue?</h5>
                                    </div>
                                    <p class="card-text text-muted">
                                        Tu pedido será procesado por nuestro equipo. 
                                        Recibirás una confirmación en breve con los detalles 
                                        y tiempo de entrega estimado.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta: Contacto -->
                        <div class="col-md-6">
                            <div class="card h-100 info-card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="fas fa-headset fa-2x text-success"></i>
                                        </div>
                                        <h5 class="card-title mb-0">¿Necesitas ayuda?</h5>
                                    </div>
                                    <p class="card-text text-muted">
                                        Si tienes alguna duda o consulta sobre tu pedido, 
                                        no dudes en contactarnos. Estamos aquí para ayudarte.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="row justify-content-center mt-4">
                <div class="col-lg-8">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="./presupuestos" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-shopping-bag me-2"></i>Presupuestos
                        </a>
                        <a href="./productos" class="btn btn-outline-secondary btn-lg px-5">
                            <i class="fas fa-home me-2"></i>Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>

            <!-- Nota adicional -->
            <!-- <div class="row justify-content-center mt-4">
                <div class="col-lg-8">
                    <div class="alert alert-info border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                            <div>
                                <h5 class="alert-heading mb-2">Importante</h5>
                                <p class="mb-0">
                                    Guarda este comprobante para futuras consultas. 
                                    Puedes imprimirlo usando el botón de tu navegador o 
                                    hacer una captura de pantalla.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        // Limpiar el carrito de la sesión después de mostrar la página
        // (Esto se puede hacer desde PHP también)
        
        // Animación de confetti (opcional)
        $(document).ready(function() {
            // Scroll suave al inicio
            $('html, body').animate({ scrollTop: 0 }, 'fast');

            // Auto-scroll para móvil
            if ($(window).width() < 768) {
                setTimeout(function() {
                    $('.success-animation').addClass('animate__animated animate__pulse');
                }, 500);
            }
        });

        // Atajo de teclado para imprimir (Ctrl+P)
        $(document).keydown(function(e) {
            if (e.ctrlKey && e.keyCode === 80) {
                window.print();
                return false;
            }
        });
    </script>
</body>

</html>

