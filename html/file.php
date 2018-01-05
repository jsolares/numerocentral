<?php
/*
    file.php
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

    download mp3 of call file for authenticated user that is allowed to see that call.
*/

include 'db.inc.php';
include 'prepend.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select accountcode, id_plan, supervisa from users where uid = $userid" );
$db -> next_record();
$accountcode = $db -> f ("accountcode");
$planid = $db -> f ("id_plan");
$supervisa = $db -> f ("supervisa");

getpost_ifset(("file"));
$path = "/var/spool/asterisk/monitor";

if ( $planid == 13 ) {
	$accountcode = substr($accountcode,0,8);
}

if ( $planid != 12 ) {
	$db -> query ( "select count(*) as qty from callrecords_table where accountcode = '$accountcode' and userfield like '%:$file%'");
} else {
	$db -> query ( "select count(*) as qty from callrecords_table where accountcode in ($supervisa) and userfield like '%:$file%'");
}
$db -> next_record();
$qty = $db -> f ("qty");

if ( file_exists("$path/$file.mp3") && $qty > 0 ) {

$filename = "$file.mp3";
// Fix IE bug [0]

$filename = str_replace(".","",$file) . ".mp3";

header('Vary: User-Agent');
header("Content-Type: audio/mpeg3"); 
header("Content-Disposition: attachment; filename=\"" . $filename. "\";"); 
header("Content-Length: ".filesize("$path/$file.mp3")); 
header("Pragma: cache"); 
header("Expires: 0"); 
header("Cache-Control: private");
header("Content-Transfer-Encoding: binary\n");

$fp=fopen("$path/$file.mp3","r"); 
print fread($fp,filesize("$path/$file.mp3")); 
fclose($fp); 
}
exit();
?>
