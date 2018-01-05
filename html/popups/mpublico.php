<?php
include '../db.inc.php';

$db = new DB_Sql("mysql", "localhost", "numerocentral", "root", "");

getpost_ifset(array( "account", "ivr"));

$db -> query ("select count(*) as allowed from users where accountcode='$account'");
$db -> next_record();
$allowed = $db -> f ("allowed");

if ( $allowed ) {
	$ivr = $ivr + 0;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="Ingrese su número telefónico y le devolveremos la llamada" />
<meta name="robots" content="index,follow" />
<title>Llámenme - Numero Central</title>
<script language="javascript" type="text/javascript">
var XMLHttpRequestObject_test = false;
var strSend;

if (window.XMLHttpRequest) {
   XMLHttpRequestObject_test = new XMLHttpRequest();
} else if (window.ActiveXObject) {
   XMLHttpRequestObject_test = new ActiveXObject("Microsoft.XMLHTTP");
}

var i;
var finished;

function webcall()
{
   if (XMLHttpRequestObject_test) {
      strSend = "https://www.numerocentral.com/webcall.php?";
      i = 0
      finished = false;
      while (finished != true)
      {
         if (document.callform1.elements[i].type == "text") {
            strSend = strSend + "&" + document.callform1.elements[i].name + "=" + document.callform1.elements[i].value;
         }
         else if (document.callform1.elements[i].type == "checkbox") {
            strSend = strSend + "&" + document.callform1.elements[i].name + "=" + document.callform1.elements[i].checked;
         }
         else if (document.callform1.elements[i].type == "hidden") {
            strSend = strSend + "&" + document.callform1.elements[i].name + "=" + document.callform1.elements[i].value;
         }
         else if (document.callform1.elements[i].type == "password") {
            strSend = strSend + "&" + document.callform1.elements[i].name + "=" + document.callform1.elements[i].value;
         }

         i = i + 1;

         if (! document.callform1.elements[i]) {
            finished = true;
         }
         else
         {
            finished = false;


 }
      }

      if ( window.XDomainRequest ) {
        var xdr = new XDomainRequest();
        if ( xdr ) {
           xdr.open("get", strSend);
           xdr.send();
           alert("En un momento conectamos su llamada.");
        }
      } else {
         XMLHttpRequestObject_test.open("GET", strSend);

         XMLHttpRequestObject_test.onreadystatechange = function ()
         {
            if (XMLHttpRequestObject_test.readyState == 4 && XMLHttpRequestObject_test.status == 200) {
                   if ( XMLHttpRequestObject_test.responseText == "fail" ) {
                        alert("No podemos completar su llamada en este momento.");
                   } else {
                        alert("En un momento conectamos su llamada.");
                  }
           }
        }
        XMLHttpRequestObject_test.send(null);
     }
   }
}
</script>
</head>
<body>
<div id="call-form" align="center">
<form id="callform1" name="callform1" action="javascript:void(null);" onsubmit="webcall();">
<input type="Hidden" name="account" value="<? echo $account; ?>">
<input type="Hidden" name="ivr" value="<? echo $ivr; ?>">
<table>        <tr><td><a href="https://www.numerocentral.com"><img src="https://www.numerocentral.com/pics/TELEFONO.png"></td></tr><tr><td>Mi N&uacute;mero Telef&oacute;nico: </td></tr><tr><td><input type="Text" name="phonenumber" value=""></td></tr>
        
<tr><td colspan=2><input type="Submit" value="Llamenme"></td></tr>
</table>
</form>
<br>
</div>

</body>
</html>

<?
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="Ingrese su número telefónico y le devolveremos la llamada" />
<meta name="robots" content="index,follow" />
<title>Numero Central</title>
</head>
<body>
<p>En este momento no se puede completar su solicitud, porfavor intente mas tarde.
</body>
</html>
<?
}

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

?>
