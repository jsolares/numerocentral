<?php
/*
    recupera-clave.php
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

    Do password recovery via email
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
	<title>N&uacute;mero Central</title>
	<link rel="stylesheet" type="text/css" href="/recupera-clave.css"/>

	<script type="text/javascript" src="xajax_js/jquery.js"></script>
	<script type="text/javascript" src="xajax_js/jquery-ui.js"></script>
	<script type="text/javascript" src="xajax_js/jquery.complexify.banlist.js"></script>
	<script type="text/javascript" src="xajax_js/jquery.complexify.js"></script>

	<script type="text/javascript">
                $j = jQuery.noConflict();
	</script>
</head>

<body>

<div align="center">
<br/>
<img src="images/logo-small.jpg">
</div>

<div id="content">
  <div id="centerSection">

    <div class="padder">
      <div class="padder">
<?php
include 'db.inc.php';

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");

getpost_ifset(array("email","numero","token","clave","verifica"));

if ( strlen($email) > 0 && strlen($numero) > 0 ) {
	if ( strlen($numero) != 8 || !is_numeric($numero) ) {
		echo "El N&uacute;mero Central debe de ser de 8 d&iacute;gitos.";
	} else if ( is_valid_email($email) ) { 
		$db -> query ( "select * from users where accountcode = '$numero' and email = '$email'" );
		if ( $db -> next_record() ) {
			$db -> query ("select count(*) as qty from recupera_clave where accountcode = '$numero' and date_add(fecha_creado, interval 1 day) > now() and isnull(fecha_utilizado)");
			if ( $db -> next_record() && $db -> f ("qty") == 0 ) {
				$token = hash("haval224,5", "NC" . $numero . $email . time() . "NC");
				$db -> query ("insert into recupera_clave values ( '$token', '$numero', now(), null, null );" );

				$subject = "Proceso para recuperar tu clave de Numero Central.";
				$message = "<html><body>Has solicitado una nueva contrase&ntilde;a de N&uacute;mero Central, <a href=\"https://www.numerocentral.com/recupera-clave.php?token=$token\">Haz click aqui</a> para reestablecerla.<br/>Si no la solicitaste, puedes hacer caso omiso de este correo.<br/><br/>Atentamente,<br/>N&uacute;mero Central.</body></html>";
				$headers = "From: \"Numero Central\" <planes@numerocentral.com>\r\n"; 
				$headers .= "Content-type: text/html\r\n"; 
				if ( !mail($email, $subject, $message, $headers) ) {
					echo "Hubo un error enviando el correo electr&oacute;nico, por favor contactenos (planes@numerocentral.com).";
				} else {
					echo "Le hemos enviado un correo electr&oacute;nico para poder recuperar su contrase&ntilde;a.";
				}
			} else {
				echo "Ya tiene una solicitud pendiente de cambio de contrase&ntilde;a, Verifique su carpeta de SPAM.";
			}
		} else {
			echo "Los datos que ingresaste no son correctos, por favor intenta nuevamente.";
		}
	} else {
		echo "El correo electr&oacute;nico ingresado no es correcto, por favor intenta nuevamente.";
	}
} else if ( strlen($token) > 0 ) {
	if ( strlen($clave) > 7 || strlen($verifica) > 7 ) {
		if ( $clave == $verifica ) {
			$db -> query ("select accountcode from recupera_clave where token = '$token' and date_add(fecha_creado, interval 1 day) > now() and isnull(fecha_utilizado)");
			if ( $db -> next_record() ) {
if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
   if ($_SERVER["HTTP_CLIENT_IP"]) {
    $proxy = $_SERVER["HTTP_CLIENT_IP"];
  } else {
    $proxy = $_SERVER["REMOTE_ADDR"];
  }
  $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
  if ($_SERVER["HTTP_CLIENT_IP"]) {
    $ip = $_SERVER["HTTP_CLIENT_IP"];
  } else {
    $ip = $_SERVER["REMOTE_ADDR"];
  }
}

				$accountcode = $db -> f ("accountcode");
				$db -> query ("update users set passwd = md5('$clave') where accountcode = '$accountcode'" );
				$db -> query ("update recupera_clave set fecha_utilizado= now(), ip = '" . $ip . ':' . $proxy ."' where token = '$token'");
				echo "Contrase&ntilde;a cambiada exitosamente, puedes ingresar <a href=\"https://www.numerocentral.com/user.php\">Aqu&iacute;</a>.";
			} else {
				echo "Solicitud invalida, por favor intentalo de nuevo.";
			}
		} else {
			echo "Las contrase&ntilde;as no coinciden, por favor intentalo nuevamente.";
		}
	} else {
		$db -> query ("select accountcode from recupera_clave where token = '$token' and date_add(fecha_creado, interval 1 day) > now() and isnull(fecha_utilizado)");
		if ( $db -> next_record() ) {
?>
        <form action="recupera-clave.php" method="post">
		<input type="hidden" name="token" value="<?php echo $token; ?>">
                <table border="0" cellpadding="0" cellspacing="2">
                        <tr>
                                <td colspan="3" align="center">
                                        <br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Por favor ingresa tu nueva contrase&ntilde;a:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <br />
                                <br />
                                </td>
                        </tr>
                        <tr>
                                <td align=right>Contrase&ntilde;a:</td>
                                <td colspan=2><input id="clave" type="password" name="clave" value=""></td>
                        </tr>
			<tr>
				<td>&nbsp;</td>
				<td><meter value=0 id="PassValue" max="100"/></meter></td><td><div id="complexity">0%</div><br/></td>
                        <tr>
                                <td align=right>Confirmaci&oacute;n:</td>
                                <td colspan=2><input id="verifica" type="password" name="verifica" value=""></td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr>
                                <td colspan="3" align="center"><input id="resetpass" type="Submit" value="Enviar"></td>
                        </tr>

                	<tr><td>&nbsp;</td></tr>
                </table>
        </form>

<script>
	$j("#resetpass").attr('disabled','disabled');
	$j(function () {
		$j("#clave").complexify({}, function (valid, complexity) {
			document.getElementById("PassValue").value = complexity;
			$j("#complexity").text(Math.round(complexity) + '%');

			check(valid, complexity);
		});
		$j("#verifica").complexify({}, function (valid, complexity) {
			check(valid, complexity);
		});
	});

	function check( valid, complexity) {
		var pass = $j("#clave").val();
		var pass2 = $j("#verifica").val();

		if (( valid || complexity > 50 ) && ( pass == pass2 )) {
			$j("#resetpass").removeAttr('disabled');
		} else {
			$j("#resetpass").attr('disabled','disabled');
		}
}
</script>
<?php
		
		} else {
			echo "Solicitud invalida, por favor intentalo nuevamente.";
		}
	}
} else {
?>
        <form action="recupera-clave.php" method="post">
                <table border="0" cellpadding="0" cellspacing="2">
                        <tr>
                                <td colspan="2" align="center">
                                        <br />
			Por favor ingresa tu N&uacute;mero Central y tu correo electr&oacute;nico para recuperar tu contrase&ntilde;a.
                                <br />
                                <br />
                                </td>
                        </tr>
                        <tr>
                                <td align=right>N&uacute;mero Central:</td>
                                <td><input type="Text" name="numero" value=""></td>
                        </tr>
                        <tr>
                                <td align=right>Correo Electr&oacute;nico:</td>
                                <td><input type="Text" name="email" value=""></td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr>
                                <td colspan="2" align="center"><input type="Submit" value="Enviar"></td>
                        </tr>

                	<tr><td>&nbsp;</td></tr>
                </table>
        </form>
<?php
}

?>
      </div>
    </div>

  </div>

  <div id="footer">
    Copyright Codevoz. Todos los derechos reservados.&nbsp;&nbsp;&nbsp;
  </div>

<br />
<span id="siteseal"><script type="text/javascript" src="https://seal.godaddy.com/getSeal?sealID="></script><br/><a style="font-family: arial; font-size: 9px" href="http://www.godaddy.com/ssl/ssl-certificates.aspx" target="_blank">SSL Cert</a></span>
</div>
</body>
</html>
<?php
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

function is_valid_email( $address )
{
   $rx = "^[a-z0-9\\_\\.\\-]+\\@[a-z0-9\\-]+\\.[a-z0-9\\_\\.\\-]+\\.?[a-z]{1,4}$";
   return (preg_match("~".$rx."~i", $address));
}

?>
