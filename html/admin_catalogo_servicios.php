<?php
/*
    admin_catalogo_servicios.php
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

    print a catalog of services provided by id, detail on invoice, abbreviation and price.
	meant to be added to a catalog used by electronic invoicing in Guatemala.
*/

include 'db.inc.php';
include 'prepend_admin.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select id, detallefac, abreviacion, 'N', 'N', price, 1 from plans;");

header('Vary: User-Agent');
header("Content-Type: text/plain"); 
header("Content-Disposition: attachment; filename=\"catalogo_servicios.txt\";"); 
header("Pragma: cache"); 
header("Expires: 0"); 
header("Cache-Control: private");

while ( $db -> next_record() ) {
	$id = $db -> f ("id");
	$desc = $db -> f ("detallefac");
	$abrevia = $db -> f ("abreviacion");
	$precio = $db -> f ("price");

	print "$id|$desc|$abrevia|S|N|$precio|1\r\n";
}

exit();

?>
