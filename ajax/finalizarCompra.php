<?php
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
require_once '../conn/loader.php';
require_once '../libs/PHPMailer/src/PHPMailer.php';
require_once '../libs/PHPMailer/src/SMTP.php';
require_once '../libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


if (!isset($_SESSION['vendedor']) || empty($_SESSION['vendedor'])) {
  header('Location: ./login.php');
  exit;
}

//? Manejo de numeradores
$query = sqlsrv_query($conmsql, "SELECT Prefijo, Numero, IdNumerador FROM vueNumeradores WHERE TipoComprobante = 'PR' AND PuntoVenta = '1' ");
$numeradores = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
$idNumerador = $numeradores['IdNumerador'];
$numero = $numeradores['Numero'] + 1;
$numero_digitos = strlen($numero);

for ($t = $numero_digitos; $t < 8; $t++) {
  $numero = '0' . $numero;
}


$query = sqlsrv_query($conmsql, "UPDATE Numeradores SET Numero = '" . $numero . "' WHERE IdNumerador = '" . $idNumerador . "'");
$insert = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
//? Fin de manejo de numeradores


//? Array datos Cabecera Presupuesto
$impTotal = 0;
$impIva = 0;

foreach ($_SESSION['carrito'] as $key => $value) {
  $impTotal += $value['subtotal'];
}


$impIva += $impTotal * (21 / 100);
$numero = $numeradores['Prefijo'] . '' . $numero;
$fecha = date("Y-d-m");
$insertORCab['Numero'] = $numero;
$insertORCab['Fecha'] = $fecha;
$insertORCab['IdCliente'] = $_SESSION['vendedor']['IdCliente'];
$insertORCab['Nombre'] = $_SESSION['vendedor']['RazonSocial'];
$insertORCab['Domicilio'] = $_SESSION['vendedor']['Domicilio'] . " - " . $_SESSION['vendedor']['Localidad'];
$insertORCab['Vendedor'] = $_SESSION['vendedor']['Vendedor'];
$insertORCab['Lista'] = $_SESSION['vendedor']['ListaPrecio'];
$insertORCab['Descuento'] = "";
$insertORCab['ImpTotal'] = $impTotal;
$insertORCab['ImpIva'] = $impIva;
$insertORCab['Totales'] = "1";
$insertORCab['Estado'] = "P";
$insertORCab['Notas'] = "";
$insertORCab['Detalle'] = "";
$insertORCab['Moneda'] = "PES";
$insertORCab['Terminal'] = "";
$insertORCab['Usuario'] = "APP";
$insertORCab['FechaAlta'] = $fecha;
$insertORCab['PorDescuento'] = $_SESSION['vendedor']['Descuento'];
$insertORCab['Tipo'] = "P";
$insertORCab['CUIT'] = $_SESSION['vendedor']['Cuit'];
$insertORCab['VendedorSalon'] = "0";
$insertORCab['PuntoVenta'] = "1";
$insertORCab['CondVenta'] = $_SESSION['vendedor']['CondPago'];
$insertORCab['CondIva'] = $_SESSION['vendedor']['CondIva'];
$insertORCab['CampoExtra1'] = "";
$insertORCab['Autorizado'] = "1";
$insertORCab['Solicitud'] = "";
$insertORCab['VendedorExtra'] = null;
$insertORCab['NroOCompra'] = "";
$insertORCab['Vencimiento'] = "";
$insertORCab['Control'] = "1";
$insertORCab['Asignado'] = "1";
$insertORCab['PorPercepIB'] = "0";
$insertORCab['Percepcion'] = "0";
$insertORCab['PorPercepIBArba'] = "0";
$insertORCab['PercepcionIBArba'] = "0";
$insertORCab['FechaEntrega'] = $fecha;
$insertORCab['Motivo'] = "";
$insertORCab['PorPercepIBMisiones'] = "0";
$insertORCab['PercepcionIBMisiones'] = "0";
$insertORCab['PorPercepcionIVA'] = "0";
$insertORCab['PercepcionIVA'] = "0"; //44
//$insertORCab['PorPercepcionIBTucuman'] = "0";
//$insertORCab['PercepcionIBTucuman'] = "0";

$tsql_callSP = "{call qryPresupuestosGrabarCabecera( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)}"; //44
$IdPR = '';
$params = array(
  array(&$IdPR, SQLSRV_PARAM_INOUT), //@IdPresupuesto	int OUTPUT
  array($insertORCab['Numero'], SQLSRV_PARAM_IN), //@Numero	varchar(12),
  array($insertORCab['Fecha'], SQLSRV_PARAM_IN), //@Fecha	smalldatetime,
  array($insertORCab['IdCliente'], SQLSRV_PARAM_IN), //@IdCliente	int,
  array($insertORCab['Nombre'], SQLSRV_PARAM_IN), //@Nombre	varchar(50),
  array($insertORCab['Domicilio'], SQLSRV_PARAM_IN),  //@Domicilio	varchar(50),
  array($insertORCab['Vendedor'], SQLSRV_PARAM_IN), //@Vendedor	int,
  array($insertORCab['Lista'], SQLSRV_PARAM_IN), //@Lista  varchar(5),
  array($insertORCab['Descuento'], SQLSRV_PARAM_IN), //@Descuento	money,
  array($insertORCab['ImpTotal'], SQLSRV_PARAM_IN), //@ImpTotal	money,//10
  array($insertORCab['ImpIva'], SQLSRV_PARAM_IN), //@ImpIva	money,
  array($insertORCab['Totales'], SQLSRV_PARAM_IN), //@Totales	bit,
  array($insertORCab['Estado'], SQLSRV_PARAM_IN), //@Estado	varchar(1),
  array($insertORCab['Notas'], SQLSRV_PARAM_IN), //@Notas		varchar(300),
  array($insertORCab['Detalle'], SQLSRV_PARAM_IN), //@Detalle	varchar(800),
  array($insertORCab['Moneda'], SQLSRV_PARAM_IN), //@Moneda	varchar(5),
  array($insertORCab['Terminal'], SQLSRV_PARAM_IN),  //@Terminal	smallint,
  array($insertORCab['Usuario'], SQLSRV_PARAM_IN), //@Usuario	varchar(10),
  array($insertORCab['FechaAlta'], SQLSRV_PARAM_IN), //@FechaAlta	datetime,
  array($insertORCab['PorDescuento'], SQLSRV_PARAM_IN), //@PorDescuento	smallmoney,
  array($insertORCab['Tipo'], SQLSRV_PARAM_IN), //@Tipo		varchar(1),
  array($insertORCab['CUIT'], SQLSRV_PARAM_IN), //@CUIT		varchar(13),
  array($insertORCab['VendedorSalon'], SQLSRV_PARAM_IN), //@VendedorSalon varchar(20), 
  array($insertORCab['PuntoVenta'], SQLSRV_PARAM_IN), //@PuntoVenta	varchar(20), 
  array($insertORCab['CondVenta'], SQLSRV_PARAM_IN), //@CondVenta	varchar(5),
  array($insertORCab['CondIva'], SQLSRV_PARAM_IN), //@CondIva	int,
  array($insertORCab['CampoExtra1'], SQLSRV_PARAM_IN), //@CampoExtra1	varchar(5), 
  array($insertORCab['Autorizado'], SQLSRV_PARAM_IN), //@Autorizado	bit,
  array($insertORCab['Solicitud'], SQLSRV_PARAM_IN), //@Solicitud	varchar(100),
  array($insertORCab['VendedorExtra'], SQLSRV_PARAM_IN), //@VendedorExtra int, 
  array($insertORCab['NroOCompra'], SQLSRV_PARAM_IN), //@NroOCompra varchar(12), 
  array($insertORCab['Vencimiento'], SQLSRV_PARAM_IN), //@Vencimiento datetime,
  array($insertORCab['Control'], SQLSRV_PARAM_IN), //@Control	bit,
  array($insertORCab['Asignado'], SQLSRV_PARAM_IN), //@Asignado	bit,  
  array($insertORCab['PorPercepIB'], SQLSRV_PARAM_IN), //@PorPercepIB smallmoney, 
  array($insertORCab['Percepcion'], SQLSRV_PARAM_IN), //@Percepcion  smallmoney, 
  array($insertORCab['PorPercepIBArba'], SQLSRV_PARAM_IN), //@PorPercepIBArba smallmoney, 
  array($insertORCab['PercepcionIBArba'], SQLSRV_PARAM_IN), //@PercepcionIBArba smallmoney, 
  array($insertORCab['FechaEntrega'], SQLSRV_PARAM_IN), //@FechaEntrega datetime,   
  array($insertORCab['Motivo'], SQLSRV_PARAM_IN), //@Motivo		varchar(5), 
  array($insertORCab['PorPercepIBMisiones'], SQLSRV_PARAM_IN), //@PorPercepIBMisiones smallmoney,  
  array($insertORCab['PercepcionIBMisiones'], SQLSRV_PARAM_IN), //@PercepcionIBMisiones smallmoney,  
  array($insertORCab['PorPercepcionIVA'], SQLSRV_PARAM_IN), //@PorPercepcionIVA	smallmoney,
  array($insertORCab['PercepcionIVA'], SQLSRV_PARAM_IN), //@PercepcionIVA	smallmoney, //44
  //array($insertORCab['PorPercepcionIBTucuman'], SQLSRV_PARAM_IN), //@PorPercepcionIBTucuman smallmoney,
  //array($insertORCab['PercepcionIBTucuman'], SQLSRV_PARAM_IN) //@PercepcionIBTucuman smallmoney //46


);
$cabecera = sqlsrv_query($conmsql, $tsql_callSP, $params);
if ($cabecera === false) {
  die(print_r(sqlsrv_errors(), true)); // Muestra el motivo real del error SQL
}
while ($IdPR <= 0) {
  sqlsrv_next_result($cabecera);
}
$idPresupuesto = $IdPR;

$_SESSION['idPresupuesto'] = $idPresupuesto;
//? Fin Cabecera Presupuesto

//?inicio de datos detalle presupuesto
$x = 1;
foreach ($_SESSION['carrito'] as $key => $value) {
  //$articulo = getArticuloById($dbh, $value['CodArticulo'], $value['DesModelo'], $value['DesColor'], $_SESSION['cliente']['ListaPrecio']);
  /*  $stmt = $dbh->prepare("select * from vueWebArticulosDisponibilidad 
LEFT JOIN  vueArticulos  ON vueWebArticulosDisponibilidad .CodArticulo = vueArticulos.CodArticulo WHERE vueWebArticulosDisponibilidad.CodBarra = '$value[CodBarra]'");

    $stmt->execute(); */
  $codBarra = $value['CodBarra'];
  $stmt = $dbh->prepare("SELECT * FROM vueWebArticulosDisponibilidad 
    LEFT JOIN vueArticulos ON vueWebArticulosDisponibilidad.CodArticulo = vueArticulos.CodArticulo 
    WHERE vueWebArticulosDisponibilidad.CodBarra = :codBarra");

  $stmt->bindParam(':codBarra', $codBarra, PDO::PARAM_STR);
  $stmt->execute();
  $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!empty($articulo['CantidadPresentacion'])) {
    $cantidadTotal = $value['cantidad'] * $articulo['CantidadPresentacion'];
  } else {
    $cantidadTotal = $value['cantidad'];
  }
  $insertDetalle['IdPresupuesto'] = $idPresupuesto;
  $insertDetalle['Renglon'] = $x;
  $insertDetalle['CodArticulo'] = $articulo['CodArticulo'];
  $insertDetalle['Cantidad'] = $cantidadTotal;
  $insertDetalle['ImpUnitario'] = $articulo['Importe'] / (1+($articulo['PorIva'] /100));
  $insertDetalle['PorIva'] = $articulo['PorIva'];
  $insertDetalle['Lista'] = $_SESSION['vendedor']['ListaPrecio'];
  $insertDetalle['Pendiente'] = $cantidadTotal;
  $insertDetalle['Notas'] = '';
  $insertDetalle['Deposito'] = '1'; //cordoba es 0 ///Parana es depo 1
  $insertDetalle['IdDespacho'] = '0';
  $insertDetalle['Unidad'] = $articulo['CantUnidades'];
  $insertDetalle['NotasExtras'] = '';
  $insertDetalle['Presentacion'] = $articulo['Presentacion'];
  $insertDetalle['ImpPresentacion'] = $articulo['CantidadPresentacion'];
  $insertDetalle['ImpDescuentoRenglon'] = '0.00';
  $insertDetalle['PorDescuentoRenglon'] = '0.0';
  $insertDetalle['UnitarioLista'] = $articulo['Importe']; //!preguntar si va Importe


  $tsql_callSP = "{call qryPresupuestosGrabarDetalle( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)}"; //18 params
  $params = array(
    array((int)$insertDetalle['IdPresupuesto'], SQLSRV_PARAM_IN),
    array($insertDetalle['Renglon'], SQLSRV_PARAM_IN),
    array($insertDetalle['CodArticulo'], SQLSRV_PARAM_IN),
    array($insertDetalle['Cantidad'], SQLSRV_PARAM_IN),
    array($insertDetalle['ImpUnitario'], SQLSRV_PARAM_IN),
    array($insertDetalle['PorIva'], SQLSRV_PARAM_IN),
    array($insertDetalle['Lista'], SQLSRV_PARAM_IN),
    array($insertDetalle['Pendiente'], SQLSRV_PARAM_IN),
    array($insertDetalle['Notas'], SQLSRV_PARAM_IN),
    array($insertDetalle['Deposito'], SQLSRV_PARAM_IN),
    array($insertDetalle['IdDespacho'], SQLSRV_PARAM_IN),
    array($insertDetalle['Unidad'], SQLSRV_PARAM_IN),
    array($insertDetalle['NotasExtras'], SQLSRV_PARAM_IN),
    array($insertDetalle['Presentacion'], SQLSRV_PARAM_IN),
    array((int)$insertDetalle['ImpPresentacion'], SQLSRV_PARAM_IN),
    array($insertDetalle['ImpDescuentoRenglon'], SQLSRV_PARAM_IN),
    array($insertDetalle['PorDescuentoRenglon'], SQLSRV_PARAM_IN),
    array($insertDetalle['UnitarioLista'], SQLSRV_PARAM_IN)
  );

  /* $stmt = sqlsrv_prepare($conmsql, $tsql_callSP, $params);

    sqlsrv_execute($stmt);

    sqlsrv_free_stmt($stmt); */
  try {
    $stmt = sqlsrv_prepare($conmsql, $tsql_callSP, $params);
    if ($stmt === false) {
      throw new Exception(print_r(sqlsrv_errors(), true));
    }

    if (!sqlsrv_execute($stmt)) {
      throw new Exception(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
  } catch (Exception $e) {
    // Podés registrar el error en el log y/o mostrar algo amigable
    echo ("Error SQLSRV: " . $e->getMessage());
    echo "❌ Error al ejecutar la consulta SQL.";
  }

  $renglonDetalle['IdPresupuesto'] = $idPresupuesto; //int
  $renglonDetalle['Renglon'] = $x; //tinyint x
  $renglonDetalle['CodArticulo'] = $articulo['CodArticulo']; //varchar(12)
  $renglonDetalle['Modelo'] = $articulo['Modelo']; //varchar(5)
  $renglonDetalle['Color'] = $articulo['Color']; //varchar(5)
  $renglonDetalle['Cantidad'] = $value['cantidad']; //float

  $tsql_callSP = "{call qryPresupuestosGrabarDetalleDefinicion( ?,?,?,?,?,?)}"; //6 params
  $params = array(
    array((int)$renglonDetalle['IdPresupuesto'], SQLSRV_PARAM_IN),
    array($renglonDetalle['Renglon'], SQLSRV_PARAM_IN), //TODO: ver que va en modelo y color
    array($renglonDetalle['CodArticulo'], SQLSRV_PARAM_IN),
    array($renglonDetalle['Modelo'], SQLSRV_PARAM_IN),
    array($renglonDetalle['Color'], SQLSRV_PARAM_IN),
    array($renglonDetalle['Cantidad'], SQLSRV_PARAM_IN),
  );

  /* $stmt = sqlsrv_prepare($conmsql, $tsql_callSP, $params);
    sqlsrv_execute($stmt);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true)); 
    sqlsrv_free_stmt($stmt); */
  try {
    $stmt = sqlsrv_prepare($conmsql, $tsql_callSP, $params);
    if ($stmt === false) {
      throw new Exception(print_r(sqlsrv_errors(), true));
    }

    if (!sqlsrv_execute($stmt)) {
      throw new Exception(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
  } catch (Exception $e) {
    echo "❌ Error SQL: " . $e->getMessage();
  }

  $x++;
}

// Enviar email con detalle del pedido antes de limpiar el carrito
try {
  // Construir el HTML con los productos del carrito
  $productosHTML = '';
  $totalGeneral = 0;

  foreach ($_SESSION['carrito'] as $key => $value) {
    $subtotal = $value['subtotal'];
    $totalGeneral += $subtotal;
    $modelo = isset($value['ModDescripcion']) ? $value['ModDescripcion'] : '-';
    $color = isset($value['ColDescripcion']) ? $value['ColDescripcion'] : '-';
    $productosHTML .= '
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eeeeee;">' . htmlspecialchars($value['Descripcion']) . '</td>
            <td style="padding: 10px; border-bottom: 1px solid #eeeeee;">' . htmlspecialchars($modelo) . '</td>
            <td style="padding: 10px; border-bottom: 1px solid #eeeeee;">' . htmlspecialchars($color) . '</td>
            <td style="padding: 10px; border-bottom: 1px solid #eeeeee; text-align: center;">' . htmlspecialchars($value['cantidad']) . '</td>
            <td style="padding: 10px; border-bottom: 1px solid #eeeeee; text-align: right;">$' . number_format($subtotal, 2, ',', '.') . '</td>
        </tr>';
  }

  $mensaje = '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Reserva de Productos - Presupuesto ' . htmlspecialchars($numero) . '</title>
      </head>
      <body style="font-family: Arial, sans-serif; font-size: 14px; color: #333333; background-color: #f6f6f6; padding: 20px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f6f6f6">
          <tr>
            <td align="center">
              <table width="600" cellpadding="20" cellspacing="0" border="0" bgcolor="#ffffff" style="border: 1px solid #dddddd;">
                <tr>
                  <td>
                    <h2 style="color: #333333; margin-top: 0;">Nueva Reserva de Productos</h2>
                    
                    <p style="font-size: 14px; line-height: 1.5;">
                      Se ha generado un nuevo presupuesto con los siguientes datos:
                    </p>
                    
                    <table width="100%" cellpadding="5" cellspacing="0" border="0" style="margin: 20px 0;">
                      <tr>
                        <td style="padding: 5px;"><strong>Presupuesto N°:</strong></td>
                        <td style="padding: 5px;">' . htmlspecialchars($numero) . '</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px;"><strong>Cliente:</strong></td>
                        <td style="padding: 5px;">' . htmlspecialchars($_SESSION['vendedor']['RazonSocial']) . '</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px;"><strong>CUIT:</strong></td>
                        <td style="padding: 5px;">' . htmlspecialchars($_SESSION['vendedor']['Cuit']) . '</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px;"><strong>Domicilio:</strong></td>
                        <td style="padding: 5px;">' . htmlspecialchars($_SESSION['vendedor']['Domicilio']) . ' - ' . htmlspecialchars($_SESSION['vendedor']['Localidad']) . '</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px;"><strong>Fecha:</strong></td>
                        <td style="padding: 5px;">' . date('d/m/Y H:i') . '</td>
                      </tr>
                    </table>
                    
                    <h3 style="color: #333333; margin-top: 30px; margin-bottom: 10px;">Productos Solicitados:</h3>
                    
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 10px 0;">
                      <thead>
                        <tr style="background-color: #f0f0f0;">
                          <th style="padding: 10px; text-align: left; border-bottom: 2px solid #dddddd;">Producto</th>
                          <th style="padding: 10px; text-align: left; border-bottom: 2px solid #dddddd;">Modelo</th>
                          <th style="padding: 10px; text-align: left; border-bottom: 2px solid #dddddd;">Color</th>
                          <th style="padding: 10px; text-align: center; border-bottom: 2px solid #dddddd;">Cantidad</th>
                          <th style="padding: 10px; text-align: right; border-bottom: 2px solid #dddddd;">Subtotal</th>
                        </tr>
                      </thead>
                      <tbody>
                        ' . $productosHTML . '
                        <tr>
                          <td colspan="4" style="padding: 15px 10px; text-align: right; border-top: 2px solid #333333;"><strong>Total:</strong></td>
                          <td style="padding: 15px 10px; text-align: right; border-top: 2px solid #333333;"><strong>$' . number_format($totalGeneral, 2, ',', '.') . '</strong></td>
                        </tr>
                      </tbody>
                    </table>
                    
                    <p style="font-size: 14px; line-height: 1.5; margin-top: 30px;">
                      Este presupuesto ya fue registrado en el sistema y está en proceso de revisión.
                    </p>
                    
                    <p style="font-size: 14px; margin-bottom: 0; margin-top: 30px;">
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

  // Configurar y enviar el email
  $mail = new PHPMailer(true);
  $mail->SMTPDebug = 0; // Cambiar a 0 en producción
  $mail->isSMTP();
  $mail->Host       = 'mail.tiendajeancartierhogar.com.ar';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'presupuesto@tiendajeancartierhogar.com.ar';
  $mail->Password   = 'FaruSae0ujoh';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port       = 465;

  $mail->SMTPOptions = [
    'ssl' => [
      'verify_peer'       => false,
      'verify_peer_name'  => false,
      'allow_self_signed' => true
    ]
  ];

  $mail->setFrom('presupuesto@tiendajeancartierhogar.com.ar', 'Tienda Jean Cartier Hogar');

  // Enviar a la dirección de contacto y al cliente
  //$mail->addAddress('marcos.kukuchka@gmail.com');
  $mail->addAddress('jeancartierhogarparana@gmail.com');


  $mail->isHTML(true);
  $mail->Subject = 'Reserva Web - Presupuesto ' . $numero;
  $mail->Body    = $mensaje;
  $mail->AltBody = 'Nueva reserva - Presupuesto ' . $numero . ' - Cliente: ' . $_SESSION['vendedor']['RazonSocial'];

  $emailEnviado = $mail->send();
} catch (Exception $e) {
  // Log del error pero no detener el proceso
  //error_log("Error al enviar email: {$mail->ErrorInfo}");
}

// Limpiar el carrito después de finalizar el pedido exitosamente
unset($_SESSION['carrito']);

$data = array();

/* $data['insertORCab'] = $insertORCab;
$data['renglonDetalle'] = $renglonDetalle;
$data['insertDetalle'] = $insertDetalle; */
$data['estado'] = 1;
$data['mensaje'] = "Pedido finalizado correctamente";
echo json_encode($data);
