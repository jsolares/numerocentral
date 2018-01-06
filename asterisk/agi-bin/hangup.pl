#!/usr/bin/perl
#
#    hangup.pl
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
#    Hangup AGI, email users if they have missed call email configured
#	and decrease their available minutes.
#

use Asterisk::AGI;
use DBI;
use DBD::mysql;
use POSIX qw(ceil floor);

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

$AGI = new Asterisk::AGI;
%input = $AGI->ReadParse();
$AGI->setcallback(\&disconnect);

$update = $dbh -> prepare( "UPDATE saldo
			       SET saldo_minutos = saldo_minutos - ?
			     WHERE uid = ?" );

$getacc = $dbh -> prepare( "SELECT uid
			      FROM users
			     WHERE accountcode = ?" );

$accountcode = $AGI -> get_variable('CDR(accountcode)');
$userfield   = $AGI -> get_variable('CDR(userfield)');
$callerid    = $AGI -> get_variable('OLDCALLERID');
$billsec     = $AGI -> get_variable('CDR(billsec)');
$uniqueid    = $AGI -> get_variable('CDR(uniqueid)');
$dialednum   = $AGI -> get_variable('CALLEDNUMBER');
$callback    = $AGI -> get_variable('CALLBACK');
$disposition = $AGI -> get_variable('CDR(disposition)');
$email       = $AGI -> get_variable('EMAIL');
$sendemail   = $AGI -> get_variable('SENDEMAIL');
$missemail   = $AGI -> get_variable('MISSEMAIL');

if ( $accountcode eq '77589595' ) {
	$email .= ', ideas@bbqantigua.com';
}

if ( $accountcode eq '24584454' ) {
	$email .= ', marleni.espinoza@unicarga.com';
}

$getacc -> execute($accountcode);
$getacc -> bind_col( 1, \$uid );
$getacc -> fetch();

$AGI -> verbose ("Call on $accountcode for $callerid with duration $billsec , uid $uid - userfield: $userfield");

if ( $userfield =~ /callback:.*/ ) {
	$minutos = ceil($billsec/60);
	$AGI -> verbose ("User called $dialednum and will be billed $minutos");
	$update -> execute( $minutos, $uid);
} elsif ($userfield =~ /callback-favorite:.*/ )  {
	$minutos = ceil($billsec/60);
	$AGI -> verbose ("User called $dialednum and will be billed $minutos");
	$update -> execute( $minutos, $uid);
} elsif ($userfield =~ /memo:.*/ )  {
	$minutos = ceil($billsec/60);
	$AGI -> verbose ("User called $dialednum and will be billed $minutos");
	$update -> execute( $minutos, $uid);
} elsif ($userfield =~ /marcador:.*/ ) {
	$minutos = ceil($billsec/60);
	if ( $billsec > 0 && $minutos eq '0' ) {
		$minutos = 1;
	}
	$AGI -> verbose ("User called $dialednum and will be billed $minutos");
	$update -> execute( $minutos, $uid);
} else {
}

if ( ($disposition eq 'NO ANSWER' && $callback eq '0') || $sendemail ) {
	#We send email to $mail lost call.
	if ( $missemail eq '1' ) {
		use Email::MIME;
		use HTML::Entities;
		use DateTime;

		$fecha = DateTime->now(qw[ time_zone local ], locale => 'es' )->strftime(' %A, %d de %B del %Y a las %H:%M:%S.');
		$fecha = encode_entities($fecha);

		$contacts = $dbh -> prepare("SELECT name,
						    email
					       FROM contacts, groups
					      WHERE contacts.id_group = groups.id
						AND accountcode = ?
						AND REPLACE(number,'-','') = ?");

		$contacts -> execute($accountcode, $callerid);
		$contacts -> bind_col(1,\$contact_name);
		$contacts -> bind_col(2,\$contact_email);
		if ( $contacts -> fetch() ) {
			if ( $contact_email ne '' ) {
				$contact = "<a href=\"mailto:$contact_email\">$contact_name</a>";
			} else {
				$contact = "$contact_name"
			}
		} else {
			$contact = "";
		}

		my $message = Email::MIME->create(
			header_str => [
				From => 'Numero Central <noreply@numerocentral.com>',
				To => $email,
				Subject => $accountcode . ', llamada perdida del ' . $callerid,
			],
			attributes => {
				encoding => 'quoted-printable',
				charset => 'ISO-8859-1',
				content_type => 'text/html',
			},
			body_str => "Tiene una llamada perdida de " . $contact . " " . $callerid . " el d&iacute;a $fecha<br/><br/>Favor no responder este correo.<br/><br/>Gracias por utilizar nuestro servicio.<br/><br/><div align=center><img src=\"https://www.numerocentral.com/images/logo-small.jpg\"><p><a href=\"https://www.numerocentral.com\">https://www.numerocentral.com</a><p>Equipo de Soporte N&uacute;mero Central<p>NC. 24584700</div>\n",
		);

		use Email::Sender::Simple qw(sendmail);
		sendmail($message);
	}
}

exit 1;

sub disconnect() {
	$AGI -> hangup;
	exit 0;
}
