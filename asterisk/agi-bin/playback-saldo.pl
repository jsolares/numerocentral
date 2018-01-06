#!/usr/bin/perl
#
#    admin_catalogo_clientes.php
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
#    Tell the user how much minutes and valid days he has
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

$saldo_query = $dbh -> prepare ("SELECT saldo_minutos,
					valid_days - datediff(now(), fecha_ingreso_saldo)
				   FROM users, plans, saldo
				  WHERE id_plan = plans.id
				    AND users.uid = saldo.uid
				    AND accountcode = ?" );

$accountcode = $AGI -> get_variable('CDR(accountcode)');

$saldo_query -> execute ( $accountcode );
$saldo_query -> bind_col( 1, \$saldo);
$saldo_query -> bind_col( 2, \$dias);

$AGI -> answer();

if ( $saldo_query -> fetch() ) {
	if ($saldo > 0 ) {
		$AGI -> verbose ("User has $saldo minutes left, and $dias day(s) to use it.");
		$AGI -> exec ( 'playback', 'bienvenido' );
		$AGI -> exec ( 'SayNumber', $saldo );
		$AGI -> exec ( 'playback', 'minutos' );
		$AGI -> exec ( 'playback', 'saldo' );
		$AGI -> exec ( 'SayNumber', $dias);
		$AGI -> exec ( 'playback', 'dias' );
		disconnect();
	} else {
		$AGI -> exec ( 'playback', 'mensaje-saldo-insuficiente');
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
