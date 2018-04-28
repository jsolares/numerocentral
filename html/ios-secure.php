<?php
/*
    ios-secure.php
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

    Interface for ios App
*/

include 'db.inc.php';

$db = new DB_Sql("mysqli", "localhost", "numerocentral", "root", "");

$request = 0;

$months = array("Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");

getpost_ifset(array("user","pass","call","imei","phonenumber", "request", "data", "file", "ivr"));

$extension = "";

$db -> query ("select count(*) as allowed, users.accountcode, plans.record as plan_record, incomming_prefs.record as prefs_record from plans, incomming_prefs, nc_mynumber, users where nc_mynumber.accountcode = users.accountcode and users.username = '$user' and nc_mynumber.number = '$phonenumber' and passwd = '$pass' and incomming_prefs.accountcode = nc_mynumber.accountcode and users.id_plan = plans.id");
$db -> next_record();
$allowed = $db -> f ("allowed");

if ( $allowed == "0" && strlen($ivr) == 0 ) {
	//Try Regular IVR
	$db -> query ("select count(*) as allowed, users.accountcode, plans.record as plan_record, incomming_prefs.record as prefs_record from plans, incomming_prefs, ivr_option, users where ivr_option.accountcode = users.accountcode and users.username = '$user' and ivr_option.number = '$phonenumber' and passwd = '$pass' and incomming_prefs.accountcode = ivr_option.accountcode and users.id_plan = plans.id");
	$db -> next_record();
	$allowed = $db -> f ("allowed");
}


if ( $allowed == "0" && strlen($ivr) > 0 ) {
	$extension = $ivr;
	
	$db -> query ("select count(*) as allowed, ivr_option.accountcode, plans.record as plan_record, incomming_prefs.record as prefs_record from plans, incomming_prefs, ivr_option, users where ivr_option.accountcode = substring(users.accountcode,1,8) and users.username = '$user' and ivr_option.number = '$phonenumber' and passwd = '$pass' and incomming_prefs.accountcode = ivr_option.accountcode and users.id_plan = plans.id and ivr_option.keypad = '$extension' and users.accountcode like '%-$extension'");

	$db -> next_record();
	$allowed = $db -> f ("allowed");
}

$accountcode = $db -> f ("accountcode");
$plan_record = $db -> f ("plan_record");
$prefs_record = $db -> f ("prefs_record");

$busqueda = array ("(",")"," ","-");
$call = str_replace($busqueda,"",$call);

$db -> query ("insert into android_test values ( '$user', '$pass', '$call', '$imei', '$phonenumber', now(), 'ios $data')");

if ( $allowed ) {
	// $request == 0 => Llamada
	if ( $request == 0 ) {
		$phonenumber = substr($phonenumber, -8);
		$record = (($plan_record) && ($prefs_record == '3' || $prefs_record == "1"))?1:0;

		if ( strlen($extension) > 0 ) {
			$exec = "/usr/sbin/callback-android-ivr.sh $phonenumber $accountcode $call $record $extension";
		} else {
			$exec = "/usr/sbin/callback-android.sh $phonenumber $accountcode $call $record";
		}
		system($exec);
		echo "success";
	}
	// $request == 1 => # contactos
	if ( $request == 1 ) {
		$db -> query ( "select count(*) as qty from contacts, groups where id_group = groups.id and groups.accountcode = '$accountcode'");
		$db -> next_record();
		echo $db -> f ("qty");

		if ( strlen($extension) > 0 ) {
		} else {

		$db -> query ("select id from groups where groups.accountcode = '$accountcode' and group_name = 'Sin Grupo'");
		if ( $db -> next_record() ) {
			$id_group = $db -> f ("id");

	 		$contacts = preg_split("/\n/",$data);
			foreach ($contacts as $my_contact) {
				if ( strlen($my_contact) > 0 ) {
					$contact = preg_split("/,/",$my_contact);
					$name = str_replace(".","",$contact[0]);
					$number = $contact[1];

					$db -> query ( "select count(*) as qty, id_group from contacts, groups where id_group = groups.id and groups.accountcode = '$accountcode' and contacts.number = '$number'");
					$db -> next_record();
					if ( $db -> f ("qty") == 0 ) {
						$db -> query ("insert into contacts values ( null, $id_group, '$name', '', '', '" . $contact[1] . "', 0)");
					} else {
						$id_group = $db -> f ("id_group");
						$db -> query ("update contacts set contacts.name = '$name' where number = '$number' and id_group = $id_group");
					}
				}
			}
		}
		}
	}
	// $request == 2 => info de contactos
	if ( $request == 2 ) {
		$db -> query ( "select name, email, address, number from contacts, groups where id_group = groups.id and groups.accountcode = '$accountcode' order by name");
		while ( $db -> next_record() ) {
			$toreplace = array(",",".","'");
			$name = trim(str_replace($toreplace,"",$db -> f ("name")));
			$email = trim(str_replace(",","",$db -> f ("email")));
			$address = trim(str_replace(",","",$db -> f ("address")));
			$number = trim(str_replace(",","",$db -> f ("number")));

			echo "$name,$email,$address,$number\n";
		}
	}
	// $request == 3 => CDR
	if ( $request == 3 ) {
		$contactos = array();

		$db -> query ("select number, name from contacts where id_group in ( select id from groups where accountcode = '$accountcode')");
	        while ( $db -> next_record() ) {
			$toreplace = array(",",".","'");
        	        $number = trim(str_replace(",","",$db -> f ("number")));
			$contactos[ $number ] = trim(str_replace($toreplace,"",$db -> f ("name")));
		}

		if ( strlen($extension) > 0 ) {
			$conditional = " and ( userfield like '%:%:$extension:%' or userfield like '%:$phonenumber' )";

			$db -> query ( "select lastapp, month(calldate) as month, day(calldate) as day, date(calldate) as thedate, time(calldate) as thetime, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table where accountcode = '$accountcode' and lastapp <> 'DISA' and dcontext <> 'default' and userfield <> '' $conditional order by calldate desc limit 80");
		} else {
			$db -> query ("select lastapp, month(calldate) as month, day(calldate) as day, date(calldate) as thedate, time(calldate) as thetime, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table where accountcode = '$accountcode' and lastapp <> 'DISA' and dcontext <> 'default' and userfield <> '' order by calldate desc limit 80");
		}

		while ( $db -> next_record() ) {
			$lastapp = $db -> f ("lastapp");

                        $userfield = $db -> f ("userfield");
                        $uservars = explode(":",$userfield);
			$dcontext = $db -> f ("dcontext");
			$date = $db -> f ("thedate");
			$time = $db -> f ("thetime");
			$month = $db -> f ("month");
			$day = $db -> f("day");

			$day = ( $day < 10 )?"0" . $day:$day;

			$date = $months[$month-1] . "/" . $day;
				$time = substr($time,0,5);

                        $minutos = ceil ( $db -> f ( "duration" ) / 60 ) . "";

			$android_file = "";
			if ( ! ( $uservars[1] == "" ) ) {
                                $file = "/var/spool/asterisk/monitor/" . $uservars[1] . ".mp3";
				if ( file_exists("$file")) {
					$android_file = $uservars[1];
				}
			}

			if ( $dcontext == "callback-android" || $dcontext == "callback-android-record" || $dcontext == "callback-movil" || $dcontext == "callback-movil-record" || 
				$dcontext == "callback-web" || $dcontext == "callback-web-record") {
				$recordingvars = explode("-",$uservars[1]);

				$numero = $recordingvars[0];
                                $pos = 0;
                                $pos = strrpos($numero, "/" );

                                if ( $pos > 0 ) {
                                        $numero = substr($numero, $pos+1);
                                } else {
                                        $numero = $recordingvars[0];
                                }

				if ( $numero == "" )
					$numero = " ";	
	
                                if ( isset( $contactos[$numero] ) ) {
                                        $name = str_replace(",","",$contactos[$numero]);
					print "$date $time,$name,$minutos,2,$android_file,$numero\n";
                                } else {
					print "$date $time,$numero,$minutos,2,$android_file,$numero\n";
                                }

                        } else {

			if ($accountcode != $db -> f ( "dst")) {
                                $numero = $db -> f ("dst");
                                if ( isset( $contactos[$numero] ) ) {
                                        $name = str_replace(",","",$contactos[$numero]);
					print "$date $time,$name,$minutos,2,$android_file,$numero\n";
                                } else {
					print "$date $time,$numero,$minutos,2,$android_file,$numero\n";
                                }
                        } else {
                                $numero = $db -> f ("src");
                                if ( isset( $contactos[$numero] ) ) {
                                        $name = str_replace(",","",$contactos[$numero]);
					print "$date $time,$name,$minutos,1,$android_file,$numero\n";
                                } else {
					print "$date $time,$numero,$minutos,1,$android_file,$numero\n";
                                }
                        }
			}

		}
	}
	// $request == 4 => Grabacion
	if ( $request == 4 ) {
		$path = "/var/spool/asterisk/monitor";

		$db -> query ( "select count(*) as qty from callrecords_table where accountcode = '$accountcode' and userfield like '%:$file%'");
		$db -> next_record();
		$qty = $db -> f ("qty");

		if ( file_exists("$path/$file.mp3") && $qty == 1 ) {
			set_time_limit(0);

			header("Content-Type: audio/mpeg"); 
			header("Content-Length: ".filesize("$path/$file.mp3")); 
			header("Content-Disposition: atachment; filename=$file.mp3"); 
			header('X-Pad: avoid browser bug');
			header("Transfer-Encoding: none");
			header("Connection: close");
			header("Pragma: no-cache"); 
			header("Expires: 0"); 
			$fp=fopen("$path/$file.mp3","r"); 
			print fread($fp,filesize("$path/$file.mp3")); 
			fclose($fp); 
		}
		
	}
	// $request == 5 => Toggle Habilitado
	if ( $request == 5 ) {
		$disabled = $data;
		$disabled = ( $disabled )?0:1;
	
		if ( strlen($extension) > 1 ) {
			echo "nodisponible";
		} else {
			$db -> query ("update incomming_prefs set disabled=$disabled where accountcode = '$accountcode'");
			echo "success";
		}
	}
	if ( $request == 6 ) {
                $db -> query ( "select disabled from incomming_prefs where accountcode = '$accountcode'");
                if ( $db -> next_record() ) {
                        $disabled = $db -> f ("disabled");
			echo "$disabled";
                } else {
                	echo "fail";
		}
        }
	if ( $request == 7 ) {
		if ( strlen($extension) > 1 ) {
			$accountcode = $accountcode . "-" . $extension;
		}

		$db -> query ("delete from android_devices where accountcode not in ('$accountcode') and deviceID = '$data'");

		$db -> query ("select count(*) as qty from android_devices where accountcode = '$accountcode'");
		$db -> next_record();
		$qty = $db -> f ("qty");

		if ( $qty == 0 ) {
			$db -> query ("insert into android_devices values ( null, '$accountcode' , '$data')");
		} else if ( $qty == 1 ) {
			$db -> query ("update android_devices set deviceID = '$data' where accountcode = '$accountcode'");
		} else if ( $qty > 1 ) {
			$db -> query ("delete from android_devices where accountcode = '$accountcode'");
			$db -> query ("insert into android_devices values ( null, '$accountcode' , '$data')");
		}
		echo "success";
	}
	if ( $request == 8 ) {
		$db -> query ("select saldo_minutos from users, saldo where users.uid = saldo.uid and accountcode = '$accountcode'");
		$db -> next_record();
		echo $db -> f ("saldo_minutos");
	}
} else {
	echo "fail";
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
