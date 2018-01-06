#!/usr/bin/perl
#
#    saldo-cron.pl
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
#    CRON used to invalidate minutes if they have no remaining days
#

use DBI;
use DBD::mysql;
use Date::Format;

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";
$logging  = 1;
$minutos  = 50;
$precio   = 0.4;

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

if($logging){ open FILE, ">>/var/log/saldo-cron.log";}

$saldo_query = $dbh -> prepare ("SELECT users.uid,
					saldo_minutos
				   FROM users,
					plans,
					saldo
				  WHERE id_plan = plans.id
				    AND users.uid = saldo.uid
				    AND saldo_minutos > 0
				    AND datediff(now(), fecha_ingreso_saldo) > valid_days");

$precio_query = $dbh -> prepare ("SELECT round(sum(minutos*precio)/sum(minutos),2) 
                                    FROM mminutos 
                                   WHERE estado=0");

$mercado_add = $dbh -> prepare ("INSERT INTO mminutos values ( null, ?, ?, ?, now(), 0, 0 )");

$saldo_update= $dbh -> prepare ("UPDATE saldo
				    SET saldo_minutos = 0,
					fecha_saldo_vencido = now(),
					saldo_vencido = ?
				  WHERE uid = ?");

$precio_query -> execute();
$precio_query -> bind_col(1, \$precio );
$precio_query -> fetch();

if ( $precio > 0.35 ) {
	$precio = 0.35;
}

$saldo_query -> execute ();
$saldo_query -> bind_col( 1, \$uid);
$saldo_query -> bind_col( 2, \$saldo);

while ( $saldo_query -> fetch() ) {
	logit( "UID: $uid, $saldo");
	$saldo_update -> execute($saldo, $uid);
	while ( $saldo - $minutos > 0 ) {
		logit( "\t$uid, $minutos, $precio");
		$mercado_add -> execute($uid, $minutos, $precio);
		$saldo -= $minutos;
	}
	logit( "\t$uid, $saldo, $precio");
	$mercado_add -> execute($uid, $saldo, $precio);
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
