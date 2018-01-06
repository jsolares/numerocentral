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

    Upload a campaign file to the dialer
	It contains a number to be dialed on each line
*/

include '../db.inc.php';
include '../prepend.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select accountcode from users where uid = $userid" );
$db -> next_record();

$accountcode = $db -> f ("accountcode");

$file_name = $_FILES['campaign']['tmp_name'];
$nombre    = sanitize($_POST['nombre']);
$fecha_ini = sanitize($_POST['fechainicio']);

$db -> query ( "insert into marcador_event values ( null, '$accountcode', '$nombre', '$fecha_ini', 0)");
$db -> query ( "select last_insert_id() as id");

$db -> next_record();
$id = $db -> f ("id");

print "$id<br/>";

if ( $file_handle = fopen($file_name, "r")) {
	while (!feof($file_handle)) {
		$tmp = trim(fgets($file_handle));
		if (strlen($tmp) > 0 ) {
			$numero = sanitize($tmp);
			
			$db -> query ( "insert into marcador_event_detail values ( null, $id, '$numero', 0, null )" );
		}
	}
}

Header('Location:https://www.numerocentral.com/marcador/');

function sanitize($data) {
	// remove whitespaces (not a must though)
	$data = trim($data);

	// apply stripslashes if magic_quotes_gpc is enabled
	if(get_magic_quotes_gpc()) {
		$data = stripslashes($data);
	}

	// a mySQL connection is required before using this function
	$data = mysql_real_escape_string($data);

	return $data;
}
