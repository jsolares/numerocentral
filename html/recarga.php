<?php
/*
    recarga.php
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

    Recarga area.php, it's meant to use Transpagos to buy air time for
	Tigo, Claro and Telefonica in Guatemala.
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
header("Pragma: no-cache"); 
require_once ("xajax_core/xajaxAIO.inc.php");

$xajax = new xajax("ajax_server_recarga.php");
$xajax->registerFunction("mmSaldoTel");
$xajax->registerFunction("mmSaldoTelForm");

include 'prepend_recarga.php';
include 'db.inc.php';

getpost_ifset ( ("ivr") );
$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
//$db -> query ( "select accountcode, saldo_minutos from users where uid = $userid" );
$db -> query ( "select id_plan, accountcode, saldo_minutos, valid_days - datediff(now(), fecha_ingreso_saldo) as vence from users, plans, saldo where id_plan = plans.id and users.uid = saldo.uid and users.uid = $userid" );
$db -> next_record();
$accountcode = $db -> f ("accountcode");

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
        <link rel="stylesheet" type="text/css" href="/recarga.css"/>

	<script type="text/javascript" src="xajax_js/jquery.js"></script>
	<script type="text/javascript" src="xajax_js/jquery-ui.js"></script>
	
	<script type="text/javascript">
		$j = jQuery.noConflict();
		
		function next(id) {
			if ( id == 1 ) {
				$j("#cellno").css('display', 'none');
				$j("#emptd").css('display', 'none');
        			$j("#ammt").css('display', 'table-cell');
				$j("#next").attr('onclick', 'next(2)' );
				$j("#back").attr('onclick', 'next(0)' );
				$j("#next").text('Siguiente');
				$j("#back").removeClass("btnd");
				$j("#back").addClass("btn");
			}
			if ( id == 0 ) {
				$j("#cellno").css('display', 'none');
				$j("#emptd").css('display', 'table-cell');
        			$j("#ammt").css('display', 'none');
				$j("#next").attr('onclick', 'next(1)' );
				$j("#back").attr('onclick', '' );
				$j("#back").removeClass("btn");
				$j("#back").addClass("btnd");
			}
		        if ( id == 2 ) {
				$j("#cellno").css('display', 'table-cell');
				$j("#emptd").css('display', 'none');
        			$j("#ammt").css('display', 'none');
				$j("#back").attr('onclick', 'next(1)' );
				$j("#next").attr('onclick', 'next(3)' );
				$j("#next").text('Comprar');
			}
		        if ( id == 3 ) {
				xajax_mmSaldoTelForm(xajax.getFormValues('mmsaldo'), 0);
				return false;
			}
		}

		function add(valor) {
			var value = $j("#telefono").val();
			if (valor == -1) {
				value = value.substring(0, value.length - 1);
				$j("#telefono").val(value);
				return 0;
			} else {
				if ( value.length < 8 )
				value = value + valor;
			}
			$j("#telefono").val(value);
		}
	</script>

</head>

<body>

<div id="content">

<!-- <div> -->
<!-- <span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter" displayText="Recomi&eacute;ndanos"></span> -->
<!-- </div> -->

<!--  </div> -->

  <div id="centerSection">
    <div class="padder">
	<div align="right">
<!--
		<a href="#" onclick="getAccount()">Mi Cuenta</a>&nbsp;&nbsp;--><a class="btn" href="logout_recarga.php">Salir</a>
	</div>
	<div id="contenido">
        <strong></strong>
      </div>
     </div>
  </div>

  <div id="footer">
Codevoz, S.A. Recargas Celular.&nbsp;&nbsp;
  </div>

</div>

<script type="text/javascript">
	$j("#fechade").datepicker({ showOtherMonths: true, inline: true, dateFormat: "yy-mm-dd", buttonImage: "/images/cal.gif", showOn: "both", buttonImageOnly: true, constrainInput: true});
	$j("#fechaa").datepicker({ showOtherMonths: true, inline: true, dateFormat: "yy-mm-dd", buttonImage: "/images/cal.gif", showOn: "both", buttonImageOnly: true, constrainInput: true});
	$j(document).ready(function() { xajax_mmSaldoTel(); });

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

