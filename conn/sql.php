<?php

function getArticuloById($dbh, $codArticulo, $DesModelo = "", $DesColor = "", $listaPrecio)
{
    $sql = "SELECT Art.CodArticulo, Art.Descripcion, Art.AreaFiscal, TIVA.Descripcion AS DesIVA, TIVA.Importe AS PorIva, AP.Lista, 
    TLPR.Descripcion AS DesListaPrecio, Art.Unidad, TUNI.Descripcion AS DesUnidad, ISNULL(TUNI.Importe, 1) AS ImpUnidad,
    Art.Presentacion, TPRE.Descripcion AS DesPresentacion, TPRE.Importe AS ImpPresentacion, AP.Importe AS ImporteUnitario, 
    Art.Rubro, TRUB.Descripcion AS DesRubro, Art.Marca, TMAR.Descripcion AS DesMarca, Art.TipoArticulo, TTIP.Descripcion AS DesTipoArticulo,
    ArtDef.Modelo, TMOD.Descripcion AS DesModelo, ArtDef.Color, TCOL.Descripcion AS DesColor, 
    ROUND(AP.Importe + (AP.Importe * TIVA.Importe / 100), 2) AS ImporteBruto 
    FROM Articulos AS Art
    LEFT JOIN ArticulosPrecio AS AP ON Art.CodArticulo = AP.CodArticulo
    LEFT JOIN Tablas AS TIVA ON TIVA.IdTabla = 'AREASFISCALES' AND TIVA.IdCodigo = Art.AreaFiscal
    LEFT JOIN Tablas AS TLPR ON TLPR.IdTabla = 'LISTAPRECIO' AND TLPR.IdCodigo = AP.Lista
    LEFT JOIN Tablas AS TUNI ON TUNI.IdTabla = 'UNIDADES' AND TUNI.IdCodigo = Art.Unidad
    LEFT JOIN Tablas AS TPRE ON TPRE.IdTabla = 'PRESENTACION' AND TPRE.IdCodigo = Art.Presentacion
    LEFT JOIN Tablas AS TRUB ON TRUB.IdTabla = 'RUBROS' AND TRUB.IdCodigo = Art.Rubro 
    LEFT JOIN Tablas AS TMAR ON TMAR.IdTabla = 'MARCAS' AND TMAR.IdCodigo = Art.Marca 
    LEFT JOIN Tablas AS TTIP ON TTIP.IdTabla = 'TIPOARTICULO' AND TTIP.IdCodigo = Art.TipoArticulo 
    LEFT JOIN ArticulosDefinicion AS ArtDef ON ArtDef.CodArticulo = Art.CodArticulo AND ArtDef.Suspendido = 0
    LEFT JOIN Tablas AS TMOD ON TMOD.IdTabla = 'ARTMODELOS' AND TMOD.IdCodigo = ArtDef.Modelo
    LEFT JOIN Tablas AS TCOL ON TCOL.IdTabla = 'ARTCOLORES' AND TCOL.IdCodigo = ArtDef.Color
    WHERE Art.Suspendido = 0 AND AP.Importe > 0 AND AP.Lista = :listaPrecio AND Art.CodArticulo = :codArticulo";

    // Agregar condiciones solo si los parámetros no están vacíos
    if (!empty($DesModelo)) {
        $sql .= " AND TMOD.Descripcion = :desModelo";
    }

    if (!empty($DesColor)) {
        $sql .= " AND TCOL.Descripcion = :desColor";
    }

    $stmt = $dbh->prepare($sql);

    // Bind de parámetros obligatorios
    $stmt->bindParam(':listaPrecio', $listaPrecio);
    $stmt->bindParam(':codArticulo', $codArticulo);

    // Bind de parámetros opcionales solo si no están vacíos
    if (!empty($DesModelo)) {
        $stmt->bindParam(':desModelo', $DesModelo);
    }

    if (!empty($DesColor)) {
        $stmt->bindParam(':desColor', $DesColor);
    }

    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function getLoginVendedor($dbh, $email, $pass)
{
    $sql = "SELECT * FROM webClientes WHERE Email = :email AND Clave = :pass AND Suspendido = 0";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':pass', $pass);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getVendedorClientes($dbh, $vendedor)
{
    $sql = "SELECT * FROM VueClientes WHERE Vendedor = :vendedor AND Activo = '1'";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':vendedor', $vendedor);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getCliente($dbh, $vendedor, $cliente)
{
    $sql = "SELECT * FROM VueClientes WHERE Vendedor = :vendedor AND IdCliente = :cliente AND Activo = '1'";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':vendedor', $vendedor);
    $stmt->bindParam(':cliente', $cliente);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getPresupuestoCliente($dbh, $clienteId)
{
    $stmt = $dbh->prepare("SELECT * FROM vuePresupuestosImpPendiente WHERE IdCliente = :clienteId AND Tipo = 'P' AND Estado = 'P';");
    $stmt->bindParam(':clienteId', $clienteId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getFacturasCliente($dbh, $clienteId)
{
    $stmt = $dbh->prepare("SELECT  Fecha, Tipo, Letra, Numero, ImpTotal, Saldo FROM vueFacturasSaldoCtaCte WHERE Saldo <> 0 AND IdCliente = :clienteId;");
    $stmt->bindParam(':clienteId', $clienteId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
