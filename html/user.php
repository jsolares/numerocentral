<?php
/*
    user.php
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

    End user area
*/

if(!isset($_SESSION))
{
ini_set('session.use_trans_sid', false);
ini_set("url_rewriter.tags","");
ini_set('session.use_only_cookies', 1);
session_start();
}

//ini_set('session.use_trans_sid', 0);
//ini_set('session.use_only_cookies', 1); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header('Content-Type: text/html; charset=utf-8');
header("Pragma: no-cache"); 
require_once ("xajax_core/xajaxAIO.inc.php");

$xajax = new xajax("ajax_server.php");
$xajax->registerFunction("processCalls");
$xajax->registerFunction("getCalls");
$xajax->registerFunction("getCallsNumber");
$xajax->registerFunction("getCallsTag");
$xajax->registerFunction("getContacts");
$xajax->registerFunction("getNums");
$xajax->registerFunction("getAccount");
$xajax->registerFunction("getPrefs");
$xajax->registerFunction("getPayments");
$xajax->registerFunction("getTags");
$xajax->registerFunction("getFavorites");
$xajax->registerFunction("getFavoritesContacts");
$xajax->registerFunction("callsPage");
$xajax->registerFunction("callsReload");
$xajax->registerFunction("contactsEditGroup");
$xajax->registerFunction("contactsDeleteGroup");
$xajax->registerFunction("contactsDeleteContact");
$xajax->registerFunction("contactsEditContacts");
$xajax->registerFunction("contactsGetContacts");
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
$xajax->registerFunction("editCustomer");
$xajax->registerFunction("customerProcessForm");
$xajax->registerFunction("custAddMinutes");
$xajax->registerFunction("search");
$xajax->registerFunction("contactsearch");
$xajax->registerFunction("searchSet");
$xajax->registerFunction("searchCSet");
$xajax->registerFunction("searchcontactbtn");
$xajax->registerFunction("newCall");
$xajax->registerFunction("newCallProcess");
$xajax->registerFunction("newIVRAudio");
$xajax->registerFunction("ivrHorario");
$xajax->registerFunction("ivrConfEdit");
$xajax->registerFunction("ivrOptAdd");
$xajax->registerFunction("ivrMove");
$xajax->registerFunction("ivrDelete");
$xajax->registerFunction("SelectAcct");
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

include 'prepend.php';
include 'db.inc.php';

getpost_ifset ( ("ivr") );
$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
//$db -> query ( "select accountcode, saldo_minutos from users where uid = $userid" );
$db -> query ( "select id_plan, accountcode, saldo_minutos, valid_days - datediff(now(), fecha_ingreso_saldo) as vence from users, plans, saldo where id_plan = plans.id and users.uid = saldo.uid and users.uid = $userid" );
$db -> next_record();
$accountcode = $db -> f ("accountcode");
$saldo = $db -> f ("saldo_minutos");
$vence = $db -> f ("vence");
$planid = $db -> f ("id_plan");

trigger_error("$accountcode", E_USER_NOTICE);

if ( $vence > 0 ) {
	if ( $vence == 1 ) {
		$vence = "Vence en 1 dia.";
	} else {
		$vence = "Vence en $vence dias.";
	}
} else {
	$vence = "";
}

echo '<?xml version="1.0" encoding="UTF-8"?>'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>

<head>
	<?php $xajax->printJavascript(); ?>
        <title>N&uacute;mero Central</title>
	<link rel="stylesheet" type="text/css" href="/font-awesome.min.css"/>
        <link rel="stylesheet" type="text/css" href="/main.css"/>
	<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<script type="text/javascript" src="xajax_js/prototype.js"></script>
	<script type="text/javascript" src="xajax_js/scriptaculous.js"></script>
	<script type="text/javascript" src="xajax_js/effects.js"></script>
	<script type="text/javascript" src="xajax_js/livepipe.js"></script>
	<script type="text/javascript" src="xajax_js/scrollbar.js"></script>
	<script type="text/javascript" src="xajax_js/datetimepicker_css.js"></script>
	<script type="text/javascript" src="xajax_js/jquery.js"></script>
	<script type="text/javascript" src="xajax_js/date.js"></script>
	<script type="text/javascript" src="xajax_js/jquery-ui.js"></script>
	<script type="text/javascript" src="xajax_js/jquery.flot.js"></script>
	<script type="text/javascript" src="xajax_js/jquery.flot.stack.js"></script>
	<script type="text/javascript" src="xajax_js/jquery.complexify.banlist.js"></script>
	<script type="text/javascript" src="xajax_js/jquery.complexify.js"></script>
	
	<script type="text/javascript">
		$j = jQuery.noConflict();

		function clearOptions(FormName, SelectName) {
			document.forms[FormName].elements[SelectName].options.length = 0;
		}

		function submitCalls() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";
			xajax.$('submitButton').disabled=true;
			xajax.$('submitButton').value="espere...";
			xajax_processCalls(xajax.getFormValues("callsForm"));
		}

		function newCall() {
			xajax_newCall();
		}

		function submitContactSearch() {
			xajax_searchcontactbtn(xajax.getFormValues("contactsearchForm"));
		}

		function getCalls() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax.$('submitButton').disabled=true;
			xajax.$('submitButton').value="espere...";
			xajax_getCalls();
		}

		function tags(etiqueta) {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax.$('submitButton').disabled=true;
			xajax.$('submitButton').value="espere...";
			xajax_getCallsTag(etiqueta);
		}

		function llamadas(numero) {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax.$('submitButton').disabled=true;
			xajax.$('submitButton').value="espere...";
			xajax_getCallsNumber(numero);
		}

		function getAccount() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_getAccount();
		}

		function getPayments() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_getPayments();
		}

		function getNums() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_getNums();
		}

		function addMyNumber() {
			var content = document.getElementById('contenido');
                        content.innerHTML = "<p>------<br/>" + content.innerHTML;
		}

		function getTags() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_getTags();
		}

		function getPrefs() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_getPrefs();
		}

		function getStats() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_getStats();
		}

		function FavoritesAdd() {
			xajax_favsProcessAdd(xajax.getFormValues("FavoritesForm"));
		}

		function getFavorites() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_getFavorites(-1);
		}

		function getContacts() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_getContacts();
		}

		function group_GetContacts(id_group) {
			var content = document.getElementById('contact_content');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_contactsGetContacts(id_group);
		}

		function group_GetDetails(id_contact) {
			var content = document.getElementById('contact_content');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_contactsEditContacts(id_contact);
		}

		function deleteMyTag( id_tag, etiqueta)  {
			var answer = confirm("Quiere quitar la etiqueta: " + etiqueta );
			if (answer) {
				xajax_tagsDelete(id_tag);
			}
		} 

		function deleteMyNum( id_num, phone )  {
			var answer = confirm("Quiere quitar el numero: " + phone);
			if (answer) {
				xajax_numsDelete(id_num);
			}
		} 

		function deleteaudio( file, fecha ) {
			var answer = confirm("Esta seguro que quiere borrar la grabacion de fecha: " + fecha );
			if (answer) {
				xajax_audioDelete(file);
			}
		}

		function makePayments() {
			var answer = confirm("Esta seguro que desea pagar su mensualidad." );
			if (answer) {
				xajax_makePayments();
			}
		}


		function group_DeleteGrupo(id_group, group_name) {
			var answer = confirm("Quiere borrar grupo: " + group_name);
			if (answer) {
				xajax_contactsDeleteGroup(id_group);
			}
		}

		function group_DeleteContact(id_contact, name) {
			var answer = confirm("Quiere borrar el contacto : " + name + ", Tambien lo eliminara de favoritos.");
			if (answer) {
				xajax_contactsDeleteContact(id_contact);
			}

		}

		function MyNumsProcess(id) {
			var name = "NumsForm" + id;
			xajax_numsProcessForm(xajax.getFormValues(name));
		}

		function MyTagsProcess(id) {
			var name = "TagsForm" + id;
			xajax_tagsProcessForm(xajax.getFormValues(name));
		}

		function contactsProcessGroup(param) {
			xajax_contactsProcessForm(xajax.getFormValues("contactGroupForm"),param);
		}

		function prefsProcessForm() {
			xajax_prefsProcessForm(xajax.getFormValues("prefsForm"));
		}

		function ChangeAccount() {
			xajax_accountProcessForm(xajax.getFormValues("ChangeAccountForm"));
		}

		function callsGetPage(pagina) {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_callsPage(pagina);
		}
		function updateTag(callid, etiqueta) {
			xajax_tagSave(callid, etiqueta.options[etiqueta.selectedIndex].value);
		}
		function statsProcessForm() {
			xajax_statsProcessForm(xajax.getFormValues("statsForm"));
		}

		function showTooltip(x, y, contents) {
                        $j('<div id="tooltip">' + contents + '</div>').css( {
                            position: 'absolute',
                            display: 'none',
                            top: y + 5,
                            left: x + 5,
                            border: '1px solid #fdd',
                            padding: '2px',
                            'background-color': '#fee',
                            opacity: 0.80
                        }).appendTo("body").fadeIn(200);
                }

		function getMercado() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax.$('submitButton').disabled=true;
			xajax.$('submitButton').value="espere...";
			xajax_mmGetList();
		}

		function confirmarMMC( id_compra, minutos, precio ) {
			var answer = confirm("Esta seguro que quiere comprar " + minutos + " minutos a Q. " + precio + "?");
			if (answer) {
				xajax_mmComprar(id_compra);
			}
		}

	</script>

</head>

<body>

<div id="content">
  <div id="logo">
	<img src="images/logo-small.jpg">
</div>

<!-- <div> -->
<!-- <span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter" displayText="Recomi&eacute;ndanos"></span> -->
<!-- </div> -->

<!--  </div> -->

  <div id="header">
	<div id="title">
    	<h1 align=right><?php if ( $planid != 12 ) { echo $accountcode . "/<font size=3>Saldo" . $saldo . " min.</font><br/><font size=2>" . $vence . "</font>"; }?></h1>
	</div>

     <ul id="menu">
<!--      <li><a href="#" class="selected" onclick="getCalls()">LLamadas</a></li>
      <li><a href="#" onclick="getContacts()">Agenda</a></li>
      <li><a href="#" onclick="getNums()">N&uacute;meros</a></li>
      <li><a href="#" onclick="getFavorites(-1)">Favoritos</a></li>
      <li><a href="#" onclick="getPrefs()">Caracter&iacute;sticas</a></li> -->
     </ul>

  </div>
  
  <div id="centerSection">
    <div class="padder">
	<div align="right">
<?
	if ( $accountcode == '24584710' || $accountcode == '24584711' || $accountcode == 'admin' )
		echo '<a href="admin.php">Admin</a>&nbsp;&nbsp;&nbsp;';
?>
<!--
		<a href="#" onclick="getAccount()">Mi Cuenta</a>&nbsp;&nbsp;--><a href="logout.php">Salir</a>
	</div>
	<div align="center">
<!--	<h2>Numero Central : <?php echo "$accountcode"; ?></h2> -->
	</div>
	<div align="center" id="busquedaNum">
	<br/>
<font size=1>
<form id="callsForm" action="javascript:void(null);" onsubmit="submitCalls();">
	<table><tr>
        <td>N&uacute;mero: </td><td><input type="text" size=22 id="number" name="number" onkeyup="xajax_search(this.value,0)"/></td>
        <!-- <td>Fecha De:</td><td><input type="text" size=9 name="fechade" id="fechade" /><a href="javascript:NewCssCal('fechade','yyyymmdd')"><img border=0 src="/images/cal.gif"></a></td>
        <td>A:</td><td><input type="text" size=9 name="fechaa" id="fechaa" /><a href="javascript:NewCssCal('fechaa','yyyymmdd')"><img border=0 src="/images/cal.gif"></a></td> -->
	<td>Fecha De:</td><td><input type="text" size=10 name="fechade" id="fechade" class="date-pick" /></td>
	<td>A:</td><td><input type="text" size=10 name="fechaa" id="fechaa" class="date-pick" /></td>
        <td>Estado: </td><td><select name="estado"/>
		<option value=0>Todas</option>
		<option value=1>Entrante</option>
		<option value=2>Saliente</option>
		<option value=7>Marcador</option>
		<option value=3>Fax</option>
		<option value=5>Conferencia</option>
		<option value=6>Perdida</option>
<!--		<option value=4>B&uacute;zon de Voz</option>-->
	</select></td>
	<td>Etiqueta:</td><td><div id="etiquetas"><select name="tag" id="tag"></select></div></td>
        <!--Contacto :<input type="text" size=9  name="coment" /> -->
        <td><input id="submitButton" type="submit" value="Busqueda"/></td></tr>
	<tr><td></td><td><div id="livesearch"></div></td></tr>
	</table>
</form>
</font>
	</div>
      <div class="padder">
	<div id=newcall align=center><a href="#" onclick="newCall()"><font size=5><table width=120px><tr><td><img src=/images/phone.jpg></td><td>Llamar</td></tr></table></font></a></div>
	<div id="contenido">
        <strong></strong>
	</div>
      </div>
    </div>
  </div>

  <div id="footer">
 <i class="fa fa-copyright"></i>Codevoz. Todos los derechos reservados.&nbsp;&nbsp;<i class="fa fa-bookmark">&nbsp;</i>
  </div>

</div>

<br/>

<script type="text/javascript">
	$j("#fechade").datepicker({ showOtherMonths: true, inline: true, dateFormat: "yy-mm-dd", buttonImage: "/images/cal.gif", showOn: "both", buttonImageOnly: true, constrainInput: true});
	$j("#fechaa").datepicker({ showOtherMonths: true, inline: true, dateFormat: "yy-mm-dd", buttonImage: "/images/cal.gif", showOn: "both", buttonImageOnly: true, constrainInput: true});
<?php if ( $ivr == 1 ) {
	echo '$j(document).ready(function() { getNums(); });';
#	echo "window.onload = getNums;";
   } else {
	echo '$j(document).ready(function() { getCalls(); });';
#	echo "window.onload = getCalls;";
   }
?>
</script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-19049180-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>


</body>

</html>
