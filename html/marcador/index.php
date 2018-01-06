<?php
/*
    index.php
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

    Main page area for the dialer
*/

if(!isset($_SESSION))
{
ini_set('session.use_trans_sid', false);
ini_set("url_rewriter.tags","");
ini_set('session.use_only_cookies', 1);
session_start();
}

header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 
require_once ("../xajax_core/xajaxAIO.inc.php");

$xajax = new xajax("ajax_server.php");
$xajax -> registerFunction("getSettings");
$xajax -> registerFunction("getCampaign");
$xajax -> registerFunction("getMain");
$xajax -> registerFunction("getCampaignDetail");
$xajax -> registerFunction("deleteCampaign");
$xajax -> registerFunction("stopCampaign");
$xajax -> registerFunction("marcadorhorario");

include '../prepend.php';
include '../db.inc.php';

getpost_ifset ( ("ivr") );
$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
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

	<script type="text/javascript" src="/xajax_js/prototype.js"></script>
	<script type="text/javascript" src="/xajax_js/scriptaculous.js"></script>
	<script type="text/javascript" src="/xajax_js/effects.js"></script>
	<script type="text/javascript" src="/xajax_js/livepipe.js"></script>
	<script type="text/javascript" src="/xajax_js/scrollbar.js"></script>
	<script type="text/javascript" src="/xajax_js/datetimepicker_css.js"></script>
	<script type="text/javascript" src="/xajax_js/jquery.js"></script>
	<script type="text/javascript" src="/xajax_js/date.js"></script>
	<script type="text/javascript" src="/xajax_js/jquery-ui.js"></script>
	<script type="text/javascript" src="/xajax_js/jquery.flot.js"></script>
	<script type="text/javascript" src="/xajax_js/jquery.flot.stack.js"></script>
	<script type="text/javascript" src="/xajax_js/jquery.complexify.banlist.js"></script>
	<script type="text/javascript" src="/xajax_js/jquery.complexify.js"></script>
	
	<script type="text/javascript">
		$j = jQuery.noConflict();

		function clearOptions(FormName, SelectName) {
			document.forms[FormName].elements[SelectName].options.length = 0;
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

		function deleteCampaign( id, name ) {
			var answer = confirm("Quiere eliminar la campaña: " + name );
			if ( answer ) {
				xajax_deleteCampaign( id );
			}
		}

		function stopCampaign( id, name ) {
			var answer = confirm("Quiere cancelar la campaña: " + name );
			if ( answer ) {
				xajax_stopCampaign( id );
			}
		}
	</script>
</head>

<body>

<div id="content">
  <div id="logo">
	<img src="/images/logo-small.jpg">
</div>

  <div id="header">
	<div id="title">
    	<h1 align=right>Marcador : <?php if ( $planid != 12 ) { echo $accountcode . "/<font size=3>Saldo " . $saldo . " min.</font><br/><font size=2>" . $vence . "</font>"; }?></h1>
	</div>

     <ul id="menu">
     </ul>

  </div>
  
  <div id="centerSection">
    <div class="padder">
	<div align="right">
		<a href="logout.php">Salir</a>
	</div>
	<div align="center">
	</div>
      <div class="padder">
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
	$j(document).ready(function() { xajax_getMain(); });
</script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA--1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>


</body>

</html>

