<?php
/*
    admin_facturas.php
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

    print a catalog of expected tax invoices in Guatemala
*/

include 'db.inc.php';
include 'prepend_admin.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select uid, email, users.name, accountcode, nit, monto, plans.detallefac as descr, id_plan from users, plans where nit <> \"\" and id_plan = plans.id");

$fecha = date("Ymd");

header('Vary: User-Agent');
header("Content-Type: text/plain"); 
header("Content-Disposition: attachment; filename=\"facturas_$fecha.txt\";"); 
header("Pragma: cache"); 
header("Expires: 0"); 
header("Cache-Control: private");

while ( $db -> next_record() ) {
	$nit = $db -> f ("nit");
	$monto = $db -> f ("monto");
	$desc = $db -> f ("accountcode") . " - Plan " . $db -> f ("descr");
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
