<?php
/*
    ajax_server_recarga.php
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

    AJAX Server for the telephone time recharge with transpagos in Guatemala
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
include 'prepend.php';

$userid = $user->requireAuthentication( "" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
$db2 = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");

$mes = array( 1 => "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" );


if ( $userid === false ) {
} else {

$db -> query ( "select minutos,supervisa,fax,id_plan,accountcode,saldo_minutos, saldo_qtz, valid_days - datediff(now(), ifnull(fecha_ingreso_saldo,now())) as vence, day(fecha_inicio) as diacorte, extensiones, exten1digit, monto from users, plans, saldo where id_plan = plans.id and users.uid = saldo.uid and users.uid = $userid" );
$db -> next_record();
$accountcode = $db -> f ("accountcode");
$saldo       = $db -> f ("saldo_minutos");
$saldo_qtz   = number_format($db -> f ("saldo_qtz"),2,".",",");
$saldo_qtz_no = $db -> f ("saldo_qtz");

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
}

setcookie(
ini_get("session.name"),
session_id(),
time()+ini_get("session.cookie_lifetime"),
ini_get("session.cookie_path"),
ini_get("session.cookie_domain"),
ini_get("session.cookie_secure"),
ini_get("session.cookie_httponly")
);

$requested = "";
if(isset($_GET['xjxfun']))
	$requested=$_GET['xjxfun'];
if(isset($_POST['xjxfun']))
	$requested=$_POST['xjxfun'];
trigger_error("$accountcode - $requested - ", E_USER_NOTICE);

$xajax = new xajax("ajax_server_recarga.php");
$xajax->registerFunction("mmSaldoTel");
$xajax->registerFunction("mmSaldoTelForm");

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

function mmSaldoTel() {
	global $accountcode, $userid, $db, $saldo_qtz, $saldo_qtz_no;

	$objResponse = new xajaxResponse();

	$contenido = "<div id=\"nntext\">Saldo: Q $saldo_qtz<span></span>";

	$contenido .= "<br/><div class='padder'>";
	$contenido .= '<form id="mmsaldo" action="javascript:void(null);" onsubmit="xajax_mmSaldoTelForm(xajax.getFormValues(\'mmsaldo\'), 0);"><table id=\"recarga\"><tr><td id="emptd">';
	$contenido .= '
	<div class="empresa-selector">
		<input id="claro" type="radio" name="empresa" value="claro" />
		<label class="empresa-opt claro" for="claro"></label>
		<input id="telefonica" type="radio" name="empresa" value="telefonica" />
		<label class="empresa-opt telefonica" for="telefonica"></label>
		<input id="tigo" type="radio" name="empresa" value="tigo" />
		<label class="empresa-opt tigo" for="tigo"></label>  
	</div></td>
';
	$contenido .= "<td id=\"ammt\">&nbsp;&nbsp;Monto: <br/>";
	$contenido .= '<div class="monto-selector">';
	$disabled = "";
	if ( $saldo_qtz_no >= 5 ) {
		$contenido .= '<input id="cincoq" type="radio" name="monto" value="5" /><label class="monto-opt cincoq" for="cincoq"></label>';
	} else {
		$disabled = "disabled='disabled'";
	}	
	if ( $saldo_qtz_no >= 10 ) {
		$contenido .= '<input id="diezq" type="radio" name="monto" value="10" /><label class="monto-opt diezq" for="diezq"></label>';
	}
	if ( $saldo_qtz_no >= 25 ) {
		$contenido .= '<input id="veinte" type="radio" name="monto" value="25" /><label class="monto-opt veinte" for="veinte"></label>';
	}
	if ( $saldo_qtz_no >= 50 ) {
		$contenido .= '<input id="cincuentaq" type="radio" name="monto" value="50" /><label class="monto-opt cincuentaq" for="cincuentaq"></label>';
	}
	$contenido .= "</div></td>";

	$contenido .= "<td id=\"cellno\">&nbsp;&nbsp;Celular: <input class=\"phone-input\" type=\"number\" id=\"telefono\" name=\"telefono\" maxlength=\"8\" size=\"10\" placeholder=\"########\"/><br/><br/>";
	$contenido .= "<table>";
	$contenido .= '<tr>';
	$contenido .= '<td><a class="btn" onclick="add(\'1\');" href="#">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '<td><a class="btn" onclick="add(\'2\');" href="#">&nbsp;&nbsp;&nbsp;2&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '<td><a class="btn" onclick="add(\'3\');" href="#">&nbsp;&nbsp;&nbsp;3&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '</tr>';
	$contenido .= '<tr><td>&nbsp;</td></tr>';
	$contenido .= '<tr>';
	$contenido .= '<td><a class="btn" onclick="add(\'4\');" href="#">&nbsp;&nbsp;&nbsp;4&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '<td><a class="btn" onclick="add(\'5\');" href="#">&nbsp;&nbsp;&nbsp;5&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '<td><a class="btn" onclick="add(\'6\');" href="#">&nbsp;&nbsp;&nbsp;6&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '<td><a class="btn" onclick="add(\'0\');" href="#">&nbsp;&nbsp;&nbsp;0&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '</tr>';
	$contenido .= '<tr><td>&nbsp;</td></tr>';
	$contenido .= '<tr>';
	$contenido .= '<td><a class="btn" onclick="add(\'7\');" href="#">&nbsp;&nbsp;&nbsp;7&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '<td><a class="btn" onclick="add(\'8\');" href="#">&nbsp;&nbsp;&nbsp;8&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '<td><a class="btn" onclick="add(\'9\');" href="#">&nbsp;&nbsp;&nbsp;9&nbsp;&nbsp;&nbsp;</a></td>';
	$contenido .= '<td><a class="btn" onclick="add(-1);" href="#">&nbsp;&nbsp;<img src="/images/backspace.png">&nbsp;&nbsp;</a></td>';
	$contenido .= '</tr>';
	$contenido .= '<tr><td>&nbsp;</td></tr>';
	$contenido .= '</table>';
	$contenido .= "</td></tr>";
	$contenido .= "</table></form>";
	$contenido .= "<br/><div id='btns' align='right'>";
	$contenido .= "<a id=\"back\" class=\"btnd\" href=\"#\" disabled=\"disabled\">Anterior</a>&nbsp;&nbsp;&nbsp;<a id=\"next\" class=\"btn\" href=\"#\" onclick=\"next(1);\">Siguiente</a>";
	$contenido .= "</div>";
	$contenido .= "</div>";

	$objResponse -> assign("contenido","innerHTML", "$contenido" );
	$objResponse -> script("\$j('.phone-input').unbind('keyup change input paste').bind('keyup change input paste', function(e){
			var \$this = \$j(this);
			var val = \$this.val();
			var valLength = val.length;
			var maxCount = \$this.attr('maxlength');
			if ( valLength > maxCount ) {
				\$this.val(\$this.val().substring(0,maxCount));
			}
		});");
	$objResponse -> script("\$j(document).ready(function() {
			\$j('.phone-input').keypress(function(key) {
					if(key.charCode < 48 || key.charCode > 57) return false;
				});
			});");
	$objResponse -> script("\$j(\"#ammt\").css('display', 'none');");
	$objResponse -> script("\$j(\"#cellno\").css('display', 'none');");
	$objResponse -> script("\$j('#mmsaldo input').on('change', function() { 
			var empresa = \$j('input[name=empresa]:checked', '#mmsaldo').val();
			var monto = \$j('input[name=monto]:checked', '#mmsaldo').val();
			empresa = empresa.charAt(0).toUpperCase() + empresa.slice(1);
			if ( empresa && empresa.length > 0 ) {
				if ( monto && monto.length > 0 ) {
					monto = 'Q. ' + monto + '.00';
					\$j('#nntext span').html( ', Recarga ' + empresa + ' de ' + monto );
				} else {
					\$j('#nntext span').html( ', Recarga ' + empresa );
				}
			}
				 });");
	return $objResponse;
}	

function mmSaldoTelForm( $form , $step) {
	global $accountcode, $userid, $db, $saldo_qtz_no, $saldo_qtz;

	$objResponse = new xajaxResponse();

	$empresa   = trim($form["empresa"]);
	$numero    = intval(trim($form["telefono"]));
	$monto     = intval(trim($form["monto"]));

	if ( $empresa == "" ) {
		$objResponse -> alert ("Debe escoger la empresa para compra de saldo.");
		return $objResponse;
	}

	if ( strlen($numero) != 8 ) {
		$objResponse -> alert ("El Celular debe de tener 8 digitos.");
		return $objResponse;
	}	

	if ( !is_numeric($monto) ) {
		$objResponse -> alert ("Debe escoger el monto.");
	}

	if ( $step == 0 ) {
		$contenido = "<div>Saldo: Q $saldo_qtz<br/>";
		$contenido .= '<form id="mmsaldo" action="javascript:void(null);" onsubmit="xajax_mmSaldoTelForm(xajax.getFormValues(\'mmsaldo\'), 1);">';
		$contenido .= '<input type=hidden name="empresa" value="' . $empresa . '">';
		$contenido .= '<input type=hidden name="telefono" value="' . $numero. '">';
		$contenido .= '<input type=hidden name="monto" value="' . $monto . '"></form>';
		$contenido .= '<h2>Esta seguro de hacer una recarga por Q.' . number_format($monto,2);
		$contenido .= '<br/>Al celular : <strong>' . $numero . '</strong> de la empresa: ' . $empresa . '</h2>';
		$contenido .= "<br/><div id='btns' align='right'>";
		$contenido .= '<a class="btn" href="#" onclick="xajax_mmSaldoTel()">Cancelar</a>&nbsp;&nbsp;&nbsp';
		$contenido .= '<a class="btn" href="#" onclick="xajax_mmSaldoTelForm(xajax.getFormValues(\'mmsaldo\'), 1);">Confirmar</a><br/><br/>';
		$contenido .= "</div>";

		$objResponse -> assign("contenido","innerHTML", "$contenido" );
		return $objResponse;
	} else if ( $step == 1 ) {
	$codigo = "";
	$codempresa = "";
	if ( $empresa == "claro" ) {
		$codempresa = 1;
		switch ( $monto ) {
			case 5:  $codigo = "110760";break;
			case 10: $codigo = "110453";break;
			case 25: $codigo = "110454";break;
			case 50: $codigo = "110455";break;
		}
	} else if ( $empresa == "telefonica" ) {
		$codempresa = 2;
		switch ( $monto ) {
			case 5:  $codigo = "107672";break;
			case 10: $codigo = "107673";break;
			case 25: $codigo = "107675";break;
			case 50: $codigo = "107676";break;
		}
	} else if ( $empresa == "tigo" ) {
		$codempresa = 3;
		switch ( $monto ) {
			case 5:  $codigo = "110762";break;
			case 10: $codigo = "107802";break;
			case 25: $codigo = "107803";break;
			case 50: $codigo = "107804";break;
		}
	}

	$db -> query("update saldo set saldo_qtz=saldo_qtz-$monto where uid=$userid");
	$db -> query("insert into transpagos values ( null, '$accountcode' , $userid, $codempresa, '$codigo', '$numero', $monto, 0, '', '', now(), null)");
	
	$saldo_ant = number_format($saldo_qtz_no, 2);
	$saldo_post = number_format(($saldo_qtz_no - $monto), 2);
	$contenido = "<div>Saldo: Q $saldo_post<br/>";
	$contenido .= "Compra realizada con exito del numero $numero<br/><br/><a href=\"#\" class=\"btn\" onclick=\"xajax_mmSaldoTel();\">Continuar</a><br/>";
	$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th style=\"font-size:80%;\">Fecha</th><th style=\"font-size:80%;\">Numero</th><th style=\"font-size:80%;\">Monto</th><th style=\"font-size:80%;\">Estado</th></tr>";

	$db -> query ( "select * from transpagos where accountcode = '$accountcode' order by ingreso desc limit 5" );
	while ( $db -> next_record () ) {
		$contenido .= "<tr><td style=\"font-size:70%;\">" . $db -> f ("ingreso") . "</td>";
		$contenido .= "<td>" . $db -> f ("celular") . "</td>";
		$contenido .= "<td>" . number_format($db -> f ("monto"),2) . "</td>";
		$contenido .= "<td style=\"font-size:70%;\">" . $db -> f ("result" ) . "</td></tr>";
	}
	
	$contenido .= "</table>";
	$objResponse -> assign("contenido","innerHTML", $contenido );
	return $objResponse;
	}
}

$xajax->processRequest();
?>
