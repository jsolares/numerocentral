<?php
/*
    receipt.php
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

    Process form for payment made through Cybersource
*/

ini_set('session.use_trans_sid', 0);
ini_set('session.use_only_cookies', 1); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 

session_start();

include 'security.php';

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

include '../db.inc.php';

$req_dump = date("D, d M Y H:i:s") . "\n";
$req_dump .= print_r( $_REQUEST, true );
$fp = file_put_contents( '/var/log/tarjeta.log', $req_dump, FILE_APPEND);

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");

echo '<?xml version="1.0" encoding="UTF-8"?>'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>

<head>
        <title>N&uacute;mero Central</title>
	<link rel="stylesheet" type="text/css" href="/font-awesome.min.css"/>
	<link rel="stylesheet" type="text/css" href="/main.css"/>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script type="text/javascript" src="/xajax_js/jquery.payment.js"></script>
	<script type="text/javascript" src="cybs_devicefingerprint.js"></script>

	<style type="text/css" media="screen">
		.has-error input {
			border-width: 2px;
			border-color: red;
		}

		.validation.text-danger:after {
			content: 'Validation failed';
		}

		.validation.text-success:after {
			content: 'Validation passed';
		}
	</style>
</head>

<body>

<div id="content">
  <div id="logo">
	<img src="/images/logo-small.jpg">
</div>

  <div id="header">
	<div id="title">
	</div>

  </div>
  
  <div id="centerSection">
    <div class="padder">
	<div align="right">
	</div>
	<div align="center">
	</div>
	<div align="center" id="busquedaNum">
	<br/>
	</div>
      <div class="padder">
	<div id="contenido">
	<br/>
	<?php
		foreach($_REQUEST as $name => $value) {
                     $params[$name] = $value;
#			echo "<span>" . $name . "</span><input type=\"text\" name=\"" . $name . "\" size=\"50\" value=\"" . $value . "\" readonly=\"true\"/><br/>";
                 }

                 if (strcmp($params["signature"], sign($params))==0) {
			$item_code = $params["req_item_0_code"];
			$account   = $params["req_bill_to_phone"];
			$txn_id    = $params["transaction_id"];
			$txn_uuid  = $params["req_transaction_uuid"];
			$result    = $params["decision"];
			$refno     = $params["req_reference_number"];
			$message   = $params["message"];
			$code      = $params["reason_code"];

			if ( $params["auth_amount"] == $params["req_amount"] ) {
				$db -> query ("select uid from users where accountcode = '$account';");
				if ( $db -> next_record() ) {
					$uid = $db -> f ("uid");
					$monto = $params["auth_amount"];

					if ( strcasecmp( $result, "ACCEPT") == 0 ) {
						$db -> query ("select count(*) as qty from visanet where txn_id = '$txn_id'");
						$db -> next_record();
						if ( $db -> f("qty") == 0 ) {
							$db -> query ("insert into pagos values ( null, '$account', now(), now(), 1, 10, '$txn_id', $monto, $monto, 'PEND. VisaNET $txn_id', 1, now(), 2, 1 );");
							$db -> query ("UPDATE visanet set status=1,id_pago = LAST_INSERT_ID(), txn_id = '$txn_id', fecha_process=now(), decision='$result', message = '$message', reason_code = $code WHERE accountcode = '$account' and txn_uuid = '$txn_uuid'");
//Descomentar al mover a produccion
							$db -> query ("update saldo set saldo_qtz=saldo_qtz+$monto where uid=$uid;");
						} else {
							$db -> query ("UPDATE visanet set status=2, txn_id = '$txn_id', fecha_process=now(), decision='$result', message = '$message', reason_code = $code WHERE accountcode = '$account' and txn_uuid = '$txn_uuid'");
						}
#						echo "<h2>$message</h2>";
						echo "<h2>Solicitud procesada exitosamente.</h2>";
						echo "<br/>Gracias por su compra de Q. $monto";
						echo "<br/><a href=\"/user.php\"><input type=\"button\" value=\"Continuar\"/></a>";
					}
				} else {
					//Error procesando transaccion, no existe el usuario
					$db -> query ("UPDATE visanet set status=2, txn_id = '$txn_id', fecha_process=now(), decision='$result', message = '$message', reason_code = $code WHERE accountcode = '$account' and txn_uuid = '$txn_uuid'");
					echo "<h2>Ocurrio un error por favor intente mas tarde.</h2>";
					echo "<h2>$message</h2>";
					echo "<br/><a href=\"/user.php\"><input type=\"button\" value=\"Regresar\"/></a>";
				}
			} else {
				//Transaccion Denegada
				$db -> query ("UPDATE visanet set status=2, txn_id = '$txn_id', fecha_process=now(), decision='$result', message = '$message', reason_code = $code WHERE accountcode = '$account' and txn_uuid = '$txn_uuid'");
				echo "<h2>Ocurrio un error por favor intente mas tarde.</h2>";
				echo "<h2>$message</h2>";
				echo "<br/><a href=\"/user.php\"><input type=\"button\" value=\"Regresar\"/></a>";
			}
		
                 } else {
			echo "<h2>Ocurrio un error por favor intente mas tarde.</h2>";
			echo "<br/><a href=\"/user.php\"><input type=\"button\" value=\"Regresar\"/></a>";
                 }
	?>
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

