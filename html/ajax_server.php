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

    AJAX server with client area
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

$db->query("SET CHARACTER SET 'utf8'");
$db2->query("SET CHARACTER SET 'utf8'");

$mes = array( 1 => "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" );


if ( $userid === false ) {
} else {

$db -> query ( "select minutos,supervisa,fax,id_plan,accountcode,saldo_minutos, saldo_qtz, valid_days - datediff(now(), ifnull(fecha_ingreso_saldo,now())) as vence, day(fecha_inicio) as diacorte, extensiones, exten1digit, monto from users, plans, saldo where id_plan = plans.id and users.uid = saldo.uid and users.uid = $userid" );
$db -> next_record();
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

$showtag     = 0;
switch ( substr($accountcode,0,8) ) {
	case "24584711" :
	case "24584710" :
	case "24584555" :
	case "24584444" :
	case "24584735" :
	case "24584701" :
	case "24584410" :
		$showtag = 1;
		break;
}



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

$allowed_numbers = $db -> f ("numbers");
if ( $max1exten > 0 ) {
	$allowed_numbers = $max1exten;
}

if ( $planid != 12 && $planid != 13 && $planid != 18 ) {
	$global_record = $db -> f ("record");
} else {
	$global_record = 1;
}

if ( $planid == 14 ) {
	$global_record = 0;
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

$xajax = new xajax("ajax_server.php");
$xajax->registerFunction("processCalls");
$xajax->registerFunction("getCalls");
$xajax->registerFunction("getCallsNumber");
$xajax->registerFunction("getCallsTag");
$xajax->registerFunction("getContacts");
$xajax->registerFunction("getNums");
$xajax->registerFunction("getPrefs");
$xajax->registerFunction("getTags");
$xajax->registerFunction("getFavorites");
$xajax->registerFunction("getFavoritesContacts");
$xajax->registerFunction("getAccount");
$xajax->registerFunction("getPayments");
$xajax->registerFunction("callsPage");
$xajax->registerFunction("callsReload");
$xajax->registerFunction("contactsEditGroup");
$xajax->registerFunction("contactsDeleteGroup");
$xajax->registerFunction("contactsDeleteContact");
$xajax->registerFunction("contactsGetContacts");
$xajax->registerFunction("contactsEditContacts");
$xajax->registerFunction("contactsProcessForm");
$xajax->registerFunction("prefsProcessForm");
$xajax->registerFunction("tagsEdit");
$xajax->registerFunction("tagsDelete");
$xajax->registerFunction("tagsProcessForm");
$xajax->registerFunction("numsEdit");
$xajax->registerFunction("numsDelete");
$xajax->registerFunction("numsProcessForm");
$xajax->registerFunction("favsProcessAdd");
$xajax->registerFunction("favsAdd");
$xajax->registerFunction("favsDelete");
$xajax->registerFunction("favsMove");
$xajax->registerFunction("accountProcessForm");
$xajax->registerFunction("search");
$xajax->registerFunction("contactsearch");
$xajax->registerFunction("searchSet");
$xajax->registerFunction("searchCSet");
$xajax->registerFunction("searchcontactbtn");
$xajax->registerFunction("newCall");
$xajax->registerFunction("newCallProcess");
$xajax->registerFunction("newIVRAudio");
$xajax->registerFunction("ivrOptAdd");
$xajax->registerFunction("ivrHorario");
$xajax->registerFunction("ivrConfEdit");
$xajax->registerFunction("ivrDelete");
$xajax->registerFunction("ivrMove");
$xajax->registerFunction("SelectAcct");
$xajax->registerFunction("tagEdit");
$xajax->registerFunction("tagSave");
$xajax->registerFunction("callsAddContact");
$xajax->registerFunction("callsAddContactClose");
$xajax->registerFunction("audioDelete");
$xajax->registerFunction("getStats");
$xajax->registerFunction("statsProcessForm");
$xajax->registerFunction("mmGetList");
$xajax->registerFunction("mmOfertas");
$xajax->registerFunction("mmCompras");
$xajax->registerFunction("mmAddEdit");
$xajax->registerFunction("mmAddEditForm");
$xajax->registerFunction("mmComprar");
$xajax->registerFunction("mmSaldoTel");
$xajax->registerFunction("mmSaldoTelForm");
$xajax->registerFunction("pagoPaypal");
$xajax->registerFunction("pagoVisanet");
$xajax->registerFunction("makePayments");

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

function contactsDeleteContact( $id ) {
	global $accountcode;
	global $db;

	favsDelete($id);

	$db -> query ( "delete from contacts where id_contact = $id");

	return getContacts();
}

function numsDelete( $id ) {
	global $accountcode;
	global $db;

	$db -> query ("delete from nc_mynumber where id=$id and accountcode='$accountcode'");
	return getNums();
}

function searchcontactbtn( $form ) {
	global $accountcode;
	global $db;

	$objResponse = new xajaxResponse();

	$objResponse->assign("contactlivesearch","innerHTML","");
	$objResponse->assign("contactlivesearch","style.border","0px");
	$objResponse -> assign("contact_content", "innerHTML", "");
	$objResponse -> assign("contacts_content", "innerHTML", "<ul>" . getCContactsSTR($form['contact']). "</ul>");
	$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
	$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
	$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
	$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");


	return $objResponse;
}

function numsProcessForm( $form ) {
	global $accountcode;
	global $db, $allowed_numbers;

	$objResponse = getNums();

	$id = trim($form['id']);
	$numero = trim($form['numero']);
	$desc   = trim($form['descripcion']);

	if ( ! is_numeric( $numero ) ) {
		$objResponse -> script("alert('El N\u00famero de telefono debe de ser n\u00famerico.');");
		return $objResponse;
	}

	if ( strlen($numero) != 8 ) {
		$objResponse -> alert ("El Numero de telefono debe tener 8 digitos.");
		return $objResponse;
	}

	$db -> query ( "SELECT count(*) as qty FROM nc_mynumber where number = '$numero' and accountcode = '$accountcode'");
	$db -> next_record();
	
	if ( $db -> f ( "qty" ) > 0 && $id < 0 ) {
		$objResponse -> alert ("El Numero ya existe como destino en Numero Central.");
		return $objResponse;
	}

	$db -> query ( "SELECT count(*) as qty FROM nc_mynumber where accountcode = '$accountcode'");
	$db -> next_record();
	if ( $db -> f ("qty") >= $allowed_numbers && $id < 0 ) {
		$objResponse -> script("alert('Ya tiene la cantidad permitida de n\u00fameros en su N\u00famero Central.');");
		return $objResponse;
	}

	if ( $id < 0 ) {
		$db -> query ( "insert into nc_mynumber values ( null, '$accountcode', '$numero', '$desc', 0)");
	} else {
		$db -> query ( "update nc_mynumber set number='$numero', description='$desc' where id = $id and accountcode='$accountcode'");
	}

	$objResponse = getNums();
	return $objResponse;
	
}

function tagsProcessForm( $form ) {
	global $accountcode;
	global $db;

	$objResponse = getTags();

	$id = trim($form['id']);
	$nombre = trim($form['nombre']);
	$desc   = trim($form['descripcion']);

	if ( strlen($nombre) == 0 || strlen($desc) == 0 ) {
		$objResponse -> alert ("La etiqueta debe de tener nombre y descripcion .");
		return $objResponse;
	}

	$db -> query ( "SELECT count(*) as qty FROM etiquetas where nombre = '$nombre' and accountcode = '$accountcode'");
	$db -> next_record();
	
	if ( $db -> f ( "qty" ) > 0 && $id < 0 ) {
		$objResponse -> alert ("La Etiqueta ya existe en Numero Central.");
		return $objResponse;
	}

	if ( $id < 0 ) {
		$db -> query ( "insert into etiquetas values ( null, '$accountcode', '$nombre', '$desc')");
	} else {
		$db -> query ( "update etiquetas set nombre='$nombre', descripcion='$desc' where id = $id and accountcode='$accountcode'");
	}

	$objResponse = getTags();
	return $objResponse;
	
}


function tagsEdit( $id_tag ) {
	global $accountcode;
	global $db;

	$objResponse = new xajaxResponse();

	if ( $id_tag > 0 ) {
		$db -> query ("SELECT * FROM etiquetas WHERE accountcode = '$accountcode'");

	$form_head  = "<form id=\"TagsForm$id_tag\" action=\"javascript:void(null);\" onsubmit=\"MyTagsProcess($id_tag);\">";
	$form_head .= "<input type=\"hidden\" name=\"id\" value=\"$id_tag\" />";
	$form_head .= "<input type=\"hidden\" name=\"type\" value=\"mytag\" />";
	$form_head .= "<br/><p><div id='prefs_container'><h3>Mis Etiquetas</h3><br/><table cellspacing=1 class=\"llamadas\"><tr><th>Etiqueta</th><th>Descripci&oacute;n</th><th></th></tr>";

	while ($db -> next_record()) {
		$id = $db -> f ("id");
		$etiqueta = $db -> f ("nombre");
		if ( $id == $id_tag) {
			$form_head .= "<tr><td><input size=8 type=\"text\" name=\"nombre\" value =\"" . $etiqueta . "\"/></td>";
			$form_head .= "<td><input size=8 type=\"text\" name=\"descripcion\" value =\"" . $db -> f ("descripcion") . "\"/></td>";
			$form_head .= "<td><input id=\"addTagFormSubmit\" type=\"submit\" value=\"Editar\"/></td></tr>";
		} else {
			$form_head .= "<div id=\"tag$id\"><tr id=\"tag$id tr\"><td><div id=\"tag$id nombre\">" . $etiqueta . "</div></td><td><div id=\"tag$id desc\">" . $db -> f ("descripcion");	
			$form_head .= "</div><td><div id=\"tag$id buttons\"><a href='#' onclick='xajax_tagsEdit($id)'><img border=0 src=/images/edit.jpg></a>";
			$form_head .= "<a href='#' onclick='deleteMyNum($id, \"$etiqueta\")'><img border=0 src=/images/delete.jpg></a></div></td></tr></div>";
		}
	}

	$form_head .= "</table></form></div>";

	$objResponse -> Assign("contenido","innerHTML", $form_head );
} else {
$form =
'
<br/>
<form id="TagsForm0" action="javascript:void(null);" onsubmit="MyTagsProcess(0);">
<div class="padder">
<table>
<tr><td>Etiqueta:</td><td><input type="text" name="nombre" /></td></tr>
<tr><td>Descripci&oacute;n:</td><td><input type="text" name="descripcion" /></td></tr>
<input type="hidden" name="id" value="-1" />
<input type="hidden" name="type" value="mytag" />
<tr><td colspan=2>
<input id="addTagFormSubmit" type="submit" value="Agregar"/>
</td></tr>
</div>
</form>
';

	$objResponse -> assign ("num$id_tag", "innerHTML", $form);	
}

return $objResponse;
}

function tagsDelete ( $id_tag ) {
	global $accountcode;
	global $db;

	if ( $id_tag > 0 ) {
		$db -> query ("delete FROM etiquetas WHERE accountcode = '$accountcode' and id = $id_tag;");
	}

	
	return getTags();

}


function numsEdit( $id_num ) {
	global $accountcode;
	global $db;

	$objResponse = new xajaxResponse();

	if ( $id_num > 0 ) {
		$db -> query ("SELECT * FROM nc_mynumber WHERE accountcode = '$accountcode'");

	$form_head  = "<form id=\"NumsForm$id_num\" action=\"javascript:void(null);\" onsubmit=\"MyNumsProcess($id_num);\">";
	$form_head .= "<input type=\"hidden\" name=\"id\" value=\"$id_num\" />";
	$form_head .= "<input type=\"hidden\" name=\"type\" value=\"mynum\" />";
	$form_head .= "<br/><p><div id='prefs_container'><h3>Mis N&uacute;meros</h3><br/><table cellspacing=1 class=\"llamadas\"><tr><th>N&uacute;mero</th><th>Descripci&oacute;n</th><th></th></tr>";

	while ($db -> next_record()) {
		$id = $db -> f ("id");
		$number = $db -> f ("number");
		if ( $id == $id_num ) {
			$form_head .= "<tr><td><input size=8 type=\"text\" name=\"numero\" value =\"" . $db -> f ("number") . "\"/></td>";
			$form_head .= "<td><input size=8 type=\"text\" name=\"descripcion\" value =\"" . $db -> f ("description") . "\"/></td>";
			$form_head .= "<td><input id=\"addNumFormSubmit\" type=\"submit\" value=\"Editar\"/></td></tr>";
		} else {
			$form_head .= "<div id=\"num$id\"><tr id=\"num$id tr\"><td><div id=\"num$id number\">" . $db -> f ("number") . "</div></td><td><div id=\"num$id desc\">" . $db -> f ("description");	
			$form_head .= "</div><td><div id=\"num$id buttons\"><a href='#' onclick='xajax_numsEdit($id)'><img border=0 src=/images/edit.jpg></a>";
			$form_head .= "<a href='#' onclick='deleteMyNum($id, $number)'><img border=0 src=/images/delete.jpg></a></div></td></tr></div>";
		}
	}

	$form_head .= "</table></form></div>";

	$objResponse -> Assign("contenido","innerHTML", $form_head );
} else {
$form =
'
<br/>
<form id="NumsForm0" action="javascript:void(null);" onsubmit="MyNumsProcess(0);">
<div class="padder">
<table>
<tr><td>N&uacute;mero:</td><td><input type="text" name="numero" /></td></tr>
<tr><td>Descripci&oacute;n:</td><td><input type="text" name="descripcion" /></td></tr>
<input type="hidden" name="id" value="-1" />
<input type="hidden" name="type" value="mynum" />
<tr><td colspan=2>
<input id="addNumFormSubmit" type="submit" value="Agregar"/>
</td></tr>
</div>
</form>
';

	$objResponse -> assign ("num$id_num", "innerHTML", $form);	
}

return $objResponse;
}

function getTags() {
	global $faxno,$accountcode,$saldo,$allowed_numbers,$vence,$planid,$maxexten;
	global $db;

	$res = "";

	$objResponse = new xajaxResponse();
	buildMenu($objResponse,4);

	$res = "<br/><p><div id='prefs_container'><h3>Mis Etiquetas</h3><br/><div id=\"num-1\"><a href='#' class='btn' onclick='xajax_tagsEdit(-1)'>";
	$res .= '<i class="fa fa-tag fa-lg"></i>&nbsp;';
	$res .= "Agregar Etiqueta</a></div><br/><table cellspacing=1 class=\"llamadas\"><tr><th>Etiqueta</th><th>Descripci&oacute;n</th><th></th></tr>";

	$db -> query ("SELECT * FROM etiquetas WHERE accountcode = '$accountcode' order by id");
	while ( $db -> next_record() ) {
		$id = $db -> f ("id");
		$etiqueta= $db -> f ("nombre");
		$res .= "<div id=\"tag$id\"><tr id=\"tag$id tr\"><td><div id=\"tag$id number\"><a href='#' onclick='tags($id)'>" . $etiqueta . "</a></div></td><td><div id=\"tag$id desc\">" . $db -> f ("descripcion");
		$res .= "</div><td><div id=\"tag$id buttons\"><a class='override' href='#' onclick='xajax_tagsEdit($id)'><i class=\"fa fa-edit fa-fw\"></i></a>";
		$res .= "<a class='override' href='#' onclick='deleteMyTag($id,\"$etiqueta\")'><i class=\"fa fa-trash fa-fw\"></i></a></div></td></tr></div>";
	}

	$res .= "</div></table>";

	$objResponse -> assign("contenido","innerHTML",$res);
	return $objResponse;
}

function getNums() {
	global $faxno,$accountcode,$saldo,$allowed_numbers,$vence,$planid,$maxexten, $max1exten;
	global $db;

	$res = "";

	$objResponse = new xajaxResponse();
	buildMenu($objResponse,3);

	if ( $planid == 5 || $planid == 6 || $planid == 3 || $planid == 4 || $planid == 15 || $planid == 16 || $planid == 17 || $planid == 18 || $planid == 19) {
		$recording = 0;

		if ( $planid != 16 ) {
			$res .= "<h3>Men&uacute; de Voz</h3>";
			if ( $planid != 17 && $planid != 18 ) {
				$res .= "<p><div id='prefs_container'>";
			} else {
				$res .= "<p><div id='prefswide_container'>";
			}

			$db -> query ( "select * from ivr_audio where accountcode='$accountcode'");
			if ( $db -> next_record() ) {
				$path = "/var/lib/asterisk/sounds/ivr/" . $db -> f ("recording") . ".wav";
				if (file_exists($path)) {
					$res .= "<h4>Men&uacute; de horario de oficina</h4>";
					$res .= "Su men&uacute; de horario de oficina tiene una duraci&oacute;n de: " . wavDur($path);

$flash = '<object type="application/x-shockwave-flash" data="player_mp3_maxi.swf" width="120" height="12">
<param name="movie" value="player_mp3_maxi.swf" />
<param name="FlashVars" value="mp3=fileproducto.php?file=' . $accountcode . '-menu' . '&amp;bgcolor1=eeeeee&amp;bgcolor2=aaaaaa&amp;buttoncolor=443344&amp;buttonovercolor=0&amp;slidercolor1=aaaaaa&amp;slidercolor2=443344&amp;slid
erovercolor=666666&amp;textcolor=0&amp;" />
</object>';
$flash .= '<a href="fileproducto-wav.php?file=' . $accountcode . '-menu" class="override">&nbsp;<i class="fa fa-download fa-lg"></i></a>';
					$res .= "&nbsp;$flash";
	
					$recording = 1;

					$inicio = $db -> f ("hora_inicio");
					$hini = explode(":", $inicio);
					$fin = $db -> f ("hora_fin");
					$hfin = explode(":", $fin);

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

					$res .= "<form id=\"ivrHorario\" name=\"ivrHorario\" action='javascript:void(null);\' onsubmit='xajax_ivrHorario(xajax.getFormValues(\"ivrHorario\"))'>";
					$res .= "Horario de Oficina: <b>Apertura:</b> <select name=horainicio>$hinih</select>:<select name=minutoinicio>$minih</select> <b>Cierre:</b> <select name=horafin>$hfinh</select>:<select name=minutofin>$mfinh</select>";
					$res .= "<input type=submit value='Guardar'/></form>";

	

				} else {
					$res .= "No Hay grabaci&oacute;n para el IVR, por favor subir su archivo de audio en formato wav.";
				}
			} else {
				$res .= "No Hay grabaci&oacute;n para el IVR, por favor subir su archivo de audio en formato wav.";
			}

			if ( $recording == 1 ) {
				$res .= "<br/>";
				$res .= "<h4>Men&uacute; de horario fuera de oficina</h4>";
				$db -> query ("select * from ivr_ooaudio where accountcode='$accountcode'");
				if ( $db -> next_record() ) {
					$path = "/var/lib/asterisk/sounds/ivroo/" . $db -> f ("recording") . ".wav";
					if (file_exists($path)) {
						$res .= "Su men&uacute; de horario fuera de oficina  tiene una duraci&oacute;n de: " . wavDur($path);

$flash = '<object type="application/x-shockwave-flash" data="player_mp3_maxi.swf" width="120" height="12">
<param name="movie" value="player_mp3_maxi.swf" />
<param name="FlashVars" value="mp3=fileproducto-oo.php?file=' . $accountcode . '-menu' . '&amp;bgcolor1=eeeeee&amp;bgcolor2=aaaaaa&amp;buttoncolor=443344&amp;buttonovercolor=0&amp;slidercolor1=aaaaaa&amp;slidercolor2=443344&amp;slid
erovercolor=666666&amp;textcolor=0&amp;" />
</object>';
$flash .= '<a href="fileproducto-wav-oo.php?file=' . $accountcode . '-menu" class="override">&nbsp;<i class="fa fa-download fa-lg"></i></a>';
				$res .= "&nbsp;$flash";
					}
				}
			}
		} else {
			$res .= "<h3>Control de Extensiones</h3>";
			$res .= "<p><div id='prefs_container'>";
			$recording = 1;
		}

		if ( $recording ) {
			if ( $planid ==5 || $planid ==6) {
				$res .= "<br/><br><h4>Extensiones (de 1 d&iacute;gitos):</h4>";
			} else if ( $planid != 17 && $planid != 18 ) {
				$res .= "<br/><br><h4>Extensiones:</h4>";
			}


			$db -> query ( "select count(*) as qty from ivr_option where accountcode = '$accountcode' and keypad < 10");
			$db -> next_record();
			
			if ( $db -> f ("qty") < $max1exten ) {
				$res .= "<form id=\"ivrOptions\" name=\"ivrOptions\" action=\"javascript:void(null);\" onsubmit='xajax_ivrOptAdd(xajax.getFormValues(\"ivrOptions\"))'>";
				$res .= "<input type=hidden name=extension value=0/>";
				$res .= 'Tel&eacute;fono: <input type=text size=10 name="numero"/><input type=submit value="Agregar extensi&oacute;n"/></form>';
			}

			$db -> query ( "select * from ivr_option where accountcode = '$accountcode' and keypad < 10 order by keypad");

			if ( $db -> next_record() ) {
				$res .= "<p><table cellspacing=1 class=\"llamadas\"><tr><th>Extensi&oacute;n</th><th>Tel&eacute;fono</th><th></th></tr>";
				do {
					$id = $db -> f ("id"); $keypad = $db -> f ("keypad");
					$up = $down = "";
		

					if ( $keypad == 0 ) {
						$up = "";
					} else {
						$up = "<a href='#' onclick='xajax_ivrMove($id,-1)' class='override'><i class='fa fa-chevron-up fa-fw'></i></a>";
					}

					if ( $keypad == 9 ) {
						$down = "<img border=0 height=16 width=16 src='/images/blank.gif'/>";
					} else {
						$down = "<a href='#' onclick='xajax_ivrMove($id,1)' class='override'><i class='fa fa-chevron-down fa-fw'></i></a>";
					}	

					$res .= "<tr><td>" . $db -> f ( "keypad" ) .
						"</td><td>" . $db -> f ( "number" ) .
						"</td><td>" .
						"<a href='#' onclick='xajax_ivrDelete($id)' class='override'><i class='fa fa-trash fa-fw'></i></a>" .
						$down . $up.
						"</td>";
				} while ( $db -> next_record() );
				$res .= "</table>";
			}

			if ( $planid == 17 || $planid == 18 ) {
				$res .= "<br/><h4>Extensiones (de 4 d&iacute;gitos):</h4>";
			} else if ( $planid ==5 || $planid ==6) {
				$res .= "<br/><h4>Extensiones (de 4 d&iacute;gitos):</h4>";
			}

			$db -> query ( "select count(*) as qty from ivr_option where accountcode = '$accountcode' and keypad > 10");
			$db -> next_record();
			
			if ( $planid == 5 || $planid == 6 ) {
				if ( $db -> f ("qty") < $maxexten ) {
					$res .= "<form id=\"ivrExtensions\" name=\"ivrExtensions\" action=\"javascript:void(null);\" onsubmit='xajax_ivrOptAdd(xajax.getFormValues(\"ivrExtensions\"))'>";
					$res .= 'Extensi&oacute;n: <input type=text size=10 name="extension"/>  Tel&eacute;fono: <input type=text size=10 name="numero"/><input type=submit value="Agregar extensi&oacute;n"/></form>';
				}
			}

			if ( $planid == 17 || $planid == 18 ) {
				$maxexten = ($maxexten > 0)?$maxexten:5;

				if ( $db -> f ("qty") < $maxexten ) {
					$res .= "<form action='ivr-extension-upload.php' enctype='multipart/form-data' method='post'>";
					$res .= 'Extensi&oacute;n: <input type=text size=10 name="extension"/>  Tel&eacute;fono: <input type=text size=10 name="numero"/>';
					$res .= "Audio: <input type=file name=\"extenaudio\"/>";
		                        $res .= "<input type=hidden name='accountcode' value='$accountcode'/>";
					$res .= '<input type=submit value="Agregar Extensi&oacute;n"/>';
				}
			}

			$db -> query ( "select * from ivr_option where accountcode = '$accountcode' and keypad > 10 order by keypad");

			if ( $db -> next_record() ) {
				if ( $planid == 17 || $planid == 18 ) {
					$res .= "<p><table cellspacing=1 class=\"llamadas\"><tr><th>Extensi&oacute;n</th><th>Tel&eacute;fono</th><th>Audio</th><th></th></tr>";
				} else {
					$res .= "<p><table cellspacing=1 class=\"llamadas\"><tr><th>Extensi&oacute;n</th><th>Tel&eacute;fono</th><th></th></tr>";
				}
				do {
					$id = $db -> f ("id"); $keypad = $db -> f ("keypad");

					if ( $planid == 17 || $planid == 18 ) {
$flash = '<object type="application/x-shockwave-flash" data="player_mp3_maxi.swf" width="120" height="12">
<param name="movie" value="player_mp3_maxi.swf" />
<param name="FlashVars" value="mp3=fileproducto.php?file=' . $accountcode . '-' . $keypad . '&amp;bgcolor1=eeeeee&amp;bgcolor2=aaaaaa&amp;buttoncolor=443344&amp;buttonovercolor=0&amp;slidercolor1=aaaaaa&amp;slidercolor2=443344&amp;slid
erovercolor=666666&amp;textcolor=0&amp;" />
</object>';
$flash .= '<a href="fileproducto-wav.php?file=' . $accountcode . '-' . $keypad .'"><img border=0 src=/images/download.png /></a>';
						$res .= "<tr><td>" . $db -> f ( "keypad" ) .
						"</td><td>" . $db -> f ( "number" ) .
						"</td><td>" . $flash . "</td><td>" .
						"<a href='#' onclick='xajax_ivrDelete($id)'><img border=0 src=/images/delete.jpg></a>" .
						"</td>";

					} else {
						$res .= "<tr><td>" . $db -> f ( "keypad" ) .
						"</td><td>" . $db -> f ( "number" ) .
						"</td><td>" .
						"<a href='#' onclick='xajax_ivrDelete($id)'><img border=0 src=/images/delete.jpg></a>" .
						"</td>";
					}
				} while ( $db -> next_record() );
				$res .= "</table>";
			}

			if ( $planid == 17 || $planid == 18 ) {
			} else {
				$confno = "";
				$confpin = "";
				$res .= "<br/><h4>El Cuarto de Conferencias es la extensi&oacute;n 9:</h4>";
				$db -> query ("select * from conferences where confno like '$accountcode%'");
				if ( $db -> next_record() ) {
					$confno = str_replace("$accountcode","",$db -> f ("confno"));
					$confpin = $db -> f ("pin");
				}

				$res .= "<form id=\"ivrConf\" name=\"ivrConf\" action=\"javascript:void(null);\" onsubmit='xajax_ivrConfEdit(xajax.getFormValues(\"ivrConf\"))'>";
				$res .= "<input type=hidden name=extension value=\"9\"/>";
				$res .= "Pin: <input type=text size=8 name=\"pin\" value=\"$confpin\"/>";
				$res .= "<input type=submit value='Guardar'/>";
				
			}



		}

		$res .= "</div>";
	} else {
		$qty = 0;
		$db -> query ( "SELECT count(*) as qty FROM nc_mynumber where accountcode = '$accountcode'");
		$db -> next_record();
		$qty = $db -> f ("qty");
		if ( $qty < $allowed_numbers ) {
			$res = "<br/><p><div id='prefs_container'><h3>Mis N&uacute;meros</h3><br/><div id=\"num-1\"><a href='#' class='btn' onclick='xajax_numsEdit(-1)'>";
			$res .= '<i class="fa fa-plus-square"></i>&nbsp;';
			$res .= "Agregar N&uacute;mero</a></div><br/><table cellspacing=1 class=\"llamadas\"><tr><th>N&uacute;mero</th><th>Descripci&oacute;n</th><th></th></tr>";
		} else {
			$res = "<br/><p><div id='prefs_container'><h3>Mis N&uacute;meros</h3><br/><table cellspacing=1 class=\"llamadas\"><tr><th>N&uacute;mero</th><th>Descripci&oacute;n</th><th></th></tr>";
		}
		$db -> query ("SELECT * FROM nc_mynumber WHERE accountcode = '$accountcode' order by id");
		while ( $db -> next_record() ) {
			$id = $db -> f ("id");
			$number = $db -> f ("number");
			$res .= "<div id=\"num$id\"><tr id=\"num$id tr\"><td><div id=\"num$id number\">" . $db -> f ("number") . "</div></td><td><div id=\"num$id desc\">" . $db -> f ("description");	
			$res .= "</div><td><div id=\"num$id buttons\"><a class='override' href='#' onclick='xajax_numsEdit($id)'><i class=\"fa fa-edit fa-fw\"></i></a>";
			$res .= "<a class='override' href='#' onclick='deleteMyNum($id, $number)'><i class=\"fa fa-trash fa-fw\"></i></a></div></td></tr></div>";
		}

		$res .= "</div></table>";
	}

	$objResponse -> assign("contenido","innerHTML",$res);
	return $objResponse;
}

function ivrConfEdit($form) {
	global $accountcode, $db;

	$objResponse = new xajaxResponse();

	$pin = trim($form['pin']);
	$extension = trim($form['extension']);

	if ( !is_numeric($extension) && strlen($extension) > 0  ) {
		$objResponse -> alert ("La extension debe ser numerica.");
		return $objResponse;
	}

	if ( !is_numeric($pin) && strlen($pin) > 0) {
		$objResponse -> alert ("El pin debe ser numerico.");
		return $objResponse;
	}

	if ( strlen($extension) == 0 ) {
		$objResponse -> alert ("La extension para el cuarto de conferencias debe de tener 4 digitos.");
		return $objResponse;
	}
	
	if ( strlen($pin) != 4 ) {
		$objResponse -> alert ("La pin para el cuarto de conferencias debe de tener 4 digitos.");
		return $objResponse;
	}

	if ( strlen($extension) ) {
	$db -> query ("select count(*) as qty from ivr_option where accountcode='$accountcode' and keypad='$extension'");
	$db -> next_record();

	if ( $db -> f ("qty") > 0 ) {
		$objResponse = getNums();
		$objResponse -> alert ("Ya existe la extension en el IVR.");
		return $objResponse;
	}
	}
	
	$db -> query ("delete from conferences where confno like '$accountcode%'");	

	if ( strlen($pin) > 0 && strlen($extension) > 0 ) {
		$db -> query ("insert into conferences values ( '$accountcode$extension','$pin','',0)");
	}
	
	$objResponse = getNums();
	$objResponse -> alert ("Operacion realizada exitosamente.");

	return $objResponse;
}

function ivrHorario($form) {
	global $accountcode, $db;

	$objResponse = new xajaxResponse();

	$horainicio = trim($form['horainicio']);
	$horafin = trim($form['horafin']);
	$minutoinicio = trim($form['minutoinicio']);
	$minutofin = trim($form['minutofin']);

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

	$db -> query ("update ivr_audio set hora_inicio='$inicio', hora_fin='$fin' where accountcode = $accountcode" );	
	
	$objResponse = getNums();
	$objResponse -> alert ("Operacion realizada exitosamente.");

	return $objResponse;
}

function ivrOptAdd( $form ) {
	global $accountcode, $db, $maxexten, $max1exten;

	$objResponse = new xajaxResponse();

	$extension = trim($form['extension']);

	$numero = trim($form['numero']);
	if ( !is_numeric($numero) ) {
		$objResponse -> script("alert('La opci\u00f3n ingresada no es numerica, debe de ser un n\u00famero telef\u00f3nico de 8 d\u00edgitos.');");
		return $objResponse;
	}
	
	if ( strlen($numero) != 8 ) {
		$objResponse -> script("alert('La opci\u00f3n ingresada debe de ser un n\u00famero telef\u00f3nico de 8 d\u00edgitos.');");
		return $objResponse;
	}

	if ( $extension == 0 ) {
		$db -> query ("select count(*) as qty, max(keypad) as keypad from ivr_option where accountcode='$accountcode' and keypad < 10");
		$db -> next_record();
		$qty = $db -> f ("qty");
		$keypad = 0 + $db -> f ("keypad");

		if ( $qty >= $max1exten  ) {
			$objResponse -> alert ("Ya hay $max1exten opciones en el IVR.");
			return $objResponse;
		} else {
			if ( $qty > 0 ) { $keypad++; }
			$db -> query ( "insert into ivr_option values ( null, '$accountcode', $keypad, $numero)");
		}
	} else {
		if ( strlen($extension) != 4 ) {
			$objResponse -> alert ("La extension debe de tener 4 digitos.");
			return $objResponse;
		}

		$db -> query ("select count(*) as qty from ivr_option where accountcode='$accountcode' and keypad='$extension'");
		$db -> next_record();

		if ( $db -> f ("qty") > 0 ) {
			$objResponse -> alert ("Ya existe la extension en el IVR.");
			return $objResponse;
		}

		$db -> query ("select substr(confno,9,4) as confno from conferences where confno like '$accountcode%'");

		if ( $db -> next_record() ) {
			$confno = $db -> f ("confno");
		}

		if ( $extension == $confno ) {
			$objResponse -> alert ("La extension ingresada esta asignada al cuarto de conferencias.");
			return $objResponse;
		}

		$db -> query ("select count(*) as qty from ivr_option where accountcode='$accountcode' and keypad > 10");
		$db -> next_record();
		if ( $db -> f ("qty") >= $maxexten ) {
			$objResponse -> alert ("Ya hay $maxexten extensiones el IVR.");
			return $objResponse;
		}

		$id_vendedor = 0;
		$db -> query ("select id_vendedor from users where accountcode='$accountcode'");
		if ( $db -> next_record() ) {
			$id_vendedor = $db -> f ("id_vendedor");
		}

		$db -> query ("insert into ivr_option values ( null, '$accountcode', $extension, $numero )");
		$db -> query ("insert into users values ( null, '$accountcode-$extension',md5('$numero'),'','$extension','$accountcode-$extension',13, NULL, now(), NULL, $id_vendedor, 0, 0, '', 0, 0 )");
	}
	return getNums();
}

function getAccount() {
global $accountcode,$saldo,$faxno,$planid,$vence;
global $db;
$objResponse = new xajaxResponse();

	buildMenu($objResponse, 0);

$contenido = "<div id=\"prefs_container\"><h3></h3><br/><div class=\"padder\">";

$db -> query ( "select * from users where accountcode='$accountcode'" );
if ( $db -> next_record() ) {
	$opciones = '
<form id="ChangeAccountForm" name="ChangeAccountForm" action="javascript:void(null);" onsubmit="ChangeAccount()">
<table>
<tr><td>Nombre:</td><td><input size=35 type="text" name="name" value="' . $db -> f ("name") . '"/></td></tr>
<tr><td>E-Mail:</td><td><input size=35 type="text" name="email" value="' . $db -> f ("email") .'"/></td></tr>
<tr><td>Clave Web:</td><td><input size=15 type="password" name="password" value="' . $db -> f ("passwd") . '" /></td></tr>
<tr><td>Confirmar:</td><td><input size=15 type="password" name="password2" value="' . $db -> f ("passwd") . '" /></td></tr>';
	
	$db -> query ( "select * from voicemail_users where customer_id = '$accountcode'" );
	$db -> next_record();

	$opciones .= '
<tr><td>Clave del buz&oacute;n:</td><td><input size=5 type="text" name="vmpass" value="' . $db -> f("password") . '"/></td></tr>
<tr><td>Confirmar:</td><td><input size=5 type="text" name="vmpass2" value="' . $db -> f("password") . '"/></td></tr>
<tr><td></td><td>&nbsp;</td></tr>
<tr><td></td><td><input type="submit" value="Guardar Cambios"></td></tr></table></form></br></br>&nbsp;
			';
} else {
	$opciones = "";
}

$contenido .= "$opciones</div></div>";

$objResponse -> assign("contenido","innerHTML", $contenido);
return $objResponse;
}

function getFavorites( $id_group ) {
global $accountcode,$saldo,$faxno,$vence;
global $db;
$objResponse = new xajaxResponse();

buildMenu($objResponse,4);

$db -> query ("select groups.id as id, group_name, count(*) as qty from contacts, groups where contacts.id_group = groups.id and accountcode = '$accountcode' group by groups.id");

$opciones = '<form id="FavoritesForm" name="FavoritesForm" action="javascript:void(null);" onsubmit="FavoritesAdd()">
		Grupo:<select name="grupo" id="grupo" onChange="xajax_getFavorites(this.value);">
		<option value="-1">(Seleccione un Grupo)</option>';

while ( $db -> next_record() ) {
	$id = $db -> f ( "id" );
	$name  = $db -> f ( "group_name" );
	$qty = $db -> f ( "qty" );
	if ( $id_group == $id ) {
		$opciones .= "<option value=\"$id\" selected>$name ($qty)</option>";
	} else {
		$opciones .= "<option value=\"$id\">$name ($qty)</option>";
	}
}

$opciones .= '</select>&nbsp;&nbsp;Contacto:<select name="contact" id="contact">'; 

if ( $id_group > 0 ) {

	$db -> query ( "select count(*) as qty from groups where id = $id_group");
	$db -> next_record();
	if ( $db -> f ("qty") > 0 ) {

		$db -> query ( "select * from contacts where id_group = $id_group order by name");

		while ( $db -> next_record() ) {
			$id = $db -> f ( "id_contact");
			$name = $db -> f ( "name");

			$opciones .= "<option value=\"$id\">$name</option>";
		}
	} else {
		$opciones .= "<option value=\"-1\">(Seleccione un Grupo)</option>";
	}
} else {
	$opciones .= "<option value=\"-1\">(Seleccione un Grupo)</option>";
}

$opciones .= '</select>&nbsp;&nbsp;<input id="favsFormSubmit" type="submit" value="Agregar"/></form>';

$db -> query ( "select max(keypad) as keypad from favorites where accountcode = '$accountcode'");
$db -> next_record();
$max_keypad = 0 + $db -> f ("keypad");
$db -> query ( "select favorites.id_contact as id, name, email, number, keypad from contacts, favorites where contacts.id_contact = favorites.id_contact and accountcode = '$accountcode' order by keypad");

if ( $db -> next_record() ) {
	$opciones .= "<p><table cellspacing=1 class=\"llamadas\"><tr><th>Codigo</th><th>Nombre</th><th>N&uacute;mero</th><th>E-Mail</th><th></th></tr>";
	do {
		$id = $db -> f ("id"); $keypad = $db -> f ("keypad");
		$up = $down = "";
		

		if ( $keypad == 0 ) {
			$up = "";
		} else {
			$up = "<a href='#' onclick='xajax_favsMove($id,-1)'><img border=0 src='/images/arrow_up_green.gif'/></a>";
		}

		if ( $keypad == $max_keypad ) {
			$down = "<img border=0 height=16 width=16 src='/images/blank.gif'/>";
		} else {
			$down = "<a href='#' onclick='xajax_favsMove($id,1)'><img border=0 src='/images/arrow_down_blue.gif'/></a>";
		}	

		$opciones .= "<tr><td>" . $db -> f ( "keypad" ) .
				"</td><td>" . $db -> f ( "name" ) .
				"</td><td>" . $db -> f ( "number" ) .
				"</td><td>" . $db -> f ( "email" ) .
				"</td><td>" .
				"<a href='#' onclick='xajax_favsDelete($id)'><img border=0 src=/images/delete.jpg></a>" .
				$down . $up.
				"</td>";
	} while ( $db -> next_record() );
} else {
}

$objResponse -> assign("contenido","innerHTML", $opciones );
return $objResponse;
}

function getFavoritesContacts( $id_group ) {
global $accountcode;
global $db;

$objResponse = new xajaxResponse();

$opciones = "";
if ( $id_group > 0 ) {

	$db -> query ( "select count(*) as qty from groups where id = $id_group");
	$db -> next_record();
	if ( $db -> f ("qty") > 0 ) {

		$db -> query ( "select * from contacts where id_group = $id_group order by name");

		while ( $db -> next_record() ) {
			$id = $db -> f ( "id_contact");
			$name = $db -> f ( "name");

			$opciones .= "<option value=\"$id\">$name</option>";
		}
	} else {
		$opciones = "<option value=\"-1\">(Seleccione un Grupo)</option>";
	}
} else {
	$opciones = "<option value=\"-1\">(Seleccione un Grupo)</option>";
}

$objResponse -> script("clearOptions('FavoritesForm','contact')");
$objResponse -> assign('contact', 'innerHTML', $opciones);
return $objResponse;
}

function getPayments() {
	global $accountcode,$saldo,$global_record,$faxno,$vence,$saldo_qtz_no,$monto;
	global $db;
	$objResponse = new xajaxResponse();

	buildMenu($objResponse, 5);

	$habilitarpago = 0;
	$db -> query("select TIMESTAMPDIFF(MONTH, fecha_aplica,DATE_FORMAT(NOW() ,'%Y-%m-01')) as meses from pagos where accountcode = '$accountcode' and motivo_pago in (1, 3) order by fecha_aplica desc limit 1;");
	if ( $db -> next_record() ) {
		$meses = $db -> f ("meses");
	} else {
		$meses = 1;
	}

	if ( $meses > 0 && $monto > 0 && $saldo_qtz_no >= $monto ) {
		$habilitarpago = 1;
	}

	$res = "<div id='prefswide_container'><h3></h3><div id=\"num-1\"></div>"; 

	$res .= "<div class='padder'><br/>";
	$res .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_pagoPaypal()\"><i class=\"fa fa-cc-paypal fa-lg\"></i>&nbsp;Paypal</a>&nbsp;&nbsp;";
	$res .= "<a class=\"btn\" href=\"/tarjeta/compra.php\" ><i class=\"fa fa-cc-visa fa-lg\"></i>&nbsp;Visanet</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	if ( $habilitarpago ) {
		$res .= "<a class=\"btn\" href=\"#\" onclick=\"makePayments()\"><i class=\"fa fa-money fa-lg\"></i><b>&nbsp;Pagar Mensualidad</b></a>";
	} else {
		$res .= "<a class=\"btnd\" href=\"#\" title=\"Sin suficiente saldo en quetzales o est&aacute; al d&iacute;a.\"><i class=\"fa fa-money fa-lg\"></i><b>&nbsp;Pagar Mensualidad</b></a>";
	}
	$res .= "</div>";
	$res .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha</th><th>Documento</th><th>Banco</th><th>Qtz.</th><th>Min.</th><th>Factura</th><th>Mes</th><th>Motivo</th></tr>";

	$db -> query ("SELECT *, date(fecha_pago) as fecha FROM pagos WHERE accountcode = '$accountcode' order by fecha_aplica desc");
	$total = 0;
	$total_min = 0;
	while ( $db -> next_record() ) {
		$banco = "N/A";
		switch( $db -> f ("banco") ) {
			case 1 : $banco = "Industrial";break;
			case 2 : $banco = "BAM";break;
			case 3 : $banco = "GyT";break;
			case 4 : $banco = "Banrural";break;
			case 5 : $banco = "BAC";break;
			case 10 : $banco = "VisaNet";break;
			case 11 : $banco = "Serbipagos";break;
			case 12 : $banco = "Paypal";break;
			case 13 : $banco = "Interno";break;
			case 14 : $banco = "Saldo";break;
		}
		$tipo = "N/A";
		switch ( $db -> f ("forma_pago") ) {
			case 1: $tipo = "Efectivo";break;
			case 2: $tipo = "Cheque";break;
			case 3: $tipo = "Cheque O/B";break;
			case 4: $tipo = "Saldo Q.";break;
		}
		
		$factura = $db -> f ("factura");

		if ( $factura != "N/D" ) {
		if ( strlen($factura) == 3 ) {
			$factura= "130000000" . $factura;
		} else if ( strlen($factura) == 2 ) {
			$factura= "1300000000" . $factura;
		} else if ( strlen($factura) == 1 ) {
			$factura= "13000000000" . $factura;
		}
		}

		$minutos = number_format($db -> f ("minutos"),0);

		$fecha_aplica = $db -> f ("fecha_aplica");
		$ee = explode("-", $fecha_aplica);
		$mes_aplica = $ee[1];
		$ano_aplica = $ee[0];
		$fecha_aplica = "";

		if ( !( $mes_aplica == "00" && $ano_aplica == "0000")) {
			$fecha_aplica = "$mes_aplica" . "/" . $ano_aplica;
		}

		$pdf_mes = ltrim($mes_aplica, '0');
		$pdf_file = "";
		
		if ( file_exists ( "/var/spool/asterisk/facturas/$ano_aplica/$pdf_mes/$factura.pdf" ) ) {
			$pdf_file = "<a class='override' href=\"factura.php?file=$ano_aplica/$pdf_mes/$factura\"><i class=\"fa fa-file-pdf-o fa-fw\"></i></a>";
		}

		$motivo_pago = $db ->f ("motivo_pago");
		$monto = $db -> f ("monto");

		$motivo = "";
		if ( $motivo_pago == 0 ) {
			$motivo = "Minutos";
		} else if ( $motivo_pago == 1 ) {
			$motivo = "Mes";
		} else if ( $motivo_pago == 2 ) {
			$motivo = "Quetzales";
			$minutos = "-";
		} else if ( $motivo_pago == 3 ) {
			$motivo = "Mes";
			$total -= $monto;
			$monto = "<i>$monto</i>";
		}

		$res .= "<tr><td>" . $db -> f ("fecha" ) . "</td><td>" . $db -> f ("documento") . "</td><td>$banco</td><td>" . $monto . "</td><td>" . $minutos . "</td><td>" . $factura . "</td><td>" . $fecha_aplica . "</td><td>$motivo</td><td>$pdf_file</td></tr>";

		$total += $db -> f ("monto");
		$total_min += $db -> f ("minutos");
	}

	$total = number_format($total,2);
	$total_min = number_format($total_min,0);

	$res .= "<tr><td colspan=3 align=right></td><td></td><td></td><td colspan=3>&nbsp;</td>";

	$res .= "</div></table>";

	$objResponse -> assign("contenido","innerHTML",$res);
	
	return $objResponse;
}

function getPrefs() {
global $accountcode,$saldo,$global_record,$faxno,$vence;
global $db;
$objResponse = new xajaxResponse();

buildMenu($objResponse, 6);

$db -> query ("select * from incomming_prefs where accountcode = '$accountcode'");	
if ( $db -> next_record() ) {
	$callback = $callthru = $voicemail = $menu = "";

	$callback = "";
	$callthru = "";
	$voicemail = "";
	$menu = "";
	$serial = "";
	$paralelo = "";
	$noscreen = "";
	$screen = "";
	$recordno  = "";
	$recordall = "";
	$recordinc = "";
	$recordout = "";
	$blockno  = "";
	$blockyes = "";
	$playno  = "";
	$playcontrol = "";
	$playseguridad = "";
	$connno = "";
	$connyes = "";
	$missno = "";
	$missyes = "";
	$disabledyes = "";
	$disabledno = "";

	switch ( $db -> f ("mode") ) {
		case 1: $callback = "selected";break;
		case 2: $callthru = "selected";break;
		case 3: $voicemail = "selected";break;
		case 4: $menu = "selected";break;
	}

	switch ( $db -> f ("dialmode") ) {
		case 1: $serial = "selected";break;
		case 2: $paralelo = "selected";break;
	}

	$opscreen = $db -> f ("screen");
	if ( $opscreen == "" ) {
		$opscreen = 0;
	}
	switch ( $db -> f ("screen") ) {
		case 0: $noscreen = "selected";break;
		case 1: $screen = "selected";break;
	}

	switch ( $db -> f ("record") ) {
		case 0: $recordno  = "selected";break;
		case 1: $recordall = "selected";break;
		case 2: $recordinc = "selected";break;
		case 3: $recordout = "selected";break;
	}

	switch ( $db -> f ("blockanon") ) {
		case 0: $blockno  = "selected";break;
		case 1: $blockyes = "selected";break;
	}

	switch ( $db -> f ("playrecording" ) ) {
		case 0: $playno  = "selected";break;
		case 1: $playseguridad = "selected";break;
		case 2: $playcontrol = "selected";break;
	}

	switch ( $db -> f ("missemail") ) {
		case 0 : $missno = "selected";break;
		case 1 : $missyes = "selected";break;
	}

	switch ( $db -> f ("connplay") ) {
		case 0: $connno = "selected";break;
		case 1: $connyes = "selected";break;
	}

	switch ( $db -> f ("disabled") ) {
		case 0: $disabledno = "selected";break;
		case 1: $disabledyes = "selected";break;
	}

	$select  = "<option value=1 $callback>Devoluci&oacute;n de Llamada</option>";
	$select .= "<option value=3 $menu>Voicemail</option>";

	$dialmode  = "<option value=1 $serial>Serial</option>";
	$dialmode .= "<option value=2 $paralelo>Paralelo</option>";

	$screenop  = "<option value=0 $noscreen>Sin Identificador de Contacto</option>";
	$screenop .= "<option value=1 $screen>Con Identificador de Contacto</option>";

	$record  = "<option value=0 $recordno >No Grabar llamadas.</option>";
	$record .= "<option value=1 $recordall>Grabar todas las llamadas.</option>";
	$record .= "<option value=2 $recordinc>Grabar llamadas entrantes unicamente.</option>";
	$record .= "<option value=3 $recordout>Grabar llamadas salientes unicamente.</option>";

	$blocked  = "<option value=0 $blockno>No</option>";
	$blocked .= "<option value=1 $blockyes>Si</option>";

	$play  = "<option value=0 $playno>No</option>";
	$play .= "<option value=1 $playseguridad>Seguridad</option>";
	$play .= "<option value=2 $playcontrol>Control de Calidad</option>";

	$conn  = "<option value=0 $connno>No</option>";
	$conn .= "<option value=1 $connyes>Si</option>";

	$miss  = "<option value=0 $missno>No</option>";
	$miss .= "<option value=1 $missyes>Si</option>";

	$disabled  = "<option value=0 $disabledno>Disponible</option>";
	$disabled .= "<option value=1 $disabledyes>Inaccesible</option>";
} else {
	$select  = "<option value=1>Devoluci&oacute;n de Llamada</option>";
	$select .= "<option value=2>Llamada Directa</option>";
	$select .= "<option value=3>Voicemail</option>";
	$select .= "<option value=4>Menu</option>";
	
	$dialmode = "<option value=1>Serial</option>";
	$dialmode .= "<option value=2>Paralelo</option>";

	$screenop  = "<option value=0>Sin Identificador de Contacto</option>";
	$screenop .= "<option value=1>Con Identificador de Contacto</option>";
	$opscreen = 0;

	$record  = "<option value=0>No Grabar llamadas.</option>";
	$record .= "<option value=1>Grabar todas las llamadas.</option>";
	$record .= "<option value=2>Grabar llamadas entrantes unicamente.</option>";
	$record .= "<option value=3>Grabar llamadas salientes unicamente.</option>";

	$blocked  = "<option value=0>No</option>";
	$blocked .= "<option value=1>Si</option>";

	$play  = "<option value=0>No</option>";
	$play .= "<option value=1>Si</option>";

	$conn  = "<option value=0>No</option>";
	$conn .= "<option value=1>Si</option>";

	$miss  = "<option value=0>No</option>";
	$miss .= "<option value=1>Si</option>";

	$disabled  = "<option value=0>Disponible</option>";
	$disabled .= "<option value=1>Inaccesible</option>";
}

$opciones =    '<form id="prefsForm" action="javascript:void(null);" onsubmit="prefsProcessForm();">
		<input type="hidden" name="mode" value="1">
		<div id="prefs_container"><div class="padder">';

$db -> query ( "select * from users where accountcode='$accountcode'" );
$db -> next_record();
$username = $db -> f ("username");

$opciones .= 
'<table><tr><td><h4>Notificaciones:</h4></td><td>&nbsp;&nbsp;</td></tr></table><p style="font-size:1px"></p><table><tr><td>E-Mail:</td><td><input size=35 type="text" name="email" value="' . $db -> f ("email") .'"/></td></tr></table><br/>
<table><tr><td><h4>Cuenta:</h4></td><td>&nbsp;&nbsp;</td><tr/></table><table>
<tr><td>&nbsp;</td><td>Usuario:</td><td>'. $username . '</td></tr>
<tr><td>&nbsp;</td><td>Contrase&ntilde;a:</td><td><input size=15 id="txtpassword" type="password" name="password" value="' . $db -> f ("passwd") . '" /><meter value=0 id="PassValue" max="100"></meter><h3 id="complexity">0%</h3></td></tr>
<tr><td>&nbsp;</td><td>Confirmar:</td><td><input size=15 id="confirm" type="password" name="password2" value="' . $db -> f ("passwd") . '" /></td></tr></table><br/>';

$opciones .= '<input type="hidden" name="screen" value="' . $opscreen . '"/>';
$opciones .= '<input type="hidden" name="dialmode" value="2"/>';

if ( $global_record ) {

$opciones .= '
		<table><tr><td valign=top><h4>Grabaci&oacute;n de Llamadas:</h4>&nbsp;</td><td valign=top><select name="record">' . $record . '</td></tr>' .
		'<tr><td colspan=2><font size=1 color=red>Al grabar las llamadas el usuario se hace responsable de cualquier uso que se haga de las mismas, tanto entrantes como salientes. Para uso legal, es responsabilidad del usuario indicar que la llamada esta siendo grabada. Numero Central no es responsable directa ni indirectamente del uso indebido que se haga de las grabaciones hechas por cada usuario.</font></td></tr>'.
		'<tr><td valign=top><strong>Aviso de grabaci&oacute;n llamadas entrantes:</strong></td><td valign=top><select name="playrecord">' . $play . '</td></tr>' .
		'</table><br/>';
} else {
	$opciones .= '<input type="hidden" name="record" value="0"/>';
	$opciones .= '<input type="hidden" name="playrecord" value="0"/>';
}

$opciones .= '
		<table><tr><td valign=top><h4>Bloqueo de Llamadas an&oacute;nimas:&nbsp; </h4></td><td valign=top><select name="block">' . $blocked .
	'</td></tr></table><br/>';

$opciones .= '
		<table><tr><td valign=top><h4>Enviar E-Mail en llamadas perdidas:&nbsp; </h4></td><td valign=top><select name="missemail">' . $miss .
	'</td></tr></table><br/>';

$opciones .= '
		<table><tr><td valign=top><h4>Estado:&nbsp; </h4></td><td valign=top><select name="disabled">' . $disabled.
	'</td></tr></table><br/>';

$opciones .= '
		<input id="prefsFormSubmit" type="submit" value="Guardar"/></form></div></div>';

$objResponse -> assign("contenido","innerHTML", $opciones );
$objResponse -> script('$j(function () {
				$j("#txtpassword").complexify({}, function (valid, complexity) {
					document.getElementById("PassValue").value = complexity;
					$j("#complexity").text(Math.round(complexity) + \'%\');
				});
			});');
$objResponse -> script("document.getElementById('txtpassword').style.height=\"25px\";
document.getElementById('txtpassword').style.fontSize=\"14pt\";");
$objResponse -> script("document.getElementById('confirm').style.height=\"25px\";
document.getElementById('confirm').style.fontSize=\"14pt\";");
return $objResponse;
}


function contactsEditGroup( $id_group ) {
global $accountcode;
global $db;

$objResponse = new xajaxResponse();

if ( $id_group > 0 ) {
} else {
	$form =
'
<br/>
<form id="contactGroupForm" action="javascript:void(null);" onsubmit="contactsProcessGroup(0);" accept-charset="utf-8">
<tr><td><b>Agregar el nombre del Grupo</b></td></tr>
<div class="padder">
Nombre:<input type="text" name="name" />
<input type="hidden" name="id_group" value="-1" />
<input type="hidden" name="type" value="group" />
<input id="contactGroupFormSubmit" type="submit" value="Agregar"/>
</div>
</form>
';
}

$objResponse -> assign("contact_content","innerHTML", "$form");

return $objResponse;
}

function contactsEditContacts( $id_contact ) {
global $accountcode;
global $db, $planid, $supervisa;

$objResponse = new xajaxResponse();

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

if ( $planid == 13 ) {
	list ( $myaccountcode, $extension ) = split('-',$accountcode);
}

$id_group = $_SESSION['id_group'];

if ( trim($id_group) == "" ) {
	$db -> query ("select id from groups where group_name='Sin Grupo' and accountcode='$myaccountcode'");
	if ( $db -> next_record() ) {
		$id_group = $db -> f ("id");
	} else {
	}
}

if ( $id_contact > 0 ) {
	$db -> query ("select * from contacts where id_contact=$id_contact");
	if ( $db -> next_record() ) {
		$name = $db -> f ( "name" );
		$number = $db -> f ( "number" );
		$email = $db -> f ( "email" );
		$address = $db -> f ( "address" );
		$blocked = $db -> f ("blocked" );
		$id_group = $db -> f ("id_group" );

		$db -> query ( "select id, group_name from groups where accountcode='$myaccountcode' order by group_name" );
		$grupo = "<select name=\"id_group\">";
		while ( $db -> next_record() ) {
			if ( $db -> f ("id") == $id_group ) {
				$grupo .= "<option value=\"" . $db -> f ("id") . "\" selected>" . $db -> f ("group_name") . "</option>";
			} else {	
				$grupo .= "<option value=\"" . $db -> f ("id") . "\">" . $db -> f ("group_name") . "</option>";
			}
		}

		$bloqueo = "<select name=\"block\">";
		if ( $blocked ) {
			$bloqueo .= "<option value=\"1\" selected>S&iacute;</option>";
			$bloqueo .= "<option value=\"0\">No</option>";
		} else {
			$bloqueo .= "<option value=\"0\" selected>No</option>";
			$bloqueo .= "<option value=\"1\">S&iacute;</option>";
		}
	$form =
'
<br/>
<form id="contactGroupForm" action="javascript:void(null);" onsubmit="contactsProcessGroup(0);" accept-charset="utf-8">
<div class="padder">
<table>
<tr><td>Nombre:</td><td><input type="text" id="contact_form_name" name="name" value="' . $name . '" /></td>
<tr><td>N&uacute;mero:</td><td><input size=11 id="contact_form_number" type="text" name="number" value="' . $number . '" /></td>
<tr><td>E-mail:</td><td><input type="text" name="email" value="' . $email . '" /></td>
<tr><td>Direcci&oacute;n:</td><td><input size=30 type="text" name="address" value="' . $address . '" /></td>
<tr><td>Grupo:</td><td>' . $grupo . '</td></tr>
<tr><td><font color="red">Bloqueado:</font></td><td>' . $bloqueo . '</td></tr>
</table>
<input type="hidden" name="id_contact" value="' . $id_contact .'" />
<input type="hidden" name="type" value="contact" />
<input id="contactGroupFormSubmit" type="submit" value="Editar"/>
<a href="#" onclick="group_DeleteContact(' . $id_contact . ', \'' . $name . '\' )">Eliminar</a>
</div>
</form>
<br/>
<div class="padder" id="contactOptions" ></div>
';
	} else {
		$objResponse -> alert ("Error");
	}
} else {
	$form =
'
<br/>

<form id="contactGroupForm" action="javascript:void(null);" onsubmit="contactsProcessGroup(0);" accept-charset="utf-8">
<tr><td><b>Agregar los datos del Contacto</b></td></tr>
<div class="padder">
<table>
<tr><td>Nombre:</td><td><input type="text" name="name" /></td>
<tr><td>N&uacute;mero:</td><td><input size=11 type="text" name="number" /></td>
<tr><td>E-mail:</td><td><input type="text" name="email" /></td>
<tr><td>Direcci&oacute;n:</td><td><input size=30 type="text" name="address" /></td>
</table>
<input type="hidden" name="id_group" value="' . $id_group . '" />
<input type="hidden" name="id_contact" value="-1" />
<input type="hidden" name="type" value="contact" />
<input id="contactGroupFormSubmit" type="submit" value="Agregar"/>
</div>
</form>
';
}

$objResponse -> assign("contact_content","innerHTML", "$form");

return $objResponse;
}

function getCGroups($id_group) {
global $accountcode;
global $db, $planid, $supervisa;

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

if ( $planid == 13 ) {
	list ( $myaccountcode, $extension ) = split('-',$accountcode);
}

$_SESSION['id_group']="$id_group";
$db -> query ( "select id, group_name from groups where accountcode = '$myaccountcode' order by group_name" );
if ( $db -> next_record() ) {
	$content = "";
	do {
		if ( $db->f("id") == $id_group ) {
		$content .= "<li><a href='#group_container' class=\"selected\" onclick='group_GetContacts(" . $db->f("id") . ")'>" . $db->f("group_name") . "</a><a href=\"#\" onclick='group_DeleteGrupo(" . $db->f("id") . ", \"" . $db->f("group_name") . "\")' class=\"delete\"><i class=\"fa fa-trash fa-fw\"></i></a></li>";
		} else {
		$content .= "<li><a href='#group_container' onclick='group_GetContacts(" . $db->f("id") . ")'>" . $db->f("group_name") . "</a><a href=\"#\" onclick='group_DeleteGrupo(" . $db->f("id") . ",\"" . $db->f("group_name") . "\")' class=\"delete\"><i class=\"fa fa-trash fa-fw\"></i></a></li></li>";
		 }

	} while ( $db -> next_record() );

	if ( $id_group == -1 ) {
		$content .= "<li><a href='#group_container' class=\"selected\" onclick='group_GetContacts(-1)'>Todos</a>";
	} else {
		$content .= "<li><a href='#group_container' onclick='group_GetContacts(-1)'>Todos</a>";
	}
	return $content;
} else {
	$db -> query ( "insert into groups values (null, 'Sin Grupo', '$myaccountcode')" );
	$db -> query ( "select last_insert_id() as id_group" );
	$db -> next_record();
	$id_group = $db -> f ( "id_group" );
	return getCGroups($id_group);
}
}

function getCContacts($id_group) {
global $accountcode;
global $db, $planid, $supervisa;

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

if ( $planid == 13 ) {
	list ( $myaccountcode, $extension ) = split('-',$accountcode);
}

if ( $id_group == "-1" ) {
	$db -> query ( "select id_contact, name from contacts where id_group in ( select id from groups where accountcode = '$myaccountcode') order by name");
} else {
	$db -> query ( "select id_contact, name from contacts where id_group=$id_group order by name" );
}

if ( $db -> next_record() ) {
	$content = "";
	do {
		$name = $db -> f ("name");
		if ( strlen($name) > 23 ) {
			$name = substr($name, 0, 23) . "...";
		}
		$content .= "<li><a href='#' onclick='group_GetDetails(" . $db->f("id_contact") . ")'>" . $name . "</a><a href='#' onclick='group_DeleteContact(" . $db -> f ("id_contact") . ",\"" . $db -> f ("name") . "\")' class='delete'><i class=\"fa fa-trash fa-fw\"></i></a></li>";
	} while ( $db -> next_record() );
	return $content;
}

return "";
}

function contactsDeleteGroup( $id_group ) {
global $db, $planid, $supervisa;
global $accountcode;

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

if ( $planid == 13 ) {
	list ( $myaccountcode, $extension ) = split('-',$accountcode);
}

$objResponse = new xajaxResponse();
$db -> query ( "select group_name from groups where id=$id_group" );
$db -> next_record();

if ( $db -> f ("group_name") == "Sin Grupo" ) {
	$objResponse -> alert("No se puede eliminar el grupo por defecto.");
} else {
	$db -> query ( "select count(*) as qty from contacts where id_group = $id_group" );
	$db -> next_record();
	if ( $db -> f ("qty") > 0 ) {
		$objResponse -> alert("El Grupo contiene Contactos");
	} else {
		$db -> query ( "delete from groups where id = $id_group" );
		$id_group = 0;
		$db -> query ("select id from groups where group_name='Sin Grupo' and accountcode='$myaccountcode'");
		if ( $db -> next_record() ) {
			$id_group = $db -> f ("id");
		}
		$objResponse -> assign("contact_content", "innerHTML", "");
		$objResponse -> assign("contacts_content", "innerHTML", "<ul>" . getCContacts($id_group) . "</ul>");
		$objResponse -> assign("group_content", "innerHTML", "<ul>" . getCGroups($id_group) . "</ul>");

		$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
		$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
		$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
		$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
	}
}
return $objResponse;
}

function contactsGetContacts( $id_group ) {
$objResponse = new xajaxResponse();
$objResponse -> assign("contact_content", "innerHTML", "");
$objResponse -> assign("contacts_content", "innerHTML", "<ul>" . getCContacts($id_group) . "</ul>");
$objResponse -> assign("group_content", "innerHTML", "<ul>" . getCGroups($id_group) . "</ul>");

$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
return $objResponse;
}

function getContacts() {
global $db, $planid, $supervisa;
global $accountcode,$saldo,$faxno,$vence;

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}
if ( $planid == 13 ) {
	list ( $myaccountcode, $extension ) = split('-',$accountcode);
}

$_SESSION['id_group']="";
$grupos = "";
$usuarios = "";
$id_group = "";
$db -> query ("select id from groups where group_name='Sin Grupo' and accountcode='$myaccountcode'");
if ( $db -> next_record() ) {
	$id_group = $db -> f ( "id" );
	$grupos = getCGroups($id_group);
	$usuarios = getCContacts($id_group);
} else {	
	$grupos = getCGroups(0);
	$usuarios = getCContacts(0);
}
$info_pane = "";
$contenido = 
'
<p><form action="/contacts.php" method=post id="contactsaveform">
<input type=hidden name=accountcode value=' . $myaccountcode . ' />
<a class="btn" href="javascript:document.getElementById(\'contactsaveform\').submit();"><i class="fa fa-download fa-2x"></i>&nbsp;Descargar Contactos</a>
</form>
<br/>
<p>
<form id="contactsearchForm" action="javascript:void(null);" onsubmit="submitContactSearch();">
<table><tr>
<td>Busqueda Contacto:</td><td><input type="text" size=22 id="contact" name="contact" onkeyup="xajax_contactsearch(this.value)"/><input type=submit value=Buscar></td>
</tr>
<tr><td></td><td><div id="contactlivesearch"></div></td></tr>
</table>
</form>
<br/>
<!--<div id="contacts_options">-->
<div>
<a class="btn" href="#contacts_pane" onclick="xajax_contactsEditGroup(-1)"><i class="fa fa-group fa-2x"></i>&nbsp;Agregar Grupo </a>&nbsp;&nbsp;
<a class="btn" href="#contacts_pane" onclick="xajax_contactsEditContacts(-1)"><i class="fa fa-user-plus fa-2x"></i>&nbsp;Agregar Contacto</a>

</div>
<br/>
<div id="contacts_pane">
<div id="group_container">  
<div id="group_track"><div id="group_handle"></div></div>  
<div id="group_content">
<ul>
'
. $grupos .
'
</ul>
</div>  
</div>
<div id="contacts_container">  
<div id="contacts_track"><div id="contacts_handle"></div></div>  
<div id="contacts_content">
<ul>
'
. $usuarios .
'
</ul>
</div>  
</div>
<div id="contact_container">  
<div id="contact_track"><div id="contact_handle"></div></div>  
<div id="contact_content">
'
. $info_pane .
'
</div>
</div>
</div>
<br/>&nbsp;';
$objResponse = new xajaxResponse();
buildMenu($objResponse,2);
$objResponse -> assign("contenido","innerHTML",$contenido);
$objResponse -> script("new Control.ScrollBar('group_content','group_track');");
$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
$objResponse -> script("document.getElementById('contact').style.height=\"30px\";
document.getElementById('contact').style.fontSize=\"15pt\";");
return $objResponse;
}

function getCalls() {
global $accountcode,$saldo,$faxno,$planid,$vence;
$objResponse = new xajaxResponse();

$_SESSION['number'] = "";
$_SESSION['fechade'] = "";
$_SESSION['fechaa'] = "";
$_SESSION['estado'] = "";
$_SESSION['tag'] = "";
$contenido = llamadas(1,"","","",0,"");

buildMenu($objResponse,1);
$objResponse->assign("contenido","innerHTML",$contenido);
$objResponse->assign("submitButton","value","Busqueda");
$objResponse->assign("submitButton","disabled",false);
return $objResponse;
}

function getCallsNumber($number) {
global $accountcode,$saldo,$faxno,$vence;
$objResponse = new xajaxResponse();

$_SESSION['number'] = $number;
$_SESSION['fecha'] = "";
$_SESSION['fechaa'] = "";
$_SESSION['estado'] = "";
$_SESSION['tag'] = "";
$contenido = llamadas(1,$number,"", "", 0, "");
buildMenu($objResponse,1);
$objResponse -> assign("contenido","innerHTML",$contenido);
$objResponse->assign("submitButton","value","Busqueda");
$objResponse->assign("submitButton","disabled",false);
return $objResponse;

}

function getCallsTag($tag) {
global $accountcode,$saldo,$faxno,$vence;
$objResponse = new xajaxResponse();

$_SESSION['number'] = "";
$_SESSION['fecha'] = "";
$_SESSION['fechaa'] = "";
$_SESSION['estado'] = "";
$_SESSION['tag'] = $tag;
$contenido .= llamadas(1, "","", "", 0, $tag);
buildMenu($objResponse,1);
$objResponse -> assign("contenido","innerHTML",$contenido);
$objResponse->assign("submitButton","value","Busqueda");
$objResponse->assign("submitButton","disabled",false);
return $objResponse;

}


function callsPage($pagina) {
$objResponse = new xajaxResponse();

$_SESSION['page'] = $pagina;
$contenido = llamadas($pagina,$_SESSION['number'],$_SESSION['fechade'],$_SESSION['fechaa'],$_SESSION['estado'],$_SESSION['tag']);
$objResponse->assign("contenido","innerHTML",$contenido);
return $objResponse;
}

function callsReload() {
$objResponse = new xajaxResponse();

if ( is_numeric($_SESSION['page']) && $_SESSION['page'] > 0 ) {
	$contenido = llamadas($_SESSION['page'],$_SESSION['number'],$_SESSION['fechade'],$_SESSION['fechaa'],$_SESSION['estado'],$_SESSION['tag']);
} else {
	$contenido = llamadas(1,$_SESSION['number'],$_SESSION['fechade'],$_SESSION['fechaa'],$_SESSION['estado'],$_SESSION['tag']);
}
$objResponse->assign("contenido","innerHTML",$contenido);
return $objResponse;
}

function contactsProcessForm( $form, $param ) {
global $accountcode;
global $db, $planid, $supervisa;
$objResponse = new xajaxResponse();

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

if ( trim($form['type']) == "group" ) {
	$id_group = $_SESSION['id_group'];
	//Forma de Grupos
	if ( trim($form['id_group']) > 0 ) {
	} else {
		//Agregar Grupo
		$name = trim($form['name']);
		$db -> query ( "select count(*) as qty from groups where group_name = '$name' and accountcode = '$myaccountcode'");
		$db -> next_record();
		if ( $db -> f ( "qty" ) == 0 ) {
			$db -> query ( "insert into groups values ( null, '$name', '$myaccountcode')" );
		} else {
		}
		$objResponse -> assign("contact_content","innerHTML","<br/>&nbsp;&nbsp;&nbsp;&nbsp;Grupo Agregado");
		$objResponse -> assign("contacts_content","innerHTML","");
		$objResponse -> assign("group_content", "innerHTML", "<ul>" . getCGroups($id_group) . "</ul>");
		$objResponse -> script("new Control.ScrollBar('group_content','group_track');");
		$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
		$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
		$objResponse -> script("new Control.ScrollBar('group_content','group_track');");
		$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
		$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
		return $objResponse;
	}
}

if ( trim($form['type']) == "contact" ) {
	if ( trim($form['name']) == "" ) {
		$objResponse -> alert("Por favor ingrese el nombre.");
		return $objResponse;
	}
	if ( trim($form['number']) == "" ) {
		$objResponse -> script("alert('Por favor ingrese el n\u00famero.');");
		return $objResponse;
	}
	if ( trim($form['id_contact']) > 0 ) {
		//Editar Contacto
		$block    = escape($form['block']);
		$name     = escape(htmlspecialchars($form['name'], ENT_NOQUOTES, "UTF-8"));
		$number   = escape($form['number']);
		$address  = escape($form['address']);
		$email    = escape($form['email']);
		$id_group = escape($form['id_group']);
		$id_contact = escape($form['id_contact']);

		$db -> query ( "update contacts set blocked=$block,id_group=$id_group, name='$name', number='$number', address='$address', email='$email' where id_contact = $id_contact" );

		$objResponse -> assign("contact_content","innerHTML","<br/>&nbsp;&nbsp;&nbsp;&nbsp;Contacto Editado");
		$objResponse -> assign("contacts_content","innerHTML","<ul>" . getCContacts($id_group) . "</ul>");
		$objResponse -> assign("group_content", "innerHTML", "<ul>" . getCGroups($id_group) . "</ul>");
		$objResponse -> script("new Control.ScrollBar('group_content','group_track');");
		$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
		$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
		$objResponse -> script("new Control.ScrollBar('group_content','group_track');");
		$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
		$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
		return $objResponse;
	} else {
		//Agregar Contacto	
		$name     = escape(trim($form['name']));
		$number   = escape(trim($form['number']));
		$address  = escape(trim($form['address']));
		$email    = escape(trim($form['email']));
		$id_group = escape(trim($form['id_group']));

		if ( $param == 1 && $id_group == "-1" ) {
			$objResponse -> alert ("Porfavor seleccione grupo.");
			return $objResponse;
		}

		if ( $id_group == -1 ) {
			$db -> query ("select id from groups where group_name='Sin Grupo' and accountcode='$myaccountcode'");
			if ( $db -> next_record() ) {
				$id_group = $db -> f ("id");
			}
		}

		if ( $id_group == -1 ) {
			$objResponse -> alert ("Porfavor seleccione grupo.");
			return $objResponse;
		}

		$db -> query ( "insert into contacts values ( null, $id_group, '$name', '$email', '$address', '$number', 0 )" );

		if ( $param == 1 ) {
			$objResponse = getCalls();
			$objResponse -> alert ("Contacto Agregado.");
			return $objResponse;
		} else {
			$objResponse -> assign("contact_content","innerHTML","<br/>&nbsp;&nbsp;&nbsp;&nbsp;Contacto Agregado");
			$objResponse -> assign("contacts_content","innerHTML","<ul>" . getCContacts($id_group) . "</ul>");
			$objResponse -> assign("group_content", "innerHTML", "<ul>" . getCGroups($id_group) . "</ul>");
			$objResponse -> script("new Control.ScrollBar('group_content','group_track');");
			$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
			$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
			$objResponse -> script("new Control.ScrollBar('group_content','group_track');");
			$objResponse -> script("new Control.ScrollBar('contacts_content','contacts_track');");
			$objResponse -> script("new Control.ScrollBar('contact_content','contact_track');");
			return $objResponse;
		}
	}
}
}

function escape ($value) {
if (get_magic_quotes_gpc()) {
$value = stripslashes($value);
}
$newValue = @mysql_real_escape_string($value);
if(FALSE === $newValue) {
$newValue = @mysql_escape_string($value);
}
return $newValue;
} 

function processCalls($aFormValues) {
$objResponse = new xajaxResponse();

$contenido = "";

$objResponse->assign("contenido","innerHTML","");

$_SESSION['number'] = $aFormValues['number'];
$_SESSION['fechade'] = $aFormValues['fechade'];
$_SESSION['fechaa'] = $aFormValues['fechaa'];
$_SESSION['estado'] = $aFormValues['estado'];
$_SESSION['tag'] = $aFormValues['tag'];

$contenido = llamadas(1,$_SESSION['number'],$_SESSION['fechade'], $_SESSION['fechaa'], $_SESSION['estado'], $_SESSION['tag']);

$objResponse->assign("contenido","innerHTML",$contenido);
$objResponse->assign("submitButton","value","Busqueda");
$objResponse->assign("submitButton","disabled",false);

return $objResponse;
}

function llamadas($pagina, $numero, $fechade, $fechaa, $status, $tag, $tagedit = "") {
global $accountcode,$global_record,$faxno,$vence,$supervisa,$planid,$showtag;
global $db;

$myaccountcode = substr($accountcode,0,8);

if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

$contactos = array();
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
	if ( $status == 6 ) {
		$conditional = $conditional . " and (disposition != 'ANSWERED' or (dcontext = 'from-pstn' and userfield not like '%:g1/%'))";
	}
	if ( $status == 7 ) {
		$conditional = $conditional . " and dcontext = 'marcador'";
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

$db -> query ("select number, name from contacts where id_group in ( select id from groups where accountcode = '$myaccountcode')");
while ( $db -> next_record() ) {
	$number = $db ->f ( "number" );
	$contactos[ $number ] = $db -> f ("name");
}

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
	$paginas = ceil ( $qty / 20 );
	$pagina = ($pagina>0)?$pagina:1;

	$url = "Paginas :";
	if ( $pagina > 1 )
		$url = $url . " <a href=\"#\" onclick=\"callsGetPage(" . ( $pagina - 1 ) . ")\">&lt;&lt;</a>";

	if ( $pagina == 1 )
		$url .= " 1 ";
	else
		$url = $url . "<a href=\"#\" onclick=\"callsGetPage(1)\"> 1 </a>";

	if ( $pagina + 5 > $paginas )
		$index = $pagina - 5;
	else
		$index = $pagina - 3;

	if ( $index < 2 )
		$index = 2;
	else
		if ( $index > 2 )
			$url .= " ... ";

	while ( $index <= $pagina + 4 && $index <= $paginas) {
		if ( $index != $pagina )
			$url = $url . "<a href=\"#\" onclick=\"callsGetPage(" . $index . ")\"> $index </a>";
		else
			$url .= " $index ";
		$index++;
	}

	if ( $index < $paginas )
		$url .= " ... ";
	if ( $index <= $paginas)
		$url = $url . "<a href=\"#\" onclick=\"callsGetPage(" . $paginas. ")\"> $paginas</a>";

	if ( $pagina < $paginas )
		$url = $url . "<a href=\"#\" onclick=\"callsGetPage(" . ( $pagina + 1 ) . ")\">&gt;&gt;</a>";

	$indice = ( $pagina - 1 ) * 20;

	if ( $planid != 12 ) {
	}
	
	$contenido = "<div id=addcontact></div><div align=center>";

	if ( $planid != 12 ) {
		if ( $global_record ) {
			if ( $showtag ) {
				$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha/Hora</th><th>Origen</th><th>Destino</th><th>Tipo</th><th>Tiempo</th><th colspan=\"3\">Grabaci&oacute;n</th><th title='Las etiquetas van separadas por coma.'>Tag</th></tr>";
			} else {
				$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha/Hora</th><th>Origen</th><th>Destino</th><th>Tipo</th><th>Tiempo</th><th colspan=\"3\">Grabaci&oacute;n</th></tr>";
			}
		} else {
			if ( $showtag ) {
				$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha/Hora</th><th>Origen</th><th>Destino</th><th>Tipo</th><th>Tiempo</th><th colspan=\"2\"></th><th title='Las etiquetas van separadas por coma.'>Tag</th></tr>";
			} else {
				$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha/Hora</th><th>Origen</th><th>Destino</th><th>Tipo</th><th>Tiempo</th><th colspan=\"3\"></th></tr>";
			}
		}

		if ( $planid == 14 ) {
			$db -> query ("select ifnull(etiqueta,'') as etiqueta, c.uniqueid, c.accountcode as accountcode, lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode in ($supervisa) $conditional limit $indice,20" );
			$myaccountcode = $supervisa;
		} else {
			$db -> query ("select ifnull(etiqueta,'') as etiqueta, c.uniqueid, lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode = '$myaccountcode' $conditional limit $indice,20" );
		}
	} else {
		$contenido .= "<table cellspacing=2 class=\"llamadas\"><tr><th>Fecha/Hora</th><th>Origen</th><th>Destino</th><th>Duraci&oacute;n</th><th>Grabac&oacute;n</th></tr>";
		if ( $_SESSION['account'] > 0 ) {
			$account = $_SESSION['account'];
			$db -> query ("select ifnull(etiqueta,'') as etiqueta, c.uniqueid, lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode = '$account' $conditional limit $indice,20" );
		} else {
			$db -> query ("select ifnull(etiqueta,'') as etiqueta, c.uniqueid, c.accountcode, lastapp, calldate, src, dst, duration, billsec, disposition, userfield,dcontext from callrecords_table as c left join etiquetas_llamadas as e on c.uniqueid = e.uniqueid where c.accountcode in ($supervisa) $conditional limit $indice,20" );
		}
	}
	while ( $db -> next_record() ) {
		$td = "";
		$tdcall = "style=\"white-space:nowrap;\"";
		if ( ! ($db -> f ("disposition") == "ANSWERED") ) {
			$td = "style=\"background-color: #f0a0af\"";
			$tdcall = "style=\"white-space:nowrap;background-color: #f0a0af;\"";
		}

		$calldate = $db -> f ("calldate");

		$oldtag = $db -> f ("etiqueta");	

		$uniqueid = $db -> f ("uniqueid");

		$llamadas_tag[] = $uniqueid;

		$etiqueta  = "<select id=\"$uniqueid\" onchange=\"updateTag('$uniqueid',this)\">";
		$etiqueta .= "<option value=\"-1\">...</option>";
		foreach ( $etiquetas as $k => $v ) {
			if ( $oldtag == $k ) {
				$etiqueta .= "<option value=\"$k\" selected>$v</option>";
			} else {
				$etiqueta .= "<option value=\"$k\">$v</option>";
			}
		}
		$etiqueta .= "</select>";

		$lastapp = $db -> f ("lastapp");

		$userfield = $db -> f ("userfield");

		$dcontext = $db -> f ("dcontext");
		$uservars = explode(":",$userfield);
		$flash = "";
		$descarga = "";

		$minutos = ceil ( $db -> f ( "billsec" ) / 60 ) . " min.";

		$origendest = "-";
		$borrargrabacion = "";

		if ( ! ( $uservars[1] == "" ) ) {
			$file = "/var/spool/asterisk/monitor/" . $uservars[1] . ".mp3";

			$filevars = explode("/",$uservars[1]);
			$newpath = "/recordings/" . $filevars[0] . "/$filevars[3].mp3";

			$flashstyle="";

			if ( file_exists("$newpath") ) {
				$flash = "Borrada por Usuario";
				$flashstyle = "style=\"background-color: #F4A460\"";
			}
			if ( file_exists("$file") && ( $global_record )) {

$flash = '<object type="application/x-shockwave-flash" data="player_mp3_maxi.swf" width="120" height="12">
<param name="movie" value="player_mp3_maxi.swf" />
<param name="FlashVars" value="mp3=file.php?file=' . $uservars[1] . '&amp;bgcolor1=eeeeee&amp;bgcolor2=aaaaaa&amp;buttoncolor=443344&amp;buttonovercolor=0&amp;slidercolor1=aaaaaa&amp;slidercolor2=443344&amp;slid
erovercolor=666666&amp;textcolor=0&amp;" />
</object>';
				if ( $planid != 12 ) {
					$borrargrabacion = '<a class="override" href="#" onclick="deleteaudio(\'' . $uservars[1] . '\', \'' . $calldate . '\')"><i class="fa fa-trash fa-fw"></i></a>';
					$descarga = '<a class="override" href="file.php?file=' . $uservars[1] . '"><i class="fa fa-download fa-fw"></i></a>';
				}
	
				if ( $uservars[0] == "memo" ) {
					$flash .= " CF";
					$origendest = "Conferencia";
				}
			} else {
				$file = "/var/spool/asterisk/fax/" . $uservars[1] . ".tif";
				if ( file_exists("$file") ) {
					$flash = "<a href=\"fax.php?file=" . $uservars[1] . "\">Ver Fax</a>";
					$fax = 1;
				}
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
			$origentmp = str_replace("g1/","",$uservars[3]);
			$origendest = str_replace("G1/","",$origentmp);

			if (strlen($origendest) == 0 && ($db->f("billsec") < 60)) {
				$td = "style=\"background-color: #f0a0af\"";
				$tdcall = "style=\"white-space:nowrap;background-color: #f0a0af;\"";
			}
			if ( $uservars[3] == "" ) {
				$origendest = $uservars[2];
			}
		}

		if ($uservars[0] == "callthrough") {
			$origendest = $db -> f ("src");
		}
		if (strlen($origendest) == 0 ) {
			$origendest = "-";
			$td = "style=\"background-color: #f0a0af\"";
			$tdcall = "style=\"white-space:nowrap;background-color: #f0a0af;\"";
		}

		if (isset($contactos[$origendest])) {
			$name = $contactos[$origendest];
			if ( strlen ( $name ) > 50 ) {
				$name = substr($name,0,50) . ".";
			}
			$name = "<a href=\"#\" onclick=\"llamadas($origendest)\">$name</a>";
			$origendest = $name;
		} else {
			if ( $origendest == "-") {
				$name = "";
			} else {	
				$name = "<a href=\"#\" onclick=\"xajax_callsAddContact($origendest)\">&nbsp;<i class=\"fa fa-user-plus\"></a>";
			}

			if ( $planid != 12 ) {
				$origendest = $origendest . $name;
			}
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

			if ( isset( $contactos[$numero] ) ) {
				$name = $contactos[$numero];
				if ( strlen ( $name ) > 50 ) {
					$name = substr($name,0,50) . ".";
				}
				$name = "<a href=\"#\" onclick=\"llamadas($numero)\">$name</a>";
				if ( $showtag ) {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td valign=\"top\" $flashstyle>$flash</td><td>$borrargrabacion</td><td>$etiqueta</td></tr>";
				} else {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td $flashstyle>$flash</td><td>$borrargrabacion</td></tr>";
				}
			} else {
				if ( $numero )	
					$name = "<a href=\"#\" onclick=\"xajax_callsAddContact($numero)\">&nbsp;<i class=\"fa fa-user-plus\"></a>";
				if ( $showtag ) {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" . $numero . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td valign=top $flashstyle>$flash</td><td>$borrargrabacion</td><td>$etiqueta</td></tr>";
				} else {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" . $numero . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td $flashstyle>$flash</td><td>$borrargrabacion</td></tr>";
				}
			}

		} else 
		if ( $dcontext == "callback-android" || $dcontext == "callback-android-record" ||
			$dcontext == "callback-movil" || $dcontext == "callback-movil-record" ||
			$dcontext == "callback-web" || $dcontext == "callback-web-record" || $dcontext == "marcador" ) {
			
			$recordingvars = explode("-",$uservars[1]);
			$numero = $recordingvars[0];

			if ( $dcontext == "marcador" ) {
				$numero = $uservars[5];
				$origendest = "Marcador";
			}
		
			$pos = 0;
			$pos = strrpos($numero, "/" );
			if ( $pos > 0 ) {
				$numero = substr($numero, $pos + 1 );
			}

			if ( isset( $contactos[$numero] ) ) {
				$name = $contactos[$numero];
				if ( strlen ( $name ) > 50 ) {
					$name = substr($name,0,50) . ".";
				}
				$name = "<a href=\"#\" onclick=\"llamadas($numero)\">$name</a>";
				if ( $showtag ) {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td valign=\"top\" $flashstyle>$flash</td><td>$borrargrabacion</td><td>$etiqueta</td></tr>";
				} else {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td $flashstyle>$flash</td><td>$borrargrabacion</td></tr>";
				}
			} else {
				if ( $numero )	
					$name = "<a href=\"#\" onclick=\"xajax_callsAddContact($numero)\">&nbsp;<i class=\"fa fa-user-plus\"></a>";
				if ( $showtag ) {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" . $numero . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td valign=top $flashstyle>$flash</td><td>$borrargrabacion</td><td>$etiqueta</td></tr>";
				} else {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" . $numero . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td $flashstyle>$flash</td><td>$borrargrabacion</td></tr>";
				}
			}

		} else {
		if ($myaccountcode != $db -> f ( "dst") && $faxno != $db->f("dst")) {
			$numero = $db -> f ("dst");
			if ( isset( $contactos[$numero] ) ) {
				$name = $contactos[$numero];
				if ( strlen ( $name ) > 50 ) {
					$name = substr($name,0,50) . ".";
				}
				$name = "<a href=\"#\" onclick=\"llamadas($numero)\">$name</a>";
				if ( $showtag ) {
					$contenido .= "<tr><td $tdcall>". $calldate . "</td><td $td>$origendest</td><td $td>" . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td valign='middle' $flashstyle>$flash</td><td>$borrargrabacion</td><td>$etiqueta</td></tr>";
				} else {
					$contenido .= "<tr><td $tdcall>". $calldate . "</td><td $td>$origendest</td><td $td>" . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td $flashstyle>$flash</td><td>$borrargrabacion</td></tr>";
				}
			} else {
				if ( $numero )	
					$name = "<a href=\"#\" onclick=\"xajax_callsAddContact($numero)\">&nbsp;<i class=\"fa fa-user-plus\"></a>";
				if ( $showtag ) {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" .$numero . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td valign='middle' $flashstyle>$flash</td><td>$borrargrabacion</td><td>$etiqueta</td></tr>";
				} else {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>$origendest</td><td $td>" .$numero . $name . "</td><td $td>Sal." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td $flashstyle>$flash</td><td>$borrargrabacion</td></tr>";
				}
			}
		} else {
			$numero = $db -> f ("src");
			if ( !is_numeric($numero) ) {
			}
			if ( isset( $contactos[$numero] ) ) {
				$name = $contactos[$numero];
				if ( strlen ( $name ) > 50 ) {
					$name = substr($name,0,50) . ".";
				}
				$name = "<a href=\"#\" onclick=\"llamadas($numero)\">$name</a>";
				if ( $showtag ) {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>" . $name . "</td><td $td>$origendest</td><td $td>Ent." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td valign=\"top\" $flashstyle>$flash</td><td>$borrargrabacion</td><td>$etiqueta</td></tr>";
				} else {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>" . $name . "</td><td $td>$origendest</td><td $td>Ent." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td $flashstyle>$flash</td><td>$borrargrabacion</td></tr>";
				}
			} else {
				$name = "<a href=\"#\" onclick=\"xajax_callsAddContact($numero)\">&nbsp;<i class=\"fa fa-user-plus\"></a>";
				if ( $showtag ) {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>" . $numero .$name ."</td><td $td>$origendest</td><td $td>Ent." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td valign=top $flashstyle>$flash</td><td>$borrargrabacion</td><td>$etiqueta</td></tr>";
				} else {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>" . $numero .$name ."</td><td $td>$origendest</td><td $td>Ent." .
					"</td><td $td>" . $minutos . "</td><td>$descarga</td><td $flashstyle>$flash</td><td>$borrargrabacion</td></tr>";
				}
			}
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


				$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>" . $db -> f ("src") . "</td><td $td>$numero</td><td $td>" . $minutos . "<td $flashtyle>$flash</td><td>$etiqueta</td></tr>";
			} else {
				$pos = strpos($supervisa, $db -> f ("dst"));
				if ( $pos === false ) {
					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>" . $db -> f ("src") . "</td><td $td>" . $db -> f ("dst") . "</td><td $td>" . $minutos . "<td $flashstyle>$flash</td><td>$etiqueta</td></tr>" ;
				} else {
					$name = "";
					$numero = $db -> f ("src");
					if ( isset( $contactos[$numero] ) ) {
						$name = $contactos[$numero];
						if ( strlen ( $name ) > 50 ) {
							$name = substr($name,0,50) . ".";
						}
						$name = "<a href=\"#\" onclick=\"llamadas($numero)\">$name</a>";
					} else {
						$name = $numero;
					}

					$contenido .= "<tr><td $tdcall>" . $calldate . "</td><td $td>" . $name . "</td><td $td>" . $origendest . "</td><td $td>" . $minutos . "<td valign=top $flashstyle>$flash</td><td>$etiqueta</td></tr>" ;
				}
			}
		}
	}
	$contenido .= "</table></div><div align=right>" . $url . "</div>";
	$contenido .= '<p><div align=center><form action="/cdr.php" method=post>
<input type=hidden name=accountcode value="' . $myaccountcode . '" />
<input type=hidden name=number value="' . $_SESSION['number'] . '" />
<input type=hidden name=fechade value="' . $_SESSION['fechade'] . '" />
<input type=hidden name=fechaa value="' . $_SESSION['fechaa'] . '" />
<input type=hidden name=estado value="' . $_SESSION['estado'] . '" />
<input type=hidden name=tag value="' . $_SESSION['tag'] . '" />
<input type=submit value="Descargar Historial de Llamadas" />';

	return $contenido;
} else {
	$contenido = "";
	return $contenido . "<br/>No Encontramos Llamadas!";
}
}

function tagEdit( $uniqueid ) {
	global $accountcode;
	global $db,$planid;

	$myaccountcode = $accountcode;
	if ( $planid == 14 ) {
		$myaccountcode = $supervisa;
	}

	if ( $planid == 13 ) {
		list ( $myaccountcode, $extension ) = split('-',$accountcode);
	}

	$db -> query ( "select * from etiquetas_llamadas where uniqueid = '$uniqueid' and accountcode = '$myaccountcode'");
	if ( $db -> next_record() ) {
		$tag = $db -> f ("etiqueta");
	} else {
		$tag = "";
	}

	$contenido = $tag;

	$contenido = "<form id='form$uniqueid' name='form$uniqueid' action='javascript:void(null);' onsubmit='xajax_tagSave(\"$uniqueid\",xajax.getFormValues(\"form$uniqueid\"))'>";
	$contenido .= "<table border=0 cellspacing=0 class=\"sample\"><tr><td><input type=text name=tag id=tag size=15 value='$tag'></td><td><input type=image src=images/save.gif value=\"Guardar\" alt=\"Guardar\"></td></tr></table>";

	$objResponse = new xajaxResponse();
        $objResponse->assign("$uniqueid","innerHTML","$contenido");
	return $objResponse;
}

function tagSave( $uniqueid, $tag) {
	global $accountcode;
	global $db;

	$myaccountcode = $accountcode;
        if ( $planid == 14 ) {
                $myaccountcode = $supervisa;
        }

	if ( $planid == 13 ) {
		list ( $myaccountcode, $extension ) = split('-',$accountcode);
	}

        $db -> query ( "select * from etiquetas_llamadas where uniqueid = '$uniqueid' and accountcode = '$myaccountcode'");
	if ( $db -> next_record() ) {
		$db -> query ( "update etiquetas_llamadas set etiqueta=$tag where uniqueid = '$uniqueid' and accountcode = '$myaccountcode'");
	} else {
		$db -> query ( "insert into etiquetas_llamadas values ( null, '$myaccountcode', '$uniqueid', $tag)");
	}
}


function accountProcessForm( $form ) {
global $accountcode;
global $db,$planid;

$email = trim($form['email']);
$password = $form['password'];
$password2 = $form['password2'];
$vmpass = trim($form['vmpass']);
$vmpass2 = trim($form['vmpass2']);
$name = trim($form['name']);

if (strlen($name) == 0 ) {
	$objResponse = getAccount();
	$objResponse -> alert ("Porfavor Ingrese el Nombre.");
	return $objResponse;
}

if (!( $vmpass == $vmpass2 ) || !is_numeric($vmpass)) {
	$objResponse = getAccount();
	$objResponse -> alert ("Las Claves del Buzon no coinciden o no son numericas.");
	return $objResponse;
}

if (!(strlen($vmpass) == 4)) {
	$objResponse = getAccount();
	$objResponse -> alert ("La Clave del Buzon debe de ser de 4 digitos.");
	return $objResponse;
}

if ( strlen($password) < 10 ) {
	$objResponse = getAccount();
	$objResponse -> script("alert('La contrase\u00f1a web debe de tener 10 caracteres como minimo.');");
	return $objResponse;
}

list($userName, $mailDomain) = split("@", $email);
if ( eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) ) {
	if ( checkdnsrr($mailDomain, "MX") ) {
		if ( $password == $password2 ) {
			$db -> query ("select count(*) as qty from users where accountcode='$accountcode' and passwd='$password'");
	                $db -> next_record();
        	        $qty = $db -> f ("qty");

			if ( $qty == 0 ) {
				if (preg_match("(^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$)",$password)) {
					$db -> query ("update users set name='$name',email='$email',passwd=md5('$password') where accountcode = '$accountcode'");
				} else {
					$objResponse = getAccount();
					$objResponse -> alert ("La clave necesita 1 mayuscula y 1 numero como minimo para su seguridad.");
					return $objResponse;
				}
			} else {
				$db -> query ("update users set name='$name',email='$email' where accountcode = '$accountcode'");
			}
			if ( $planid != 14 && $planid != 13 && $planid != 12) {
				$db -> query ("update voicemail_users set fullname='$name',password='$vmpass',email='$email' where customer_id = '$accountcode'");
			}
		} else {
			$objResponse = getAccount();
			$objResponse -> alert ("Las Claves de la Web no coinciden.");
			return $objResponse;
		}
	} else {
		$objResponse = getAccount();
		$objResponse -> alert ("El E-Mail especificado es invalido 1.");
		return $objResponse;
	}
} else {
	$objResponse = getAccount();
	$objResponse -> alert ("El E-Mail especificado es invalido 2.");
	return $objResponse;
}

$objResponse = getAccount();
$objResponse -> alert ("Cambios Guardados.");

return $objResponse;
}

function prefsProcessForm( $form ) {
global $accountcode;
global $db;

$email = trim($form['email']);
$password = $form['password'];
$password2 = $form['password2'];
$mode = trim($form['mode']);
$dialmode = trim($form['dialmode']);
$screen = trim($form['screen']);
$record = trim($form['record']);
$block  = trim($form['block']);
$playrecord = trim($form['playrecord']);
$conn = 0; #Disable Conection Message
$missemail = trim($form['missemail']);
$disabled = trim($form['disabled']);

$objResponse = getPrefs();

if ( strlen($password) < 10 ) {
        $objResponse -> alert ("La contraseña web debe de tener 10 caracteres como minimo.");
	return $objResponse;
}

list($userName, $mailDomain) = split("@", $email);
if ( eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) ) {
	if ( checkdnsrr($mailDomain, "MX") ) {
		if ( $password == $password2 ) {
			$db -> query ("select count(*) as qty from users where accountcode='$accountcode' and passwd='$password'");
	                $db -> next_record();
        	        $qty = $db -> f ("qty");

			if ( $qty == 0 ) {
				if (preg_match("(^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$)",$password)) {
					$db -> query ("update users set email='$email',passwd=md5('$password') where accountcode = '$accountcode'");
                                } else {
					$objResponse -> alert ("La clave necesita 1 mayuscula y 1 numero como minimo para su seguridad.");
					return $objResponse;
                                }
				$db -> query ("update users set email='$email',passwd=md5('$password') where accountcode = '$accountcode'");
			} else {
				$db -> query ("update users set email='$email' where accountcode = '$accountcode'");
			}
			if ( $planid != 14 && $planid != 13 && $planid != 12) {
				$db -> query ("update voicemail_users set email='$email' where customer_id = '$accountcode'");
			}
		} else {
			$objResponse -> alert ("Las Claves de la Web no coinciden.");
			return $objResponse;
		}
	} else {
		$objResponse -> alert ("El E-Mail especificado es invalido 1.");
		return $objResponse;
	}
} else {
	$objResponse -> alert ("El E-Mail especificado es invalido 2.");
	return $objResponse;
}


$db -> query ("select count(*) as qty from incomming_prefs where accountcode = '$accountcode'");
$db -> next_record();
if ( $db -> f ("qty") == 1 ) {
	$db -> query ("update incomming_prefs set record=$record, mode=$mode, dialmode=$dialmode, screen=$screen, blockanon=$block, playrecording=$playrecord, connplay=$conn, missemail=$missemail , disabled=$disabled where accountcode = '$accountcode'");
} else {
	$db -> query ("delete from incomming_prefs where accountcode = '$accountcode'");
	$db -> query ("insert into incomming_prefs values ( null, '$accountcode', $mode, $dialmode, $screen, $record, $block, $playrecord, $conn, $missemail, $disabled)");
}

$objResponse = getPrefs();
$objResponse -> alert ("Cambios Guardados.");
return $objResponse;
}

function favsProcessAdd ( $form ) {
global $accountcode;
global $db;

$id_group = trim($form["grupo"]);
$id_contact = trim($form["contact"]);

if ( $id_group > 0 && $id_contact > 0 ) {
	$db -> query ("select count(*) as qty from groups where accountcode = '$accountcode' and id = $id_group");
	$db -> next_record();
	if ( $db -> f ( "qty") > 0 ) {
		return favsAdd( $id_contact );
	} else return getFavorites(-1);
} else {
	$objResponse = new xajaxResponse();
	$objResponse -> alert ("Por favor escoja el contacto.");
	return $objResponse;
}
}

function favsAdd ( $id_contact ) {
global $accountcode;
global $db;

$db -> query ( "select count(*) as qty, max(keypad) as keypad from favorites where accountcode = '$accountcode'");
$db -> next_record();

$qty = $db -> f ("qty");
$keypad = 0 + $db -> f ("keypad");

if ( $qty >= 10 ) {
	$objResponse = new xajaxResponse();
	$objResponse -> alert ("Ya hay 10 favoritos.");
	return $objResponse;
} else {
	$db -> query ( "select count(*) as qty from favorites where accountcode ='$accountcode' and id_contact = $id_contact");
	$db -> next_record();
	if ( $db -> f ("qty") > 0 ) {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("El contacto ya esta en favoritos.");
		return $objResponse;
	} else {
		if ( $qty > 0 ) { $keypad++; }
		$db -> query ( "insert into favorites values ( null, '$accountcode', $keypad, $id_contact)");
	}
	return getFavorites(-1);
}
}

function favsDelete ( $id_contact ) {
global $accountcode;
global $db;

$db -> query ( "select keypad from favorites where accountcode = '$accountcode' and id_contact=$id_contact");
if ($db -> next_record()) {
	$keypad = $db -> f ("keypad");
	$db -> query ( "update favorites set keypad=keypad-1 where accountcode='$accountcode' and keypad>$keypad");
	$db -> query ( "delete from favorites where accountcode='$accountcode' and id_contact=$id_contact");
}
return getFavorites(-1);
}

function favsMove ( $id_contact, $step ) {
global $accountcode;
global $db;

$db -> query ( "select keypad from favorites where accountcode = '$accountcode' and id_contact=$id_contact");
if ($db -> next_record()) {
	$keypad = $db -> f ("keypad");

	$db -> query ("select max(keypad) as max from favorites where accountcode= '$accountcode'");
	$db -> next_record();
	$max = $db -> f ("max");

	if ( $step > 0 ) {
		if ( $keypad < $max ) {
			$db -> query ( "update favorites set keypad=keypad-1 where keypad=$keypad+1" );
			$db -> query ( "update favorites set keypad=keypad+1 where keypad=$keypad and id_contact=$id_contact" );
		}
	} else {
		if ( $keypad >= 1 ) {
		$db -> query ( "update favorites set keypad=keypad+1 where keypad=$keypad-1" );
		$db -> query ( "update favorites set keypad=keypad-1 where keypad=$keypad and id_contact=$id_contact" );
		}
	}
}

return getFavorites(-1);
}

function custAddMinutes( $id_user ) {
global $db;

$form ='<div class="padder"><form id="customerForm" name="customerForm" action="javascript:void(null);" onsubmit="editCustomer()">
	<input type="hidden" name="type" value="2"/>
	<input type="hidden" name="id" value="' . $id_user . '"/>
	<table>';

$db -> query ("select name, id_plan, accountcode from users where uid = $id_user");
if ( $db -> next_record() ) {
	$id_plan = $db -> f ("id_plan");
	$account = $db -> f ("accountcode");
	$customer = $db -> f ("name");

	$db -> query ("select name, price, minutes from plans where id = $id_plan");
	$db -> next_record();
	$name = $db -> f ("name");
	$price = number_format($db -> f ("price"),2);
	$minutes = $db -> f ("minutes");

	$form.='<tr><td>Cliente:</td><td>' . $customer .'</tr></tr>
		<tr><td>N&uacute;mero:</td><td>' . $account . '</td></tr>
		<tr><td>Plan:</td><td>' . $name . '</td></tr>
		<tr><td>Precio:</td><td>' . $price . '</td></tr>
		<tr><td>Minutos:</td><td>' . $minutes . '</td></tr>
		<tr><td>Recarga:</td><td><select name="charges">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			</select></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td></td><td><input type="submit" value="Agregar Saldo"></td></tr></table></form></div></br></br>&nbsp;';

	$objResponse = new xajaxResponse();
	$objResponse->assign("contenido","innerHTML",$form);
	return $objResponse;
}
}

function editCustomer( $id_user ) {
global $db, $db2;

if ( $id_user > 0 ) {
	$db -> query ( "select * from users where uid='$id_user'" );
	if ( $db -> next_record() ) {
		$opciones = '
<form id="customerForm" name="customerForm" action="javascript:void(null);" onsubmit="editCustomer()">
<table>
<tr><td>N&uacute;mero:</td><td>' . $db -> f ("accountcode") . '</td></tr>
<tr><td>Nombre:</td><td><input size=35 type="text" name="name" value="' . $db -> f ("name") . '"/></td></tr>
<tr><td>E-Mail:</td><td><input size=35 type="text" name="email" value="' . $db -> f ("email") .'"/></td></tr>
<tr><td>Supervisa:</td><td><input size=25 type="text" name="supervisa" value="' . $db -> f("supervisa") . '"/></td></tr>
<tr><td>Usuario Web:</td><td>' . $db -> f ("username") . '</td></tr>
<tr><td>Clave Web:</td><td><input size=15 type="password" name="password" value="' . $db -> f("passwd") . '"/></td></tr>
<tr><td>Confirmar:</td><td><input size=15 type="password" name="password2" value="' . $db -> f("passwd") . '"/></td></tr>
<input type="hidden" name="number" value="' . $db -> f ("accountcode"). '"/>
<input type="hidden" name="user" value="' . $db -> f ("username"). '"/>';
		$id_plan = $db -> f ("id_plan");
		$accountcode = $db -> f ("accountcode");
		$id_vendedor = $db -> f ("id_vendedor");

		$db -> query ( "select id, name from plans");
		while ( $db -> next_record() ) {
			$selected = "";
			if ( $id_plan == $db -> f ("id") ) {
				$selected = "SELECTED";
			}
			$plan .= "<option value=\"" . $db -> f ("id") . "\" $selected>" . $db -> f ("name") . "</option>";
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

		$opciones .= '
<input type="hidden" name="id" value="' . $id_user . '"/>
<input type="hidden" name="type" value="1"/>
<tr><td>Clave Buzon:</td><td><input size=5 type="text" name="vmpass" value="' . $db -> f("password") . '"/></td></tr>
<tr><td>Confirmar:</td><td><input size=5 type="text" name="vmpass2" value="' . $db -> f("password") . '"/></td></tr>
<tr><td>Plan:</td><td><select name="plan">' . $plan .'</select>
<tr><td>Vendedor:</td><td><select name="vendedor">' . $vendedores .'</select>
<tr><td></td><td>&nbsp;</td></tr>
<tr><td></td><td><input type="submit" value="Guardar Cambios"></td></tr></table></form></br></br>&nbsp;
			';
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


	$opciones = '
<form id="customerForm" name="customerForm" action="javascript:void(null);" onsubmit="editCustomer()">
<table>
<tr><td>N&uacute;mero:</td><td><input size=10 type="text" name="number" value=""/>(Numero de Celular en Plan Saliente)</td></tr>
<tr><td>Nombre:</td><td><input size=35 type="text" name="name" value=""/></td></tr>
<tr><td>E-Mail:</td><td><input size=35 type="text" name="email" value=""/></td></tr>
<tr><td>Supervisa:</td><td><input size=25 type="text" name="supervisa" value=""/></td></tr>
<tr><td>Usuario Web:</td><td><input size=15 type="text" name="user" value=""/></td></tr>
<tr><td>Clave Web:</td><td><input size=15 type="password" name="password" value=""/></td></tr>
<tr><td>Confirmar:</td><td><input size=15 type="password" name="password2" value=""/></td></tr>
<tr><td>Clave Buzon:</td><td><input size=5 type="text" name="vmpass" value=""/></td></tr>
<tr><td>Confirmar:</td><td><input size=5 type="text" name="vmpass2" value=""/></td></tr>
<tr><td>Plan:</td><td><select name="plan">' . $plan .'</select>
<tr><td>Vendedor:</td><td><select name="vendedor">' . $vendedores .'</select>
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

function searchSet ( $contacto, $form ) {
global $accountcode;
global $db;

if ( $form == 0 ) {
	$iddiv = "livesearch";
} else if ( $form == 1 ) {
	$iddiv = "llamarlive";
}

$objResponse = new xajaxResponse();
$db -> query ( "select name, number from contacts where id_contact = $contacto");
if ( $db -> next_record() ) {
	$objResponse->assign("$iddiv","innerHTML","");
	$objResponse->assign("$iddiv","style.border","0px");

	if ( $form == 0 ) {
		$objResponse->assign("number","value",$db -> f ("name"));
	} else if ( $form == 1 ) {
		$objResponse->assign("numero","value",$db -> f ("number"));
	}
	return $objResponse;
}
}

function searchCSet ( $contacto ) {
global $accountcode;
global $db;

$objResponse = new xajaxResponse();
$db -> query ( "select name from contacts where id_contact = $contacto");
if ( $db -> next_record() ) {
	$objResponse->assign("contactlivesearch","innerHTML","");
	$objResponse->assign("contactlivesearch","style.border","0px");
	$objResponse->assign("contact","value",$db -> f ("name"));
	$objResponse->script("xajax_contactsEditContacts($contacto)");
	return $objResponse;
}
}

function search( $contacto, $form ) {
global $accountcode;
global $db, $planid, $supervisa;

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

if ( $planid == 13 ) {
	list ( $myaccountcode, $extension ) = split('-',$accountcode);
}

$objResponse = new xajaxResponse();
$contactos = "";

$iddiv = "";

if ( $form == 0 ) {
	$iddiv = "livesearch";
} else if ( $form == 1 ) {
	$iddiv = "llamarlive";
}

if ( $contacto == "" || strlen($contacto) < 3) {
	$objResponse -> assign("$iddiv","style.border", "0px");
} else {
	if ( is_numeric($contacto) ) {
		$db -> query ("select id_contact, number, name from contacts where id_group in ( select id from groups where accountcode = '$myaccountcode') and number like '%$contacto%'");
	} else {
		$db -> query ("select id_contact, number, name from contacts where id_group in ( select id from groups where accountcode = '$myaccountcode') and name like '%$contacto%'");
	}

	while ( $db -> next_record() ) {
		if ( strlen($db->f("name")) > 28 ) {
			$name = substr($db->f("name"),0,28) . "...";
		} else {
			$name = $db -> f ("name");
		}
		
		$id = $db -> f ("id_contact");

		$url = "<a href=\"#\" onclick=\"xajax_searchSet($id, $form)\">$name</a>";

		if ( $contactos == "" ) {
			$contactos .= $url;
		} else {
			$contactos .= "<br/>" . $url;
		}
	}
	$objResponse -> assign("$iddiv","style.border", "1px solid #A5ACB2");
	$objResponse -> assign("$iddiv","style.background", "#FFF");

	if ( $contactos == "" ) {
		$contactos = "no hay contactos...";
	}
}
$objResponse -> assign("$iddiv","innerHTML", $contactos);
return $objResponse;
}

function contactsearch( $contacto ) {
global $accountcode;
global $db, $planid, $supervisa;

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

if ( $planid == 13 ) {
	list ( $myaccountcode, $extension ) = split('-',$accountcode);
}

$objResponse = new xajaxResponse();
$contactos = "";
if ( $contacto == "" || strlen($contacto) < 3) {
	$objResponse -> assign("contactlivesearch","style.border", "0px");
} else {
	if ( is_numeric($contacto) ) {
		$db -> query ("select id_contact, number, name from contacts where id_group in ( select id from groups where accountcode = '$myaccountcode') and number like '%$contacto%'");
	} else {
		$db -> query ("select id_contact, number, name from contacts where id_group in ( select id from groups where accountcode = '$myaccountcode') and name like '%$contacto%'");
	}

	while ( $db -> next_record() ) {
		if ( strlen($db->f("name")) > 28 ) {
			$name = substr($db->f("name"),0,28) . "...";
		} else {
			$name = $db -> f ("name");
		}
		
		$id = $db -> f ("id_contact");

		$url = "<a href=\"#\" onclick=\"xajax_searchCSet($id)\">$name</a>";

		if ( $contactos == "" ) {
			$contactos .= $url;
		} else {
			$contactos .= "<br/>" . $url;
		}
	}
	$objResponse -> assign("contactlivesearch","style.border", "1px solid #A5ACB2");
	$objResponse -> assign("contactlivesearch","style.background", "#FFF");

	if ( $contactos == "" ) {
		$contactos = "no hay contactos...";
	}
}
$objResponse -> assign("contactlivesearch","innerHTML", $contactos);
return $objResponse;
}

function getCContactsSTR($search) {
global $accountcode;
global $db;

$db -> query ( "select id_contact, name from contacts where id_group in ( select id from groups where accountcode = '$accountcode') and name like '%$search%' order by name");

if ( $db -> next_record() ) {
	$content = "";
	do {
		$name = $db -> f ("name");
		if ( strlen($name) > 23 ) {
			$name = substr($name, 0, 23) . "...";
		}
		$content .= "<li><a href='#' onclick='group_GetDetails(" . $db->f("id_contact") . ")'>" . $name . "</a><a href='#' onclick='group_DeleteContact(" . $db -> f ("id_contact") . ",\"" . $db -> f ("name") . "\")' class='delete'><img border=0 src='/images/delete.jpg'/></a></li>";
	} while ( $db -> next_record() );
	return $content;
}

return "";
}

function newCall() {
	global $accountcode,$planid,$supervisa;
	global $db;

	$objResponse = new xajaxResponse();

	$myaccountcode = $accountcode;
	if ( $planid == 14 ) {
		$myaccountcode = $supervisa;
	}

	if ( $planid == 13 ) {
		list ( $myaccountcode, $extension ) = split('-',$accountcode);
	}

	$option = "<div class=\"styled-select\"><select id='numeronc' name='numeronc' class=\"selectopt\"><optgroup>";
	if ( $planid == '5' || $planid == '6' || $planid == '3' || $planid == '4' || $planid == '16' || $planid == '15' || $planid == '19' ) {
		$db -> query ("select distinct(number) as number from ivr_option where accountcode = '$accountcode' order by number");
		while ( $db -> next_record() ) {
			$option .= "<option class=\"optclass\" value=\"" . $db -> f ("number") . "\">" . $db -> f ("number") . "</option>";
		}
 	} elseif ( $planid == 13 ) {
		$extension = substr($accountcode,9,4);
		$accountcode = substr($accountcode,0,8);
		$db -> query ("select distinct(number) as number from ivr_option where accountcode = '$accountcode' and keypad = '$extension' order by number");
		while ( $db -> next_record() ) {
			$option .= "<option class=\"optclass\" value=\"" . $db -> f ("number") . "\">" . $db -> f ("number") . "</option>";
		}
	} else {
		$db -> query ("select * from nc_mynumber where accountcode = '$myaccountcode' order by preferred desc");
		while ( $db -> next_record() ) {
			$option .= "<option class=\"optclass\" value=\"" . $db -> f ("number") . "\">" . $db -> f ("number") . "</option>";
		}
		$db -> query ("select distinct(number) as number from ivr_option where accountcode = '$myaccountcode' order by number");
		while ( $db -> next_record() ) {
			$option .= "<option class=\"optclass\" value=\"" . $db -> f ("number") . "\">" . $db -> f ("number") . "</option>";
		}
	}
	$option .= "</optgroup></select></div>";

	$form  = "<div align='center'> <form id='newCallForm' name='newCallForm' action='javascript:void(null);' onsubmit='xajax_newCallProcess(xajax.getFormValues(\"newCallForm\"))'>";
	$form .= "<font size=5><table><tr><td>N&uacute;mero a llamar:</td><td><input type=text name='numero' id='numero' size=11 onkeyup=\"xajax_search(this.value,1)\">";
	$form .= "</td><td>&nbsp;&nbsp;Conectar a:</td><td>$option</td><td><input id=\"llamasubmit\" type=submit value='Llamar' style='font-size=14pt;'></td><tr/>";
	$form .= "<tr><td></td><td colspan=3><font size=4><div id=\"llamarlive\"></div></font></td></tr>";
	$form .= "</table></form></font></div>";

	$objResponse -> assign("newcall","innerHTML", "$form");
	$objResponse -> script("document.getElementById('llamasubmit').style.fontSize=\"16pt\";");
	$objResponse -> script("document.getElementById('numero').style.height=\"30px\";
document.getElementById('numero').style.fontSize=\"14pt\";");
	return $objResponse;
}

function newCallProcess( $form ) {
	global $accountcode;
	global $db, $planid, $supervisa;

	$objResponse = new xajaxResponse();

	$contact = trim($form["numero"]);
	$mync    = trim($form["numeronc"]);

	$myaccountcode = substr($accountcode,0,8);

	if ( $planid == 14 ) {
		$myaccountcode = $supervisa;
	}

	if ( ! is_numeric ( $contact ) ) {
		$objResponse -> alert ("El numero a llamar debe de ser numerico.");
		return $objResponse;
	}

	if ( strlen($contact) != 8 && strlen($contact) != 4 ) {
		$objResponse -> alert ("El numero a llamar debe de tener 4 o 8 digitos.");
		return $objResponse;
	}

	if ( $planid == '5' || $planid == '6' || $planid == '3' || $planid == '4' || $planid == '14' || $planid == '15' || $planid == '16' || $planid == '19') {
	} else {
		$db -> query ("update nc_mynumber set preferred = 0 where accountcode = '$accountcode'");
		$db -> query ("update nc_mynumber set preferred = 1 where accountcode = '$accountcode' and number='$mync'");
	}

	$db -> query ("select record from plans, users where id_plan = plans.id and accountcode = '$myaccountcode'");
	if ($db -> next_record()) {
		$global_record = $db -> f ("record");
	} else {
		$global_record = 0;
	}

	$db -> query ("select record from incomming_prefs where accountcode = '$myaccountcode'");
	if ($db -> next_record()) {
		$record = $db -> f ("record");
	} else {
		$record = 0;
	}

	$record_out = 0;
	if ( $global_record && ( $record == '1' || $record == '3') ) {
		$record_out = 1;
	}

	if ( $accountcode == '24584655' ) {
		$record_out = 1;
	}

	$extension = substr($accountcode,9,4);

	system ( "/usr/sbin/callback-web.sh $mync $myaccountcode $contact $record_out $extension");

	$objResponse -> assign("newcall", "innerHTML", "<a href=\"#\" onclick=\"newCall()\"><font size=5><table width=120px><tr><td><img src=/images/phone.jpg></td><td>Llamar</td></tr></table></font></a>" );
	
	$objResponse -> alert("En un momento le conectamos su llamada.");
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

function buildMenu( &$objResponse, $selected ) {
	global $faxno,$accountcode,$saldo,$allowed_numbers,$vence,$planid,$supervisa,$db,$saldo_qtz,$showtag;

	if ( $selected > 1 ) {
		$objResponse -> script("new Effect.Fade(\"busquedaNum\");");
	}

	$calls = $stats = $contacts = $nums = $tags = $prefs = $payments = $mercado = "";
	
	switch($selected) {
		case 1: $calls='class="selected"';
			$objResponse -> script("new Effect.Appear(\"busquedaNum\");");break;
		case 2: $contacts='class="selected"';break;
		case 3: $nums='class="selected"';break;
		case 4: $tags='class="selected"';break;
		case 5: $payments='class="selected"';break;
		case 6: $prefs='class="selected"';break;
		case 7: $stats='class="selected"';break;
		case 8: $mercado='class="selected"';break;
	}

	$menu = '<li><a href="#" onclick="getCalls()" ' . $calls . '><i class="fa fa-history fa-lg"></i>&nbsp;Llamadas</a></li>' .
		'<li><a href="#" onclick="getContacts()" ' . $contacts . '><i class="fa fa-user fa-lg"></i>&nbsp;Agenda</a></li>' . 
		'<li><a href="#" onclick="getNums()" ' . $nums . '><i class="fa fa-mobile fa-lg"></i>&nbsp;N&uacute;meros</a></li>'; 

	if ( $showtag ) {
		$menu .= '<li><a href="#" onclick="getTags()" ' . $tags. '><i class="fa fa-tags fa-lg"></i>&nbsp;Tags</a></li>';
	}
	$menu .=
		'<li><a href="#" onclick="getPayments()" ' . $payments . '><i class="fa fa-money fa-lg"></i>&nbsp;Pagos</a></li>' .
		'<li><a href="#" onclick="getStats()" ' . $stats . '><i class="fa fa-bar-chart fa-lg"></i>&nbsp;Stats</a></li>' .
		'<li><a href="#" onclick="getMercado()" ' . $mercado . '><i class="fa fa-shopping-cart fa-lg"></i>&nbsp;Mercado</a></li>' .
		'<li><a href="#" onclick="getPrefs()" ' . $prefs . '><i class="fa fa-cog fa-lg"></i>&nbsp;Config.</a></li>' ;

	if ( $planid == 12 ) {
		$menu = '<li><a href="#" onclick="getCalls()" ' . $calls . '>Llamadas</a></li>' ;
	}

	if ( $planid == 13 ) {
		$menu = '<li><a href="#" onclick="getCalls()" ' . $calls . '>Llamadas</a></li>' .
		'<li><a href="#" onclick="getContacts()" ' . $contacts . '>Agenda</a></li>' ;
	}

	if ( $planid == 14 ) {
		$menu = '<li><a href="#" onclick="getCalls()" ' . $calls . '>Llamadas</a></li>' .
			'<li><a href="#" onclick="getContacts()" ' . $contacts . '>Agenda</a></li>';
	}

	if ( $planid == 7 ) {
		$menu = '<li><a href="#" onclick="getCalls()" ' . $calls . '>Llamadas</a></li>';
	}

	if ( $planid == 5 || $planid == 6 || $planid == 3 || $planid == 4 || $planid == 15 || $planid == 17 || $planid == 18 || $planid == 19) {
		$menu = '<li><a href="#" onclick="getCalls()" ' . $calls . '><i class="fa fa-history fa-lg"></i>&nbsp;Llamadas</a></li>' .
			'<li><a href="#" onclick="getContacts()" ' . $contacts . '><i class="fa fa-user fa-lg"></i>&nbsp;Agenda</a></li>' . 
			'<li><a href="#" onclick="getNums()" ' . $nums . '><i class="fa fa-volume-up fa-lg"></i>&nbsp;Men&uacute; de Voz</a></li>';
		if ( $showtag ) {
			$menu .= '<li><a href="#" onclick="getTags()" ' . $tags. '><i class="fa fa-tags fa-lg"></i>&nbsp;Tags</a></li>';
		}
		$menu .='<li><a href="#" onclick="getPayments()" ' . $payments . '><i class="fa fa-money fa-lg"></i>&nbsp;Pagos</a></li>' .
			'<li><a href="#" onclick="getStats()" ' . $stats . '><i class="fa fa-bar-chart fa-lg"></i>&nbsp;Stats</a></li>' .
			'<li><a href="#" onclick="getMercado()" ' . $mercado . '><i class="fa fa-shopping-cart fa-lg"></i>&nbsp;Mercado</a></li>' .
			'<li><a href="#" onclick="getPrefs()" ' . $prefs . '><i class="fa fa-cog fa-lg"></i>&nbsp;Config.</a></li>';
	}
	
	if ( $planid == 16 ) {
		$menu = '<li><a href="#" onclick="getCalls()" ' . $calls . '>Llamadas</a></li>' .
			'<li><a href="#" onclick="getContacts()" ' . $contacts . '>Agenda</a></li>' . 
			'<li><a href="#" onclick="getNums()" ' . $nums . '>Extensiones</a></li>';
		if ( $showtag ) {
			$menu .= '<li><a href="#" onclick="getTags()" ' . $tags. '>Tags</a></li>';
		}
		$menu .='<li><a href="#" onclick="getPrefs()" ' . $prefs . '>Configuraci&oacute;n</a></li>';
			'<li><a href="#" onclick="getStats()" ' . $stats . '>Stats</a></li>';
	}
	
	$objResponse -> assign("menu","innerHTML",$menu);

	if ( $planid == 12 ) {
		$accounts = explode(",", $supervisa);
		$menu = '<br/><h4 align=right>Escoja Cuenta: <select name=selectacct onChange="xajax_SelectAcct(this.value)">';
		$menu .= "<option value=-1>Todos</option>";
		foreach( $accounts as $account ) {
			if ( $_SESSION['account'] == $account ) {
				$menu .= "<option value=$account selected>$account</option>";
			} else {
				$menu .= "<option value=$account>$account</option>";
			}
		}
		$menu .= "</h3>";
		$objResponse -> assign("title","innerHTML",$menu);
	} elseif ( $planid == 13 ) {
		$objResponse -> assign("title","innerHTML", "<h1 align=right>$accountcode</h1>");
	} elseif ( $planid == 14 ) {
		$objResponse -> assign("title","innerHTML", "<h1 align=right>$supervisa</h1>");
	} else {
		if ( strlen($faxno) > 0 ) {
			$objResponse -> assign("title","innerHTML", "<h1 align=right>$accountcode<font size=2>(Fax $faxno)</font>/<font size=3>S1aldo Q.$saldo_qtz | $saldo min.</font><br/><font size=2>$vence</font></h1>");
		} else {
			$objResponse -> assign("title","innerHTML", "<h1 align=right>$accountcode/<font size=3>Saldo Q $saldo_qtz | $saldo min.</font><br/><font size=2>$vence</font></h1>");
		}
	} 

	$myaccountcode = $accountcode;
	if ( $planid == 14 ) {
		$myaccountcode = $supervisa;
	}

	if ( $planid == 13 ) {
		list ( $myaccountcode, $extension ) = split('-',$accountcode);
	}

	$etiquetas = array();

	$db -> query ("select id, nombre from etiquetas where accountcode = '$myaccountcode'" );
	while ( $db -> next_record() ) {
		$id = $db -> f ("id");
		$etiquetas [ $id ] = $db -> f ("nombre");
	}

	$etiqueta  = "<select name=\"tag\" id=\"tag\">";
	$etiqueta .= "<option value=\"-1\">...</option>";
	foreach ( $etiquetas as $k => $v ) {
		$etiqueta .= "<option value=\"$k\">$v</option>";
	}
	$etiqueta .= "</select>";

	$objResponse -> assign("etiquetas","innerHTML", $etiqueta);
}

function ivrMove ( $id_contact, $step ) {
	global $accountcode;
	global $db;

	$db -> query ( "select keypad from ivr_option where accountcode = '$accountcode' and id=$id_contact");
	if ($db -> next_record()) {
		$keypad = $db -> f ("keypad");

		$db -> query ("select max(keypad) as max from ivr_option where accountcode= '$accountcode'");
		$db -> next_record();
		$max = $db -> f ("max");

		if ( $step > 0 ) {
			if ( $keypad < $max ) {
				$db -> query ( "update ivr_option set keypad=keypad-1 where keypad=$keypad+1 and accountcode='$accountcode'" );
				$db -> query ( "update ivr_option set keypad=keypad+1 where keypad=$keypad and id=$id_contact and accountcode='$accountcode'" );
			} else {
				if ($keypad < 9 ) {
					$db -> query ( "update ivr_option set keypad=keypad+1 where keypad=$keypad and id=$id_contact and accountcode='$accountcode'" );
				}
			}
		} else {
			if ( $keypad >= 1 ) {
			$db -> query ( "update ivr_option set keypad=keypad+1 where keypad=$keypad-1 and accountcode='$accountcode'" );
			$db -> query ( "update ivr_option set keypad=keypad-1 where keypad=$keypad and id=$id_contact and accountcode='$accountcode'" );
			}
		}
	}

	return getNums();
}

function ivrDelete ( $id ) {
	global $accountcode, $db, $planid;

	$db -> query ( "select * from ivr_option where accountcode='$accountcode' and id=$id");
	$db -> next_record();
	$extension = $db -> f ("keypad");

	if ( $extension > 10 ) {
		$db -> query ("delete from users where accountcode='$accountcode-$extension'");
	}

	$db -> query ( "delete from ivr_option where accountcode='$accountcode' and id=$id");
	
	$path = "/var/lib/asterisk/sounds/ivr/$accountcode-$extension.wav";
	if ( file_exists( $path ) ) {
		unlink ( $path );
		unlink ( "/var/lib/asterisk/sounds/ivr/$accountcode-$extension.mp3" );
	}

	return getNums();
}

function SelectAcct( $account ) {
	$_SESSION['account'] = $account;
	return getCalls();
}

function callsAddContact ( $number ) {
global $accountcode;
global $db, $planid, $supervisa;

$objResponse = new xajaxResponse();

$myaccountcode = $accountcode;
if ( $planid == 14 ) {
	$myaccountcode = $supervisa;
}

if ( $planid == 13 ) {
	list ( $myaccountcode, $extension ) = split('-',$accountcode);
}

$id_group = $_SESSION['id_group'];

if ( trim($id_group) == "" ) {
	$db -> query ("select id from groups where group_name='Sin Grupo' and accountcode='$myaccountcode'");
	if ( $db -> next_record() ) {
		$id_group = $db -> f ("id");
	} else {
		//alert error
	}
}

$opciones = '<select name="id_group">';

$db -> query ( "select count(*) as qty from groups");
$db -> next_record();
if ( $db -> f ("qty") > 0 ) {
	$db -> query ( "select * from groups where accountcode = '$myaccountcode' order by group_name");
        while ( $db -> next_record() ) {
        	$id = $db -> f ( "id" );
                $name = $db -> f ( "group_name");
		$opciones .= "<option value=\"$id\">$name</option>";
	}
}
$opciones .= "<option value=\"-1\" selected>Seleccione el Grupo</option></select>";

$form =
'
<br/>
<form id="contactGroupForm" action="javascript:void(null);" onsubmit="contactsProcessGroup(1);" accept-charset="utf-8">
<div class="padder">
<table>
<tr><td>Nombre:</td><td><input type="text" name="name" /></td>
<tr><td>N&uacute;mero:</td><td><input size=11 type="text" name="number" value="' . $number . '" /></td>
<tr><td>E-mail:</td><td><input type="text" name="email" /></td>
<tr><td>Direcci&oacute;n:</td><td><input size=30 type="text" name="address" /></td>
<tr><td>Grupo:</td><td>' . $opciones . '</td></tr>
</table>
<input type="hidden" name="id_contact" value="-1" />
<input type="hidden" name="type" value="contact" />
<input id="contactGroupFormSubmit" type="submit" value="Agregar"/>
</div>
</form>
';

$content = '<div id="B1" style="position:absolute;left:200px;top:400px;width:300px;height:170px;z-index:9;border-style:solid;border-width:4px 2px 2px 2px;border-color:solid #444444;background:#dfdfdf;"> <div style="float:right;cursor:pointer;" onclick="xajax_callsAddContactClose();">cerrar [x]</div>' . $form . '</div>';

$objResponse -> assign("addcontact","innerHTML", "$content");

return $objResponse;
}

function callsAddContactClose ( ) {
global $accountcode;
global $db, $planid, $supervisa;

$objResponse = new xajaxResponse();
$objResponse -> assign("addcontact","innerHTML", "");

return $objResponse;
}

function audioDelete ( $file ) {
global $accountcode;
global $db, $planid, $supervisa;

$filevars = explode("/",$file);

$oldpath = "/var/spool/asterisk/monitor/$file.mp3";
$newpath = "/recordings/" . $filevars[0] . "/";

system("sudo mv $oldpath $newpath");

$objResponse = callsReload();
$objResponse -> alert ("Grabacion Borrada Exitosamente.");
return $objResponse;
}

function getStats() {
global $accountcode,$saldo,$global_record,$faxno,$vence;
global $db;
$objResponse = new xajaxResponse();

buildMenu($objResponse, 7);
$content = "<br/><p><div id='prefswide_container' style='width:910px;'><div class='padder'><br/>";
$content .= '<form id="statsForm" action="javascript:void(null);" onsubmit="statsProcessForm();">';
$content .= 'Escoger rango de fechas, De: <input type="text" size="10" name="date-inicio" id="date-inicio" class="date-pick" /> A: <input type="text" size="10" name="date-fin" id="date-fin" class="date-pick" /> <input type=submit value="Ver"/></form>';
$content .= "<br/><div id=\"stats\"></div><br/>";
$content .= "</div></div>";

$objResponse -> assign("contenido","innerHTML", "$content");

$objResponse -> script (
'$j("#date-inicio").datepicker({ showOtherMonths: true, inline: true, dateFormat: "yy-mm-dd", buttonImage: "/images/cal.gif", showOn: "both", buttonImageOnly: true, constrainInput: true});' .
'$j("#date-fin").datepicker({ showOtherMonths: true, inline: true, dateFormat: "yy-mm-dd", buttonImage: "/images/cal.gif", showOn: "both", buttonImageOnly: true, constrainInput: true });' );

return $objResponse;
}

function statsProcessForm( $form ) {
	global $accountcode;
	global $db, $planid, $supervisa;
	$objResponse = new xajaxResponse();

	$myaccountcode = $accountcode;
	if ( $planid == 14 ) {
	        $myaccountcode = $supervisa;
	}

	$fechade = escape(trim($form['date-inicio']));
	$fechaa  = escape(trim($form['date-fin']));

	$content = "";

	$conn_in = "[";
	$conn_out = "[";
	$noconn_in = "[";
	$noconn_out = "[";
	$webcall = "[";
	$nowebcall = "[";

	$db -> query ( "select hour(calldate) as hour, count(*) as qty, disposition, substring(dcontext,1,4) as context from callrecords_table_stats where accountcode = '$myaccountcode' and calldate >= '$fechade 00:00:00' and calldate <= '$fechaa 23:59:59' group by hour(calldate), substring(dcontext,1,4), disposition" );
	while ( $db -> next_record() ) {
		if ( $db -> f ("disposition") == "ANSWERED" ) {
			if ( $db -> f ("context") == "from") {
				$conn_in .= "[" . $db -> f ("hour") . "," . $db -> f ("qty") . "],";
			} else if ( $db -> f ("context") == "webc" ) {
				$webcall .= "[" . $db -> f ("hour") . "," . $db -> f ("qty") . "],";
			} else if ( $db -> f ("context") == "call" ) {
				$conn_out .= "[" . $db -> f ("hour") . "," . $db -> f ("qty") . "],";
			}
		} else if ( $db -> f ("disposition") == "NO ANSWER" ) {
			if ( $db -> f ("context") == "from" ) {
				$noconn_in .= "[" . $db -> f ("hour") . "," . $db -> f ("qty") . "],";
			} else if ( $db -> f ("context") == "webc" ) {
                                $nowebcall .= "[" . $db -> f ("hour") . "," . $db -> f ("qty") . "],";
			} else if ( $db -> f ("context") == "call" ) {
				$noconn_out .= "[" . $db -> f ("hour") . "," . $db -> f ("qty") . "],";
			}
		}
	}

	$conn_in .= "[25,0]]";
	$noconn_in .= "[25,0]]";
	$conn_out .= "[25,0]]";
	$noconn_out .= "[25,0]]";
	$webcall .= "[25,0]]";
	$nowebcall .= "[25,0]]";

	$day_in_con  = "[";
	$day_in_no   = "[";
	$day_out_con = "[";
	$day_out_no  = "[";
	$day_web_con = "[";
	$day_web_no  = "[";
	$db -> query ( "select weekday(calldate)+1 as day, count(*) as qty, disposition, substring(dcontext,1,4) as context from callrecords_table_stats where accountcode = '$myaccountcode' and calldate >= '$fechade 00:00:00' and calldate <= '$fechaa 23:59:59' group by weekday(calldate), substring(dcontext,1,4), disposition" );
	while ( $db -> next_record() ) {
		if ( $db -> f ("disposition") == "ANSWERED" ) {
			if ( $db -> f ("context") == "from") {
				$day_in_con .= "[" . $db -> f ("day") . "," . $db -> f ("qty") . "],";
			} else if ( $db -> f ("context") == "webc" ) {
				$day_web_con .= "[" . $db -> f ("day") . "," . $db -> f ("qty") . "],";
			} else if ( $db -> f ("context") == "call" ) {
				$day_out_con .= "[" . $db -> f ("day") . "," . $db -> f ("qty") . "],";
			}
		} else if ( $db -> f ("disposition") == "NO ANSWER" ) {
			if ( $db -> f ("context") == "from" ) {
				$day_in_no .= "[" . $db -> f ("day") . "," . $db -> f ("qty") . "],";
			} else if ( $db -> f ("context") == "webc" ) {
                                $day_web_no .= "[" . $db -> f ("day") . "," . $db -> f ("qty") . "],";
			} else if ( $db -> f ("context") == "call" ) {
				$day_out_no .= "[" . $db -> f ("day") . "," . $db -> f ("qty") . "],";
			}
		}
	}

	$day_in_con  .= "[10,0]]";
	$day_in_no   .= "[10,0]]";
	$day_out_con .= "[10,0]]";
	$day_out_no  .= "[10,0]]";
	$day_web_con .= "[10,0]]";
	$day_web_no  .= "[10,0]]";

	$min_in_con  = "[";
	$min_in_no   = "[";
	$min_out_con = "[";
	$min_out_no  = "[";
	$min_web_con = "[";
	$min_web_no  = "[";

	$min_nineincon  = 0;
	$min_nineinno   = 0;
	$min_nineoutcon = 0;
	$min_nineoutno  = 0;
	$min_ninewebcon = 0;
	$min_ninewebno  = 0;


	$db -> query ( "select ceil(duration/60) as min, count(*) as qty, disposition, substring(dcontext,1,4) as context from callrecords_table_stats where accountcode = '$myaccountcode' and calldate >= '$fechade 00:00:00' and calldate <= '$fechaa 23:59:59' group by ceil(duration/60), substring(dcontext,1,4), disposition" );
	while ( $db -> next_record() ) {
		$min = $db -> f ("min");
		if ( $db -> f ("disposition") == "ANSWERED" ) {
			if ( $db -> f ("context") == "from") {
				if ( $min < 10 ) {
					$min_in_con .= "[" . $min . "," . $db -> f ("qty") . "],";
				} else {
					$min_nineincon += $db ->f ("qty");
				}
			} else if ( $db -> f ("context") == "webc" ) {
				if ( $min < 10 ) {
					$min_web_con .= "[" . $min . "," . $db -> f ("qty") . "],";
				} else {
					$min_ninewebcon += $db ->f ("qty");
				}
			} else if ( $db -> f ("context") == "call" ) {
				if ( $min < 10 ) {
					$min_out_con .= "[" . $min . "," . $db -> f ("qty") . "],";
				} else {
					$min_nineoutcon += $db ->f ("qty");
				}
			}
		} else if ( $db -> f ("disposition") == "NO ANSWER" ) {
			if ( $db -> f ("context") == "from" ) {
				if ( $min < 10 ) {
					$min_in_no .= "[" . $min . "," . $db -> f ("qty") . "],";
				} else {
					$min_nineinno += $db ->f ("qty");
				}
			} else if ( $db -> f ("context") == "webc" ) {
				if ( $min < 10 ) {
                                	$min_web_no .= "[" . $min . "," . $db -> f ("qty") . "],";
				} else {
					$min_ninewebno += $db ->f ("qty");
				}
			} else if ( $db -> f ("context") == "call" ) {
				if ( $min < 10 ) {
					$min_out_no .= "[" . $min . "," . $db -> f ("qty") . "],";
				} else {
					$min_nineoutno += $db ->f ("qty");
				}
			}
		}
	}

	$min_in_con .= "[10,$min_nineincon]]";
	$min_web_con .= "[10,$min_ninewebcon]]";
	$min_out_con .= "[10,$min_nineoutcon]]";
	$min_in_no .= "[10,$min_nineinnon]]";
	$min_web_no .= "[10,$min_ninewebno]]";
	$min_out_no .= "[10,$min_nineoutno]]";

	$content .= '<div style="width:900px;height:24px;"><div style="width:490px;float:left;text-align: center;">';
	$content .= '<h3>Por Hora</h3></div>';
	$content .= '<div style="width:180px;float:left;text-align: center;">';
	$content .= '<h3>Por Dia</h3></div>';
	$content .= '<div style="width:220px;float:left;text-align: center;">';
	$content .= '<h3>Por Duraci&oacute;n</h3></div></div>';

	$content .= '<h3>Llamadas Entrantes</h3><div style="width:900px;height:220px;"><div id="grafica-calls-in" style="width:490px;height:220px;float:left;"></div>';
	$content .= '<div id="grafica-in-days" style="width:180px;height:220px;float:left;"></div>';
	$content .= '<div id="grafica-in-min" style="width:220px;height:220px;float:left;"></div>';
	$content .= '</div>';
	$content .= '<h3>Llamadas Salientes</h3><div style="width:900px;height:220px;"><div id="grafica-calls-out" style="width:490px;height:220px;float:left;"></div>';
	$content .= '<div id="grafica-out-days" style="width:180px;height:220px;float:left;"></div>';
	$content .= '<div id="grafica-out-min" style="width:220px;height:220px;float:left;"></div>';
	$content .= '</div>';
	if ( strlen($webcall) > 9 || strlen($nowebcall) > 9 ) {
		$content .= '<h3>Llamadas Web (Entrantes)</h3> <div style="width:900px;height:220px;">
		<div id="grafica-calls-web" style="width:490px;height:220px;float:left;"></div>
		<div id="grafica-web-days" style="width:180px;height:220px;float:left;"></div>
		<div id="grafica-web-min" style="width:220px;height:220px;float:left;"></div>
		</div>';
	}
	$content .= '<br/><h3><div id="grafica-15min-legend"></div></h3>';

	$objResponse -> assign("stats", "innerHTML", "$content");

	$script = 'var d1 = ' . $conn_in . '; var d2 = ' . $noconn_in . '; var d3 = ' . $conn_out . '; var d4= ' . $noconn_out . '; var d5=' . $webcall . '; var d6 = ' . $nowebcall . '
        var d7 = ' . $day_in_con . '; var d8 = '. $day_in_no .'; var d9 = ' . $day_out_con . '; var d10 = ' . $day_out_no . ';
	var d11 = ' . $day_web_con . '; var d12 = ' . $day_web_no . ';
        var d13 = ' . $min_in_con . '; var d14 = '. $min_in_no .'; var d15 = ' . $min_out_con . '; var d16 = ' . $min_out_no . ';
	var d17 = ' . $min_web_con . '; var d18 = ' . $min_web_no . ';
        $j.plot($j("#grafica-calls-in"),
            [{ label:"Contestadas", data: d1, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d2, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[6,"6:00"],[8,"8:00"],[10,"10:00"],[12,"12:00"],[14,"2:00"],
                       [16,"4:00"],[18,"6:00"],[20,"8:00"],[22,"10:00"]], min: 5, max:23, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
        });
        $j.plot($j("#grafica-in-days"),
            [{ label:"Contestadas", data:d7, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d8, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[1,"L"],[2,"Ma"],[3,"Mi"],[4,"J"],[5,"V"],[6,"S"],
                       [7,"D"]], min:0.5 , max:7.5, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
        });
        $j.plot($j("#grafica-in-min"),
            [{ label:"Contestadas", data:d13, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d14, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,"10+"]], min:0.5 , max:10.5, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
        });
        $j.plot($j("#grafica-calls-out"),
            [{ label:"Contestadas", data: d3, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d4, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[4,"4:00"],[6,"6:00"],[8,"8:00"],[10,"10:00"],[12,"12:00"],[14,"2:00"],
                       [16,"4:00"],[18,"6:00"],[20,"8:00"],[22,"10:00"]], min: 5, max:23, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
        });
        $j.plot($j("#grafica-out-days"),
            [{ label:"Contestadas", data:d9, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d10, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[1,"L"],[2,"Ma"],[3,"Mi"],[4,"J"],[5,"V"],[6,"S"],
                       [7,"D"]], min:0.5 , max:7.5, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
        });
        $j.plot($j("#grafica-out-min"),
            [{ label:"Contestadas", data:d15, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d16, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,"10+"]], min:0.5 , max:10.5, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
        });
';

	if ( strlen($webcall) > 9 || strlen($nowebcall) > 9 ) {
	
        $script .= '$j.plot($j("#grafica-calls-web"),
            [{ label:"Contestadas", data: d5, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d6, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[4,"4:00"],[6,"6:00"],[8,"8:00"],[10,"10:00"],[12,"12:00"],[14,"2:00"],
                       [16,"4:00"],[18,"6:00"],[20,"8:00"],[22,"10:00"]], min: 5, max:23, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
        });
	$j.plot($j("#grafica-web-days"),
            [{ label:"Contestadas", data:d11, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d12, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[1,"L"],[2,"Ma"],[3,"Mi"],[4,"J"],[5,"V"],[6,"S"],
                       [7,"D"]], min:0.5 , max:7.5, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
        });
	$j.plot($j("#grafica-web-min"),
            [{ label:"Contestadas", data:d17, bars: { show:true, align:"center", barWidth:0.7 }, stack:true},
             { label:"Sin Contestar", data: d18, bars: {show:true, align:"center", barWidth:0.7}, stack:true}]
            ,{ legend: { show:true, noColumns:3, container: $j("#grafica-15min-legend") },
            grid: { hoverable: true }, colors : ["#5E86BF","#BF665E"],  
            xaxis: { ticks:[[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,"10+"]], min:0.5 , max:10.5, tickDecimals:0 },
            yaxis: { min: 0, tickDecimals:0 },
       });';

	}

	$script .= '
	$j("#grafica-calls-in").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

                                message = y + " " + item.series.label;
                                showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });
        $j("#grafica-calls-out").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

                                message = y + " " + item.series.label;
                                showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });
	$j("#grafica-in-days").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

                                message = y + " " + item.series.label;
                                showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });
        $j("#grafica-out-days").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

                                message = y + " " + item.series.label;
                                showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });        
        $j("#grafica-in-min").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

				if ( x >= 10 ) {
                                	message = y + " Llamadas " + item.series.label + " de " + x + " o mas Min.";
				} else {
                                	message = y + " Llamadas " + item.series.label + " de " + x + " Min.";
				}
                                showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });	
	$j("#grafica-out-min").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

				if ( x >= 10 ) {
                                	message = y + " Llamadas " + item.series.label + " de " + x + " o mas Min.";
				} else {
                                	message = y + " Llamadas " + item.series.label + " de " + x + " Min.";
				}
				showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });
		';

	if ( strlen($webcall) > 9 || strlen($nowebcall) > 9 ) {
		$script .=
	'$j("#grafica-calls-web").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

                                message = y + " " + item.series.label;
                                showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });
	$j("#grafica-web-days").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

                                message = y + " " + item.series.label;
                                showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });
	$j("#grafica-web-min").bind("plothover", function (event, pos, item) {
                if ( item ) {
                        if ( !previousPoint ||
                                previousPoint[0] != item.datapoint[0] ||
                                previousPoint[1] != item.datapoint[1] ) {
                                previousPoint = item.datapoint;

                                $j("#tooltip").remove();
                                var x = item.datapoint[0];
                                var y = 0;
                                if ( item.datapoint[2] ) {
                                    y = item.datapoint[1] - item.datapoint[2];
                                } else {
                                    y = item.datapoint[1];
                                }

                                var message = null;

				if ( x >= 10 ) {
                                	message = y + " Llamadas " + item.series.label + " de " + x + " o mas Min.";
				} else {
                                	message = y + " Llamadas " + item.series.label + " de " + x + " Min.";
				}
				showTooltip(item.pageX+5, item.pageY, message );
                        }
                } else {
                        $j("#tooltip").remove();
                        previousPoint = null;
                }
        });
	';
	}

	$objResponse -> script( $script );
		
	return $objResponse;

}

function mmGetList() {
	global $accountcode, $userid, $db, $saldo_qtz, $saldo_qtz_no;

	$contenido = "<p><div id='prefs_container'>";
	$objResponse = new xajaxResponse();
	buildMenu($objResponse, 8);

	$db -> query ("select id_oferta, mminutos.minutos, precio, accountcode, mminutos.uid from mminutos, users where estado = 0 and users.uid=mminutos.uid order by precio asc;");
	$contenido .= "<div class='padder'><br/><a class=\"btn\" href=\"#\" onclick=\"xajax_mmAddEdit(-1)\"><i class=\"fa fa-plus-square fa-lg\"></i>&nbsp;Agregar Oferta</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmOfertas()\"><i class=\"fa fa-list fa-lg\"></i>&nbsp;Mis Ofertas</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmCompras()\"><i class=\"fa fa-credit-card fa-lg\"></i>&nbsp;Mis Compras</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmSaldoTel()\">&nbsp;Recargas Celular&nbsp;</a></div>";
	$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Vendedor</th><th>Minutos</th><th>Precio x Minuto</th><th>Total</th></tr>";
	while ( $db -> next_record() ) {
		$minutos = $db -> f ("minutos");
		$uid = $db -> f ("uid");
		$precio = number_format($db -> f ("precio"), 2);
		$cuenta = $db -> f ("accountcode");
		if ($cuenta == "24584700") {
			$cuenta = "Numero Central";
		}
		$total = number_format( $minutos*$precio, 2 );
		$contenido .= "<tr><td>$cuenta</td>";
		$contenido .=     "<td>$minutos</td>";
		$contenido .=     "<td>Q.&nbsp;$precio</td>";
		$contenido .=     "<td>Q.&nbsp;$total</td>";
		if ( ( $saldo_qtz_no >= ($precio*$minutos) ) && ( $uid != $userid ) ) {
			$contenido .=     "<td><a class='override' href=\"#\" onclick=\"confirmarMMC(" . $db -> f("id_oferta") . ", $minutos, '$total' )\"><i class=\"fa fa-cart-plus fa-fw\"></i></a></td>";
		} else {
			$monto = $precio*$minutos - $saldo_qtz_no;
			$contenido .=     "<td><a class='override' href=\"https://www.numerocentral.com/tarjeta/compra.php?monto=$monto\" style=\"color:green;\"><i class=\"fa fa-dollar fa-fw\"></i></a></td>";
		}
	}

	$contenido .= "</div>";
	$objResponse -> assign("contenido","innerHTML", "$contenido" );
	return $objResponse;
}

function mmOfertas() {
	global $accountcode, $userid, $db;

	$contenido = "<p><div id='prefswide_container'>";
	$objResponse = new xajaxResponse();
	$contenido .= "<div class='padder'><br/><a class=\"btn\" href=\"#\" onclick=\"xajax_mmAddEdit(-1)\"><i class=\"fa fa-plus-square fa-lg\"></i>&nbsp;Agregar Oferta</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmOfertas()\"><i class=\"fa fa-list fa-lg\"></i>&nbsp;Mis Ofertas</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmCompras()\"><i class=\"fa fa-credit-card fa-lg\"></i>&nbsp;Mis Compras</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmSaldoTel()\">&nbsp;Recargas Celular&nbsp;</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmGetList()\"><i class=\"fa fa-shopping-cart fa-lg\"></i>&nbsp;Mercado</a></div>";
	$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha Creaci&oacute;n</th><th>Minutos</th><th>Precio</th><th>Total</th><th>Fecha Venta</th><th>Comprador</th></tr>";

	$db -> query ("select mminutos.id_oferta, mminutos.minutos, precio, fecha_ingreso, estado, accountcode, fecha_compra from mminutos left join compramm on mminutos.id_oferta=compramm.id_oferta left join users on mminutos.uid_comprador = users.uid where mminutos.uid=$userid order by fecha_compra" );

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
			$contenido .= "<td align=center colspan=2>-</td><td><a class='override' href='#' onclick='xajax_mmAddEdit($id_oferta);'><i class='fa fa-edit fa-fw'></i></a>";
		
		$contenido .= "</tr>";

		if ($estado) {
			$totalm += $minutos;
			$totalq += $total;
		}
	}

	$totalq = number_format($totalq,2);
	$contenido .= "<tr><td align=right>Total Vendido:</td><td>$totalm</td><td>&nbsp;</td><td align=right>Q. $totalq</td></tr></table>";
	$contenido .= "</div>";

	$objResponse -> assign("contenido","innerHTML", "$contenido" );
	return $objResponse;
}

function mmCompras() {
	global $accountcode, $userid, $db;

	$contenido = "<p><div id='prefswide_container'>";
	$objResponse = new xajaxResponse();
	$contenido .= "<div class='padder'><br/><a class=\"btn\" href=\"#\" onclick=\"xajax_mmAddEdit(-1)\"><i class=\"fa fa-plus-square fa-lg\"></i>&nbsp;Agregar Oferta</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmOfertas()\"><i class=\"fa fa-list fa-lg\"></i>&nbsp;Mis Ofertas</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmCompras()\"><i class=\"fa fa-credit-card fa-lg\"></i>&nbsp;Mis Compras</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmSaldoTel()\">&nbsp;Recargas Celular&nbsp;</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmGetList()\"><i class=\"fa fa-shopping-cart fa-lg\"></i>&nbsp;Mercado</a></div>";
	$contenido .= "<table cellspacing=1 class=\"llamadas\"><tr><th>Fecha</th><th>Vendedor</th><th>Minutos</th><th>Precio</th><th>Total</th></tr>";

	$db -> query ("select fecha_compra, mminutos.minutos, precio, accountcode from compramm, mminutos, users where users.uid = uid_vendedor and mminutos.id_oferta = compramm.id_oferta and mminutos.uid_comprador = $userid");

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

	$objResponse -> assign("contenido","innerHTML", "$contenido" );
	return $objResponse;
}

function mmAddEdit( $id_mm ) {
	global $accountcode, $userid, $db;

	$contenido = "<p><div id='prefs_container'>";
	$objResponse = new xajaxResponse();
	$contenido .= "<div class='padder'><br/><a class=\"btn\" href=\"#\" onclick=\"xajax_mmGetList()\"><i class=\"fa fa-shopping-cart fa-lg\"></i>&nbsp;Mercado</a><p>";
	
	if ( $id_mm < 0 ) { //Nueva Oferta
		$contenido .= '<br/><form id="addmmoffer" action="javascript:void(null);" onsubmit="xajax_mmAddEditForm(xajax.getFormValues(\'addmmoffer\'));">';
		$contenido .= '<input type="hidden" name="id" value="-1" />';
		$contenido .= '<table><tr><td><font size="4">Minutos: </font></td><td><input type="text" size="4" name="minutos" placeholder="##"/></td><td><font size="4">&nbsp;Precio/Minuto: Q0.</font>';
		$contenido .= '<input type="text" size="4" name="precio" placeholder="##"/></td><td><input type="submit" value="Agregar Oferta" /></td>';
		$contenido .= '</tr>';
	} else {
		$db -> query ("select * from mminutos where id_oferta=$id_mm and estado=0 and uid_comprador=0");
		$db -> next_record();
		$minutos = $db -> f ("minutos");
		$precio  = number_format($db->f("precio")*100,0);

		$contenido .= '<br/><form id="addmmoffer" action="javascript:void(null);" onsubmit="xajax_mmAddEditForm(xajax.getFormValues(\'addmmoffer\'));">';
		$contenido .= '<input type="hidden" name="id" value="' . $id_mm . '" />';
		$contenido .= '<input type="hidden" name="minutos" value="' . $minutos .'" />';
		$contenido .= '<table><tr><td><font size="4">Precio por Minuto: Q </font></td><td><font size="5">0.</font>';
		$contenido .= '<input type="text" size="5" name="precio" value="' . $precio . '" /></td><td><input type="submit" value="Guardar Cambios" /></td>';
		$contenido .= '</tr>';
	}

	$contenido .= "</div>";

	$objResponse -> assign("contenido","innerHTML", "$contenido" );
	return $objResponse;
}

function mmAddEditForm( $form ) {
	global $accountcode, $userid, $db;

	$objResponse = new xajaxResponse();

	$id_oferta = intval(trim($form["id"]));
	$minutos   = intval(trim($form["minutos"]));
	$precio    = floatval(trim($form["precio"]));

	if ( !( is_numeric($minutos) )) {
		$objResponse -> alert ("Debe ingresar un valor numerico mayor a cero para los minutos.");
		return $objResponse;
	}

	if ( !( $minutos > 0 )) {
		$objResponse -> alert ("Debe ingresar un valor numerico mayor a cero para los minutos.");
		return $objResponse;
	}

	if ( ! is_numeric($precio) ) {
		$objResponse -> alert ("Debe ingresar un valor numerico mayor a cero para el precio.");
		return $objResponse;
	}

	if ( !( $precio >= 1 )) {
		$objResponse -> alert ("Debe ingresar un valor numerico mayor a cero para el precio.");
		return $objResponse;
	}

	if ( !( $precio <= 99 )) {
		$objResponse -> alert ("El precio maximo es de 99 centavos el minuto.");
		return $objResponse;
	}

	$precio = $precio / 100;

	if ( $id_oferta == -1 ) { //Nueva oferta
		$db -> query ("select saldo_minutos from saldo where uid = $userid");
		$db -> next_record();
		$saldo = $db -> f ("saldo_minutos");
		if ( $minutos > $saldo ) {
			$objResponse -> alert ("No tiene suficiente minutos para crear la oferta.");
			return $objResponse;
		}
		$db -> query ("insert into mminutos values ( null, $userid, $minutos, $precio, now(), 0, 0 )");
		$db -> query ("update saldo set saldo_minutos=saldo_minutos-$minutos where uid=$userid");
		$objResponse -> alert ("Oferta creada!");
		$objResponse -> call("xajax_mmGetList");
		return $objResponse;
	}

	if ( $id_oferta > 0 ) {
		$db -> query ("update mminutos set precio=$precio where id_oferta=$id_oferta and estado=0 and uid_comprador=0");
		$db -> query ("select count(*) as qty from mminutos where id_oferta=$id_oferta and estado=0 and uid_comprador=0");
		$db -> next_record();
		$qty = $db -> f ("qty");
		if ( $qty == 0 ) {
			$objResponse -> alert ("Oferta vendida no se pueden hacer cambios!");
		} else {
			$objResponse -> alert ("Cambios realizados!");
		}
		$objResponse -> call("xajax_mmOfertas");
		return $objResponse;
	}

	return $objResponse;
}

function mmComprar( $id_mm ) {
	global $accountcode, $userid, $db;

	$objResponse = new xajaxResponse();
	$db -> query ("select saldo_qtz from saldo where uid = $userid");
	$db -> next_record();
	$saldo_qtz = $db -> f ("saldo_qtz");

	$idventa = intval($id_mm);

	$db -> query ("select precio*minutos as total, minutos from mminutos where id_oferta = $idventa and estado=0 and uid_comprador = 0");
	if ( $db -> next_record() ) {
		$total = $db -> f ("total");
		$minutos = $db -> f ("minutos");
		if ( $total > $saldo_qtz ) {
			$objResponse -> alert ("No tiene los fondos para comprar esta oferta");
		} else {
			//Realizar compra, evitar race conditions y que multiples usuarios compren a la vez la misma oferta!!
			$db -> query ("update mminutos set estado=1,uid_comprador=$userid where id_oferta=$idventa and estado = 0 and uid_comprador = 0");
			$db -> query ("select count(*) as qty, uid from mminutos where id_oferta=$idventa and estado=1 and uid_comprador=$userid");
			$db -> next_record();
			$qty = $db -> f ("qty");
			$id_vendedor = $db -> f ("uid");
			if ( $qty == 1 ) {
				$db -> query ("insert into compramm values ( null, $idventa, $id_vendedor, $userid, now() )");
				$db -> query ("update saldo set saldo_qtz=saldo_qtz-$total, saldo_minutos=saldo_minutos+$minutos,fecha_ingreso_saldo=now() where uid=$userid");
				$db -> query ("update saldo set saldo_qtz=saldo_qtz+$total where uid=$id_vendedor");

				$PID = shell_exec("nohup /var/lib/asterisk/agi-bin/venta-email.pl $idventa 2> /dev/null & echo $!");
	
				$objResponse -> alert ("Minutos comprados!");	
				$objResponse -> call ('xajax_mmGetList');
			} else {
				$objResponse -> alert ("Oferta ya no se encuentra disponible");
			}
		}
	} else {
		$objResponse = new xajaxResponse();
		$objResponse -> alert ("Oferta ya no se encuentra disponible");
	}
	
	return $objResponse;
}

function mmSaldoTel() {
	global $accountcode, $userid, $db, $saldo_qtz, $saldo_qtz_no;

	$objResponse = new xajaxResponse();
	buildMenu($objResponse, 8);

	$contenido = "<p><div id='prefswide_container'>";
	$contenido .= "<div class='padder'><br/><a class=\"btn\" href=\"#\" onclick=\"xajax_mmAddEdit(-1)\"><i class=\"fa fa-plus-square fa-lg\"></i>&nbsp;Agregar Oferta</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmOfertas()\"><i class=\"fa fa-list fa-lg\"></i>&nbsp;Mis Ofertas</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmCompras()\"><i class=\"fa fa-credit-card fa-lg\"></i>&nbsp;Mis Compras</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmSaldoTel()\">&nbsp;Recargas Celular&nbsp;</a>&nbsp;&nbsp;";
	$contenido .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_mmGetList()\"><i class=\"fa fa-shopping-cart fa-lg\"></i>&nbsp;Mercado</a></div>";

	$contenido .= "<br/>";
	$contenido .= "<br/><div class='padder'>";
	$contenido .= "<h2>Recargas Prepago de Claro, Movistar y Tigo</h2><br/>";
	$contenido .= '<form id="mmsaldo" action="javascript:void(null);" onsubmit="xajax_mmSaldoTelForm(xajax.getFormValues(\'mmsaldo\'));"><table><tr><td>';
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
	$contenido .= "<td>&nbsp;&nbsp;Monto: <select name=\"monto\">";
	$disabled = "";
	if ( $saldo_qtz_no >= 5 ) {
		$contenido .= "<option value=\"5\">Q 5</option>";
	} else {
		$disabled = "disabled='disabled'";
	}	
	if ( $saldo_qtz_no >= 10 ) {
		$contenido .= "<option value=\"10\">Q 10</option>";
	}
	if ( $saldo_qtz_no >= 25 ) {
		$contenido .= "<option value=\"25\">Q 25</option>";
	}
	if ( $saldo_qtz_no >= 50 ) {
		$contenido .= "<option value=\"50\">Q 50</option>";
	}
	$contenido .= "</select></td>";

	$contenido .= "<td>&nbsp;&nbsp;Celular: <input class=\"phone-input\" type=\"number\" name=\"telefono\" maxlength=\"8\" size=\"10\" placeholder=\"########\"/></td></tr>";
	$contenido .= "<tr></tr><td>&nbsp;</td><tr><td colspan=3 align=center><input type=\"submit\" value=\"Comprar Saldo\" $disabled\"/></td></tr>";
	$contenido .= "</table></form>";

	$db->query("select ingreso, celular, monto, estado, transaccion, result from transpagos where accountcode = '$accountcode'");
	if ( $db -> next_record() ) {
		$contenido .= "<br/><h3>Historial</h3><table cellspacing=1 class=\"llamadas\"><tr><th>Fecha</th><th>Tel&eacute;fono</th><th>Monto</th><th>Transacci&oacute;n</th><th>Estado</th></tr>";

		do {
			$fecha   = $db -> f ("ingreso");
			$celular = $db -> f ("celular");
			$monto   = number_format($db -> f ("monto"),2);
			$result  = ucwords(strtolower($db -> f ("result")));
			$transac = $db -> f ("transaccion");

			if ( $result == "" ) {
				$result = "En Proceso";
				$transac = "N/A";
			}

			$contenido .= "<tr><td>$fecha</td><td>$celular</td><td>Q. $monto</td><td>$transac</td><td>$result</td>";
		} while ( $db -> next_record() );
	}

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
	return $objResponse;
}	

function mmSaldoTelForm( $form ) {
	global $accountcode, $userid, $db, $saldo_qtz_no;

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

	if ( $saldo_qtz_no < $monto ) {
		$objResponseN = mmSaldoTel();
		$objResponseN -> alert ("No tiene suficiente saldo.");
		return $objResponseN;
	}

	$db -> query ("select count(*) as qty from transpagos where accountcode='$accountcode' and isnull(proceso)");
	$db -> next_record();
	$enproceso = $db -> f ("qty");

	if ( $enproceso > 0 ) {
		$objResponseN = mmSaldoTel();
		$objResponseN -> alert ("Ya tiene una recarga en proceso, por favor intente mas tarde.");
		return $objResponseN;
	}	

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
	$objResponse -> alert ("Compra ingresada. Saldo anterior Q $saldo_ant, nuevo saldo Q $saldo_post .");
	$objResponse -> call("xajax_mmGetList");
	return $objResponse;
}


function makePayments() {
	global $accountcode, $userid, $db, $saldo_qtz_no,$monto,$plan_minutos;

	$db -> query("select saldo_qtz from saldo where uid=$userid");
	$db -> next_record();
	$saldo = $db -> f ("saldo_qtz");

	if ( $saldo >= $monto ) {
		$db -> query("select TIMESTAMPDIFF(MONTH, fecha_aplica,DATE_FORMAT(NOW() ,'%Y-%m-01')) as meses, date_add(fecha_aplica, INTERVAL 1 MONTH) as fecha_aplica from pagos where accountcode = '$accountcode' and motivo_pago in (1,3) order by fecha_aplica desc limit 1;");
		if ( $db -> next_record() ) {
        	        $meses = $db -> f ("meses");
			$fecha_aplica = $db -> f ("fecha_aplica");
        	} else {
        	        $meses = 1;
			$fecha_aplica = date("Y") . "-" . date("m") . "-" . "01";
        	}

		$documento = uniqid('', true);
		$forma_pago = 4;
		$banco = 14;
		$motivo_pago = 3;
		$factura_pago = "N/D";
	
		$db -> query ("insert into pagos values ( null, '$accountcode', now(), now(), $forma_pago, $banco, '$documento', $monto, $plan_minutos, '$factura_pago', 0, '$fecha_aplica', $motivo_pago, 0 )");
		$db -> query ("update saldo set saldo_minutos=saldo_minutos+$plan_minutos, saldo_qtz=saldo_qtz-$monto, fecha_ingreso_saldo = now() where uid=$userid");
	}
	
	$objResponse = new xajaxResponse();
	$objResponse -> call ('xajax_getPayments');
	return $objResponse;

}

function pagoPaypal() {
	global $accountcode, $userid, $db, $saldo_qtz_no,$monto;

	$objResponse = new xajaxResponse();

	$habilitarpago = 0;
	$db -> query("select TIMESTAMPDIFF(MONTH, fecha_aplica,DATE_FORMAT(NOW() ,'%Y-%m-01')) as meses from pagos where accountcode = '$accountcode' and motivo_pago in (1,3) order by fecha_aplica desc limit 1;");
	if ( $db -> next_record() ) {
		$meses = $db -> f ("meses");
	} else {
		$meses = 1;
	}

	if ( $meses > 0 && $monto > 0 && $saldo_qtz_no >= $monto) {
		$habilitarpago = 1;
	}


	$res = "<div id='prefswide_container'><h3></h3><div id=\"num-1\"></div>"; 
	$res .= "<div class='padder'><br/>";
	$res .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_pagoPaypal()\"><i class=\"fa fa-cc-paypal fa-lg\"></i>&nbsp;Paypal</a>&nbsp;&nbsp;";
	$res .= "<a class=\"btn\" href=\"/tarjeta/compra.php\"><i class=\"fa fa-cc-visa fa-lg\"></i>&nbsp;Visanet</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	if ( $habilitarpago ) {
		$res .= "<a class=\"btn\" href=\"#\" onclick=\"makePayments()\"><i class=\"fa fa-money fa-lg\"></i><b>&nbsp;Pagar Mensualidad</b></a>";
	} else {
		$res .= "<a class=\"btnd\" href=\"#\" title=\"Sin suficiente saldo en quetzales o est&aacute; al d&iacute;a.\"><i class=\"fa fa-money fa-lg\"></i><b>&nbsp;Pagar Mensualidad</b></a>";
	}
	$res .= "</div>";

	$res .= "<div class='padder'><br/>";

	$res .= '<h3>Compra de Saldo en Quetzales con PayPal</h3><br/>';

$res .= '
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="8GKUD6RWEY2ZC">
<table>
<tr><td><input type="hidden" name="on0" value="Cantidad">Cantidad</td></tr><tr><td><select name="os0">
	<option value="Q50">Q50 $6.58 USD</option> 
	<option value="Q100">Q100 $13.16 USD</option>
	<option value="Q150">Q150 $19.74 USD</option> 
	<option value="Q200">Q200 $26.32 USD</option>
	<option value="Q250">Q250 $32.90 USD</option>
	<option value="Q300">Q300 $39.48 USD</option>
	<option value="Q500">Q500 $65.80 USD</option>
</select> </td></tr>
</table>
<input type="hidden" name="custom" value="' . $accountcode . '">
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/x-click-but6.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>';

	$res .= "</div>";

	$objResponse -> assign("contenido","innerHTML", "$res" );
	return $objResponse;
}

function pagoVisanet() {
	global $accountcode, $userid, $db;

	$objResponse = new xajaxResponse();

	$res = "<div id='prefswide_container'><h3></h3><div id=\"num-1\"></div>"; 
	$res .= "<div class='padder'><br/>";
	$res .= "<a class=\"btn\" href=\"#\" onclick=\"getPayments()\"><i class=\"fa fa-list fa-lg\"></i>&nbsp;Historial de Pagos</a>&nbsp;&nbsp;";
	$res .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_pagoPaypal()\"><i class=\"fa fa-cc-paypal fa-lg\"></i>&nbsp;Paypal</a>&nbsp;&nbsp;";
	$res .= "<a class=\"btn\" href=\"#\" onclick=\"xajax_pagoVisanet()\"><i class=\"fa fa-cc-visa fa-lg\"></i>&nbsp;Visanet</a></div>";

	$res .= "<div class='padder'><br/><h3>Proximamente...</h3></div>";
	$res .= "<br/>";

	$objResponse -> assign("contenido","innerHTML", "$res" );
	return $objResponse;
}

$xajax->processRequest();
?>
