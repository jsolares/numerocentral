#!/usr/bin/perl
#
#    saldo.pl
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
#    AGI that plays back to the user how much minutes they have
#	uses Cepstral to generate audio messages and caches it
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

$saldo_query = $dbh -> prepare ("SELECT saldo_minutos
				   FROM users, 
					saldo
				  WHERE users.uid = saldo.uid
				    AND accountcode = ?" );

$audio = tts_cepstral( "Usted esta utilizando Numero Central, pero no tiene saldo disponible para realizar la llamada.");
$accountcode = $AGI -> get_variable('CDR(accountcode)');

$saldo_query -> execute ( $accountcode );
$saldo_query -> bind_col( 1, \$saldo);

if ( $saldo_query -> fetch() ) {
	if ($saldo > 0 ) {
		$audio = tts_cepstral( "Su saldo es de $saldo minutos.");
		$AGI -> exec ( 'playback', $audio);
		$AGI -> verbose ("User has $saldo minutes left.");
		disconnect();
	} else {
		$AGI -> exec ( 'playback', $audio);
		$AGI -> verbose ("User has no minutes left.");
		disconnect();
	}
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

