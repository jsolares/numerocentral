<?php
/*
    fax.php
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

    Download specified fax file for authenticated user and given $file
*/

include 'db.inc.php';
include 'prepend.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select accountcode from users where uid = $userid" );
$db -> next_record();
$accountcode = $db -> f ("accountcode");

getpost_ifset(("file"));
$path = "/var/spool/asterisk/fax";

$db -> query ( "select count(*) as qty from callrecords_table where accountcode = '$accountcode' and userfield like '%:$file%'");
$db -> next_record();
$qty = $db -> f ("qty");

if ( file_exists("$path/$file.tif") && $qty == 1 ) {
header("Content-Type: application/pdf"); 
header("Content-Disposition: atachment; filename=fax.pdf"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
system("/usr/bin/tiff2pdf $path/$file.tif");
} else {
	print "Error $qty";
}
exit();
?>
