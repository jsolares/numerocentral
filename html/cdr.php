<?php
/*
    cdr.php
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

    Generate a downloadble call detail record file for the authenticated user.
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

getpost_ifset(array("accountcode","number","fechade","fechaa","estado","tag"));

$fecha = date("YmdHi");

$filename = "llamadas_$accountcode". "_$fecha.csv";
// Fix IE bug [0]

header('Vary: User-Agent');
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"" . $filename. "\";");
header("Pragma: cache");
header("Expires: 0");
header("Cache-Control: private");

print llamadas(-1,$number,$fechade,$fechaa,$estado,$tag);

exit();

function llamadas($pagina, $numero, $fechade, $fechaa, $status, $tag, $tagedit) {
global $accountcode,$global_record,$faxno,$vence,$supervisa,$planid;
global $db;

$myaccountcode = substr($accountcode,0,8);

if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}


$etiquetas = array();
$llamadas_tag = array();

if ( !is_numeric($numero) ) {
	$db -> query ("select number from contacts where name like '$numero' and id_group in ( select id from groups where accountcode = '$myaccountcode')");

	if ( $db -> next_record() ) {
		$numero = $db -> f ("number");
	}
}

$conditional = "and lastapp <> 'DISA' and dcontext <> 'default'";
if ( ! ( trim($numero) == "" ) ) {
	$conditional = $conditional . " and ( dst like '%" . trim($numero) . "%' or src like '%" . trim($numero) . "%' or userfield like '%" . trim($numero) . "%' )";	
}
if ( ! ( trim($fechade) == "" ) ) {
	$conditional = $conditional . " and calldate >= '" . trim($fechade) . " 00:00:00'";	
}
if ( ! ( trim($fechaa) == "" ) ) {
	$conditional = $conditional . " and calldate <= '" . trim($fechaa) . " 23:59:59'";	
}
if ( ! ( trim($tag) == "" ) ) {
	if ( $tag > 0 ) {
		$conditional = $conditional . " and e.etiqueta = $tag";
	}
}

if ( $status > 0 ) {
	if ( $status == 2 ) {
		$conditional = $conditional . " and src = '" . $accountcode . "' and userfield not like 'fax%'";
	}
	if ( $status == 1 ) {
		$conditional = $conditional . " and src <> '" . $accountcode . "' and userfield not like 'fax%'";
	}
	if ( $status == 3 ) {
		$conditional = $conditional . " and userfield like 'fax%'";
	}
	if ( $status == 4 ) {
		$conditional = $conditional . " and (lastapp like 'VoiceMail' or lastapp like 'voicemail')";
	}
	if ( $status == 5 ) {
		$conditional = $conditional . " and userfield like 'memo%'";
	}
}

if ( $planid == 13 ) {
	$extension = substr($accountcode,9,4);

	$db -> query ("select number from ivr_option where accountcode = '$myaccountcode' and keypad = '$extension'");
	$db -> next_record();
	$phonenum = $db -> f ("number");

	$conditional .= " and ( userfield like '%:%:$extension:%' or userfield like '%:$phonenum' )";
}

if ( $planid == 12 ) {
	$conditional .= " and lastapp <> 'AppDial' ";
}

$conditional .= " and userfield <> '' order by calldate desc";

$db -> query ("select round(price/minutes,2) as ppm from plans, users where id_plan = plans.id and accountcode = '$myaccountcode'");
$db -> next_record();
$ppm = $db -> f ("ppm");

$db -> query ("select id, nombre from etiquetas where accountcode = '$myaccountcode'" );
while ( $db -> next_record() ) {
	$id = $db -> f ("id");
	$etiquetas [ $id ] = $db -> f ("nombre");
}

if ( $planid == 12 ) {
	if ( $_SESSION['account'] > 0 ) {
		$account = $_SESSION['account'];
		$db -> query ("select count(*) as qty from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode = '$account' $conditional" );
	} else {
		$db -> query ("select count(*) as qty from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode in ( $supervisa ) $conditional" );
	}
} elseif ($planid == 14) {
		$db -> query ("select count(*) as qty from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode in ( $supervisa ) $conditional" );
} else  {
	$db -> query ("select count(*) as qty from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode = '$myaccountcode' $conditional" );
}
	
$db -> next_record();
$qty = $db -> f ("qty");
if ( $qty > 0 ) {
	$url = "";

	$contenido .= "";

	if ( $planid != 12 ) {
		$contenido = "Fecha/Hora,Origen,Destino,Llamada,Duracion,Etiqueta\n";

		if ( $planid == 14 ) {
			$db -> query ("select ifnull(etiqueta,'') as etiqueta, c.uniqueid, c.accountcode as accountcode, lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode in ($supervisa) $conditional" );
			$myaccountcode = $supervisa;
		} else {
			$db -> query ("select ifnull(etiqueta,'') as etiqueta, c.uniqueid, lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode = '$myaccountcode' $conditional" );
		}
	} else {
		$contenido = "Fecha/Hora,Origen,Destino,Llamada,Duracion,Etiqueta\n";
		if ( $_SESSION['account'] > 0 ) {
			$account = $_SESSION['account'];
			$db -> query ("select ifnull(etiqueta,'') as etiqueta, c.uniqueid, lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode = '$account' $conditional" );
		} else {
			$db -> query ("select ifnull(etiqueta,'') as etiqueta, c.uniqueid, c.accountcode, lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode in ($supervisa) $conditional" );
		}
	}
	while ( $db -> next_record() ) {
		$td = "";
		if ( ! ($db -> f ("disposition") == "ANSWERED") ) {
			$td = "sin contestar";
		
		}

		$calldate = $db -> f ("calldate");
		$oldtag = $db -> f ("etiqueta");	

		$uniqueid = $db -> f ("uniqueid");

		$llamadas_tag[] = $uniqueid;

		foreach ( $etiquetas as $k => $v ) {
			if ( $oldtag == $k ) {
				$etiqueta = "$v";
			} else {
				$etiqueta = "";
			}
		}

		$lastapp = $db -> f ("lastapp");

		$userfield = $db -> f ("userfield");

		$dcontext = $db -> f ("dcontext");
		$uservars = explode(":",$userfield);
		$flash = "";

		$minutos = ceil ( $db -> f ( "billsec" ) / 60 );

		$origendest = "-";
		$borrargrabacion = "";

		if ( ! ( $uservars[1] == "" ) ) {
			if ( $uservars[0] == "memo" ) {
				$flash .= " CF";
				$origendest = "Conferencia";
			}
		}

		$callback = 0; $price = "0.00";
		if ( $uservars[0] == "callback-favorite" ) {
			if ( strlen($db->f("dst")) == 8 && strlen($db->f("src")) == 8 )  {
				$price = number_format( ceil ( $db -> f ( "billsec" ) / 60 ) * $ppm, 2 );
			}
		}

		if ( $uservars[0] == "callback" || $uservars[0] == "memo" || $uservars[0] == "callback" ) {
			if ( strlen($db->f("dst")) == 8 && strlen($db->f("src")) == 8 )  {
				$price = number_format( ceil ( $db -> f ( "billsec" ) / 60 ) * $ppm, 2 );
			}
		}

		if ( $lastapp == "VoiceMail" || $lastapp == "voicemail" ) {
			if ( $flash != "Borrada por Usuario" )
				$flash .= " VM";
			else
				$flash .= " : VM";
		}

		if ( count($uservars) >= 4 ) {
			$origendest = str_replace("g1/","",$uservars[3]);
			if ( $uservars[3] == "" ) {
				$origendest = $uservars[2];
			}
		}

		if ($uservars[0] == "callthrough") {
			$origendest = $db -> f ("src");
		}
		if (strlen($origendest) == 0 ) {
			$origendest = "-";
			$td = "sin contestar";
		}

		if ( $origendest == "-") {
			$name = "";
		} else {	
		}

		if ( $planid != 12 ) {
			$origendest = $origendest . $name;
		}

		if ( $planid != 12 ) {
		if ( $dcontext == "webcall" || $dcontext == "webcall-nc" ) {
			$recordingvars = explode("-",$uservars[1]);
			$numero = $recordingvars[0];
		
			$pos = 0;
			$pos = strrpos($numero, "/" );
			if ( $pos > 0 ) {
				$numero = substr($numero, $pos + 1 );
			}

			$contenido .= $calldate . ",$origendest,$numero,Saliente,$minutos,$etiqueta\n";

		} else 
		if ( $dcontext == "callback-android" || $dcontext == "callback-android-record" ||
			$dcontext == "callback-movil" || $dcontext == "callback-movil-record" ||
			$dcontext == "callback-web" || $dcontext == "callback-web-record" ) {
			$recordingvars = explode("-",$uservars[1]);
			$numero = $recordingvars[0];
		
			$pos = 0;
			$pos = strrpos($numero, "/" );
			if ( $pos > 0 ) {
				$numero = substr($numero, $pos + 1 );
			}

			$contenido .= $calldate . ",$origendest,$numero,Saliente,$minutos,$etiqueta,$td\n";
		} else {
		if ($myaccountcode != $db -> f ( "dst") && $faxno != $db->f("dst")) {
			$numero = $db -> f ("dst");
			$contenido .= $calldate . ",$origendest,$numero,Saliente,$minutos,$etiqueta,$td\n";
		} else {
			$numero = $db -> f ("src");
			if ( !is_numeric($numero) ) {
			}
				$contenido .= "$calldate,$numero,$origendest,Entrante,$minutos,$etiqueta,$td\n";
		}
		}
		} else {
			if ( $dcontext == "callback-android" || $dcontext == "callback-android-record" ||
				$dcontext == "callback-movil" || $dcontext == "callback-movil-record" ||
				$dcontext == "callback-web" || $dcontext == "callback-web-record" ) {
				$recordingvars = explode("-",$uservars[1]);
				$numero = $recordingvars[0];

				$pos = 0;
				$pos = strrpos($numero, "/" );
				if ( $pos > 0 ) {
					$numero = substr($numero, $pos + 1 );
				}


				$contenido .= "$calldate," . $db -> f ("src") . ",$numero,$minutos,$etiqueta,$td";
			} else {
				$pos = strpos($supervisa, $db -> f ("dst"));
				if ( $pos === false ) {
					$contenido .= "$calldate," . $db -> f ("src") . "," . $db -> f ("dst") . "$minutos,$etiqueta,$td";
				} else {
					$name = "";
					$numero = $db -> f ("src");
					$name = $numero;

					$contenido .= "$calldate,$name,$origendest,$minutos,$etiqueta,$td";
				}
			}
		}
	}
	return $contenido;
} else {
	return $contenido . "No Encontramos Llamadas!";
}
}
?>
