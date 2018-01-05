<?php
/*
    factura-upload.php
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

    Upload invoice from admin area to specified id_pago, with accountcode and fecha_aplica in post.
*/

include 'db.inc.php';
include 'prepend_admin.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select accountcode from users where uid = $userid" );
$db -> next_record();

$file = $_FILES['facturapdf']['tmp_name'];

getpost_ifset( array("accountcode", "id_pago", "fecha_aplica") );

$pago = mysql_real_escape_string($id_pago);
$db -> query("select factura from pagos where id_pago=$pago");
$db -> next_record();
$factura = $db -> f ("factura");

if ($factura != '') {
$path = "/var/spool/asterisk/facturas/";

$eem = explode('-', $fecha_aplica);
$mes_aplica = $eem[1];
$ano_aplica = $eem[0];
$fecha_aplica = $mes_aplica . '/' . $ano_aplica;

if ( $mes_aplica == '00' && $ano_aplica == '0000' ) {
$fecha_aplica = '';
}

$mes_aplica = $mes_aplica + 1 - 1;

if ( $ano_aplica != '' && $mes_aplica != '') {
	$path .= "$ano_aplica/$mes_aplica/$factura.pdf";
	$m = trim(exec('file -bi '.escapeshellarg($file)));
	move_uploaded_file($file, $path);
} else {
	Header('Location:https://www.numerocentral.com/admin.php?pagos=1');
}
}

Header('Location:https://www.numerocentral.com/admin.php?pagos=1');
?>
