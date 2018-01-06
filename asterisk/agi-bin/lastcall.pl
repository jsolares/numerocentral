#!/usr/bin/perl
#
#    lastcall.pl
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
#    Use TTS to tell a customer who called them last and when
#	Not longer used due to android and ios apps
#

use Asterisk::AGI;
use DBI;
use DBD::mysql;
use Digest::MD5 md5_hex;

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

$AGI = new Asterisk::AGI;
%input = $AGI->ReadParse();
$AGI->setcallback(\&disconnect);

$saldo_query = $dbh -> prepare ("SELECT calldate,
					src
				   FROM callrecords_table 
				  WHERE accountcode = ? 
				    AND src NOT IN ( select number from nc_mynumber where accountcode = ?) 
				    AND src <> ? 
			       ORDER BY calldate 
				   DESC 
				  LIMIT 1;");

$who_query = $dbh -> prepare ("SELECT name 
				 FROM groups, contacts 
				WHERE contacts.id_group = groups.id 
				  AND groups.accountcode = ?
				  AND number = ?");

$accountcode = $AGI -> get_variable('CDR(accountcode)');

$saldo_query -> execute ( $accountcode, $accountcode, $accountcode );
$saldo_query -> bind_col( 1, \$date);
$saldo_query -> bind_col( 2, \$src);

if ( $saldo_query -> fetch() ) {
	@fecha = split(/ /,$date);
	@time = split(/:/,$fecha[1]);
	@day = split(/-/,$fecha[0]);
	@months = ("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

	$text = "";

	$numero = "";
	for ( $i = 0; $i < length($src); $i++ ) {
		$numero .= substr($src, $i, 1 ) . "  <break time='1100ms' /> ";
	}

	$text = "La ultima llamada recibida fue del numero $numero a las " . $time[0] . " horas " . $time[1] . " minutos " . $time[2] . 
		" segundos, el " . $day[2] . " de " . $months[$day[1]-1] . " del " . $day[0];

	$audio = tts_cepstral( $text );
	$AGI -> verbose ($text);
	$AGI -> exec ( 'playback', $audio);
	disconnect();
} else {
	disconnect();
}

exit 1;

sub disconnect() {
	$AGI -> hangup;
	exit 0;
}

sub tts_cepstral() {
	$text = shift;

	my $sounddir    = "/var/lib/asterisk/sounds/tts/";
	my $hash 	= md5_hex($text);
	my $wavefile	= $sounddir . "tts-$hash.wav";

	unless ( -f $wavefile ) {
		open ( fileOUT, ">/tmp/text-$hash.txt" );
		print fileOUT "$text";
		close ( fileOUT );

		$exectts = "/opt/swift/bin/swift -o $wavefile -f /tmp/text-$hash.txt";
		$result  = qx/$exectts/;

	}

	return "tts/tts-$hash";
}

