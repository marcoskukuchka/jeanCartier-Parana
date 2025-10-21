<?php

require_once './conn/loader.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['vendedor']) || empty($_SESSION['vendedor'])) {
    header('Location: ./login.php');
    exit;
}

pr($_SESSION);
//? Manejo de numeradores
$query = sqlsrv_query($conmsql, "SELECT Prefijo, Numero, IdNumerador FROM vueNumeradores WHERE TipoComprobante = 'PR' AND PuntoVenta = '1' ");
$numeradores = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
$idNumerador = $numeradores['IdNumerador'];
$numero = $numeradores['Numero'] + 1;
$numero_digitos = strlen($numero);

for ($t = $numero_digitos; $t < 8; $t++) {
    $numero = '0' . $numero;
}


/* $query = sqlsrv_query($conmsql, "UPDATE Numeradores SET Numero = '" . $numero . "' WHERE IdNumerador = '" . $idNumerador . "'");
$insert = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC); */
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
$insertORCab['Domicilio'] = $_SESSION['vendedor']['Domicilio'] . " - " . $_SESSION['cliente']['Localidad'];
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
$insertORCab['PercepcionIVA'] = "0";
$insertORCab['PorPercepcionIBTucuman'] = "0";
$insertORCab['PercepcionIBTucuman'] = "0";

$tsql_callSP = "{call qryPresupuestosGrabarCabecera( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)}"; //46
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
    array($insertORCab['ImpTotal'], SQLSRV_PARAM_IN), //@ImpTotal	money,
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
    array($insertORCab['PorPercepcionIBTucuman'], SQLSRV_PARAM_IN), //@PorPercepcionIBTucuman smallmoney,
    array($insertORCab['PercepcionIBTucuman'], SQLSRV_PARAM_IN) //@PercepcionIBTucuman smallmoney //46


);
/* $cabecera = sqlsrv_query($conmsql, $tsql_callSP, $params);

while ($IdPR <= 0) {
    sqlsrv_next_result($cabecera);
}
$idPresupuesto = $IdPR; */

//? Fin Cabecera Presupuesto

//?inicio de datos detalle presupuesto
echo "<h2>Detalle</h2>";
$x = 1;
foreach ($_SESSION['carrito'] as $key => $value) {
    //$articulo = getArticuloById($dbh, $value['CodArticulo'], $value['DesModelo'], $value['DesColor'], $_SESSION['cliente']['ListaPrecio']);
    $stmt = $dbh->prepare("select * from vueWebArticulosDisponibilidad 
LEFT JOIN  vueArticulos  ON vueWebArticulosDisponibilidad .CodArticulo = vueArticulos.CodArticulo WHERE vueWebArticulosDisponibilidad.CodBarra = '4021-1-4'");

    $stmt->execute();
    $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
    pr($articulo);
    if (!empty($articulo['CantidadPresentacion'])) {
        $cantidadTotal = $value['cantidad'] * $articulo['CantidadPresentacion'];
    } else {
        $cantidadTotal = $value['cantidad'];
    }
    $insertDetalle['IdPresupuesto'] = $idPresupuesto;
    $insertDetalle['Renglon'] = $x;
    $insertDetalle['CodArticulo'] = $articulo['CodArticulo'];
    $insertDetalle['Cantidad'] = $cantidadTotal;
    $insertDetalle['ImpUnitario'] = $articulo['Importe'];
    $insertDetalle['PorIva'] = $articulo['PorIva'];
    $insertDetalle['Lista'] = $_SESSION['vendedor']['ListaPrecio'];
    $insertDetalle['Pendiente'] = $cantidadTotal;
    $insertDetalle['Notas'] = '';
    $insertDetalle['Deposito'] = '0'; //cordoba es 0 ///Parana es depo 1
    $insertDetalle['IdDespacho'] = '0';
    $insertDetalle['Unidad'] = $articulo['Presentacion']; 
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
    $renglonDetalle['IdPresupuesto'] = $idPresupuesto; //int
    $renglonDetalle['Renglon'] = 1; //tinyint x
    $renglonDetalle['CodArticulo'] = $articulo['CodArticulo']; //varchar(12)
    $renglonDetalle['Modelo'] = $articulo['Modelo']; //varchar(5)
    $renglonDetalle['Color'] = $articulo['Color']; //varchar(5)
    $renglonDetalle['Cantidad'] = $value['cantidad']; //float

    $tsql_callSP = "{call qryPresupuestosGrabarDetalleDefinicion( ?,?,?,?,?,?)}"; //6 params
    $params = array(
        array((int)$renglonDetalle['IdPresupuesto'], SQLSRV_PARAM_IN),
        array($renglonDetalle['Renglon'], SQLSRV_PARAM_IN),
        array($renglonDetalle['CodArticulo'], SQLSRV_PARAM_IN),
        array($renglonDetalle['Modelo'], SQLSRV_PARAM_IN),
        array($renglonDetalle['Color'], SQLSRV_PARAM_IN),
        array($renglonDetalle['Cantidad'], SQLSRV_PARAM_IN)
    );

    /* $stmt = sqlsrv_prepare($conmsql, $tsql_callSP, $params);
    sqlsrv_execute($stmt);
    sqlsrv_free_stmt($stmt); */

    $x++;
    pr($insertDetalle);
    pr($renglonDetalle);
}
