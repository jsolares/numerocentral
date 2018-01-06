<?php
/*
    ajax_server.php
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

    AJAX Server for marcador (dialer) area.
*/

if(!isset($_SESSION)) 
{ 
ini_set('session.use_trans_sid', 0);
ini_set("url_rewriter.tags","");
ini_set('session.use_only_cookies', 1); 
session_start(); 
}  

header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 

include '../db.inc.php';
require_once ("../xajax_core/xajaxAIO.inc.php");
include '../prepend.php';

$userid = $user->requireAuthentication( "" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db2 = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");

$mes = array( 1 => "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" );


if ( $userid === false ) {
} else {

$db -> query ( "select marcador,minutos,supervisa,fax,id_plan,accountcode,saldo_minutos, saldo_qtz, valid_days - datediff(now(), ifnull(fecha_ingreso_saldo,now())) as vence, day(fecha_inicio) as diacorte, extensiones, exten1digit, monto from users, plans, saldo where id_plan = plans.id and users.uid = saldo.uid and users.uid = $userid" );
$db -> next_record();
$marcador    = $db -> f ("marcador");
$accountcode = $db -> f ("accountcode");
$saldo       = $db -> f ("saldo_minutos");
$saldo_qtz   = number_format($db -> f ("saldo_qtz"),2,".",",");
$saldo_qtz_no = $db -> f ("saldo_qtz");
$planid	     = $db -> f ("id_plan");
$faxno       = $db -> f ("fax");
$vence       = $db -> f ("vence");
$supervisa   = $db -> f ("supervisa");
$diacorte    = $db -> f ("diacorte");
$maxexten    = $db -> f ("extensiones");
$max1exten   = $db -> f ("exten1digit");
$monto	     = $db -> f ("monto");
$plan_minutos = $db -> f ("minutos");

$fechacorte = "";
if ( $diacorte > 0 ) {
$currday = date("j");
$fechacorte = " | <strong>D&iacute;a de corte: ";
$fechacorte .= $diacorte . ". ";

$fechacorte .= "</strong>";
}

if ( $vence > 0 ) {
	if ( $vence > 1 ) {
		$vence = "Saldo vence en $vence d&iacute;as. $fechacorte";
	} else {
		$vence = "Saldo vence en $vence d&iacute;a. $fechacorte";
	}
} else {
	if ( $accountcode == '24584711' || $accountcode == '24584710' ) {
		$vence = "Admin";
	} else {
		$vence = "";
	}
}

$db -> query ( "select * from plans where id = $planid" );
$db -> next_record();

setcookie(
ini_get("session.name"),
session_id(),
time()+ini_get("session.cookie_lifetime"),
ini_get("session.cookie_path"),
ini_get("session.cookie_domain"),
ini_get("session.cookie_secure"),
ini_get("session.cookie_httponly")
);

//$ip = $_SERVER['REMOTE_ADDR'];

$requested = "";
if(isset($_GET['xjxfun']))
	$requested=$_GET['xjxfun'];
if(isset($_POST['xjxfun']))
	$requested=$_POST['xjxfun'];
trigger_error("$accountcode - $requested - ", E_USER_NOTICE);
//file_put_contents('php://stderr', print_r($accountcode, TRUE));

$xajax = new xajax("ajax_server.php");
$xajax -> registerFunction("getSettings");
$xajax -> registerFunction("getCampaign");
$xajax -> registerFunction("getMain");
$xajax -> registerFunction("getCampaignDetail");
$xajax -> registerFunction("deleteCampaign");
$xajax -> registerFunction("stopCampaign");
$xajax -> registerFunction("marcadorhorario");


$xajax -> register(XAJAX_PROCESSING_EVENT, XAJAX_PROCESSING_EVENT_BEFORE, "checkloginstatus");

function checkloginstatus(&$callnext) {
        global $userid;
        if ( $userid === false ) {
                $callnext = array(false);
                $objResponse = new xajaxResponse();
                $objResponse -> assign("contenido","innerHTML", "Por seguridad su sesi&oacute;n ha expirado, favor volver a <a href=http://www.numerocentral.com/logout.php>ingresar</a>.");
                return $objResponse;
        }
}

function buildMenu( &$objResponse, $selected ) {
        global $faxno,$accountcode,$saldo,$allowed_numbers,$vence,$planid,$supervisa,$db,$saldo_qtz,$showtag,$marcador;

        $settings = $info = $campaign = "";

        switch($selected) {
                case 1: $campaign='class="selected"';break;
                case 2: $settings='class="selected"';break;
                case 3: $info='class="selected"';break;
        }

        $menu = '<li><a href="#" onclick="xajax_getCampaign()" ' . $campaign . '><i class="fa fa-tasks fa-lg"></i>&nbsp;&nbsp;Campa&ntilde;as</a></li>' .
                '<li><a href="#" onclick="xajax_getSettings()" ' . $settings . '><i class="fa fa-wrench fa-lg"></i>&nbsp;&nbsp;Configuraci&oacute;n</a></li>';
	
	if ( $marcador != 0 ) {
		$objResponse -> assign("menu","innerHTML",$menu);
		$objResponse -> assign("title","innerHTML", "<h1 align=right>Marcador : $accountcode/<font size=3>Saldo Q $saldo_qtz | $saldo min.</font><br/><font size=2>$vence</font></h1>");
	}
}

function getMain() {
        global $db, $accountcode, $marcador;

	$objResponse = new xajaxResponse();
	if ( $marcador != 0 ) {
		$db -> query ("select * from marcador_audio where accountcode='$accountcode'");
		if ( $db -> next_record() ) {
			return getCampaign();
		} else {
			return getSettings();
		}
	} else {
		$res = "<h3>Porfavor contactenos para habilitar el Marcador.</h3>";
		$objResponse -> assign ("contenido", "innerHTML", $res );
		return $objResponse;
	}
}

function getCampaignDetail( $id_event ) {
	global $db, $db2, $accountcode;

	$objResponse = new xajaxResponse();
	buildMenu($objResponse, 0);

	$db -> query ( "select * from marcador_event where id_event=$id_event and accountcode='$accountcode'");
	if ( $db -> next_record() ) {
		$name = $db ->f ("name");
		$inicio = $db -> f ("inicio");
		$status = $db -> f ("status");
	
		$res = "<h4>Campaña: $name @ $inicio, Estado:";
		if ( $status == 0 ) 
			$res .= 'En Espera';
		if ( $status == 1 ) 
			$res .= 'En Proceso';
		if ( $status == 2 ) 
			$res .= 'Finalizado';
		if ( $status == 3 ) 
			$res .= 'Cancelado';
		$res .= "</h4><br/>";

		$db -> query ("select * from marcador_event_detail where id_event=$id_event order by id");
		$res .= "<table class=\"llamadas\"><tr><th></th><th>N&uacute;mero</th><th>Estado</th><th>Fecha Estado</th></tr>";	
		$i = 0;
		while ( $db -> next_record() ) {
			$i++;
			$number = $db -> f ("number");
			$status = $db -> f ("status");
			$fecha  = $db -> f ("status_date");
			
			$res .= "<tr><td>$i</td><td>$number</td><td>";
			if ( $status == 0 )
				$res .= 'En Espera';
			if ( $status == 1 )
				$res .= 'Enviado';
			if ( $status == 2 )
				$res .= 'Finalizado';
		
			$res .= "</td><td>$fecha</td></td>";
		}

		$res .= "</table>";

		$objResponse -> assign ("contenido", "innerHTML", $res );
		return $objResponse;
	} else {
		return getCampaign();
	}
}

function getCampaign() {
	global $db, $db2, $accountcode;

	$objResponse = new xajaxResponse();
	buildMenu($objResponse,1);

	$res = "";
	$res .= "<h4>Agregar Campa&ntilde;a:</h4>";
	$res .= '<form enctype="multipart/form-data" action="upload-campaign.php" method="post">
	<table class="llamadas">
        <tr><th colspan=2>&nbsp;Agregar Campa&ntilde;a IVR:</th></tr>
        <tr>
        <td>Nombre:</td>
        <td><input type=text name="nombre" size=24></td>
        </tr>
        <tr>
        <td>Fecha:</td>
        <td><input type="text" id="date" name="fechainicio" size=20>
<a href="javascript:NewCssCal(\'date\',\'yyyymmdd\',\'dropdown\',true,\'24\')"><img src="/images/cal.gif" widtd="16" height="16" border="0" alt="Pick a date"></a></td>
        </tr>
        <tr><td colspan=2 align=center>El Formato de las fechas es YYYY-MM-DD HH:MM:SS</td></tr>
	<tr>
        <td>Archivo:</td>
        <td><input type=file name="campaign" size=14></td>
        </tr>
        <tr>
        <td>&nbsp;</td><td><input type="submit" value=" Agregar ">
        </tr>
        </table>
        </form>
        <br/>
	<h4>Listado de Campa&ntilde;as:</h4><br/>
';

	$res .= '<table class="llamadas">
	<tr>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Nombre</th>
        <th>Cantidad</th>
        <th>Estado</th>
        <th></th>
	</tr>
	';
	$db -> query ( "select * from marcador_event where accountcode='$accountcode' order by status, inicio");
	while ( $db -> next_record() ) {
		$id_event = $db -> f ("id_event");
		$name = $db ->f ("name");
		$db2 -> query ( "select count(*) as qty from marcador_event_detail where id_event = $id_event" );
		$db2 -> next_record();
		$qty  = $db2 -> f ("qty");
		$status = $db -> f ("status");

		$res .= '<tr><td colspan=2>' . $db -> f ("inicio") . '</td><td>' . $name . '</td>';
		$res .= '<td>' . $qty . '</td><td>';

		if ( $status == 0 ) 
			$res .= 'En Espera';
		if ( $status == 1 ) 
			$res .= 'En Proceso';
		if ( $status == 2 ) 
			$res .= 'Finalizado';
		if ( $status == 3 ) 
			$res .= 'Cancelado';

		$res .= '</td><td><a href="#" onclick="xajax_getCampaignDetail(' . $id_event . ')"><i class="fa fa-list"></i>&nbsp;</a>';
		
		if ( $status == 0 ) {
			$res .= '&nbsp;<a href="#" onclick="deleteCampaign(' . $id_event . ",'$name')\"><i class=\"fa fa-trash\"></i>&nbsp;</a>";
		}	
		if ( $status == 1 ) {
			$res .= '&nbsp;<a href="#" onclick="stopCampaign(' . $id_event . ",'$name')\"><i class=\"fa fa-stop\"></i>&nbsp;</a>";
		}	
		$res .= '</td></tr>';
	}

	$res .= '</table><i class=\"fa fa-cc-paypal fa-lg\"></i>&nbsp;';

	$objResponse -> assign("contenido","innerHTML",$res);

	return $objResponse;
}

function getSettings() {
	global $faxno,$accountcode,$saldo,$allowed_numbers,$vence,$planid,$maxexten;
        global $db;

	$objResponse = new xajaxResponse();
	buildMenu($objResponse,2);

	$res  = "<div id='prefswide_container'>";

	$db -> query ( "select * from marcador_audio where accountcode='$accountcode'");
	if ( $db -> next_record() ) {
		$path = "/var/lib/asterisk/sounds/marcador/" . $db -> f ("recording") . ".wav";
		if (file_exists($path)) {
			$res .= "<h4>Audio para campa&ntilde;a</h4><br/>";
			$res .= "Su audio tiene una duraci&oacute;n de: " . wavDur($path);

			$flash = '<object type="application/x-shockwave-flash" data="/player_mp3_maxi.swf" width="120" height="12">
<param name="movie" value="player_mp3_maxi.swf" />
<param name="FlashVars" value="mp3=audio.php?file=' . $accountcode . '-audio' . '&amp;bgcolor1=eeeeee&amp;bgcolor2=aaaaaa&amp;buttoncolor=443344&amp;buttonovercolor=0&amp;slidercolor1=aaaaaa&amp;slidercolor2=443344&amp;slid
erovercolor=666666&amp;textcolor=0&amp;" />
</object>';
			$flash .= '<a href="audio-wav.php?file=' . $accountcode . '-audio" class="override">&nbsp;<i class="fa fa-download fa-lg"></i></a>';
			$res .= "&nbsp;$flash";

			$recording = 1;

			$inicio = $db -> f ("hora_inicio");
			$hini = explode(":", $inicio);
			$fin = $db -> f ("hora_fin");
			$hfin = explode(":", $fin);

			$amd = $db -> f ("amd");
			if ( $amd == 1 ) {
				$amd = "checked";
			} else {
				$amd = "";
			}

			$minih = "";
			$mfinh = "";
			$hinih = "";
			$hfinh = "";

			for ($i=0;$i<24;$i++) {
				$horatxt = ( $i < 10 )?"0$i":$i;
				if ( $i == $hini[0] ) {
					$hinih .= "<option value=\"$i\" selected>$horatxt</option>";
				} else {
					$hinih .= "<option value=\"$i\">$horatxt</option>";
				}
				if ( $i == $hfin[0] ) {
					$hfinh .= "<option value=\"$i\" selected>$horatxt</option>";
				} else {
					$hfinh .= "<option value=\"$i\">$horatxt</option>";
				}
			}

			for($i=0;$i<=59;$i+=5) {
				$minutotxt = ( $i < 10 )?"0$i":$i;
				if ( $i == $hini[1] ) {
					$minih .= "<option value=\"$i\" selected>$minutotxt</option>";
				} else {
					$minih .= "<option value=\"$i\">$minutotxt</option>";
				}
				if ( $i == $hfin[1] ) {
					$mfinh .= "<option value=\"$i\" selected>$minutotxt</option>";
				} else {
					$mfinh .= "<option value=\"$i\">$minutotxt</option>";
				}
			}

			if ( $hini[1] == 59 ) {
				$minih .= "<option value=\"59\" selected>59</option>";
			} else {
				$minih .= "<option value=\"59\">59</option>";
			}
			if ( $hfin[1] == 59 ) {
				$mfinh .= "<option value=\"59\" selected>59</option>";
			} else {
				$mfinh .= "<option value=\"59\">59</option>";
			}

			$res .= "<form id=\"marcador_horario\" name=\"marcador_horario\" action='javascript:void(null);\' onsubmit='xajax_marcadorhorario(xajax.getFormValues(\"marcador_horario\"))'>";
			$res .= "Horario de Marcador: <b>Apertura:</b> <select name=horainicio>$hinih</select>:<select name=minutoinicio>$minih</select> <b>Cierre:</b> <select name=horafin>$hfinh</select>:<select name=minutofin>$mfinh</select>";
			$res .= "<br/>Detecci&oacute;n de Voicemail: <input type=checkbox name=\"amd\" $amd><br/>";
			$res .= "<input type=submit value='Guardar'/></form>";

			$res .= "<br/><form action='marcador-upload.php' enctype='multipart/form-data' method='post'>";
			$res .= "Modificar audio para campa&ntilde;a: <input type=file name=\"audio\"/>";
			$res .= "<input type=hidden name='accountcode' value='$accountcode'/>";
			$res .= "<input type=submit value='Subir'/></form>";
		} else {
			$res .= "No Hay grabaci&oacute;n para el Marcador, por favor subir su archivo de audio en formato wav.";
			$res .= "<br/>";
			$res .= "<form action='marcador-upload.php' enctype='multipart/form-data' method='post'>";
			$res .= "Subir audio para campa&ntilde;a: <input type=file name=\"audio\"/>";
			$res .= "<input type=hidden name='accountcode' value='$accountcode'/>";
			$res .= "<input type=submit value='Subir'/></form>";
		}
	} else {
		$res .= "No Hay grabaci&oacute;n para el Marcador, por favor subir su archivo de audio en formato wav.";
		$res .= "<br/>";
		$res .= "<form action='marcador-upload.php' enctype='multipart/form-data' method='post'>";
		$res .= "Subir audio para campa&ntilde;a: <input type=file name=\"audio\"/>";
		$res .= "<input type=hidden name='accountcode' value='$accountcode'/>";
		$res .= "<input type=submit value='Subir'/></form>";
	}

	$res .= "</div>";
	
	$objResponse -> assign("contenido","innerHTML",$res);
	return $objResponse;
}

function wavDur($file) {
  if (file_exists($file)) {
  $fp = fopen($file, 'r');
  if (fread($fp,4) == "RIFF") {
    fseek($fp, 20);
    $rawheader = fread($fp, 16);
    $header = unpack('vtype/vchannels/Vsamplerate/Vbytespersec/valignment/vbits',$rawheader);
    $pos = ftell($fp);
    while (fread($fp,4) != "data" && !feof($fp)) {
      $pos++;
      fseek($fp,$pos);
    }
    $rawheader = fread($fp, 4);
    $data = unpack('Vdatasize',$rawheader);
    $sec = @$data['datasize']/$header['bytespersec'];
    $minutes = intval(($sec / 60) % 60);
    $seconds = intval($sec % 60);
    return str_pad($minutes,2,"0", STR_PAD_LEFT).":".str_pad($seconds,2,"0", STR_PAD_LEFT);
  }
  } else {
    return -1;
  }
}

function marcadorhorario($form) {
        global $accountcode, $db;

        $objResponse = new xajaxResponse();

        $horainicio = trim($form['horainicio']);
        $horafin = trim($form['horafin']);
        $minutoinicio = trim($form['minutoinicio']);
        $minutofin = trim($form['minutofin']);
	$amd = trim($form['amd']);

        if ( $horainicio < 10 ) {
                $horainicio = "0$horainicio";
        }
        if ( $horafin < 10 ) {
                $horafin = "0$horafin";
        }
        if ( $minutoinicio < 10 ) {
                $minutoinicio = "0$minutoinicio";
        }
        if ( $minutofin < 10 ) {
                $minutofin = "0$minutofin";
        }

	if ( $amd == "on" ) {
		$amd = 1;
	} else {
		$amd = 0;
	}

        $inicio = "$horainicio:$minutoinicio:00";
        $fin = "$horafin:$minutofin:00";

        if ( !preg_match("/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/", $inicio) ) {
                $objResponse -> alert ("La hora de inicio ingresada es invalida, por favor intentelo de nuevo." );
                return $objResponse;
        }

        if ( !preg_match("/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/", $fin ) ) {
                $objResponse -> alert ("La hora de fin ingresada es invalida, por favor intentelo de nuevo." );
                return $objResponse;
        }

        $db -> query ("update marcador_audio set hora_inicio='$inicio', hora_fin='$fin', amd=$amd where accountcode = '$accountcode'" );

        $objResponse = getSettings();
        $objResponse -> alert ("Operacion realizada exitosamente.");

        return $objResponse;
}

function deleteCampaign( $id_event ) {
	global $accountcode, $db;

	$id_event = trim($id_event);
	$id_event = mysql_real_escape_string($id_event);
	$db -> query ("select * from marcador_event where id_event=$id_event and accountcode='$accountcode' and status=0");
	if ( $db -> next_record() ) {
		$db -> query ("delete from marcador_event_detail where id_event=$id_event");
		$db -> query ("delete from marcador_event where id_event=$id_event");
	}

	return getCampaign();	
}

function stopCampaign( $id_event ) {
	global $accountcode, $db;
	$id_event = trim($id_event);

	$db -> query ("select * from marcador_event where id_event=$id_event and accountcode='$accountcode' and status=1");
	if ( $db -> next_record() ) {
		$db -> query("update marcador_event set status=3 where id_event=$id_event and accountcode='$accountcode'");
	}

	return getCampaign();
}

$xajax->processRequest();
}
?>
