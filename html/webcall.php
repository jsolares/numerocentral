<?php
/*
    webcall.php
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

    Script that does the webcall
*/

header('Access-Control-Allow-Origin: *');

// Specify which request methods are allowed
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Additional headers which may be sent along with the CORS request
// The X-Requested-With header allows jQuery requests to go through
header('Access-Control-Allow-Headers: X-Requested-With');

// Set the age to 1 day to improve speed/caching.

include 'db.inc.php';

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");

$request = 0;

getpost_ifset(array("phonenumber", "account", "ivr"));

$nphonenumber = strip_tags( trim( $phonenumber ) );
$naccount = strip_tags( trim( $account ) );
$nivr = strip_tags( trim( $ivr ) );

if ( is_numeric($nphonenumber) ) {
	$phonenumber = $nphonenumber;
} else {
	exit("$nphonenumber");
}

if ( is_numeric($naccount) ) {
	$account = $naccount;
} else {
	exit("err2");
}

if ( is_numeric($nivr) ) {
	$ivr = $nivr;
} else {
	exit("err3");
}

$db -> query ("select count(*) as allowed from users where accountcode='$account'");
$db -> next_record();
$allowed = $db -> f ("allowed");

if ( $allowed ) {
        $phonenumber = substr($phonenumber, -8);
	$ivr = $ivr + 0;
	if ( $ivr < 10 ) {
		$ivr = '000' . $ivr;
	} else if ( $ivr < 100 ) {
		$ivr = '00' . $ivr;
	} else if ( $ivr < 1000 ) {
		$ivr = '0' . $ivr;
	} else if ( $ivr > 9999 ) {
		$ivr = '0000';
	}

	$db -> query ( "insert into webcall values ( now(), '$phonenumber', '$account', $ivr)" );

	$exec = "/usr/sbin/callback-ajax.sh $ivr$account $phonenumber $account $ivr";
	system($exec);
	echo "success";
} else {
	echo "fail $account";
}

function getpost_ifset ( $test_vars ) {
        if( !is_array( $test_vars ) )
                $test_vars = array( $test_vars );

        foreach( $test_vars as $test_var ) {
                if ( isset( $_POST[$test_var] ) ) {
                        global $$test_var;
                        $$test_var = $_POST[$test_var];
                } elseif ( isset( $_GET[$test_var] ) ) {
                        global $$test_var;
                        $$test_var = $_GET[$test_var];
                } elseif ( isset( $_REQUEST[$test_var] ) ) {
                        global $$test_var;
                        $$test_var = $_REQUEST[$test_var];
                } elseif ( isset( $HTTP_GET_VARS[$test_var] ) ) {
                        global $$test_var;
                        $$test_var = $HTTP_GET_VARS[$test_var];
                } else {
                        global $$test_var;
                }
        }
}

?>
