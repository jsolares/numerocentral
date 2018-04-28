<?php
/*
    ajax_server_admin.php
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

    AJAX Server with admin area
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

include 'db.inc.php';
require_once ("xajax_core/xajaxAIO.inc.php");
include 'prepend_admin.php';

$userid = $user->requireAuthentication( "" );

$db = new DB_Sql("mysqli", "localhost", "numerocentral", "root", "");
$db2 = new DB_Sql("mysqli", "localhost", "numerocentral", "root", "");

$vence = "Admin";

if ( $userid === false ) {
} else {
$db -> query ( "select level from admin where uid = $userid" );
$db -> next_record();
$level = $db -> f ("level");
}

$xajax = new xajax("ajax_server_admin.php");
$xajax->registerFunction("adminPanel");
$xajax->registerFunction("accountProcessForm");
$xajax->registerFunction("admCDR");
$xajax->registerFunction("admCDRUser");
$xajax->registerFunction("admcallsPage");
$xajax->registerFunction("admEstadistica");
$xajax->registerFunction("admMonitor");
$xajax->registerFunction("admVendedor");
$xajax->registerFunction("admEditVendedor");
$xajax->registerFunction("admEditPago");
$xajax->registerFunction("admPagos");
$xajax->registerFunction("admFacturacion");
$xajax->registerFunction("editCustomer");
$xajax->registerFunction("customerProcessForm");
$xajax->registerFunction("custAddMinutes");
$xajax->registerFunction("custPayHistory");
$xajax->registerFunction("custMM");
$xajax->registerFunction("mmList");
$xajax->registerFunction("admVShowLog");
$xajax->registerFunction("admVShowClients");
$xajax->registerFunction("processStats");
$xajax->registerFunction("processPagos");
$xajax->registerFunction("pagosProcessForm");
$xajax->registerFunction("admPagosMora");
$xajax->registerFunction("admPagosUpload");

$requested = "";
if(isset($_GET['xjxfun']))
        $requested=$_GET['xjxfun'];
if(isset($_POST['xjxfun']))
        $requested=$_POST['xjxfun'];
trigger_error("Admin : $userid - $requested - ", E_USER_NOTICE);

$xajax -> register(XAJAX_PROCESSING_EVENT, XAJAX_PROCESSING_EVENT_BEFORE, "checkloginstatus");

function checkloginstatus(&$callnext) {
        global $userid;
        if ( $userid === false ) {
                $callnext = array(false);
                $objResponse = new xajaxResponse();
                $objResponse -> assign("contenido","innerHTML", "Por seguridad su sesión ha expirado, favor volver a <a href=http://www.numerocentral.com/logout.php>ingresar</a>.");
                return $objResponse;
        }
}

function adminPanel () {
global $userid, $level;
global $db,$db2;
$objResponse = new xajaxResponse();
buildMenuAdm($objResponse,1);

$minutos_mes = Array();

$db -> query ("select accountcode, month(calldate), year(calldate), sum(ceil(billsec/60)) as minutos from callrecords_table where year(calldate) = year(now()) and month(calldate) = month(now()) and length(accountcode)=8 group by accountcode, month(calldate), year(calldate)");

while ( $db -> next_record() ) {
	$minutos_mes [ substr($db -> f ("accountcode"),0,8) ] = $db -> f ("minutos");
}

$db -> query ("select accountcode, month(calldate), year(calldate), sum(ceil(billsec/60)) as minutos from callrecords_table where year(calldate) = year(now()) and month(calldate) = month(now()) and length(accountcode)=8 and userfield like 'callback%' group by accountcode, month(calldate), year(calldate)");

while ( $db -> next_record() ) {
	$minutos_mes_out [ substr($db -> f ("accountcode"),0,8) ] = $db -> f ("minutos");
}

$db -> query ("select accountcode, month(calldate), year(calldate), sum(ceil(billsec/60)) as minutos from callrecords_table where year(calldate) = year(now()) and month(calldate) = month(now()) and length(accountcode)=8 and dcontext = 'marcador' group by accountcode, month(calldate), year(calldate)");
while ( $db -> next_record() ) {
		$minutos_mes_out [ $db -> f ("accountcode") ] += $db -> f ("minutos");
}


if ( $level == 0 ) {
$contenido  = "<a href=\"#\" onclick=\"xajax_editCustomer(-1)\">Agregar Usuario</a><p>";
}

$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Numero</th><th>Nombre</th><th>Saldo M.</th><th>Saldo Q.</th><th>Min.</th><th>-></th><th>Plan</th><th>Caduca</th><th>F. Ingreso</th><th></th></tr>";

$db -> query ("select saldo_vencido, id_vendedor, vendedores.nombre as vendedor, id_plan, users.uid, accountcode, users.name as name, email, saldo_minutos, saldo_qtz, plans.name as plan_name, valid_days - datediff(now(), fecha_ingreso_saldo) as vence, date(fecha_inicio) as cobro from users, plans, vendedores, saldo where users.uid = saldo.uid and id_vendedor = vendedores.id and id_plan = plans.id and users.uid>1 and id_plan <> 7 and length(accountcode) = 8 order by length(accountcode), accountcode asc ");

while ( $db -> next_record() ) {
	$id_plan = $db -> f ("id_plan");
	$vence = ( $db -> f ("vence") > 0 )? $db -> f ("vence") . " dias":"-";
	$cobro = ( $db -> f ("cobro") > 0 )? $db -> f ("cobro"):"";
	$vendedor = $db -> f ("vendedor");
	$saldovenc = $db -> f ("saldo_vencido");
	$saldoqtz = number_format($db -> f ("saldo_qtz"),2);

	if ( $id_plan == 14 ) {
		$contenido .= "<tr><td>" . $db -> f ("accountcode") . "</td><td>" .
			htmlentities(substr($db ->f ("name"),0,60), ENT_QUOTES, "utf-8") .
			"</td><td>" . $db -> f ("saldo_minutos") . "</td><td>$saldoqtz</td>" .
			"<td>" . @$minutos_mes[ substr($db -> f ("accountcode"),0,8)] . "</td>" .
			"<td>" . @$minutos_mes_out[ substr($db -> f ("accountcode"),0,8)] . "</td>" .
			"<td>" . substr($db -> f ("plan_name"),0,13) . "</td>" .
			"<td>" . $vence . "</td><td>" . $cobro . "</td>";
		if ( $level == 0 ) {
			$contenido .=
				"<td><a href=\"#\" onclick=\"xajax_editCustomer(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/edit.jpg\"></a>" .
				"</td></tr>";
		} else {
			$contenido .= "<td></td></tr>";
		}
	} else {
		$contenido .= "<tr><td>" . $db -> f ("accountcode") . "</td><td><a href=\"#\" onclick=\"xajax_admCDRUser(" .
			$db -> f ("accountcode") . ")\">" .  htmlentities(substr($db ->f ("name"),0,60), ENT_QUOTES, "utf-8") . "</a>".
			"</td><td>" . $db -> f ("saldo_minutos") . "</td><td>$saldoqtz</td>" .
			"<td>" . @$minutos_mes[substr($db -> f ("accountcode"),0,8)] . "</td>" .
			"<td>" . @$minutos_mes_out[ substr($db -> f ("accountcode"),0,8)] . "</td>" .
			"<td>" . substr($db -> f ("plan_name"),0,13) . "</td>" .
			"<td>" . $vence . "</td><td>" . $cobro . "</td>" ;
		if ( $level == 0 ) {
			$contenido .=
			"<td><a href=\"#\" onclick=\"xajax_editCustomer(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/edit.jpg\"></a>" .
			"<a href=\"#\" onclick=\"xajax_custAddMinutes(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/dollar.gif\"></a>" .
			"<a href=\"#\" onclick=\"xajax_custPayHistory(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/payment.png\"></a>" .
			"<a href=\"#\" class=\"override\" onclick=\"xajax_custMM(" . $db -> f ("uid") . ")\"><i class='fa fa-shopping-cart fa-fw'></i></a>" .
			"</td></tr>";
		} else {
			$contenido .= "<td></td></tr>";
		}
	}
}

$objResponse -> assign("contenido","innerHTML", $contenido );
return $objResponse;
}

function admCDR() {
global $accountcode,$saldo,$faxno,$vence;
$objResponse = new xajaxResponse();
buildMenuAdm($objResponse,2);

$_SESSION['number'] = "";
$_SESSION['fecha'] = "";
$_SESSION['admacct'] = "";
$contenido = admllamadas(1,"","","");
$objResponse -> assign("contenido","innerHTML",$contenido);
$objResponse->assign("submitButton","value","Busqueda");
$objResponse->assign("submitButton","disabled",false);
return $objResponse;

}

function admCDRUser( $accountcode_cdr ) {
global $accountcode,$saldo,$faxno,$vence;
$objResponse = new xajaxResponse();
buildMenuAdm($objResponse,2);

$_SESSION['number'] = "";
$_SESSION['fecha'] = "";
$_SESSION['admacct'] = "$accountcode_cdr";
$contenido = admllamadas(1,"","","");
$objResponse -> assign("contenido","innerHTML",$contenido);
$objResponse->assign("submitButton","value","Busqueda");
$objResponse->assign("submitButton","disabled",false);
return $objResponse;


}

function admMonitor() {
global $db;

$db -> query ("select count(*) as qty, sum(ceil(billsec/60)) as min from callrecords_table where dcontext in ('marcador') and date(calldate) = date(now())");
$db -> next_record();
$marcador = $db -> f ("qty");
$marcador_min = 0 + $db -> f ("min");

$db -> query ("select count(*) as qty, sum(ceil(billsec/60)) as min from callrecords_table where dcontext in ('callback-dial','callback','callback-dial-record','callback-record') and date(calldate) = date(now());");
$db -> next_record();
$callback = $db -> f ("qty");
$callback_min = 0 + $db -> f ("min");

$db -> query ("select count(*) as qty, sum(ceil(billsec/60)) as min from callrecords_table where dcontext in ('callback-android','callback-android-record') and date(calldate) = date(now());");
$db -> next_record();
$android = $db -> f ("qty");
$android_min = 0 + $db -> f ("min");

$db -> query ("select count(*) as qty, sum(ceil(billsec/60)) as min from callrecords_table where dcontext in ('callback-movil','callback-movil-record') and date(calldate) = date(now());");
$db -> next_record();
$movil = $db -> f ("qty");
$movil_min = 0 + $db -> f ("min");

$db -> query ("select count(*) as qty, sum(ceil(billsec/60)) as min from callrecords_table where userfield like 'callthrough%' and date(calldate) = date(now());");
$db -> next_record();
$callthrough = $db -> f ("qty");
$callthrough_min = 0 + $db -> f ("min");

$db -> query ("select count(*) as qty, sum(ceil(billsec/60)) as min from callrecords_table where dcontext like 'webcall%' and date(calldate) = date(now());");
$db -> next_record();
$web = $db -> f ("qty");
$web_min = 0 + $db -> f ("min");

$db -> query ("select count(*) as qty, sum(ceil(billsec/60)) as min from callrecords_table where dcontext like 'callback-web%' and date(calldate) = date(now());");
$db -> next_record();
$callbackweb = $db -> f ("qty");
$callbackweb_min = 0 + $db -> f ("min");

$total = $callback + $android + $movil + $callthrough + $web + $callbackweb + $marcador;
$total_min = $callback_min + $android_min + $movil_min + $callthrough_min + $web_min + $callbackweb_min + $marcador_min;

$llamadas  = "<table cellspacing=1 class=\"llamadas\"><tr><th>Metodo</th><th>Qty</th><th>Minutos</th></tr>";
$llamadas .= "<tr><td>Marcador</td><td>$marcador</td><td>$marcador_min</td></tr>";
$llamadas .= "<tr><td>Callback</td><td>$callback</td><td>$callback_min</td></tr>";
$llamadas .= "<tr><td>Android/iOS App</td><td>$android</td><td>$android_min</td></tr>";
$llamadas .= "<tr><td>Interfaz Web</td><td>$callbackweb</td><td>$callbackweb_min</td></tr>";
$llamadas .= "<tr><td>m.numerocentral.com</td><td>$movil</td><td>$movil_min</td></tr>";
$llamadas .= "<tr><td>Llamame</td><td>$web</td><td>$web_min</td></tr>";
$llamadas .= "<tr><td>Total</td><td>$total</td><td>$total_min</td></tr>";

$llamadas .= "</table>";

$objResponse = new xajaxResponse();

ob_start();
passthru("/usr/sbin/asterisk -rx \"core show channels\"");
echo "\n";
passthru("/usr/sbin/asterisk -rx \"pri show spans\"");
echo "\n";
passthru("/usr/sbin/asterisk -rx \"dahdi show status\"");
echo "\nLlamadas 1/2 Verbose\n";
passthru("/usr/sbin/asterisk -rx \"core show channels verbose\"");
echo "\nStatus Array\n";
passthru("cat /tmp/hparray");
$sip_peers = ob_get_contents();
ob_end_clean();

$sip_peers = str_replace("Unable to disable core size resource limit: Operation not permitted\n", "", $sip_peers);
$sip_peers = str_replace("Parsing /etc/asterisk/extconfig.conf", "", $sip_peers);
$sip_peers = str_replace("0 db (CSU)/0-133 feet (DSX-1)", "", $sip_peers);
$sip_peers = str_replace("Asterisk ending (0).", "", $sip_peers);
$sip_peers = str_replace("0m", "", $sip_peers);

$sip_peers = str_replace("037m", "", $sip_peers);

$result = "$llamadas" . "<pre>" . htmlspecialchars($sip_peers) . "</pre>";

buildMenuAdm($objResponse,5);
$objResponse -> Assign("contenido","innerHTML", $result );
return $objResponse;
}

function admVendedor() {
global $db, $level;
$objResponse = new xajaxResponse();

buildMenuAdm($objResponse,6);

$db -> query ("select * from vendedores order by id");

if ( $level == 0 ) {
$result = "<a href=\"#\" onclick=\"xajax_admEditVendedor(-1,0)\">Agregar Vendedor</a><br/>";
}
$result .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Nombre</th><th>Telefono Recarga</th><th>Saldo</th><th>Opciones</th></tr>";

while ( $db -> next_record() ) {
	$id = $db -> f ("id");
	$result .= "<tr><td><a href=\"#\" onclick=\"xajax_admVShowClients($id)\">" . $db -> f ("nombre" ) . "</a></td>";
	$result .= "<td><a href=\"#\" onclick=\"xajax_admVShowLog($id)\">" . $db -> f ("numero_recarga" ) . "</a></td>";
	$result .= "<td align='right'> " . number_format($db -> f ("saldo" ),2) . " Q</td>";
	if ( $id == 1 ) {
		$result .= "<td></td>";
	} else {
		if ( $level == 0 ) {
			$result .= "<td><a href='#' onclick='xajax_admEditVendedor($id,0)'><img border=0 src=/images/edit.jpg></a>";
		} else {
			$result .= "<td></td></tr>";
		}	
	}
}

$objResponse -> Assign("contenido","innerHTML", $result );
return $objResponse;
}

function admVShowLog ( $id_vendedor ) {
global $userid, $level;
global $db,$db2;
$objResponse = new xajaxResponse();

$db -> query ("select * from vendedores where id = $id_vendedor" );
$db -> next_record();

$contenido .= "<a href=\"#\" onclick=\"xajax_admVendedor()\">Regresar</a><p>";
$contenido .= "Vendedor: <strong>" . $db -> f ("nombre") . "</strong>"; 
$contenido .= ", Numero de recarga: <strong>" . $db -> f ("numero_recarga") . "</strong>";
$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha</th><th>Numero</th><th>Minutos</th></tr>";

$db -> query ("select * from log_recarga_clientes where id_vendedor = $id_vendedor order by logdate desc");
while ( $db -> next_record() ) {
	$contenido .= "<tr><td>" . $db -> f ("logdate") . "</td><td>" . $db -> f ("accountcode");
	$contenido .= "</td><td>" . $db -> f ("minutes") . "</td></tr>";
}

$objResponse -> assign("contenido","innerHTML", $contenido );
return $objResponse;
}

function admVShowClients ( $id_vendedor ) {
global $userid, $level;
global $db,$db2;
$objResponse = new xajaxResponse();

$contenido .= "<a href=\"#\" onclick=\"xajax_admVendedor()\">Regresar</a><p>";
$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Numero</th><th>Nombre</th><th>E-Mail</th><th>Saldo</th><th>Plan</th><th>Vence</th><th>Dia</th><th>Vendedor</th><th></th></tr>";

$db -> query ("select id_vendedor, vendedores.nombre as vendedor, id_plan, users.uid, accountcode, users.name as name, email, saldo_minutos, plans.name as plan_name, valid_days - datediff(now(), fecha_ingreso_saldo) as vence, day(fecha_inicio) as cobro from users, plans, vendedores, saldo where users.uid = saldo.uid and id_vendedor = vendedores.id and id_plan = plans.id and users.uid>1 and id_vendedor = $id_vendedor order by accountcode");

while ( $db -> next_record() ) {
	$id_plan = $db -> f ("id_plan");
	$vence = ( $db -> f ("vence") > 0 )? $db -> f ("vence"):"-";
	$cobro = ( $db -> f ("cobro") > 0 )? $db -> f ("cobro"):"";
	$vendedor = $db -> f ("vendedor");

	if ( $id_plan == 14 ) {
		$contenido .= "<tr><td>" . $db -> f ("accountcode") . "</td><td>" .
			htmlentities(substr($db ->f ("name"),0,25), ENT_QUOTES, "utf-8") .
			"</td><td>" . substr($db ->f ("email"),0,30) . "</td><td>" . $db -> f ("saldo_minutos") . "</td>" .
			"<td>" . substr($db -> f ("plan_name"),0,13) . "</td>" .
			"<td>" . $vence . "</td><td>" . $cobro . "</td>" .
			"<td>" . $vendedor . "</td>" ;
		if ( $level == 0 ) {
			$contenido .=
				"<td><a href=\"#\" onclick=\"xajax_editCustomer(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/edit.jpg\"></a>" .
				"<a href=\"#\" onclick=\"xajax_custAddMinutes(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/dollar.gif\"></a>" .
				"</td></tr>";
		} else {
			$contenido .= "<td></td></tr>";
		}
	} else {
		$contenido .= "<tr><td>" . $db -> f ("accountcode") . "</td><td><a href=\"#\" onclick=\"xajax_admCDRUser(" .
			$db -> f ("accountcode") . ")\">" .  htmlentities(substr($db ->f ("name"),0,25), ENT_QUOTES, "utf-8") . "</a>".
			"</td><td>" . substr($db ->f ("email"),0,30) . "</td><td>" . $db -> f ("saldo_minutos") . "</td>" .
			"<td>" . substr($db -> f ("plan_name"),0,13) . "</td>" .
			"<td>" . $vence . "</td><td>" . $cobro . "</td>" .
			"<td>" . $vendedor . "</td>" ;
		if ( $level == 0 ) {
			$contenido .=
			"<td><a href=\"#\" onclick=\"xajax_editCustomer(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/edit.jpg\"></a>" .
			"<a href=\"#\" onclick=\"xajax_custAddMinutes(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/dollar.gif\"></a>" .
			"</td></tr>";
		} else {
			$contenido .= "<td></td></tr>";
		}
	}
}

$objResponse -> assign("contenido","innerHTML", $contenido );
return $objResponse;
}



function escape ($value) {
if (get_magic_quotes_gpc()) {
$value = stripslashes($value);
}
$newValue = @mysqli_real_escape_string($value);
if(FALSE === $newValue) {
$newValue = @mysqli_escape_string($value);
}
return $newValue;
} 

function admcallsPage($pagina) {
$objResponse = new xajaxResponse();
$contenido = admllamadas($pagina,"","","");
$objResponse->assign("contenido","innerHTML",$contenido);
return $objResponse;
}

function admllamadas($pagina, $numero, $fechade, $fechaa) {
global $accountcode;
global $db;

$contactos = array();

$conditional = "and lastapp <> 'DISA' and dcontext <> 'default'";
if ( ! ( trim($numero) == "" ) ) {
	$conditional = $conditional . " and ( dst like '%" . trim($numero) . "%' or src like '%" . trim($numero) . "%')";	
}
if ( ! ( $_SESSION['admacct'] == "" ) ) {
	$conditional = $conditional . " and accountcode = '" . trim($_SESSION['admacct']) . "'";
}
$conditional .= " order by calldate desc";

$db -> query ("select count(*) as qty from callrecords_table where accountcode = accountcode $conditional" );
$db -> next_record();
$qty = $db -> f ("qty");
if ( $qty > 0 ) {
	$url = "";
	$paginas = ceil ( $qty / 20 );
	$pagina = ($pagina>0)?$pagina:1;

	if ( $pagina > 1 )
		$url = $url . "<a href=\"#\" onclick=\"admcallsGetPage(" . ( $pagina - 1 ) . ")\">&lt;&lt;</a>";

	$url = $url . " Pagina " . $pagina . " de " . $paginas . " ";

	if ( $pagina < $paginas )
		$url = $url . "<a href=\"#\" onclick=\"admcallsGetPage(" . ( $pagina + 1 ) . ")\">&gt;&gt;</a>";

	$indice = ( $pagina - 1 ) * 20;

	$contenido = "";
	$contenido = "<table cellspacing=2 class=\"llamadas\"><tr><th>Fecha/Hora</th><th>Src</th><th>Dst</th><th>Duraci&oacute;n</th><th></th><th></th></tr>";
	$db -> query ("select lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table where accountcode = accountcode $conditional limit $indice,20" );
	while ( $db -> next_record() ) {
		$td = "";
		if ( ! ($db -> f ("disposition") == "ANSWERED") ) {
			$td = "style=\"background-color: #f0a0af\"";
		}

		$lastapp = $db -> f ("lastapp");

		$userfield = $db -> f ("userfield");
		$uservars = explode(":",$userfield);
		$dcontext = $db -> f ("dcontext");
		$flash = "";

		$minutos = ceil ( $db -> f ( "duration" ) / 60 ) . " min.";

		$callback = 0; $price = "";

		if ( $lastapp == "VoiceMail" || $lastapp == "voicemail" ) {
			$flash = "Mensaje de Voz";
		}

		$dst = $db -> f ("dst");

		if ( $dcontext == "callback-android" || $dcontext == "callback-android-record" ||
                        $dcontext == "callback-movil" || $dcontext == "callback-movil-record" ||
                        $dcontext == "callback-web" || $dcontext == "callback-web-record" ) {
			$recordingvars = explode("-",$uservars[1]);
			$dst = $recordingvars[0];
			$pos = 0;
                        $pos = strrpos($dst, "/" );
                        if ( $pos > 0 ) {
                                $dst = substr($dst, $pos + 1 );
                        }
		} 

		$contenido .= "<tr><td $td>" . $db -> f ("calldate") . "</td><td $td>" . $db -> f ("src"). "</td><td $td>" .
			$dst ."</td><td $td>" . $minutos . "</td><td $td>$price</td><td $td>$flash</td></tr>";
			
	}
	$contenido .= "</table><div align=right>" . $url . "</div>";;

	return $contenido;
} else {
	return "No encontramos llamadas...";
}
}

function admEditVendedor( $id_vendedor, $action ) {
global $db;

if ( $action == "0" ) {
	$form ='<div class="padder"><form id="customerForm" name="customerForm" action="javascript:void(null);" onsubmit="editCustomer()">
		<input type="hidden" name="type" value="3"/>
		<input type="hidden" name="id" value="' . $id_vendedor. '"/>
		<table>';

	if ( $id_vendedor > 0 ) {
		$db -> query ("select nombre from vendedores where id = $id_vendedor");
		if ( $db -> next_record() ) {
			$customer = $db -> f ("nombre");
			$telefono = $db -> f ("numero_recarga");
	
			$form.='<tr><td>Nombre:</td><td><input type="text" value="' . $customer .'" name="nombre"/></td></tr>
				<tr><td>Numero Recarga:</td><td><input type="text" value="' . $telefono .'" name="telefono"/></td></tr>
				<tr><td></td><td><input type="submit" value="Editar Vendedor"></td></tr></table></form></div></br></br>&nbsp;';
		}
	} else {
			$form.='<tr><td>Nombre:</td><td><input type="text" name="nombre"/></tr></tr>
				<tr><td></td><td><input type="submit" value="Agregar Vendedor"></td></tr></table></form></div></br></br>&nbsp;';
	}

	$objResponse = new xajaxResponse();
	$objResponse->assign("contenido","innerHTML",$form);
	return $objResponse;
}

if ( $action == "1" ) {
	$db -> query ( "update users set id_vendedor = 1 where id_vendedor = $id_vendedor");
	$db -> query ( "delete from vendedores where id = $id_vendedor");
	
	$objResponse = admVendedor();
	$objResponse -> alert ("Vendedor Borrado.");
	return $objResponse;
}
}

function admEditPago( $id_pago ) {
	global $db;

	$pago = escape($id_pago);	
	$db -> query ( "select *, date(fecha_pago) as fecha_pago from pagos where id_pago = $pago" );
	if ( $db -> next_record() ) {
		$form = '<div class="padder"><form id="paymentForm" name="paymentForm" action="javascript:void(null);" onsubmit="editPagos()">
                <input type="hidden" name="id" value="' . $pago . '"/>
                <table>';
		$form .="<table><tr><td>Cuenta</td><td>" . $db -> f ("accountcode") . "</td></tr>";
		$form .="<tr><td>Minutos</td><td>" . $db -> f ("minutos") . "</td></tr>";
		$form .="<tr><td>Monto</td><td>" . $db -> f ("monto") . "</td></tr>";
		$form .="<tr><td>Fecha Pago</td><td>" . $db -> f ("fecha_pago") . "</td></tr>";
		$form .="<tr><td>Documento</td><td><input type=\"text\" name=\"docto\" size=\"35\" value=\"" . $db -> f ("documento") . "\" /></td></tr>";
		$form .="<tr><td># Factura</td><td><input type=\"text\" name=\"factura\" size=\"35\" value=\"" . $db -> f ("factura") . "\" /></td></tr>";
		$form .='<tr><td></td><td><input type="submit" value="Editar Pago"></td></tr></table></form></div></br>&nbsp;';
		$form .="</table";
	} else {
		$form = "<br/>Error!!";
	}

	$objResponse = new xajaxResponse();
	$objResponse -> assign("contenido","innerHTML",$form);
	return $objResponse;
}

function custAddMinutes( $id_user ) {
global $db;

$form ='<div class="padder"><form id="customerForm" name="customerForm" action="javascript:void(null);" onsubmit="editCustomer()">
	<input type="hidden" name="type" value="2"/>
	<input type="hidden" name="id" value="' . $id_user . '"/>
	<input type="hidden" name="tipo_pago" value="1"/>
	<table>';

$db -> query ("select name, id_plan, accountcode from users where uid = $id_user");
if ( $db -> next_record() ) {
	$id_plan = $db -> f ("id_plan");
	$account = $db -> f ("accountcode");
	$customer = $db -> f ("name");

	$db -> query ("select name, price, minutes from plans where id = $id_plan");
	$db -> next_record();
	$name = $db -> f ("name");

	$price = "";
	$minutos = "";

	$fecha = date('Y-m-d');
	
	$mes = date('m');
	$ano = date('Y');

	$form.='<tr><td>Cliente:</td><td>' . $customer .'</tr></tr>
		<tr><td>Numero:</td><td>' . $account . '</td></tr>
		<tr><td>Plan:</td><td>' . $name . '</td></tr>
<!--		<tr><td>Recarga:</td><td><select name="charges">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option> -->
			</select></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>Fecha:</td><td><input type=text length=8 name="fecha" value="'. $fecha . '"></td></tr>
<!--		<tr><td>Forma de Pago:</td><td><select name="tipo_pago">
			<option value="1">Efectivo</option>
			<option value="2">Cheque</option>
			<option value="3">Cheque otro banco</option> 
			</select></td></tr> -->
		<tr><td>Banco</td><td><select name="banco">
			<option value="1">Industrial</option>
			<option value="2">BAM</option>
			<option value="3">GyT</option> 
			<option value="4">Banrural</option> 
			<option value="5">BAC</option> 
			<option value="10">VisaNet</option> 
			<option value="11">Serbipagos</option> 
			<option value="12">Paypal</option>
			<option value="13">Interno</option>
			<option value="14">Saldo</option>
			</select></td></tr>
		<tr><td>Documento:</td><td><input type=text length=15 name="documento"></td></tr>
		<tr><td>Monto:</td><td><input type=text length=5 name="monto" value="' . $price . '"></td></tr>
		<tr><td>Minutos/Quetzales:</td><td><input type=text length=5 name="minutos" value="' . $minutes . '"></td></tr>
		<tr><td>FACE-63-NUMCE-001-:</td><td><input type=text length=15 name="factura"></td></tr>
		<tr><td>Fecha a pagar</td><td>Mes:&nbsp;<select name="mes_pago">';

		setlocale(LC_TIME, "es_GT.utf-8");

		for ($m=1;$m<=12;$m++) {
			if ($m==$mes) {
				$form .= "<option value=\"" . sprintf("%02d", $m) . "\" selected=\"selected\">" . ucfirst(strftime('%B', mktime(0,0,0,$m,1))) . "</option>";
			} else {
				$form .= "<option value=\"" . sprintf("%02d", $m) . "\" >" . ucfirst(strftime('%B', mktime(0,0,0,$m,1))) . "</option>";
			}
		}

	
		$form .= '</select>&nbsp;A&ntilde;o:&nbsp;<select name="ano_pago">';

		for ($y=$ano-1;$y<=$ano+1;$y++) {
			if ($y==$ano) {
				$form .= "<option value=\"$y\" selected=\"selected\">$y</option>";
			} else {
				$form .= "<option value=\"$y\">$y</option>";
			}
		}

		$form .= '</select></td></tr>';
		$form .= '<tr><td>Tipo de Pago</td><td><select name="recarga_pago">';
		$form .= '<option value=0>Minutos</option><option value=1 selected>Mensualidad</option><option value=2>Quetzales</option><option value=3>Mensualidad con Saldo</option></select></td></tr>';
		$form .= '<br/><tr><td>&nbsp;</td></tr>';
		$form .='<tr><td></td><td><input type="submit" value="Agregar Pago"></td></tr></table></form></div></br>&nbsp;';

if ( $id_user > 0 ) {
        $db -> query ( "select * from users where uid='$id_user'" );
        if ( $db -> next_record() ) {
		$accountcode = $db -> f ("accountcode");

$bancos = Array ( 1 => "Industrial", 2 => "BAM", 3 => "GyT", 4 => "Banrural", 5 => "BAC", 10 => "VisaNet", 11 => "Serbipagos", 12 => "Paypal", 13 => "Interno", 14 => "Saldo" );
$pagos  = Array ( 1 => "Efectivo", 2 => "Cheque", 3 => "Cheque O/B" );

                $opciones = '';
		$db -> query ( "select *, date(fecha_pago) as fecha_pago from pagos where accountcode = '$accountcode' order by fecha_pago desc limit 4" );
		if ( $db -> next_record() ) {
			$opciones .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha Ingreso</th><th>Fecha Pago</th><th>Banco</th><th>Tipo Pago</th><th>Documento</th><th>Minutos/Quetzales.</th><th>Monto</th><th>Factura</th><th>Mes</th></tr>";
			$total_minutos = 0; $total_monto = 0;

			do {

				$fecha_aplica = $db -> f ("fecha_aplica");

				$eem = explode('-', $fecha_aplica);
				$mes_aplica = $eem[1];
				$ano_aplica = $eem[0];
				$fecha_aplica = $mes_aplica . '/' . $ano_aplica;
				if ( $mes_aplica == '00' && $ano_aplica == '0000' ) {
					$fecha_aplica = '';
				}

				$factura = $db -> f ("factura");

				if ( strlen($factura) == 3 && $factura != "N/D" ) {
					$factura= "130000000" . $factura;
				} else if ( strlen($factura) == 2 ) {
					$factura= "1300000000" . $factura;
				} else if ( strlen($factura) == 1 ) {
					$factura= "13000000000" . $factura;
				}


				$minutos = number_format($db -> f ("minutos"), 0);
				$total_minutos += $db -> f ("minutos");
				$total_monto += $db -> f ("monto");
				$opciones .= "<tr><td>" . $db -> f ("fecha_ingreso") . "</td><td>" . $db -> f ("fecha_pago") . "</td>" .
					  "<td>" . $bancos[ $db -> f ("banco") ] . "</td><td>" . $pagos [ $db -> f ("forma_pago") ] . 
			                  "</td><td>" . $db -> f ("documento") . "</td><td>" . $minutos . "</td><td>Q " . $db -> f("monto") . 
					  "</td><td>" . $factura . "</td><td>" . $fecha_aplica .  "</td></tr>";
			} while ( $db -> next_record() );
			$total_minutos = number_format($total_minutos, 0);
			$total_monto   = number_format($total_monto,2);
			$opciones .= "<tr><td align=right colspan=5>Total</td><td>$total_minutos</td><td>Q $total_monto</td></tr></table>";
		} else {
			$opciones .= "<br/>No Hay Pagos Ingresados.";
		}
        } else {
                $opciones = "";
        }
}

	$objResponse = new xajaxResponse();
	$objResponse->assign("contenido","innerHTML",$form . $opciones);
	return $objResponse;
}
}

function editCustomer( $id_user ) {
global $db, $db2;

if ( $id_user > 0 ) {
	$db -> query ( "select * from users where uid='$id_user'" );
	if ( $db -> next_record() ) {
		$accountcode = $db -> f ("accountcode");
		$opciones = '
<form id="customerForm" name="customerForm" action="javascript:void(null);" onsubmit="editCustomer()">
<table>
<tr><td>Numero:</td><td>' . $db -> f ("accountcode") . '</td></tr>';
		if ( file_exists( '/var/spool/asterisk/dpi/' . $accountcode . '.pdf' ) ) {
			$pdf_file = "<a href=\"dpi_admin.php?file=$accountcode\"><img border=0 src=/images/adobe-reader-icon.gif /></a>";
			$opciones .= '<tr><td>DPI:</td><td>' . $pdf_file . ' </td></tr>';
		}

$opciones .= '<tr><td>Nombre:</td><td><input size=35 type="text" name="name" value="' . $db -> f ("name") . '"/></td></tr>
<tr><td>E-Mail:</td><td><input size=35 type="text" name="email" value="' . $db -> f ("email") .'"/></td></tr>
<tr><td>Supervisa:</td><td><input size=25 type="text" name="supervisa" value="' . $db -> f("supervisa") . '"/></td></tr>
<tr><td>Usuario:</td><td>' . $db -> f ("username") . '</td></tr>
<tr><td>Contrase&ntilde;a:</td><td><input size=15 type="password" name="password" value="' . $db -> f("passwd") . '"/></td></tr>
<tr><td>Confirmar:</td><td><input size=15 type="password" name="password2" value="' . $db -> f("passwd") . '"/></td></tr>
<input type="hidden" name="number" value="' . $db -> f ("accountcode"). '"/>
<input type="hidden" name="user" value="' . $db -> f ("username"). '"/>';
		$id_plan = $db -> f ("id_plan");
		$id_vendedor = $db -> f ("id_vendedor");

		$exten1digit = $db -> f ("exten1digit");
		$extensiones = $db -> f ("extensiones");

		$nit   = $db -> f ("nit");
		$monto = $db -> f ("monto");
		$minutos = $db -> f ("minutos");


		$db -> query ( "select id, name from plans");
		while ( $db -> next_record() ) {
			$selected = "";
			if ( $id_plan == $db -> f ("id") ) {
				$selected = "SELECTED";
			}
			$plan .= "<option value=\"" . $db -> f ("id") . "\" $selected>" . $db -> f ("name") . "</option>";
		}

		$oneselect = "";
		for ( $i = 0; $i <= 10; $i++) {
			$selected = "";
			if ( $exten1digit == $i )
				$selected = "SELECTED";
			$oneselect .= "<option value=\"$i\" $selected>$i</option>";
		}

		$fourselect = "";
		for ( $i = 0; $i <= 15; $i++) {
			$selected = "";
			if ( $extensiones== $i )
				$selected = "SELECTED";
			$fourselect .= "<option value=\"$i\" $selected>$i</option>";
		}

		$vendedores = "";
		$db -> query ( "select id, nombre from vendedores");
		while ( $db -> next_record() ) {
			$selected = "";
			if ( $id_vendedor == $db -> f ("id") ) {
				$selected = "SELECTED";
			}
			$vendedores .= "<option value=\"" . $db -> f ("id") . "\" $selected>" . $db -> f ("nombre") . "</option>";
		}

		$db -> query ( "select * from voicemail_users where customer_id = '$accountcode'" );
		$db -> next_record();

		$db -> query ( "select * from facturacion where accountcode = '$accountcode'");
		$email = "";
		$misc = "";
		if ( $db -> next_record() ) {
			$email = $db -> f ("email");
			$misc = $db -> f ("misc");
		}

		$opciones .= '
<input type="hidden" name="id" value="' . $id_user . '"/>
<input type="hidden" name="type" value="1"/>
<!-- <tr><td>Clave Buzon:</td><td> -->
<input size=5 type="hidden" name="vmpass" value="' . $db -> f("password") . '"/>
<!-- </td></tr> -->
<input size=5 type="hidden" name="vmpass2" value="' . $db -> f("password") . '"/>
<tr><td>Plan:</td><td><select name="plan">' . $plan .'</select>
<tr><td>Ext. 1 D&iacute;gito:</td><td><select name="exten1digit">' . $oneselect .'</select>
<tr><td>Ext. 4 D&iacute;gitos:</td><td><select name="extensiones">' . $fourselect  .'</select>
<tr><td>Vendedor:</td><td><select name="vendedor">' . $vendedores .'</select>
<tr><td><b>Facturaci&oacute;n:</b></td></tr>
<tr><td>NIT:</td><td><input size=14 type="text" name="nit" value="' . $nit . '"/></td></tr>
<tr><td>Monto:</td><td><input size=10 type="text" name="monto" value="' . $monto . '"/></td></tr>
<tr><td>Minutos en Plan:</td><td><input size=10 type="text" name="minutos" value="' . $minutos . '"/></td></tr>
<tr><td>E-Mail Fac.:</td><td><input size=150 type="text" name="facemail" value="' . $email. '"/></td></tr>
<tr><td>Misc Fac.:</td><td><input size=150 type="text" name="facemisc" value="' . $misc. '"/></td></tr>';

		$opciones .= '<tr><td></td><td>&nbsp;</td></tr><tr><td></td><td>';
		$db -> query ("select number from nc_mynumber where accountcode = '$accountcode'");
		while ( $db -> next_record()) {
			$opciones .= $db -> f ("number") . "&nbsp;";
		}
		$db -> query ("select number from ivr_option where accountcode = '$accountcode'");
		while ( $db -> next_record()) {
			$opciones .= $db -> f ("number") . "&nbsp;";
		}

		$opciones .= '
<tr><td></td><td>&nbsp;</td></tr>
<tr><td></td><td><input type="submit" value="Guardar Cambios"></td></tr></table></form></br></br>&nbsp;
			';

		$contenido = "<br/><form action='dpi-upload.php' enctype='multipart/form-data' method='post'>";
                $contenido .= "DPI de cliente $accountcode</br>";
                $contenido .= "Subir DPI: <input type=file name=\"dpipdf\"/>";
                $contenido .= "<input type=hidden name='accountcode' value='$accountcode'/>";
                $contenido .= "<input type=submit value='Subir'/></form>";
		$opciones .= $contenido;
		
		if ( file_exists( '/var/spool/asterisk/dpi/' . $accountcode . '.pdf' ) ) {
                        $pdf_file = "<a href=\"dpi_admin.php?file=$accountcode\"><img border=0 src=/images/adobe-reader-icon.gif /></a>";
			$opciones .= $pdf_file;
		}
	} else {
		$opciones = "";
	}
} else {
	$db -> query ( "select id, name from plans");
	$plan .= "<option value=\"-1\">Escoja un plan</option>";
	while ( $db -> next_record() ) {
		$plan .= "<option value=\"" . $db -> f ("id") . "\">" . $db -> f ("name") . "</option>";
	}

	$vendedores = "<option value=\"-1\">Escoja un vendedor</option>";
	$db -> query ( "select id, nombre from vendedores");
	while ( $db -> next_record() ) {
		$vendedores .= "<option value=\"" . $db -> f ("id") . "\">" . $db -> f ("nombre") . "</option>";
	}

	$selected = "";
	$oneselect = "";
	for ( $i = 0; $i <= 10; $i++) {
		$oneselect .= "<option value=\"$i\" $selected>$i</option>";
	}

	$fourselect = "";
	for ( $i = 0; $i <= 15; $i++) {
		$fourselect .= "<option value=\"$i\" $selected>$i</option>";
	}

	$opciones = '
<form id="customerForm" name="customerForm" action="javascript:void(null);" onsubmit="editCustomer()">
<table>
<tr><td>N&uacute;mero:</td><td><input size=10 type="text" maxlength="10" name="number" value=""/></td></tr>
<tr><td>Nombre:</td><td><input size=35 type="text" name="name" value=""/></td></tr>
<tr><td>E-Mail:</td><td><input size=35 type="text" name="email" value=""/></td></tr>
<tr><td>Supervisa:</td><td><input size=25 type="text" name="supervisa" value=""/></td></tr>
<tr><td>Usuario:</td><td><input size=15 type="text" name="user" value=""/></td></tr>
<tr><td>Contrase&ntilde;a:</td><td><input size=15 type="password" name="password" value=""/></td></tr>
<tr><td>Confirmar:</td><td><input size=15 type="password" name="password2" value=""/></td></tr>
<!-- <tr><td>Clave Buzon:</td><td> -->
<input size=5 type="hidden" name="vmpass" value="0000"/>
<!-- </td></tr> -->
<!-- <tr><td>Confirmar:</td><td> -->
<input size=5 type="hidden" name="vmpass2" value="0000"/>
<!-- </td></tr> -->
<tr><td>Plan:</td><td><select name="plan">' . $plan .'</select>
<tr><td>Vendedor:</td><td><select name="vendedor">' . $vendedores .'</select>
<tr><td>Ext. 1 D&iacute;gito:</td><td><select name="exten1digit">' . $oneselect .'</select>
<tr><td>Ext. 4 D&iacute;gitos:</td><td><select name="extensiones">' . $fourselect  .'</select>
<tr><td><b>Facturaci&oacute;n:</b></td></tr>
<tr><td>NIT:</td><td><input size=14 type="text" name="nit" value=""/></td></tr>
<tr><td>Monto:</td><td><input size=10 type="text" name="monto" value=""/></td></tr>
<tr><td>Minutos de Plan:</td><td><input size=10 type="text" name="minutos" value=""/></td></tr>
<tr><td>E-Mail Fac.:</td><td><input size=150 type="text" name="facemail" value=""/></td></tr>
<tr><td>Misc Fac.:</td><td><input size=150 type="text" name="facemisc" value=""/></td></tr>
<input type="hidden" name="id" value="-1"/>
<input type="hidden" name="type" value="1"/>
<tr><td></td><td>&nbsp;</td></tr>
<tr><td></td><td><input type="submit" value="Guardar Cambios"></td></tr></table></form></br></br>&nbsp;
			';
}

$objResponse = new xajaxResponse();
$objResponse->assign("contenido","innerHTML",$opciones);
return $objResponse;
}

function customerProcessForm( $form ) {
global $accountcode, $level, $userid;
global $db;

$type = trim($form['type']);

if ( $level <> 0 ) {
	$objResponse = new xajaxResponse();
	$objResponse -> alert ("No tiene permisos.");
	return $objResponse;
}

if ( $type == 1 ) {
	$user = trim($form['user']);
	$number = trim($form['number']);
	$email = trim($form['email']);
	$password = $form['password'];
	$password2 = $form['password2'];
	$vmpass = "0000";
	$vmpass2 = "0000";
	$name = trim($form['name']);
	$plan = trim($form['plan']);
	$id = trim($form['id']);
	$supervisa = trim($form['supervisa']);
	$id_vendedor = trim($form['vendedor']);
	$extensiones = trim($form['extensiones']);
	$exten1digit = trim($form['exten1digit']);
	$nit = trim($form['nit']);
	$monto = trim($form['monto']);
	$minutos = trim($form['minutos']);
	$facemail = trim($form['facemail']);
	$facemisc = trim($form['facemisc']);

	if ($id_vendedor == "-1") {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Porfavor Escoja un Vendedor.");
		return $objResponse;
	}

	if (strlen($name) == 0 ) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Porfavor Ingrese el Nombre.");
		return $objResponse;
	}

	if (!is_numeric($plan)  || $plan < 0) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Porfavor escoja un plan.");
		return $objResponse;
	}
	
	if ( $plan != 7 && $plan != 12 && $plan != 14 ) {
	}
		
	if (!( $password == $password2 ) || strlen($password) == 0) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Las Claves de la Web no coinciden, o es invalida.");
		return $objResponse;
	}

	if ( $plan != 12 && $plan != 14 && $plan != 13) {
		if ( strlen($number) <> 8 || !is_numeric($number) ) {
			$objResponse = new xajaxResponse();
			$objResponse -> alert ("Porfavor ingrese un Numero Valido.");
			return $objResponse;
		}
	}
	
	if ( strlen($user) == 0 ) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Porfavor ingrese un usuario");
		return $objResponse;
	}

	$db -> query ("select count(*) as qty from users where username = '$user'");
	$db -> next_record();
	if ( $id == -1 && $db ->f ("qty") > 0 ) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("El Usuario Web ya existe.");
		return $objResponse;
	}

	$db -> query ("select count(*) as qty from users where accountcode = '$number'");
	$db -> next_record();
	if ( $id == -1 && $db ->f ("qty") > 0 ) {
			$objResponse = new xajaxResponse();
			$objResponse -> alert ("El Numero ya esta asignado.");
			return $objResponse;
	}

	list($userName, $mailDomain) = split("@", $email);
	if ( eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) ) {
		if ( checkdnsrr($mailDomain, "MX") ) {
		} else {
			$objResponse = new xajaxResponse();
			$objResponse -> alert ("El E-Mail especificado es invalido error validando $mailDomain.");
			return $objResponse;
		}
	} else {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("El E-Mail especificado es invalido 2.");
		return $objResponse;
	}

	if ( $id == -1 ) {
	//Usuario Nuevo
		if ( $plan == 7 ) { // Plan Salientes
			$db -> query ("insert into users values (null, '$user', md5('$password'), '$email', '$name', '$number', $plan, NULL, now(), NULL, $id_vendedor, 0, 0, '$nit', $monto, $minutos, 0)");
			$db -> query ("select uid from users where accountcode = '$number'");
			$db -> next_record();
			$uid = $db -> f ("uid");
			$db -> query ("delete from saldo where uid = $uid");
			$db -> query ("insert into saldo values (null, $uid, 0,0,0, NULL, NULL)");
		} elseif ( $plan == 13 ) {
			$db -> query ("insert into users values (null, '$user', md5('$password'), '$email', '$name', '$number', $plan, NULL, now(), NULL, $id_vendedor, 0, 0, '$nit', $monto, $minutos, 0)");
			$db -> query ("select uid from users where accountcode = '$number'");
			$db -> next_record();
			$uid = $db -> f ("uid");
			$db -> query ("delete from saldo where uid = $uid");
			$db -> query ("insert into saldo values (null, $uid, 0,0,0, NULL, NULL)");
		} elseif ( $plan == 12 || $plan == 14 ) {
			$db -> query ("insert into users values (null, '$user', md5('$password'), '$email', '$name', '$number', $plan, NULL, now(), '$supervisa', $id_vendedor, 0, 0, '$nit', $monto, $minutos, 0)");
			$db -> query ("select uid from users where accountcode = '$number'");
			$db -> next_record();
			$uid = $db -> f ("uid");
			$db -> query ("delete from saldo where uid = $uid");
			$db -> query ("insert into saldo values (null, $uid, 0,0,0, NULL, NULL)");
		} else {
			$db -> query ("insert into users values (null, '$user', md5('$password'), '$email', '$name', '$number', $plan, NULL, now(), NULL, $id_vendedor, $extensiones, $exten1digit, '$nit', $monto, $minutos, 0)");
			$db -> query ("insert into voicemail_users values (null, '$number', 'numerocentral', '$number', '$vmpass', '$name', '$email', '', now())");
			$db -> query ("select uid from users where accountcode = '$number'");
			$db -> next_record();
			$uid = $db -> f ("uid");
			$db -> query ("delete from saldo where uid = $uid");
			$db -> query ("insert into saldo values (null, $uid, 0,0,0, NULL, NULL)");
		}

		$db -> query ( "delete from facturacion where accountcode = '$number' ");
		$db -> query ( "insert into facturacion values ( null, '$number', '$facemail', '$facemisc') ");
	} else {
		$db -> query ("select count(*) as qty from users where uid='$id' and passwd='$password'");
		$db -> next_record();
		$qty = $db -> f ("qty");

		if ( $qty == 0 ) {
			$db -> query ("update users set name='$name',email='$email',passwd=md5('$password'),id_plan=$plan,supervisa='$supervisa',id_vendedor=$id_vendedor,extensiones=$extensiones,exten1digit=$exten1digit,nit='$nit',minutos=$minutos,monto=$monto where uid = '$id'");
		} else {
			$db -> query ("update users set name='$name',email='$email',id_plan=$plan,supervisa='$supervisa',id_vendedor=$id_vendedor,extensiones=$extensiones,exten1digit=$exten1digit,nit='$nit',minutos=$minutos,monto=$monto where uid = '$id'");
		}
		if ( $plan != 12 && $plan != 13 && $plan != 14 ) {
		}

		$db -> query ( "delete from facturacion where accountcode = '$number' ");
		$db -> query ( "insert into facturacion values ( null, '$number', '$facemail', '$facemisc' )");
	}
}

if ( $type == 2 ) {
	$id = trim($form['id']);

	$fecha = trim($form['fecha']);
	$tipo_pago = trim($form['tipo_pago']);
	$banco = trim($form['banco']);
	$documento = trim($form['documento']);
	$monto = trim($form['monto']);
	$minutos = trim($form['minutos']);
	$factura = '' . trim($form['factura']);
	$mes_pago = trim($form['mes_pago']);
	$ano_pago = trim($form['ano_pago']);
	$recarga_pago = trim($form['recarga_pago']);
	$fecha_pago = "$ano_pago" . '-' . $mes_pago . '-01';

	if ( strlen($fecha) == 0 || strlen($tipo_pago) == 0 || strlen($banco) == 0 || strlen(monto) == 0 || strlen($minutos) == 0 ) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Porfavor verifique los datos del pago.");
		return $objResponse;
	}

	if ( ! is_numeric ( $monto ) ) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("El monto debe de ser mayor a 0.");
		return $objResponse;
	}

	if ( ! is_numeric ( $minutos) ) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Los minutos deben de ser mayor a 0.");
		return $objResponse;
	}

	$db -> query ("select accountcode from users where uid=$id");
	$db -> next_record();
	$accountcode = $db -> f ("accountcode");

	if ( $accountcode == "" ) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Error 1010");
		return $objResponse;
	}


	if ( $recarga_pago != 3 ) {
		$db -> query ("select count(*) as qty from pagos where documento = '$documento'");
		$db -> next_record();
	
		if ( $db -> f ("qty") > 0 ) {
			$objResponse = new xajaxResponse();
			$objResponse -> alert ("Documento ya existe");
			return $objResponse;
		}

		if ( strlen($documento) == 0 ) {
			$objResponse = new xajaxResponse();
			$objResponse -> alert ("Porfavor verifique los datos del pago.");
			return $objResponse;
		}
	}

	if ( $banco == 13 ) {
		$facturar = 0;
	} else {
		$facturar = 1;
	}

	if ( $recarga_pago == 2 ) {
		if ( $monto != $minutos ) {
			$objResponse = new xajaxResponse();
			$objResponse -> alert ("El monto en quetzales debe ser igual a la recarga en quetzales.");
			return $objResponse;
		}
		$db -> query ("update saldo set saldo_qtz=saldo_qtz+$minutos where uid=$id");
	} else if ( $recarga_pago == 3) {
		$db -> query ("select saldo_qtz from saldo where uid = $id");
		$saldo_qtz = 0;
		if ($db -> next_record()) {
			$saldo_qtz = $db -> f ("saldo_qtz");
		}

		if ( $monto > $saldo_qtz ) {
			$objResponse = new xajaxResponse();
			$objResponse -> alert ("El usuario no tiene saldo suficiente para la recarga.");
			return $objResponse;
		}
		$db -> query ("update saldo set saldo_minutos=saldo_minutos+$minutos, saldo_qtz=saldo_qtz-$monto, fecha_ingreso_saldo=now() where uid=$id");
		$factura = "N/D";
		$banco = 14;
		$tipo_pago = 4;
		$facturar = 0;
		$documento = uniqid('', true);
	} else {
		if ($monto < 0 ) {
			$db -> query ("update saldo set saldo_qtz=saldo_qtz+$monto where uid=$id");
		}
		$db -> query ("update saldo set saldo_minutos=saldo_minutos+$minutos, fecha_ingreso_saldo=now() where uid=$id");
	}
	$db -> query ("insert into pagos values ( null, '$accountcode', now(), '$fecha', $tipo_pago, $banco, '$documento', $monto, $minutos, '$factura', $userid, '$fecha_pago', $recarga_pago, $facturar)");

}

if ( $type == 3 ) {
	$nombre = trim($form['nombre']);
	$numero = trim($form['telefono']);
	$id = trim($form['id']);

	if ( $id > 0 ) {
		$db -> query ( "update vendedores set nombre='$nombre',numero_recarga='$numero' where id = $id");
	} else {
		$db -> query (" insert into vendedores values ( null, '$nombre', '$numero', 0)" );
	}
	
	$objResponse = admVendedor();
	$objResponse -> alert ("Cambios guardados.");
	return $objResponse;
}

$objResponse = adminPanel();
$objResponse -> alert ("Cambios Guardados.");
return $objResponse;
}

function buildMenuAdm(&$objResponse,$selected) { 
	$factura='';
	$usuarios='';
	$cdr='';
	$stats='';
	$pagos='';
	$monitoreo='';
	$vendedores='';
	$recargas='';
	$mercado='';
	switch($selected) {
		case 1: $usuarios='class="selected"';break;
		case 2: $cdr='class="selected"';break;
		case 3: $stats='class="selected"';break;
		case 4: $pagos='class="selected"';break;
		case 5: $monitoreo='class="selected"';break;
		case 6: $vendedores='class="selected"';break;
		case 7: $recargas='class="selected"';break;
		case 8: $factura='class="selected"';break;
		case 9: $mercado='class="selected"';break;
	}

	$menu = '<li><a href="#" onclick="xajax_adminPanel()" ' . $usuarios. '>Usuarios</a></li>' .
                '<li><a href="#" onclick="xajax_admCDR()" ' . $cdr. '>CDR</a></li>' .
                '<li><a href="#" onclick="xajax_admEstadistica()" ' . $stats. '>Estadistica</a></li>' .
                '<li><a href="#" onclick="xajax_admPagos()" ' . $pagos . '>Pagos</a></li>' .
                '<li><a href="#" onclick="xajax_admFacturacion()" ' . $factura . '>Facturacion</a></li>' .
                '<li><a href="#" onclick="xajax_admMonitor()" ' . $monitoreo. '>Monitoreo</a></li>' .
                '<li><a href="#" onclick="xajax_admVendedor()" ' . $vendedores. '>Vendedores</a></li>' .
                '<li><a href="#" onclick="xajax_mmList()" ' . $mercado. '>Mercado</a></li>';

	$objResponse -> assign("menu","innerHTML",$menu);
}

function admFacturacion() {
	global $db;
        $objResponse = new xajaxResponse();
	buildMenuAdm($objResponse, 8 );

	$result = "<div><p><a href='/admin_catalogo_servicios.php'>Descargar Catalogo de Servicios <img src='/images/download.png'></a><br/>";
	$result .="<a href='/admin_catalogo_clientes.php'>Descargar Catalogo de Clientes <img src='/images/download.png'></a><br/>";
	$result .= "<a href='/admin_facturas.php'>Generar Archivo de Facturaci&oacute;n <img src='/images/download.png'></a><br/>";
	$result .= "<br/>Generar Archivo de Facturas Diario:<br /><form action='admin_facturas_dia.php' method='post'>";
	$result .= "<select name='fecha'>";
	
	$today =  new DateTime();
	$interval = new DateInterval('P1D');
	$interval->invert = 1; 
	$i = 0;
	for ( $i = 0; $i < 15; $i++ ) {
		$fecha = $today->format('Y-m-d');
		$result .= "<option value='" . $fecha . "'>$fecha</option>";
		$today->add($interval);
	}
	$result .= "<input type=submit value='Descargar'></form>";
	

	$objResponse -> Assign("contenido","innerHTML", $result );
	return $objResponse;
}

function admEstadistica() {
	global $db;
	$objResponse = new xajaxResponse();

	$minutos_mes = Array();
	$minutos_mes_out = Array();

	$db -> query ("select accountcode, month(calldate), year(calldate), sum(ceil(billsec/60)) as minutos from callrecords_table where year(calldate) = year(now()) and month(calldate) = month(now()) and length(accountcode)=8 and lastdata <> 'mensaje-saldo-insuficiente' group by accountcode, month(calldate), year(calldate)");
	
	while ( $db -> next_record() ) {
		$minutos_mes [ $db -> f ("accountcode") ] = $db -> f ("minutos");
	}

	$db -> query ("select accountcode, month(calldate), year(calldate), sum(ceil(billsec/60)) as minutos from callrecords_table where year(calldate) = year(now()) and month(calldate) = month(now()) and length(accountcode)=8 and userfield like 'callback%' group by accountcode, month(calldate), year(calldate)");
	while ( $db -> next_record() ) {
		$minutos_mes_out [ $db -> f ("accountcode") ] = $db -> f ("minutos");
	}

	$db -> query ("select accountcode, month(calldate), year(calldate), sum(ceil(billsec/60)) as minutos from callrecords_table where year(calldate) = year(now()) and month(calldate) = month(now()) and length(accountcode)=8 and dcontext = 'marcador' group by accountcode, month(calldate), year(calldate)");
	while ( $db -> next_record() ) {
		$minutos_mes_out [ $db -> f ("accountcode") ] += $db -> f ("minutos");
	}


	$db -> query ("select accountcode from users where length(accountcode)=8 order by accountcode");

	$cuentas = "<select name=\"accountcode\">";
	$cuentas .= "<option value=\"-1\">Todos</option>";
	while ( $db -> next_record() ) {
		$account = $db -> f ("accountcode");
		$cuentas .= "<option value=\"$account\">$account</option>";
	}
	$cuentas .= "</select>";

	$contenido = "<div class=\"padder\"><form id=\"statsForm\" name=\"statsForm\" action=\"javascript:void(null);\" onsubmit=\"submitStats();\">";
	$contenido .= "<table>";
	$contenido .= "<tr><td>Cuenta</td><td>$cuentas</td></tr>";
	$contenido .= '<tr><td>Fecha De:</td><td><input type="text" size=12 name="fechade" id="fechade" /><a href="javascript:NewCssCal(\'fechade\',\'yyyymmdd\')"><img border=0 src="/images/cal.gif"></a></td>';
	$contenido .= '<td>A:</td><td><input type="text" size=12 name="fechaa" id="fechaa" /><a href="javascript:NewCssCal(\'fechaa\',\'yyyymmdd\')"><img border=0 src="/images/cal.gif"></a></td></tr>';
	$contenido .= "<tr><td><input type=submit value='Buscar'></td></tr>";

	$contenido .= "</table></form></div>";


	$contenido .= "<table cellspacing=1 class=\"llamadas\">";
	$contenido .= "<tr><th>Cuenta</th><th>Entrantes</th><th>Salientes</th><th>Total</th></tr>";

	foreach ($minutos_mes as $account => $minutos) {
		$entrantes = $minutos - @$minutos_mes_out[$account];
		$salientes = 0 + @$minutos_mes_out[$account];
		$contenido .= "<tr><td>" . $account . "</td><td>$entrantes</td><td>" . $salientes . "</td><td>" . $minutos . "</td></tr>";
	}
	$contenido .= "</table>";
	
	buildMenuAdm($objResponse, 3 );
	$objResponse -> assign("contenido","innerHTML", $contenido);
	return $objResponse;
}

function processStats ( $form ) {
	global $db;

	$fechade = trim($form['fechade']);
	$fechaa  = trim($form['fechaa']);
	$accountcode = trim($form['accountcode']);

	if ( strlen($fechade) != 10 ) {
		$fechade = '2000-01-01';
	}

	if ( strlen($fechaa) != 10 ) {
		$fechaa = date ("Y-m-d");
	}


	$query = "";
	if ( $accountcode == "-1" ) {
	$query =  "select calldate, accountcode, sum(saliente) as saliente, sum(entrante) as entrante from (select date(calldate) as calldate, accountcode, sum(ceil(billsec/60)) as saliente, 0 as entrante from callrecords_table where date(calldate) between '$fechade' and '$fechaa' and length(accountcode) = 8 and lastdata <> 'mensaje-saldo-insuficiente' and userfield like 'callback%' group by date(calldate), accountcode union select date(calldate), accountcode, 0, sum(ceil(billsec/60)) from callrecords_table where date(calldate) between '$fechade' and '$fechaa' and length(accountcode) = 8 and lastdata <> 'mensaje-saldo-insuficiente' and userfield not like 'callback%' group by date(calldate), accountcode) as minutos group by calldate, accountcode order by accountcode, calldate";
	} else {
	$query =  "select calldate, accountcode, sum(saliente) as saliente, sum(entrante) as entrante from (select date(calldate) as calldate, accountcode, sum(ceil(billsec/60)) as saliente, 0 as entrante from callrecords_table where date(calldate) between '$fechade' and '$fechaa' and length(accountcode) = 8 and lastdata <> 'mensaje-saldo-insuficiente' and userfield like 'callback%' and accountcode = '$accountcode' group by date(calldate), accountcode union select date(calldate), accountcode, 0, sum(ceil(billsec/60)) from callrecords_table where date(calldate) between '$fechade' and '$fechaa' and length(accountcode) = 8 and lastdata <> 'mensaje-saldo-insuficiente' and userfield not like 'callback%' and accountcode = '$accountcode' group by date(calldate), accountcode) as minutos group by calldate, accountcode order by calldate;";
	}

	$db -> query ($query);
	$contenido  = "<table cellspacing=1 class=\"llamadas\">";
	$contenido .= "<tr><th>Fecha</th><th>Cuenta</th><th>Entrantes</th><th>Salientes</th><th>Total</th></tr>";

	$total_in = 0;
	$total_out = 0;
	while ( $db -> next_record() ) {
		$entrantes = $db -> f ("entrante");
		$salientes = $db -> f ("saliente");
		$account   = $db -> f ("accountcode");
		$calldate  = $db -> f ("calldate");

		$minutos = $entrantes + $salientes;
		$total_in += $entrantes;
		$total_out += $salientes;

		$contenido .= "<tr><td>$calldate</td><td>" . $account . "</td><td>$entrantes</td><td>" . $salientes . "</td><td>" . $minutos . "</td></tr>";
	}
	$total = $total_in + $total_out;
	$contenido .= "<tr><td></td><td>Total</td><td>$total_in</td><td>" . $total_out. "</td><td>" . $total . "</td></tr>";

	if ( $accountcode == "-1" ) {
		$total = $total_out * 2 - $total_in * 0.3;
		$contenido .= "<tr><td></td><td>Atel (por minuto)</td><td></td><td></td><td>" . $total . "</td></tr>";

		$query =  "select calldate, accountcode, sum(saliente) as saliente, sum(entrante) as entrante from (select date(calldate) as calldate, accountcode, sum(billsec) as saliente, 0 as entrante from callrecords_table where date(calldate) between '$fechade' and '$fechaa' and length(accountcode) = 8 and lastdata <> 'mensaje-saldo-insuficiente' and userfield like 'callback%' group by date(calldate), accountcode union select date(calldate), accountcode, 0, sum(billsec) from callrecords_table where date(calldate) between '$fechade' and '$fechaa' and length(accountcode) = 8 and lastdata <> 'mensaje-saldo-insuficiente' and userfield not like 'call%' and lastdata not like 'DAHDI%' group by date(calldate), accountcode) as minutos group by calldate, accountcode order by calldate;";
		$db -> query ( $query );

		while ( $db -> next_record() ) {
			$entrantes = $db -> f ("entrante");
			$salientes = $db -> f ("saliente");
			$total_in += $entrantes;
			$total_out += $salientes;
		}
		$total = $total_out * 2 - $total_in;
		$total = $total / 60;
		$contenido .= "<tr><td></td><td>Atel (por segundo)</td><td></td><td></td><td>" . $total . "</td></tr>";
	}


	$objResponse = new xajaxResponse();
	$objResponse -> assign("contenido","innerHTML", $contenido);
	return $objResponse;
}

function custMM( $id_user ) {
	global $db, $db2;

        $db -> query ( "select * from users where uid='$id_user'" );
        if ( $db -> next_record() ) {
		$accountcode = $db -> f ("accountcode");

                $opciones = '
<table>
<tr><td>Numero:</td><td>' . $accountcode . '</td></tr>
<tr><td>Nombre:</td><td>' . $db -> f ("name") . '</td></tr>
<tr><td>E-Mail:</td><td>' . $db -> f ("email") .'</td></tr>
<tr><td></td><td>&nbsp;</td></tr></table>
                        ';

	$contenido = "<h3>Compras</h3><table cellspacing=1 class=\"llamadas\"><tr><th>Fecha</th><th>Vendedor</th><th>Minutos</th><th>Precio</th><th>Total</th></tr>";

	$db -> query ("select fecha_compra, mminutos.minutos, precio, accountcode from compramm, mminutos, users where users.uid = uid_vendedor and mminutos.id_oferta = compramm.id_oferta and mminutos.uid_comprador = $id_user");

        $totalm = 0;
        $totalq = 0;

        while ( $db -> next_record() ) {
                $vendedor = $db -> f ("accountcode");
                $minutos = $db -> f ("minutos");
                $precio = number_format($db -> f ("precio"),2);
                $total = number_format($minutos*$precio,2);

                $totalm += $minutos;
                $totalq += $total;

                if ( $vendedor == "24584700" )
                        $vendedor = "Numero Central";

                $contenido .= "<tr><td>" . $db -> f("fecha_compra") . "</td>";
                $contenido .= "<td>$vendedor</td><td>$minutos</td><td>Q. $precio</td><td align=right>Q. $total</td>";

                $contenido .= "</tr>";
        }

        $totalq = number_format($totalq,2);
        $contenido .= "<tr><td colspan=2 align=right>Total:</td><td>$totalm</td><td>&nbsp;</td><td align=right>Q. $totalq</td></tr></table>";
        $contenido .= "</div>";

	$contenido .= "<h3>Ofertas</h3><table cellspacing=1 class=\"llamadas\"><tr><th>Fecha Creaci&oacute;n</th><th>Minutos</th><th>Precio</th><th>Total</th><th>Fecha Venta</th><th>Comprador</th></tr>";

        $db -> query ("select mminutos.id_oferta, mminutos.minutos, precio, fecha_ingreso, estado, accountcode, fecha_compra from mminutos left join compramm on mminutos.id_oferta=compramm.id_oferta left join users on mminutos.uid_comprador = users.uid where mminutos.uid=$id_user order by fecha_ingreso" );

        $totalm = 0;
        $totalq = 0;

        while ( $db -> next_record() ) {
                $minutos = $db -> f("minutos");
                $id_oferta = $db -> f ("id_oferta");
                $precio = number_format($db -> f ("precio"),2);
                $total = number_format($minutos*$precio,2);
                $fecha_ingreso = $db -> f ("fecha_ingreso");
                $estado = $db -> f ("estado");
                if ( $estado ) {
                        $fecha_compra = $db -> f ("fecha_compra");
                        $comprador = $db -> f ("accountcode");
                }

                $contenido .= "<tr><td>$fecha_ingreso</td><td>$minutos</td><td align=right>Q. $precio</td><td align=right>Q. $total</td>";
                if ( $estado )
                        $contenido .= "<td>$fecha_compra</td><td>$comprador</td>";
                else
                        $contenido .= "<td align=center colspan=2>-</td>";

                $contenido .= "</tr>";

                if ($estado) {
                        $totalm += $minutos;
                        $totalq += $total;
                }
        }

        $totalq = number_format($totalq,2);
        $contenido .= "<tr><td align=right>Total Vendido:</td><td>$totalm</td><td>&nbsp;</td><td align=right>Q. $totalq</td></tr></table>";
        $contenido .= "</div>";

	$opciones .= $contenido;

	}
	
	$objResponse = new xajaxResponse();
	$objResponse->assign("contenido","innerHTML",'<div class=\'prefswide_container\'>' . $opciones . '</div>');
	return $objResponse;
}

function custPayHistory( $id_user ) {
global $db, $db2;

$bancos = Array ( 1 => "Industrial", 2 => "BAM", 3 => "GyT", 4 => "Banrural", 5 => "BAC", 10 => "VisaNet", 11 => "Serbipagos", 12 => "Paypal", 13 => "Interno", 14 => "Saldo" );
$pagos  = Array ( 1 => "Efectivo", 2 => "Cheque", 3 => "Cheque O/B" );

if ( $id_user > 0 ) {
        $db -> query ( "select * from users where uid='$id_user'" );
        if ( $db -> next_record() ) {
		$accountcode = $db -> f ("accountcode");

                $opciones = '
<table>
<tr><td>Numero:</td><td>' . $accountcode . '</td></tr>
<tr><td>Nombre:</td><td>' . $db -> f ("name") . '</td></tr>
<tr><td>E-Mail:</td><td>' . $db -> f ("email") .'</td></tr>
<tr><td></td><td>&nbsp;</td></tr></table>
                        ';
		$db -> query ( "select *, date(fecha_pago) as fecha_pago from pagos where accountcode = '$accountcode' order by fecha_aplica desc" );
		if ( $db -> next_record() ) {
			$opciones .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha Ingreso</th><th>Fecha Pago</th><th>Banco</th><th>Tipo Pago</th><th>Documento</th><th>Minutos.</th><th>Monto</th><th>Factura</th><th>Mes</th><th>Motivo</th></tr>";
			$total_minutos = 0; $total_monto = 0;

			do {

				$fecha_aplica = $db -> f ("fecha_aplica");
				$motivo = $db -> f ("motivo_pago");
				$motivo_texto = "";
				switch($motivo) {
					case 0:$motivo_texto = "Minutos";break;
					case 1:$motivo_texto = "Mensualidad";break;
					case 2:$motivo_texto = "Quetzales";break;
				}

				$eem = explode('-', $fecha_aplica);
				$mes_aplica = $eem[1];
				$ano_aplica = $eem[0];
				$fecha_aplica = $mes_aplica . '/' . $ano_aplica;
				if ( $mes_aplica == '00' && $ano_aplica == '0000' ) {
					$fecha_aplica = '';
				}

				$factura = $db -> f ("factura");

				if ( strlen($factura) == 3 && $factura != "N/D" ) {
					$factura= "130000000" . $factura;
				} else if ( strlen($factura) == 2 ) {
					$factura= "1300000000" . $factura;
				} else if ( strlen($factura) == 1 ) {
					$factura= "13000000000" . $factura;
				}


				$minutos = number_format($db -> f ("minutos"), 0);
				$total_minutos += $db -> f ("minutos");
				$total_monto += $db -> f ("monto");
				$opciones .= "<tr><td>" . $db -> f ("fecha_ingreso") . "</td><td>" . $db -> f ("fecha_pago") . "</td>" .
					  "<td>" . $bancos[ $db -> f ("banco") ] . "</td><td>" . $pagos [ $db -> f ("forma_pago") ] . 
			                  "</td><td>" . $db -> f ("documento") . "</td><td>" . $minutos . "</td><td>Q " . $db -> f("monto") . 
					  "</td><td>" . $factura . "</td><td>" . $fecha_aplica .  "</td><td>$motivo_texto</td></tr>";
			} while ( $db -> next_record() );
			$total_minutos = number_format($total_minutos, 0);
			$total_monto   = number_format($total_monto,2);
			$opciones .= "<tr><td align=right colspan=5>Total</td><td>$total_minutos</td><td>Q $total_monto</td></tr></table>";
		} else {
			$opciones .= "<br/>No Hay Pagos Ingresados.";
		}
        } else {
                $opciones = "";
        }
}

$objResponse = new xajaxResponse();
$objResponse->assign("contenido","innerHTML",$opciones);
return $objResponse;
}

function admPagos() {
	global $db;
	$objResponse = new xajaxResponse();

	$db -> query ("select accountcode from users where length(accountcode)=8 order by accountcode");

	$bancos = Array ( 1 => "Industrial", 2 => "BAM", 3 => "GyT", 4 => "Banrural", 5 => "BAC", 10 => "VisaNet", 11 => "Serbipagos", 12 => "Paypal", 13 => "Interno", 14 => "Saldo" );
	$pagos  = Array ( 1 => "Efectivo", 2 => "Cheque", 3 => "Cheque O/B" );

	$cuentas = "<select name=\"accountcode\">";
	$cuentas .= "<option value=\"-1\">Todos</option>";
	while ( $db -> next_record() ) {
		$account = $db -> f ("accountcode");
		$cuentas .= "<option value=\"$account\">$account</option>";
	}
	$cuentas .= "</select>";

	$contenido = "<a href=\"#\" onclick=\"xajax_admPagosMora(0)\"><button type=\"button\">Cuentas con Mora mes Actual</button></a><br/>";
	$contenido .= "<a href=\"#\" onclick=\"xajax_admPagosMora(1)\"><button type=\"button\">Cuentas con Mora mes Anterior</button></a><p>";
	$contenido .= "<div class=\"padder\"><form id=\"statsForm\" name=\"statsForm\" action=\"javascript:void(null);\" onsubmit=\"submitPagos();\">";
	$contenido .= "<table>";
	$contenido .= "<tr><td>Cuenta</td><td>$cuentas</td></tr>";
	$contenido .= '<tr><td>Fecha De:</td><td><input type="text" size=12 name="fechade" id="fechade" /><a href="javascript:NewCssCal(\'fechade\',\'yyyymmdd\')"><img border=0 src="/images/cal.gif"></a></td>';
	$contenido .= '<td>A:</td><td><input type="text" size=12 name="fechaa" id="fechaa" /><a href="javascript:NewCssCal(\'fechaa\',\'yyyymmdd\')"><img border=0 src="/images/cal.gif"></a></td></tr>';
	$contenido .= "<tr><td><input type=submit value='Buscar'></td></tr>";

	$contenido .= "</table></form></div>";


	$contenido .= "<table cellspacing=1 class=\"llamadas\">";
	$contenido .= "<tr><th>N&uacute;mero</th><th>Minutos</th><th>Monto</th><th>M&eacute;todo</th></th><th>Fecha Pago</th><th>Fecha Ing.</th><th>Documento</th><th>Factura</th><th>UID</th><th>Mes</th><th></th></tr>";

	$db -> query ("select id_pago, accountcode, monto, minutos, forma_pago, banco, documento, date(fecha_pago) as fecha_pago, date(fecha_ingreso) as fecha_ingreso, factura, uid, fecha_aplica from pagos where month(fecha_ingreso) = month(now()) and year(fecha_ingreso) = year(now()) order by accountcode");

	$total_minutos = 0;
	$total_monto = 0;

	while ( $db -> next_record() ) {
		$id_pago = $db -> f ("id_pago");
		$account = $db -> f ("accountcode");
		$monto = $db -> f ("monto");
		$minutos = $db -> f ("minutos");

		$total_minutos += $minutos;
		$total_monto += $monto;
	
		$minutos = number_format($db -> f ("minutos"),0);

		$fecha_aplica = $db -> f ("fecha_aplica");

		$eem = explode('-', $fecha_aplica);
		$mes_aplica = $eem[1];
		$ano_aplica = $eem[0];
		$fecha_aplica = $mes_aplica . '/' . $ano_aplica;
		if ( $mes_aplica == '00' && $ano_aplica == '0000' ) {
			$fecha_aplica = '';
		}

		$factura = $db -> f ("factura");
		$pdf_mes = ltrim($mes_aplica, '0');
                $pdf_file = "";
                
                if ( file_exists ( "/var/spool/asterisk/facturas/$ano_aplica/$pdf_mes/$factura.pdf" ) ) {
                        $pdf_file = "<a href=\"factura_admin.php?file=$ano_aplica/$pdf_mes/$factura\"><img border=0 src=/images/adobe-reader-icon.gif /></a>";
                }

		$contenido .= "<tr><td>" . $account . "</td><td>$minutos</td><td>" . $monto. "</td>";
		$contenido .= "<td>" . $bancos[$db -> f ("banco")] . "</td>";
		$contenido .= "<td>" . $db -> f ("fecha_pago") . "</td><td>" . $db -> f ("fecha_ingreso") . "</td><td>" . $db ->f ("documento") . "</td><td>" . $db -> f ("factura") . "</td><td>" . $db -> f ("uid") . "</td><td>" . $fecha_aplica;

		if ( $fecha_aplica != '' && $factura != '') {
			$contenido .= "</td><td><a href='#' onclick=\"xajax_admPagosUpload($id_pago)\"><img src='/images/upload.gif'/></a>&nbsp;$pdf_file";
		} else {
			$contenido .= "</td><td>$pdf_file";
		}

		if ( $pdf_file == "" ) {
			$contenido .= "<a href='#' onclick=\"xajax_admEditPago($id_pago)\"><img src='/images/edit.jpg'/></a>";
		}

		$contenido .= "</td></tr>";


	}
	
	$total_minutos = number_format($total_minutos, 0 );
	$total_monto   = number_format($total_monto, 2 );
	
	$contenido .= "<tr></tr><tr><td>Total</td><td>$total_minutos</td><td>" . $total_monto. "</td><td></td><td></td><td></td></tr>";
	$contenido .= "</table>";
	
	buildMenuAdm($objResponse, 4 );
	$objResponse -> assign("contenido","innerHTML", $contenido);
	return $objResponse;
}

function processPagos( $form ) {
	global $db;

	$fechade = trim($form['fechade']);
	$fechaa  = trim($form['fechaa']);
	$accountcode = trim($form['accountcode']);

	$bancos = Array ( 1 => "Industrial", 2 => "BAM", 3 => "GyT", 4 => "Banrural", 5 => "BAC", 10 => "VisaNet", 11 => "Serbipagos", 12 => "Paypal", 13 => "Interno", 14 => "Saldo" );
	$pagos  = Array ( 1 => "Efectivo", 2 => "Cheque", 3 => "Cheque O/B" );

	if ( strlen($fechade) != 10 ) {
		$fechade = '2000-01-01';
	}

	if ( strlen($fechaa) != 10 ) {
		$fechaa = date ("Y-m-d");
	}

	if ( $accountcode == "-1" ) { 
		$query = "select id_pago, fecha_aplica, accountcode, monto, minutos, forma_pago, banco, documento, date(fecha_pago) as fecha_pago, date(fecha_ingreso) as fecha_ingreso, factura, uid from pagos where fecha_ingreso between '$fechade' and '$fechaa' order by accountcode";
	} else {
		$query = "select id_pago, fecha_aplica, accountcode, monto, minutos, forma_pago, banco, documento, date(fecha_pago) as fecha_pago, date(fecha_ingreso) as fecha_ingreso, factura, uid from pagos where accountcode = '$accountcode' and fecha_ingreso between '$fechade' and '$fechaa' order by accountcode";
	}
	
	$db -> query ("select accountcode from users where length(accountcode)=8 order by accountcode");

	$cuentas = "<select name=\"accountcode\">";
	$cuentas .= "<option value=\"-1\">Todos</option>";
	while ( $db -> next_record() ) {
		$account = $db -> f ("accountcode");
		$cuentas .= "<option value=\"$account\">$account</option>";
	}
	$cuentas .= "</select>";

	$contenido = "<div class=\"padder\"><form id=\"statsForm\" name=\"statsForm\" action=\"javascript:void(null);\" onsubmit=\"submitPagos();\">";
	$contenido .= "<table>";
	$contenido .= "<tr><td>Cuenta</td><td>$cuentas</td></tr>";
	$contenido .= '<tr><td>Fecha De:</td><td><input type="text" size=12 name="fechade" id="fechade" value="' . $fechade . '" /><a href="javascript:NewCssCal(\'fechade\',\'yyyymmdd\')"><img border=0 src="/images/cal.gif"></a></td>';
	$contenido .= '<td>A:</td><td><input type="text" size=12 name="fechaa" id="fechaa" value="' . $fechaa . '" /><a href="javascript:NewCssCal(\'fechaa\',\'yyyymmdd\')"><img border=0 src="/images/cal.gif"></a></td></tr>';
	$contenido .= "<tr><td><input type=submit value='Buscar'></td></tr>";

	$contenido .= "</table></form></div>";


	$contenido .= "<table cellspacing=1 class=\"llamadas\">";
	$contenido .= "<tr><th>Cuenta</th><th>Minutos</th><th>Monto</th><th>Banco</th><th>Fecha Pago</th><th>Fecha Ing.</th><th>Docto.</th><th>Fact.</th><th>UID</th><th>Mes</th></tr>";

	$db -> query ("$query" );

	$total_minutos = 0;
	$total_monto = 0;

	while ( $db -> next_record() ) {
		$id_pago = $db -> f ("id_pago");
		$account = $db -> f ("accountcode");
		$monto = $db -> f ("monto");
		$minutos = number_format($db -> f ("minutos"),0);

		$total_minutos += $minutos;
		$total_monto += $monto;

		$fecha_aplica = $db -> f ("fecha_aplica");

		$eem = explode('-', $fecha_aplica);
		$mes_aplica = $eem[1];
		$ano_aplica = $eem[0];
		$fecha_aplica = $mes_aplica . '/' . $ano_aplica;
		if ( $mes_aplica == '00' && $ano_aplica == '0000' ) {
			$fecha_aplica = '';
		}

		$factura = $db -> f ("factura");
		$pdf_mes = ltrim($mes_aplica, '0');
                $pdf_file = "";

                if ( file_exists ( "/var/spool/asterisk/facturas/$ano_aplica/$pdf_mes/$factura.pdf" ) ) {
                        $pdf_file = "<a href=\"factura_admin.php?file=$ano_aplica/$pdf_mes/$factura\"><img border=0 src=/images/adobe-reader-icon.gif /></a>";
                }

		$contenido .= "<tr><td>" . $account . "</td><td>$minutos</td><td>" . $monto. "</td>";
		$contenido .= "<td>" . $bancos[$db -> f ("banco")] . "</td>";
		$contenido .= "<td>" . $db -> f ("fecha_pago") . "</td><td>" . $db -> f ("fecha_ingreso") . "</td><td>" . $db ->f ("documento") .
				 "</td><td>" . $db -> f ("factura") . "</td><td>" . $db -> f ("uid") . "</td><td>$fecha_aplica"; 

		if ( $fecha_aplica != '' && $factura != '') {
			$contenido .= "</td><td><a href='#' onclick=\"xajax_admPagosUpload($id_pago)\"><img src='/images/upload.gif'/></a>&nbsp;$pdf_file";
		} else {
			$contenido .= "</td><td>$pdf_file";
		}

		if ( $pdf_file == "" ) {
			$contenido .= "<a href='#' onclick=\"xajax_admEditPago($id_pago)\"><img src='/images/edit.jpg'/></a>";
		}
	
		$contenido .= "</td></tr>";
	
	}
	
	$total_minutos = number_format($total_minutos, 0 );
	$total_monto   = number_format($total_monto, 2 );
	
	$contenido .= "<tr></tr><tr><td>Total</td><td>$total_minutos</td><td>" . $total_monto. "</td></tr>";
	$contenido .= "</table>";

	$objResponse = new xajaxResponse();
        $objResponse -> assign("contenido","innerHTML", $contenido);
        return $objResponse;
}

function mmList ($form) {
	global $db;
	$contenido = "<p><div id='prefswide_container'>";
        $objResponse = new xajaxResponse();
        buildMenuAdm($objResponse, 9);

	$cuentas = Array();
	$db -> query ("select uid, accountcode from users");
	while ( $db -> next_record() ) {
                $uid = $db -> f ("uid");
                $cuenta = $db -> f ("accountcode");
		$cuentas[$uid] = $cuenta;
	}

	$fechade = $fechaa = "";

	if ( isset($form) ) {
		$fechade = $form["fechade"];
		$fechaa  = $form["fechaa"];
	}

	$contenido .= "<div class=\"padder\"><form id=\"mercadoForm\" name=\"mercadoForm\" action=\"javascript:void(null);\" onsubmit=\"xajax_mmList(xajax.getFormValues('mercadoForm'));\">";
	$contenido .= "<table>";
	$contenido .= '<tr><td>Fecha De:</td><td><input type="text" size=12 name="fechade" id="fechade" value="' . $fechade . '"/><a href="javascript:NewCssCal(\'fechade\',\'yyyymmdd\')"><img border=0 src="/images/cal.gif"></a></td>';
	$contenido .= '<td>A:</td><td><input type="text" size=12 name="fechaa" id="fechaa" value="' . $fechaa . '" /><a href="javascript:NewCssCal(\'fechaa\',\'yyyymmdd\')"><img border=0 src="/images/cal.gif"></a></td></tr>';
	$contenido .= "<tr><td><input type=submit value='Ver'></td></tr></table><br/>";

	if ( isset($form) ) {
		$contenido .= "<h3>Visanet, de $fechade a $fechaa</h3>";
		$db -> query ("select * from visanet where fecha_process between '$fechade 00:00:00' and '$fechaa 23:59:59' order by fecha_process;");
	} else {
		$contenido .= "<h3>Visanet, Ultimas 15 Operaciones</h3>";
		$db -> query ("select * from visanet order by fecha_process desc limit 15;");
	}
	$contenido .= "<table cellspacing=1 class='llamadas'><tr><th>Fecha</th><th>Cuenta</th><th>Monto</th><th>Resultado</th><th></th>";
	$total = 0;
	while ( $db -> next_record() ) {
		$contenido .= "<tr><td>" . $db -> f ("fecha_process") . "</td><td>" . $db -> f ("accountcode");
		$contenido .= "</td><td align='right'> Q. " . number_format($db -> f ("ammount_qtz"),2) . "</td><td>" . $db -> f ("decision");
		$contenido .= "</td><td>" . $db -> f("message") . "</td></tr>";
		if ( $db -> f ("decision") == "ACCEPT") {
			$total = $total + $db -> f ("ammount_qtz");
		}
	}
	$contenido .= "<tr><td colspan=2 align='right'>Total</td><td align='right'> Q. " . number_format($total,2) . "</td><td></td></tr></table>";

	if ( isset($form) ) {
		$contenido .= "<h3>Transpagos, de $fechade a $fechaa</h3><table cellspacing=1 class='llamadas'><tr><th>Fecha</th><th>Cuenta</th><th>Celular</th><th>Monto</th><th>Resultado</th>";
		$db -> query ("select * from transpagos where ingreso between '$fechade 00:00:00' and '$fechaa 23:59:59' order by ingreso;");
	} else {
		$contenido .= "<h3>Transpagos, Ultimas 15 Operaciones</h3><table cellspacing=1 class='llamadas'><tr><th>Fecha</th><th>Cuenta</th><th>Celular</th><th>Monto</th><th>Resultado</th>";
		$db -> query ("select * from transpagos order by ingreso desc limit 15;");
	}
	$total = 0;
	while ( $db -> next_record() ) {
		$result = explode(',',$db -> f ("result"));
		$contenido .= "<tr><td>" . $db -> f ("ingreso") . "</td><td>" . $db -> f ("accountcode");
		$contenido .= "</td><td>" . $db -> f ("celular"). "</td><td align='right'> Q. " . number_format($db -> f ("monto"), 2 ) ;
		$contenido .= "</td><td>" . $result[0]. "</td></tr>";
		if ( $result[0] == "APROBADO" ) {
			$total = $total + $db -> f ("monto");
		}
	}

	$contenido .= "<tr><td colspan=3 align='right'>Total</td><td align='right'> Q. " . number_format($total,2) . "</td><td></td></tr></table>";
	
	if ( isset($form) ) {
		$contenido .= "<h3>Mercado, de $fechade a $fechaa</h3><table cellspacing=1 class=\"llamadas\"><tr><th>Vendedor</th><th>Fecha Creaci&oacute;n</th><th>Minutos</th><th>Precio</th><th>Total</th><th>Fecha Compra</th><th>Comprador</th></tr>";
        	$db -> query ("select mminutos.id_oferta, mminutos.minutos, precio, fecha_ingreso, estado, accountcode, fecha_compra, mminutos.uid from mminutos left join compramm on mminutos.id_oferta=compramm.id_oferta left join users on mminutos.uid_comprador = users.uid where fecha_compra between '$fechade 00:00:00' and '$fechaa 23:59:59' order by fecha_compra, mminutos.uid " );

	} else {
		$contenido .= "<h3>Mercado, Ultimas 15 Operaciones</h3><table cellspacing=1 class=\"llamadas\"><tr><th>Vendedor</th><th>Fecha Creaci&oacute;n</th><th>Minutos</th><th>Precio</th><th>Total</th><th>Fecha Compra</th><th>Comprador</th></tr>";
        	$db -> query ("select mminutos.id_oferta, mminutos.minutos, precio, fecha_ingreso, estado, accountcode, fecha_compra, mminutos.uid from mminutos left join compramm on mminutos.id_oferta=compramm.id_oferta left join users on mminutos.uid_comprador = users.uid order by fecha_compra desc, mminutos.uid limit 15" );
	}

        $totalm = 0;
        $totalq = 0;

        while ( $db -> next_record() ) {
                $minutos = $db -> f("minutos");
		$uid = $db ->f ("uid");
                $id_oferta = $db -> f ("id_oferta");
                $precio = number_format($db -> f ("precio"),2);
                $total = number_format($minutos*$precio,2);
                $fecha_ingreso = $db -> f ("fecha_ingreso");
                $estado = $db -> f ("estado");
                if ( $estado ) {
                        $fecha_compra = $db -> f ("fecha_compra");
                        $comprador = $db -> f ("accountcode");
                }

		$cuenta = "";
		if(isset($cuentas[$uid])) {
			$cuenta = $cuentas[$uid];
		}

                $contenido .= "<tr><td>" . $cuenta . "</td><td>$fecha_ingreso</td><td>$minutos</td><td align=right>Q. $precio</td><td align=right>Q. $total</td>";
                if ( $estado )
                        $contenido .= "<td>$fecha_compra</td><td>$comprador</td>";
                else
                        $contenido .= "<td align=center colspan=2>-</td>";

                $contenido .= "</tr>";

                if ($estado) {
                        $totalm += $minutos;
                        $totalq += $total;
                }
        }

        $totalq = number_format($totalq,2);
        $contenido .= "<tr><td>&nbsp;</td><td align=right>Total Vendido:</td><td>$totalm</td><td>&nbsp;</td><td align=right>Q. $totalq</td></tr></table>";

	if ( isset($form) ) {
	        $db -> query ("select fecha_ingreso, id_oferta, mminutos.minutos, precio, accountcode, mminutos.uid from mminutos, users where estado = 0 and users.uid=mminutos.uid and fecha_ingreso between '$fechade 00:00:00' and '$fechaa 23:59:59' order by fecha_ingreso;");
		$contenido .= "<h3>Ofertas Disponibles</h3>";
	} else {
	        $db -> query ("select fecha_ingreso,id_oferta, mminutos.minutos, precio, accountcode, mminutos.uid from mminutos, users where estado = 0 and users.uid=mminutos.uid order by fecha_ingreso desc limit 15;");
		$contenido .= "<h3>Ofertas Disponibles</h3>";
	}
	$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha Ingreso</th><th>Vendedor</th><th>Minutos</th><th>Precio x Minuto</th><th>Total</th></tr>";
        while ( $db -> next_record() ) {
		$fecha = $db -> f ("fecha_ingreso");
                $minutos = $db -> f ("minutos");
                $uid = $db -> f ("uid");
                $precio = number_format($db -> f ("precio"), 2);
                $cuenta = $db -> f ("accountcode");
                if ($cuenta == "24584700") {
                        $cuenta = "Numero Central";
                }
                $total = number_format( $minutos*$precio, 2 );
                $contenido .= "<tr><td>$fecha</td><td>$cuenta</td>";
                $contenido .=     "<td>$minutos</td>";
                $contenido .=     "<td>Q.&nbsp;$precio</td>";
                $contenido .=     "<td>Q.&nbsp;$total</td></tr>";
        }

	$contenido .= "</table>";

        $contenido .= "</div>";

        $contenido .= "</div>";
        $objResponse -> assign("contenido","innerHTML", "$contenido" );
        return $objResponse;
}

function admPagosUpload( $id_pago ) {
        global $db, $db2;
        $objResponse = new xajaxResponse();

	$contenido = "";

	$db -> query ("select * from pagos where id_pago = $id_pago");

	if ( $db -> next_record() ) {
		$accountcode = $db -> f ("accountcode");
		$fecha_aplica = $db -> f ("fecha_aplica");
		
		$contenido .= "<form action='factura-upload.php' enctype='multipart/form-data' method='post'>";
		$contenido .= "Factura de cliente $accountcode de fecha $fecha_aplica</br>";
		$contenido .= "Subir Factura: <input type=file name=\"facturapdf\"/>";
		$contenido .= "<input type=hidden name='accountcode' value='$accountcode'/>";
		$contenido .= "<input type=hidden name='fecha_aplica' value='$fecha_aplica'/>";
		$contenido .= "<input type=hidden name='id_pago' value='$id_pago'/>";
		$contenido .= "<input type=submit value='Subir'/></form>";

	        $objResponse -> assign("contenido","innerHTML", $contenido);
        	return $objResponse;
	}
}

function admPagosMora( $month ) {
        global $accountcode;
        global $db, $db2;

        $objResponse = new xajaxResponse();

	if ( $month == 0 ) {
		//mes actual
		$query = "select * from users where accountcode not in ( select accountcode from pagos where month(fecha_aplica) >= month(now()) and year(fecha_aplica) = year(now()) and motivo_pago in ( 1, 3)) and length(accountcode)=8 and accountcode not in ( '24584700', '24584710', '24584711' ) order by accountcode;";
		$res = "Cuentas con Mora Mes Actual, <a href=\"#\" onclick=\"xajax_admPagos()\">Regresar</a><br/>";
	} else {
		//mas de un mes
		if ( $month == 1 ) {
			$res = "Cuentas con Mora $month Mes Anterior, <a href=\"#\" onclick=\"xajax_admPagos()\">Regresar</a><br/>";
		} else {
			$res = "Cuentas con Mora $month Meses Anteriores, <a href=\"#\" onclick=\"xajax_admPagos()\">Regresar</a><br/>";
		}
		$query = "select * from users where accountcode not in ( select accountcode from pagos where month(fecha_aplica) = month(date_sub(now(), INTERVAL $month MONTH)) and year(fecha_aplica) = year(date_sub(now(), INTERVAL $month MONTH)) and motivo_pago in (1,3) ) and length(accountcode)=8 and accountcode not in ( '24584700', '24584710', '24584711' ) and date(fecha_inicio) < date(date_sub(now(), INTERVAL $month MONTH)) order by accountcode;";
	}

	//Whitelist
	$listablanca = array (
				24584700 => "No Llamar");

	$res .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Cuenta</th><th>Nombre</th><th colspan=5>Ultimo Pago</th><th>Nota</th></tr>";

	$qty = 0;

	$db -> query ($query);
	while ( $db -> next_record() ) {
		$res .= "<tr><td>" . $db -> f ("accountcode") . "</td><td><a href=\"#\" onclick=\"xajax_admCDRUser(" .
                        $db -> f ("accountcode") . ")\">" .  htmlentities(substr($db ->f ("name"),0,25), ENT_QUOTES, "utf-8") . "</a>".
                        "</td>";

		$account = $db ->f ("accountcode");

		$query = "select * from pagos where accountcode = '$account' order by fecha_ingreso desc limit 1;";
		$db2 -> query ($query);

		if ( $db2 -> next_record() ) {
			$bancos = Array ( 1 => "Industrial", 2 => "BAM", 3 => "GyT", 4 => "Banrural", 5 => "BAC", 10 => "VisaNet", 11 => "Serbipagos", 12 => "Paypal", 13 => "Interno", 14 => "Saldo" );
			$fecha_aplica = $db2 -> f ("fecha_aplica");

			$eem = explode('-', $fecha_aplica);
			$mes_aplica = $eem[1];
			$ano_aplica = $eem[0];
			$fecha_aplica = $mes_aplica . '/' . $ano_aplica;
			if ( $mes_aplica == '00' && $ano_aplica == '0000' ) {
				$fecha_aplica = '';
			}

			$res .= "<td>" . $db2 -> f ("fecha_ingreso") . "</td><td>" . $bancos[ $db2 -> f ("banco") ] . "</td><td>Q " . $db2 -> f("monto") . "</td><td>" . $db2 -> f ("factura") . "</td><td>" . $fecha_aplica;

		} else {
			$res .= "<td colspan=5>N/A";
		}

		$res .= "</td><td>" . $listablanca[$account] . "</td>";

		$res .= "<td><a href=\"#\" onclick=\"xajax_custPayHistory(" . $db -> f ("uid") . ")\"><img border=0 src=\"/images/payment.png\"></a></td></tr>";
		$qty++;
	}

	$res .= "</table><br/>$qty";
		
	$objResponse -> assign("contenido","innerHTML",$res);
        return $objResponse;
}

function pagosProcessForm ( $form ) {
	global $db;

	$id_pago = escape(trim($form['id']));
	$docto   = escape(trim($form['docto']));
	$factura = escape(trim($form['factura']));

	$db -> query ( "update pagos set documento = '$docto',factura = '$factura' where id_pago = $id_pago" );

	$objResponse = admPagos();
	$objResponse -> alert ("Pago Editado.");
	return $objResponse;
}
	

$xajax->processRequest();
?>
