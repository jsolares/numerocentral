#!/usr/bin/perl
#
#    verifica-saldo.pl
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
#    Checks if the user has any remaining minutes left and if not plays back the audio for insufficient time.
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

$accountcode = $AGI -> get_variable('CDR(accountcode)');
$accountcode = "$accountcode";

$primerdigito = substr($accountcode, 0, 1);
$AGI->verbose("Tenemos $accountcode verificar primer digito : " . $primerdigito);

$AGI->verbose("Antes del query");
$saldo_query -> execute ( $accountcode );
$saldo_query -> bind_col( 1, \$saldo);

if ( $saldo_query -> fetch() ) {
	if ($saldo > 0 ) {
		if ( $saldo > 60 ) {
			$saldo = 60;
		}
		$tiempo = $saldo * 60 * 1000;
		$AGI -> set_variable("LIMIT", $tiempo);
		$AGI -> verbose ("Setting Time limit to $tiempo ( $saldo min. )");
	} else {
		$AGI -> exec ( 'playback', 'mensaje-saldo-insuficiente,noanswer');
		$AGI -> verbose ("User has no minutes left.");
		disconnect();
	}
} else {
	if ( $primerdigito eq '3' || $primerdigito eq '4' || $primerdigito eq '5' ) {
		$saldo = 15;
		$tiempo = $saldo * 60 * 1000;
		$AGI -> set_variable("LIMIT", $tiempo);
		$AGI -> verbose ("Setting Time limit to $tiempo ( $saldo min. )");
		$AGI->set_variable ("NUMBER", $accountcode);
	} else {
		disconnect();
	}
}

exit 1;

sub disconnect() {
	$AGI -> hangup;
	exit 0;
}
