<?php
/*
    admin.php
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

    Base admin page.
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
require_once ("xajax_core/xajaxAIO.inc.php");

$xajax = new xajax("ajax_server_admin.php");
$xajax->registerFunction("adminPanel");
$xajax->registerFunction("accountProcessForm");
$xajax->registerFunction("admEstadistica");
$xajax->registerFunction("admCDR");
$xajax->registerFunction("admCDRUser");
$xajax->registerFunction("admcallsPage");
$xajax->registerFunction("admMonitor");
$xajax->registerFunction("admVendedor");
$xajax->registerFunction("admEditVendedor");
$xajax->registerFunction("admEditPago");
$xajax->registerFunction("admPagos");
$xajax->registerFunction("admFacturacion");
$xajax->registerFunction("editCustomer");
$xajax->registerFunction("customerProcessForm");
$xajax->registerFunction("pagosProcessForm");
$xajax->registerFunction("custAddMinutes");
$xajax->registerFunction("custPayHistory");
$xajax->registerFunction("custMM");
$xajax->registerFunction("mmList");
$xajax->registerFunction("admVShowLog");
$xajax->registerFunction("admVShowClients");
$xajax->registerFunction("processStats");
$xajax->registerFunction("processPagos");
$xajax->registerFunction("admPagosMora");
$xajax->registerFunction("admPagosUpload");

include 'prepend_admin.php';
include 'db.inc.php';

getpost_ifset ( ("pagos") );
$userid = $user->requireAuthentication( "displayLogin" );

trigger_error("Admin : $userid", E_USER_NOTICE);

echo '<?xml version="1.0" encoding="UTF-8"?>'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
	<?php $xajax->printJavascript(); ?>
        <title>Numero Central - Area de Administracion</title>
	<link rel="stylesheet" type="text/css" href="/font-awesome.min.css"/>
        <link rel="stylesheet" type="text/css" href="/admin.css"/>

	<script type="text/javascript" src="xajax_js/prototype.js"></script>
	<script type="text/javascript" src="xajax_js/scriptaculous.js"></script>
	<script type="text/javascript" src="xajax_js/effects.js"></script>
	<script type="text/javascript" src="xajax_js/livepipe.js"></script>
	<script type="text/javascript" src="xajax_js/scrollbar.js"></script>
	<script type="text/javascript" src="xajax_js/datetimepicker_css.js"></script>
	<script type="text/javascript">
		function getAdmin() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_adminPanel();
		}

		function getPagos() {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_admPagos();
		}

		function admcallsGetPage(pagina) {
			var content = document.getElementById('contenido');
			content.innerHTML="<div align=center><br/><br><img src=/images/loading.gif><br/><br/></div>";	
			xajax_admcallsPage(pagina);
		}
		
		function editCustomer() {
                        xajax_customerProcessForm(xajax.getFormValues("customerForm"));
                }

		function editPagos() {
                        xajax_pagosProcessForm(xajax.getFormValues("paymentForm"));
                }

		function submitStats() {
			var form = xajax.getFormValues("statsForm");
			var form2 = document.getElementById("statsForm");
			xajax_processStats(xajax.getFormValues("statsForm"));
		}

		function submitPagos() {
			var form = xajax.getFormValues("statsForm");
			var form2 = document.getElementById("statsForm");
			xajax_processPagos(xajax.getFormValues("statsForm"));
		}
	</script>
</head>

<body>

<div id="content">
  <div id="logo">
	<h1 align="center"><img src="images/logo-small.jpg"> </h1>
  </div>

<!--  </div> -->

  <div id="header">
	<div id="title">
    	<h1 align=right>Administraci&oacute;n</h1>
	</div>

     <ul id="menu">
<!--      <li><a href="#" class="selected" onclick="getCalls()">LLamadas</a></li>
      <li><a href="#" onclick="getContacts()">Contactos</a></li>
      <li><a href="#" onclick="getNums()">N&uacute;meros</a></li>
      <li><a href="#" onclick="getFavorites(-1)">Favoritos</a></li>
      <li><a href="#" onclick="getPrefs()">Caracter&iacute;sticas</a></li> -->
     </ul>

  </div>
  
  <div id="centerSection">
    <div class="padder">
	<div align="right">
		&nbsp;<a href="logout_admin.php">Salir</a>
	</div>
	<div align="center">
	</div>
	<div align="center" id="busquedaNum">
	<br/>
<!-- <font size=1>
<form id="callsForm" action="javascript:void(null);" onsubmit="submitCalls();">
	<table><tr>
        <td>Numero :</td><td><input type="text" size=22 id="number" name="number" onkeyup="xajax_search(this.value,0)"/></td>
        <td>Fecha De:</td><td><input type="text" size=9 name="fechade" id="fechade" /><a href="javascript:NewCssCal('fechade','yyyymmdd')"><img border=0 src="/images/cal.gif"></a></td>
        <td>A:</td><td><input type="text" size=9 name="fechaa" id="fechaa" /><a href="javascript:NewCssCal('fechaa','yyyymmdd')"><img border=0 src="/images/cal.gif"></a></td>
        <td>Estado :</td><td><select name="estado"/>
		<option value=1>Entrante</option>
		<option value=2>Saliente</option>
		<option value=3>Fax</option>
		<option value=4>B&uacute;zon de Voz</option>
	</select></td>
        <td><input id="submitButton" type="submit" value="Busqueda"/></td></tr>
	<tr><td></td><td><div id="livesearch"></div></td></tr>
	</table>
</form>
</font> -->

	</div>
      <div class="padder">
	<div id="contenido">
        <strong></strong>
	</div>
      </div>
    </div>
  </div>

  <div id="footer">
    Copyright Codevoz. Todos los derechos reservados.&nbsp;&nbsp;
  </div>

</div>

<br/>
<span id="siteseal"><script type="text/javascript" src="https://seal.godaddy.com/getSeal?sealID="></script><br/><a style="font-family: arial; font-size: 9px" href="https://www.godaddy.com/ssl/ssl-certificates.aspx" target="_blank"></a></span>

<script type="text/javascript">
<?php if ( $pagos == 1 ) {
	echo "window.onload = getPagos;";
   } else {
	echo "window.onload = getAdmin;";
   }
?>
</script>
</body>

</html>

