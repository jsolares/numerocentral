#!/usr/bin/perl
#
#    pago-cron.pl
#    (C) 2018 by Jose Solares (jsolares@codevoz.com)
#
#    This file is part of numerocentral.
#
#    numerocentral is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    NumeroCentral is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with NumeroCentral.  If not, see <http://www.gnu.org/licenses/>.
#
#    CRON used for payments, if the user has available money it will be used to pay their monthly fee.
#

use DBI;
use DBD::mysql;
use Date::Format;
use Data::Uniqid qw (suniqid uniqid luniqid );
use Email::MIME;
use HTML::Entities;
use Number::Format;

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";
$logging  = 1;

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

my $gt = new Number::Format(-thousands_sep   => '.',
			    -decimal_point   => ',',
			    -int_curr_symbol => 'Q');

if($logging){ open FILE, ">>/var/log/pago-cron.log";}

$saldo_query = $dbh -> prepare ("SELECT accountcode,
                                        date_add(fecha_ingreso_saldo, interval 1 month),
                                        users.uid,
                                        saldo_minutos,
                                        saldo_qtz,
                                        monto,
                                        minutos,
					email,
					users.name
                                   FROM users,
                                        plans,
                                        saldo
                                  WHERE id_plan = plans.id
                                    AND users.uid = saldo.uid
                                    AND monto > 0
                                    AND saldo_qtz >= monto
                                    AND datediff(now(), fecha_ingreso_saldo) + 3 > valid_days;");

$saldo_update= $dbh -> prepare ("UPDATE saldo
                                    SET saldo_minutos = saldo_minutos + ?,
                                        saldo_qtz = saldo_qtz - ?,
                                        fecha_ingreso_saldo = ?
                                  WHERE uid = ?");

$pago_add = $dbh -> prepare ("INSERT INTO pagos ( id_pago, accountcode, fecha_ingreso, fecha_pago, forma_pago, banco, documento, monto, minutos, factura, uid, fecha_aplica, motivo_pago, facturar)
                                         VALUES (    null,           ?,         now(),      now(),          4,    14,         ?,     ?,       ?,   'N/D',   0,            ?,           3,       0 )");

$get_month = $dbh -> prepare ("SELECT TIMESTAMPDIFF(MONTH, fecha_aplica,DATE_FORMAT(NOW() ,'%Y-%m-01')) as meses,
                                      date_add(fecha_aplica, INTERVAL 1 MONTH) as fecha_aplica
                                 FROM pagos
                                WHERE accountcode = ?
                                  AND motivo_pago in (1, 3)
                             ORDER BY fecha_aplica desc limit 1;");

$saldo_query -> execute ();
$saldo_query -> bind_col( 1, \$accountcode);
$saldo_query -> bind_col( 2, \$new_ingreso_saldo);
$saldo_query -> bind_col( 3, \$uid);
$saldo_query -> bind_col( 4, \$saldo_minutos);
$saldo_query -> bind_col( 5, \$saldo_qtz);
$saldo_query -> bind_col( 6, \$monto );
$saldo_query -> bind_col( 7, \$minutos );
$saldo_query -> bind_col( 8, \$email );
$saldo_query -> bind_col( 9, \$name );

while ( $saldo_query -> fetch() ) {
        logit( "$accountcode : $uid \t\t: Mins $saldo_minutos, Q $saldo_qtz, Plan: $minutos, Q $monto");
        $get_month -> execute($accountcode);
        $get_month -> bind_col( 1, \$meses);
        $get_month -> bind_col( 2, \$fecha_aplica);
        if ( ! $get_month -> fetch() ) {
                @lt = localtime(time);
                $fecha_aplica = strftime("%Y-%m-01",,@lt);
                $meses = 1;
        }
        $struuid = luniqid(rand(), true);
        logit( "\tMes a pagar $fecha_aplica, $meses meses en mora");

        if ( $meses > 0 ) {
		$saldo_nuevo = $saldo_qtz - $monto;
		( $year, $month, $day ) = split ("-", $fecha_aplica);
                logit("\tSe aplicara pago $struuid, $saldo_qtz -> $saldo_nuevo para $month / $year");
                $pago_add -> execute( $accountcode, $struuid, $monto, $minutos, $fecha_aplica);
                $saldo_update -> execute ( $minutos, $monto, $new_ingreso_saldo, $uid);

		$saldo_qtz_txt = $gt -> format_price($saldo_qtz);
		$monto_txt = $gt -> format_price($monto);
		$saldo_nuevo_txt = $gt -> format_price($saldo_nuevo);

		logit("\tPreparando E-Mail");
		$body_str  = "Estimado(a) $name, te informamos que hemos utilizado parte de tu saldo en Quetzales de $saldo_qtz_txt";
		$body_str .= " para pagar $month/$year, te hemos debitado $monto_txt y acreditado $minutos minutos a tu N&uacute;mero Central";
		$body_str .= " $accountcode.<br/>Tu nuevo saldo en quetzales es de $saldo_nuevo_txt";
			
		$body_str .= ".<br/>";
		$body_str .= "<br/>No es necesario que respondas a este correo.<br/><br/>Saludos Cordiales<br/><br/><br/>N&uacute;mero Central<br/>Equipo de Soporte";

		my $message = Email::MIME->create(
                        header_str => [
                                From => 'Numero Central <noreply@numerocentral.com>',
                                To => $email,
				Cc => 'planes@numerocentral.com',
                                Subject => 'Mensualidad Numero Central : ' . $accountcode,
                        ],
                        attributes => {
                                encoding => 'quoted-printable',
                                charset => 'ISO-8859-1',
                                content_type => 'text/html',
                        },
                        body_str => $body_str
                );

		use Email::Sender::Simple qw(sendmail);
		sendmail($message);
		sendmail($message, { to => 'ignacio.ramos@codevoz.com' });
		logit("\tE-Mail enviado");
        }
}

if($logging){ close FILE;}

exit 1;

sub logit{
        if($logging){
                @lt = localtime(time);
                print FILE strftime("%Y-%m-%d %T",,@lt) . " ";
                print FILE "$_[0]\n";
        }
}
