<?php
/*
    compra.php
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

    Integration with Cybersource payment processor to pay account
*/

header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 

if(!isset($_SESSION))
{
ini_set('session.use_trans_sid', 0);
ini_set('session.use_only_cookies', 1); 
ini_set('session.use_trans_sid', false);
ini_set("url_rewriter.tags","");
ini_set('session.use_only_cookies', 1);
session_start();
}

include 'security.php';

class UUID {
  public static function v3($namespace, $name) {
    if(!self::is_valid($namespace)) return false;

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-','{','}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for($i = 0; $i < strlen($nhex); $i+=2) {
      $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
    }

    // Calculate hash value
    $hash = md5($nstr . $name);

    return sprintf('%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 3
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  public static function v4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }

  public static function v5($namespace, $name) {
    if(!self::is_valid($namespace)) return false;

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-','{','}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for($i = 0; $i < strlen($nhex); $i+=2) {
      $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
    }

    // Calculate hash value
    $hash = sha1($nstr . $name);

    return sprintf('%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 5
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  public static function is_valid($uuid) {
    return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                      '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
  }
}

function getUserIP(){
    switch(true){
      case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
      case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
      case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
      default : return $_SERVER['REMOTE_ADDR'];
    }
}
$paso2 = 0;
$monto = 0;

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

getpost_ifset ( Array("paso2", "monto") );

include '../prepend.php';
include '../db.inc.php';

$userid = $user->requireAuthentication( "displayLogin" );

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");
//$db -> query ( "select accountcode, saldo_minutos from users where uid = $userid" );
$db -> query ( "select saldo_qtz, fax, email, id_plan, accountcode, saldo_minutos, valid_days - datediff(now(), fecha_ingreso_saldo) as vence, md5(concat('cybsop', passwd)) as pass, datediff(now(), fecha_inicio) as dias_inicio from users, plans, saldo where id_plan = plans.id and users.uid = saldo.uid and users.uid = $userid" );
$db -> next_record();
$accountcode = $db -> f ("accountcode");
$saldo = $db -> f ("saldo_minutos");
$saldo_qtz = number_format($db -> f ("saldo_qtz"),2);
$vence = $db -> f ("vence");
$faxno = $db -> f ("fax");
$planid = $db -> f ("id_plan");
$email = $db -> f ("email");
$data8 = $db -> f ("pass");
$data3 = "VISA";
$data15 = $db -> f ("dias_inicio");


$db -> query ( "select count(*) as qty, datediff(now(),min(fecha_pago)) as dias from pagos where accountcode = '$accountcode'" );
$db -> next_record();
$data1 = $db -> f ("qty");
$data13 = $db -> f ("dias");

$db -> query ( "(select number from nc_mynumber where accountcode = '$accountcode') UNION (select number from ivr_option where accountcode = '$accountcode' )");
$data4 = "N/A";
if ( $db -> next_record() ) {
	$data4 = $db -> f ("number");
}

$data5 = "Numero Central";
$data6 = "Carga de Saldo";
$data7 = "S";
$data9 = "N/A";
$data10 = "Web";
$data11 = "S";
$data12 = "0";
$data16 = "N";

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
        <title>N&uacute;mero Central</title>
	<link rel="stylesheet" type="text/css" href="/font-awesome.min.css"/>
	<link rel="stylesheet" type="text/css" href="/main.css"/>

<!--	<script type="text/javascript" src="/xajax_js/jquery.js"></script>
	<script type="text/javascript" src="/xajax_js/jquery-ui.js"></script> -->
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
	
<!--	<script type="text/javascript">
		$j = jQuery.noConflict();

	</script> -->

</head>

<body>

<div id="content">
  <div id="logo">
	<img src="/images/logo-small.jpg">
</div>

<!-- <div> -->
<!-- <span class="st_email"></span><span class="st_facebook"></span><span class="st_twitter" displayText="Recomi&eacute;ndanos"></span> -->
<!-- </div> -->

<!--  </div> -->

  <div id="header">
	<div id="title">
    	<h1 align=right>
<?php
	if ( strlen($faxno) > 0 ) {
		echo "<h1 align=right>$accountcode<font size=2>(Fax $faxno)</font>/<font size=3>Saldo <img src=../images/smallQ.gif style=width:18px;height:16px>  $saldo_qtz | $saldo min.</font><br/><font size=2>$vence</font></h1>";
        } else {
		echo "<h1 align=right>$accountcode/<font size=3>Saldo <img src=../images/smallQ.gif style=width:18px;height:16px> $saldo_qtz | $saldo min.</font><br/><font size=2>$vence</font></h1>";
                }
?></h1>
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
	</div>
	<div align="center">
<!--	<h2>Numero Central : <?php echo "$accountcode"; ?></h2> -->
	</div>
	<div align="center" id="busquedaNum">
	<br/>
	</div>
      <div class="padder">
	<div id="contenido">
        <strong></strong>
	<h2>Compra de Saldo en Quetzales con Visa.</h2><br/>
	<?php
		$skuval = "";
		if ($paso2 == '1') {
			echo '<form id="payment_confirmation" action="https://secureacceptance.cybersource.com/silent/pay" method="post"/>';
			foreach($_REQUEST as $name => $value) {
				if ( $name == "paso2" ) {
				} else {
					if ( $name == "amount" ) {
						$skuval = number_format((float)$value, 2, '','');
						$value = number_format((float)$value, 2, '.','');
					}
					if ( $name == "card_number" ) {
						$value = str_replace(" ","",$value);
						$params['merchant_defined_data2'] = substr($value,0,6);
						echo "<input type=\"hidden\" id=\"merchant_defined_data2\" name=\"merchant_defined_data2\" value=\"" . $params['merchant_defined_data2'] . "\"/>\n";
					}

					if ( $name == "card_expiry_date" ) {
						$value = str_replace(" ","",$value);
						$value = str_replace("/","-",$value);

						$myarr = explode( '-', $value );
						$mes = $myarr[0];
						$ano = $myarr[1];
						if (strlen($ano) == 2 ) {
							$ano = "20" . $myarr[1];
						}
						$value = $mes . '-' . $ano;
					}

				        $params[$name] = $value;
					echo "<input type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";

					if ( $name == "bill_to_forename" ) {
#                                                echo "ship_to_forename = $value<br/>";
                                                $params["ship_to_forename"] = $value;
                                                echo "<input type=\"hidden\" id=\"ship_to_forename\" name=\"ship_to_forename\" value=\"" . $value . "\"/>\n";
                                        }
                                        if ( $name == "bill_to_surname" ) {
#                                                echo "ship_to_surname = $value<br/>";
                                                $params["ship_to_surname"] = $value;
                                                echo "<input type=\"hidden\" id=\"ship_to_surname\" name=\"ship_to_surname\" value=\"" . $value . "\"/>\n";
                                        }
                                        if ( $name == "bill_to_address_state" ) {
#                                                echo "ship_to_address_state = $value<br/>";
                                                $params["ship_to_address_state"] = $value;
                                                echo "<input type=\"hidden\" id=\"ship_to_address_state\" name=\"ship_to_address_state\" value=\"" . $value . "\"/>\n";
                                        }
                                        if ( $name == "bill_to_phone" ) {
#                                                echo "ship_to_phone = $value<br/>";
                                                $params["ship_to_phone"] = $value;
                                                echo "<input type=\"hidden\" id=\"ship_to_phone\" name=\"ship_to_phone\" value=\"" . $value . "\"/>\n";
                                        }
                                        if ( $name == "bill_to_address_city" ) {
#                                                echo "ship_to_address_city = $value<br/>";
                                                $params["ship_to_address_city"] = $value;
                                                echo "<input type=\"hidden\" id=\"ship_to_address_city\" name=\"ship_to_address_city\" value=\"" . $value . "\"/>\n";
                                        }
                                        if ( $name == "bill_to_address_country" ) {
#                                                echo "ship_to_address_country= $value<br/>";
                                                $params["ship_to_address_country"] = $value;
                                                echo "<input type=\"hidden\" id=\"ship_to_address_country\" name=\"ship_to_address_country\" value=\"" . $value . "\"/>\n";
                                        }
                                        if ( $name == "bill_to_address_line1" ) {
#                                                echo "ship_to_address_line1= $value<br/>";
                                                $params["ship_to_address_line1"] = $value;
                                                echo "<input type=\"hidden\" id=\"ship_to_address_line1\" name=\"ship_to_address_line1\" value=\"" . $value . "\"/>\n";
                                        }
                                        if ( $name == "bill_to_address_postal_code" ) {
#                                                echo "ship_to_address_postal_code= $value<br/>";
                                                $params["ship_to_address_postal_code"] = $value;
                                                echo "<input type=\"hidden\" id=\"ship_to_address_postal_code\" name=\"ship_to_address_postal_code\" value=\"" . $value . "\"/>\n";
                                        }

				}
			}

			$params["item_0_code"] = $accountcode . '-' . $skuval;
			$params["item_0_sku"] = $params["item_0_code"];
			$params["item_0_name"] = "Compra de Saldo por Q" . $params["amount"];
			$params["item_0_quantity"] = 1;
			$params["item_0_unit_price"] = $params["amount"];

    			$tmp = '<input type="hidden" name="item_0_code" value="' .$params["item_0_code"] . '">';
			echo $tmp;
			echo '<input type="hidden" name="item_0_name" value="' . $params["item_0_name"] . '">';
			echo '<input type="hidden" name="item_0_quantity" value="1">';
			echo '<input type="hidden" name="item_0_sku" value="' . $params["item_0_sku"] . '">';
			echo '<input type="hidden" name="item_0_unit_price" value="' . $params["amount"] . '">';

			echo "<input type=\"hidden\" id=\"signature\" name=\"signature\" value=\"" . sign($params) . "\"/>\n";
			echo "<h3>&iquest;Est&aacute; seguro que desea cargar: " . $params["amount"] . " Quetzales a su Tarjeta de Credito?<br/>";
			echo "Tarjeta No. " . substr($params["card_number"],0,4) . "-xxxx-xxxx-" . substr($params["card_number"],-4) . "<h3><br/>";
			echo '<input type="submit" id="submit" value="Confirmar"/><a href="/user.php">&nbsp;<input type="button" value="Cancelar">';
			echo "</form>";


			$db->query("SELECT count(*) as qty from visanet WHERE txn_uuid = '" . $params["transaction_uuid"] . "'");
			$db -> next_record();
			if ( $db -> f ("qty") == 0 ) {
				$db->query("INSERT INTO visanet values ( null, '', '" . $params["transaction_uuid"] . "', '" .
					$params["reference_number"] . "', '" . $accountcode . "', " . $params["amount"] . ", now(), null, '', '', 0, '', 0 )");
			} else {
				$db->query("UPDATE visanet set fecha_requested=now() where txn_uuid = '" . $params["transaction_uuid"] . "'");
			}
		} else {
			$form = '
    <form id="payment_form" action="compra.php" method="post" >
    <input type="hidden" name="access_key" value="**access_key here">
    <input type="hidden" name="profile_id" value="**profile_id here">
    <input type="hidden" name="transaction_uuid" value="' . uniqid() . '">
    <input type="hidden" name="signed_field_names" value="access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,ship_to_forename,ship_to_surname,ship_to_phone,ship_to_address_line1,ship_to_address_city,ship_to_address_state,ship_to_address_country,ship_to_address_postal_code,line_item_count,item_0_code,item_0_name,item_0_quantity,item_0_sku,item_0_unit_price,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4,merchant_defined_data5,merchant_defined_data6,merchant_defined_data7,merchant_defined_data8,merchant_defined_data9,merchant_defined_data10,merchant_defined_data11,merchant_defined_data12,merchant_defined_data13,merchant_defined_data15,merchant_defined_data16,merchant_defined_data20,customer_ip_address,BillTo_CustomerID,BillTo_IpAddress,decisionManager_enabled,bill_to_customer_id,consumer_id,skip_decision_manager,device_fingerprint_id">
    <input type="hidden" name="unsigned_field_names" value="card_type,card_number,card_expiry_date,card_cvn">
    <input type="hidden" name="signed_date_time" value="' . gmdate("Y-m-d\TH:i:s\Z") . '">
    <input type="hidden" name="locale" value="es-ES">
    <input type="hidden" name="skip_decision_manager" value="false">
    <input type="hidden" name="BillTo_CustomerID" value="' . $userid . '">
    <input type="hidden" name="bill_to_customer_id" value="' . $userid . '">
    <input type="hidden" name="consumer_id" value="' . $userid . '">
    <input type="hidden" name="decisionManager_enabled" value="true">
    <input type="hidden" name="BillTo_IpAddress" value="' . getUserIP() . '">
    <input type="hidden" name="customer_ip_address" value="' . getUserIP() . '">
    <input type="hidden" name="transaction_type" value="sale">
    <input type="hidden" name="currency" value="GTQ">
    <input type="hidden" name="reference_number" value="' . $accountcode . uniqid() .'">
    <input type="hidden" name="payment_method" value="card">
    <input type="hidden" name="paso2" value="1">
    <input type="hidden" name="bill_to_email" value="' . $email . '">
    <input type="hidden" name="bill_to_phone" value="' . $accountcode . '">
    <input type="hidden" name="bill_to_address_country" value="GT"> 
    <input type="hidden" name="card_type" value="001">
    <input type="hidden" name="line_item_count" value="1">
    <input type="hidden" name="merchant_defined_data1" value="' . $data1 . '">
    <input type="hidden" name="merchant_defined_data3" value="' . $data3 . '">
    <input type="hidden" name="merchant_defined_data4" value="' . $data4 . '">
    <input type="hidden" name="merchant_defined_data5" value="' . $data5 . '">
    <input type="hidden" name="merchant_defined_data6" value="' . $data6 . '">
    <input type="hidden" name="merchant_defined_data7" value="' . $data7 . '">
    <input type="hidden" name="merchant_defined_data8" value="' . $data8 . '">
    <input type="hidden" name="merchant_defined_data9" value="' . $data9 . '">
    <input type="hidden" name="merchant_defined_data10" value="' . $data10 . '">
    <input type="hidden" name="merchant_defined_data11" value="' . $data11 . '">
    <input type="hidden" name="merchant_defined_data12" value="' . $data12 . '">
    <input type="hidden" name="merchant_defined_data13" value="' . $data13 . '">
    <input type="hidden" name="merchant_defined_data15" value="' . $data15 . '">
    <input type="hidden" name="merchant_defined_data16" value="' . $data16 . '">
    <input type="hidden" name="device_fingerprint_id" value="' . UUID::v4() . '">
    <script>
        document.write(\'<input type="hidden" name="merchant_defined_data20" value="\' + cybs_dfprofiler("**profiler here","live") + \'">\');
    </script>

	<fieldset>
        <legend>Datos de Tarjeta VISA</legend>
	<br/>
	<table>
	<tr><td><h3>Monto:</td><td><h4><h3><div class="form-group"><input type="text" id="cc-amount" class="form-control cc-amount" name="amount" size="4" placeholder="100" value="' . $monto . '"></div><h4>M&iacute;nimo Q25</td></tr>
	<tr><td>Nombre: </td><td><div class="form-group"><input type="text" id="cc-name" class="form-control cc-name" name="bill_to_forename"></div></td>
        <td>Apellido: </td><td><div class="form-group"><input type="text" id="cc-last" class="form-control cc-last" name="bill_to_surname"></div></td></tr>
        <tr><td>N&uacute;mero de Tarjeta:</td><td colspan=4><div class="form-group"><input type="tel" id="card_number" class="form-control card_number" name="card_number" placeholder="#### #### #### ####" size="25"></td></div></tr>
        <tr><td>Fecha Expiraci&oacute;n:</td><td><div class="form-group"><input type="tel" id="cc-exp" class="form-control cc-exp" name="card_expiry_date" size="10" placeholder="## / ####"></div></td>
        <td>CCV:</td><td><div class="form-group"><input type="tel" id="cc-cvc" class="form-control cc-cvc" name="card_cvn" size="5" placeholder="###"></td></div></tr>
        <tr><td>Direcci&oacute;n: </td><td colspan=4><div class="form-group"><input type="text" id="cc-addr" class="form-control cc-addr" name="bill_to_address_line1" size="40"></div></td></tr>
        <tr><td>Ciudad:</td><td><div class="form-group"><input type="text" id="cc-city" class="form-control cc-city" name="bill_to_address_city" placeholder="Guatemala"></div></td></tr>
        <tr><td>Departamento:</td><td><div class="form-group"><input type="id="cc-state" class="form-control cc-state" text" name="bill_to_address_state" placeholder="Guatemala"></div></td></tr>
        <tr><td>C&oacute;digo Postal:</td><td><div class="form-group"><input type="text" id="cc-post" class="form-control cc-post" name="bill_to_address_postal_code" size="8" placeholder="#####"></div></td></tr>
	</table>
	<br/>
	</fieldset>

    <a href="/user.php"><input type="button" value="Cancelar"></a>&nbsp;&nbsp;<input type="submit" id="submit" name="submit" value="Siguiente"/>&nbsp;</form>';
			echo $form;
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
	function validateForm() {
		var valid = $.payment.validateCardNumber($('input.card_number').val());
		if (!valid) {
			alert ('La tarjeta ingresada no es valida!');
			return false;
		}

		valid = $.payment.validateCardCVC($('input.cc-cvc').val());
		if (!valid) {
			alert ('El codigo de validacion debe de ser de 3 digitos!');
			return false;
		}

		var values = $('input.cc-exp').val().split("/");
		valid = $.payment.validateCardExpiry(values[0].replace(/^\s+|\s+$/g, ''),values[1].replace(/^\s+|\s+$/g, ''));
		if (!valid) {
			alert ('La fecha de expiracion no es valida!');
			return false;
		}
	}


<?php 
	if($paso2 == '1') {
	} else {
?>
	jQuery(function($) {
		$('.card_number').payment('formatCardNumber');
		$('.cc-exp').payment('formatCardExpiry');
		$('.cc-cvc').payment('formatCardCVC');
		$('.cc-amount').payment('restrictNumeric');
		$('.cc-post').payment('formatCardPostal');

		$.fn.toggleInputError = function(erred) {
       			this.parent('.form-group').toggleClass('has-error', erred);
			return this;
			};

		$('form').submit(function(e) {
			var cardType = $.payment.cardType($('.card_number').val());

			var valid = !(!$.payment.validateCardNumber($('.card_number').val()) || !$.payment.validateCardExpiry($('.cc-exp').payment('cardExpiryVal')) ||
				!$.payment.validateCardCVC($('.cc-cvc').val(), cardType) || !($('.cc-amount').val()>=25) || !($('.cc-name').val().length>2) ||
				!($('.cc-last').val().length>2) || !($('.cc-addr').val().length>5) || !($('.cc-city').val().length>3) || !($('.cc-state').val().length>3)
				|| !($('.cc-post').val().length==5) || !($('.cc-amount').val()<=1000) );

			if (!valid) {
				e.preventDefault();
			} 

			$('.card_number').toggleInputError(!$.payment.validateCardNumber($('.card_number').val()));
			$('.cc-exp').toggleInputError(!$.payment.validateCardExpiry($('.cc-exp').payment('cardExpiryVal')));
			$('.cc-cvc').toggleInputError(!$.payment.validateCardCVC($('.cc-cvc').val(), cardType));
			$('.cc-amount').toggleInputError(!($('.cc-amount').val()>=25 && $('.cc-amount').val()<=1000));
			$('.cc-name').toggleInputError(!($('.cc-name').val().length>2));
			$('.cc-last').toggleInputError(!($('.cc-last').val().length>2));
			$('.cc-addr').toggleInputError(!($('.cc-addr').val().length>5));
			$('.cc-city').toggleInputError(!($('.cc-city').val().length>3));
			$('.cc-state').toggleInputError(!($('.cc-state').val().length>3));
			$('.cc-post').toggleInputError(!($('.cc-post').val().length==5));
		});
	});
<?php
	}
?>
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

