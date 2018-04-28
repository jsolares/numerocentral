<?php
/*
    fileproducto-wav.php
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

    Same as fileproducto.php but download wav audio instead.
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

getpost_ifset(("file"));
$path = "/var/lib/asterisk/sounds/ivr";

if ( $planid == 13 ) {
	$accountcode = substr($accountcode,0,8);
}

if ( file_exists("$path/$file.wav") ) {

$filename = str_replace(".","",$file) . ".wav";

header('Vary: User-Agent');
header("Content-Type: audio/wav"); 
header("Content-Disposition: attachment; filename=\"" . $filename. "\";"); 
header("Content-Length: ".filesize("$path/$file.wav")); 
header("Pragma: cache"); 
header("Expires: 0"); 
header("Cache-Control: private");
header("Content-Transfer-Encoding: binary\n");

$fp=fopen("$path/$file.wav","r"); 
print fread($fp,filesize("$path/$file.wav")); 
fclose($fp); 
}

exit();
?>
