<?php
/*
    contacts.php
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

    prints a csv file with the contacts for the authenticated user
*/

include 'db.inc.php';
include 'prepend.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysqli", "localhost", "numerocentral", "root", "");
$db -> query ( "select accountcode, id_plan, supervisa from users where uid = $userid" );
$db -> next_record();
$accountcode = $db -> f ("accountcode");
$planid = $db -> f ("id_plan");
$supervisa = $db -> f ("supervisa");

if ( $planid == 13 ) {
	$accountcode = substr($accountcode,0,8);
}

if ( $planid != 12 ) {
	$db -> query ( "select replace(name,',',' ') as name, replace(number,'-','') as number, group_name, concat('\"',email,'\"') as email, address from contacts c, groups g where c.id_group = g.id and g.accountcode = '$accountcode' order by name");
} else {
	$db -> query ( "select replace(name,',',' ') as name, replace(number,'-','') as number, group_name, concat('\"',email,'\"') as email, address from contacts c, groups g where c.id_group = g.id and g.accountcode in ($supervisa) order by name");
}

$filename = "contactos_$accountcode.csv";
// Fix IE bug [0]

header('Vary: User-Agent');
header("Content-Type: text/csv"); 
header("Content-Disposition: attachment; filename=\"" . $filename. "\";"); 
header("Pragma: cache"); 
header("Expires: 0"); 
header("Cache-Control: private");

print "Nombre,Numero,Grupo,E-Mail,Direccion\n";
while ($db -> next_record()) {
	$name = $db ->f ("name");
	$number = $db -> f ("number");
	$group = $db -> f ("group_name");
	$email = $db -> f ("email");
	$addr = $db -> f ("address");
	print "$name,$number,$group,$email,$addr\n";
}

exit();
?>
