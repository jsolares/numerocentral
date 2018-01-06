#!/usr/bin/perl
#
#    venta-email.pl
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
#    send email when someone buys from the minutes market
#

use DBI;
use DBD::mysql;
use POSIX qw(ceil floor);
use Email::MIME;
use HTML::Entities;
use DateTime;
use Number::Format;
use Date::Format;

die "Usage: $0 ID_VENTA\n" if @ARGV != 1;

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";
$logging  = 1;

if($logging){ open FILE, ">>/var/log/email-mminutos.log";}

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

$id_oferta = shift;

my $gt = new Number::Format(-thousands_sep   => '.',
			    -decimal_point   => ',',
			    -int_curr_symbol => 'Q');

$saldo_query = $dbh -> prepare ("SELECT email,
                                        name,
					accountcode,
					mminutos.minutos,
					mminutos.precio,
					date(fecha_ingreso),
					saldo_qtz
                                   FROM users,
                                        mminutos,
					saldo
                                  WHERE users.uid = mminutos.uid
				    AND saldo.uid = users.uid
				    AND mminutos.estado = 1
                                    AND id_oferta = ?");

$saldo_query -> execute ( $id_oferta );
$saldo_query -> bind_col( 1, \$email);
$saldo_query -> bind_col( 2, \$name);
$saldo_query -> bind_col( 3, \$accountcode);
$saldo_query -> bind_col( 4, \$minutos);
$saldo_query -> bind_col( 5, \$precio);
$saldo_query -> bind_col( 6, \$fecha_ingreso);
$saldo_query -> bind_col( 7, \$saldo_qtz);

if ( $saldo_query -> fetch() ) {
	$monto = $precio * $minutos;
	$monto = $gt -> format_price($monto);
	$saldo_qtz = $gt -> format_price($saldo_qtz);

	logit ( "$email - $name - $accountcode - $minutos - $precio - $fecha_ingreso - $saldo_qtz - $monto" );

	my $message = Email::MIME->create(
			header_str => [
				From => 'Numero Central <noreply@numerocentral.com>',
				To => $email,
				Subject => 'Mercado de Minutos: ' . $accountcode,
			],
			attributes => {
				encoding => 'quoted-printable',
				charset => 'ISO-8859-1',
				content_type => 'text/html',
			},
			body_str => 
				"Estimado(a) $name, tu oferta de <b>$minutos</b> minutos del d&iacute;a <b>$fecha_ingreso</b> ha sido comprada por " .
				"<b>$monto</b>, tu nuevo saldo en Quetzales es de <b>$saldo_qtz</b>, " .
				"el cual puedes usar para comprar minutos o pagar tu mensualidad.<br>" .
				"<br>Gracias por ser parte de la familia N&uacute;mero Central.<br>", 
		);

	use Email::Sender::Simple qw(sendmail);
	sendmail($message);
} else {
	#Offer not found.
}

sub logit{
        if($logging){
                @lt = localtime(time);
                print FILE strftime("%Y-%m-%d %T",,@lt) . " ";
                print FILE "$_[0]\n";
        }
}
