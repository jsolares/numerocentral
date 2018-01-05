<?php
/*
    logout_admin.php
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

    Do a logout and redirect to admin page which shows login when not authenticated.
*/
include 'prepend_admin.php';

$userid = $user->requireAuthentication( "" );

if ( $userid === false ) {
} else {
$user -> logout();
}

if( ini_get( "register_globals" )) {
	$GLOBALS["patUserData"] = array();
	if(!isset($_SESSION))
	{
	ini_set('session.use_trans_sid', 0);
	ini_set('session.use_only_cookies', 1);
	session_start();
	}
	session_unset();
	session_destroy();
} else {
	if( isset( $_SESSION ) ) {
               $_SESSION["patUserData"] = array();
	} else {
		$GLOBALS["HTTP_SESSION_VARS"]["patUserData"] = array();
	}
}

header('location: https://www.numerocentral.com/admin.php');
?>
