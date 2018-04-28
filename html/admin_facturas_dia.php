<?php
/*
    admin_facturas_dia.php
    (C) 2018 by Jose Solares (jsolares@codevoz.com)

    This file is part of numerocentral.

    numerocentral is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    NumeroCentral is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with NumeroCentral.  If not, see <http://www.gnu.org/licenses/>.

    output a downloadble file meant to be used for tax invoicing in Guatemala, for an specific date.
*/

include 'db.inc.php';
include 'prepend_admin.php';

date_default_timezone_set('America/Guatemala');

$userid = $user->requireAuthentication( "displayLogin" );
getpost_ifset(array("fecha"));

$fecha_db = trim($fecha);
$fecha_db = mysqli_real_escape_string($fecha_db);
$fecha = $fecha_db;
$mes = array( 1 => "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" );

$db = new DB_Sql("mysqli", "localhost", "numerocentral", "root", "");
$db -> query ( "select id_plan, pagos.accountcode, date(fecha_pago) as fecha_pago, date(fecha_aplica) as fecha_aplica, forma_pago, banco, documento, pagos.monto, pagos.minutos, motivo_pago, nit from pagos, users where users.accountcode = pagos.accountcode and date(fecha_ingreso) = '$fecha' and facturar =1 and nit <> \"\" and banco <> 12 and factura not regexp '^[0-9]+$';");

header('Vary: User-Agent');
header("Content-Type: text/plain"); 
header("Content-Disposition: attachment; filename=\"facturas_$fecha.txt\";"); 
header("Pragma: cache"); 
header("Expires: 0"); 
header("Cache-Control: private");

$fecha = date("Ymd");

while ( $db -> next_record() ) {
	$nit = $db -> f ("nit");
	$monto = $db -> f ("monto");
	$minutos = $db -> f ("minutos");
	$documento = $db -> f ("documento");
	$motivo_pago = $db -> f ("motivo_pago");
	$account = $db -> f ("accountcode" );
	$fecha_aplica = explode('-', $db -> f ("fecha_aplica"));
	if ( $motivo_pago == 0 ) {
		$desc = "$account - Compra de $minutos minutos de saldo, Documento $documento de " . $fecha_aplica[2] . " de " . $mes[intval($fecha_aplica[1])] . " del " . $fecha_aplica[0] . ".";
	} else if ( $motivo_pago == 1 ) {
		$desc = "$account - Pago mensualidad de " . $mes[intval($fecha_aplica[1])] . " del " . $fecha_aplica[0]. ", Documento $documento.";
	} else if ( $motivo_pago == 2 ) {
		$desc = "$account - Compra de Q. $minutos de saldo. Documento $documento de " . $fecha_aplica[2] . " de " . $mes[intval($fecha_aplica[1])] . " del " . $fecha_aplica[0] . ".";
	} else if ( $motivo_pago == 3 ) {
		$desc = "$account - Pago mensualidad de" . $mes[intval($fecha_aplica[1])] . " del " . $fecha_aplica[0] . ".";
	}	
	$prod = $db -> f ("id_plan");

	$nit = str_replace('-','',$nit);
	$siniva = number_format( $monto / 1.12, 2, ".", "" );
	$iva = number_format( $monto - $siniva, 2, ".", "" );

	print "1|$fecha|FACE|$nit|1|1||S|1|S||\r\n";
	print "2|1|1|$monto|0|0|$monto|0|$siniva|$iva|0|$monto|$prod|$desc\r\n";
	print "4|$monto|0|0|$siniva|$iva|0|$monto|0|0|1|0\r\n";
}

exit();
?>
