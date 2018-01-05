<?php
/*
    admin_catalogo_clientes.php
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

    print a catalog of customers by nit (tax identifying number) their id and email.
	Meant to add a catalog of customers to a electronic invoicing for tax purposes in
	Guatemala.
*/

include 'db.inc.php';
include 'prepend_admin.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select email, nit, uid from users where nit <> \"\";");

header('Vary: User-Agent');
header("Content-Type: text/plain"); 
header("Content-Disposition: attachment; filename=\"catalogo_clientes.txt\";"); 
header("Pragma: cache"); 
header("Expires: 0"); 
header("Cache-Control: private");

while ( $db -> next_record() ) {
	$nit = $db -> f ("nit");
	$email = $db -> f ("email");
	$id = $db -> f ("uid");

	$nit = str_replace('-','',$nit);

	print "$nit|$id|$email\r\n";
}

exit();

?>
