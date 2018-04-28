<?php
/*
    ivroo-upload.php
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

    Upload IVR Audio file for when out of order (out of time set)
*/

include 'db.inc.php';
include 'prepend.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysqli", "localhost", "numerocentral", "root", "");
$db -> query ( "select accountcode from users where uid = $userid" );
$db -> next_record();

$accountcode = $db -> f ("accountcode");

$path = "/var/lib/asterisk/sounds/ivroo/$accountcode-menu.wav";

$file = $_FILES['ivraudio']['tmp_name'];

$sox  = "sox $file -c 1 $path rate -l 8000";

system($sox);

unlink ("/var/lib/asterisk/sounds/ivroo/$accountcode-menu.mp3");

$db -> query ( "delete from ivr_ooaudio where accountcode='$accountcode'");
$db -> query ( "insert into ivr_ooaudio values ( null, '$accountcode', '$accountcode-menu')");

Header('Location:https://www.numerocentral.com/user.php?ivr=1');
?>
