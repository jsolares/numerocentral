<?php
/*
    ivr-extension-upload.php
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

    Upload audio for extension ivr.
*/

include 'db.inc.php';
include 'prepend.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db -> query ( "select accountcode from users where uid = $userid" );
$db -> next_record();

$accountcode = $db -> f ("accountcode");
getpost_ifset( array("extension", "numero") );

if ( $extension && $numero ) {
	if ( strlen($extension) != 4 ) {
		Header('Location:https://www.numerocentral.com/user.php?ivr=1');
		exit;
	}
	if ( strlen($numero) != 8 ) {
		Header('Location:https://www.numerocentral.com/user.php?ivr=1');
		exit;
	}
	$db -> query ("select count(*) as qty from ivr_option where accountcode='$accountcode' and keypad='$extension'");
	$db -> next_record();

	if ( $db -> f ("qty") > 0 ) {
		Header('Location:https://www.numerocentral.com/user.php?ivr=1');
		exit;
	}

	$db -> query ("select count(*) as qty from ivr_option where accountcode='$accountcode' and keypad > 10");
	$db -> next_record();
	if ( $db -> f ("qty") >= 30 ) {
		Header('Location:https://www.numerocentral.com/user.php?ivr=1');
		exit;
	}

	$path = "/var/lib/asterisk/sounds/ivr/$accountcode-$extension.wav";
	$file = $_FILES['extenaudio']['tmp_name'];
	if ( file_exists($file) ) {
		$sox  = "sox $file -c 1 $path rate -l 8000";
		system($sox);
	} else {
		Header('Location:https://www.numerocentral.com/user.php?ivr=1');
		exit;
	}

	if ( file_exists($path) ) {
		$db -> query ("insert into ivr_option values ( null, '$accountcode', $extension, $numero )");
		Header('Location:https://www.numerocentral.com/user.php?ivr=1');
	} else {
		Header('Location:https://www.numerocentral.com/user.php?ivr=1');
	}
}
Header('Location:https://www.numerocentral.com/user.php?ivr=1');
?>
