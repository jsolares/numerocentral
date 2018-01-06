<?php
/*
    marcador-upload.php
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

    Upload audio file for dialer campaign and use SoX to conver it to 8000khz mono
*/

include '../db.inc.php';
include '../prepend.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select accountcode from users where uid = $userid" );
$db -> next_record();

$accountcode = $db -> f ("accountcode");

$db -> query ( "select * from marcador_audio where accountcode='$accountcode'");

if ( $db -> next_record() ) {
	$amd = $db -> f ("amd");
	$hin = $db -> f ("hora_inicio");
	$hfn = $db -> f ("hora_fin");
} else {
	$amd = 0;
	$hin = "07:00:00";
	$hfn = "18:59:59";
}

$path = "/var/lib/asterisk/sounds/marcador/$accountcode-audio.wav";

$file = $_FILES['audio']['tmp_name'];

$sox  = "sox $file -c 1 $path rate -l 8000";

print $sox;

print "<br/>";
system($sox);

unlink ("/var/lib/asterisk/sounds/marcador/$accountcode-audio.mp3");

$db -> query ( "delete from marcador_audio where accountcode='$accountcode'");
$db -> query ( "insert into marcador_audio values ( null, '$accountcode', '$accountcode-audio', '$hin', '$hfn', $amd)");

Header('Location:https://www.numerocentral.com/marcador/');
?>
