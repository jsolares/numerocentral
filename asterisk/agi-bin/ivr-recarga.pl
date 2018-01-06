#!/usr/bin/perl
#
#    ivr-recarga.pl
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
#    AGI for loading minutes into accounts via an IVR
#	Used by resellers, no longer actually in use in production
#

use Asterisk::AGI;
use DBI;
use DBD::mysql;
use POSIX;

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

$AGI = new Asterisk::AGI;
%input = $AGI->ReadParse();
$AGI->setcallback(\&disconnect);

$callerid = $input{'callerid'};

$AGI -> verbose ("ivr-recarga.pl Llamando a IVR-Recarga : $callerid" );

$vendedor_qry = $dbh -> prepare ("SELECT id,
					 saldo
				    FROM vendedores
				   WHERE numero_recarga = ?");

$cliente_qry = $dbh -> prepare ("SELECT uid 
				   FROM users
				  WHERE id_vendedor = ?
				    AND accountcode = ?");

$cliente_existe = $dbh -> prepare ("SELECT count(*) 
				   FROM users
				  WHERE accountcode = ?");

$ingresar_cliente = $dbh -> prepare ("INSERT INTO users VALUES ( null, ?, md5(?), ?, ?, ?, 7, 0, NULL, NULL, 0, NULL, NULL, now(), NULL, ? )");
$update_cliente = $dbh -> prepare ("UPDATE saldo set saldo_minutos=saldo_minutos+?,fecha_ingreso_saldo=now() where uid = ?");
$update_vendedor = $dbh -> prepare ("UPDATE vendedores set saldo=saldo-? where id=? and numero_recarga = ?");
$ingresar_log = $dbh -> prepare ("INSERT INTO log_recarga_clientes values ( null, now(), ?, ?, ? )");

$vendedor_qry -> execute ( $callerid );
$vendedor_qry -> bind_col( 1, \$id_vendedor );
$vendedor_qry -> bind_col( 2, \$saldo );

if ( $vendedor_qry -> fetch() ) {
	$AGI -> answer();

	if ( $saldo > 0 ) {
		$continue = 1;
		do {
			undef $cliente;
			undef $existe;
			undef $id_cliente;
			undef $quetzales;
			undef $realizar;

			$cliente = $AGI -> get_data('ivr-recarga/intro',6000,8);
			if ( $cliente eq '' ) {
				$AGI -> verbose ("ivr-recarga.pl No ingreso numero");
				$AGI -> exec ('playback', 'ivr-recarga/datos-incorrectos');
				$continue++;
			} else {
				$AGI -> verbose ("ivr-recarga.pl Recargando el numero $cliente");
	
				$cliente_existe -> execute ( $cliente );
				$cliente_existe -> bind_col( 1, \$existe );
				$cliente_existe -> fetch();

				$cliente_qry -> execute ( $id_vendedor, $cliente );
				$cliente_qry -> bind_col( 1, \$id_cliente );
				if ( $cliente_qry -> fetch() || $existe eq '0') {
					$AGI -> verbose ("ivr-recarga.pl Cliente validado correctamente");
					$quetzales = $AGI -> get_data('ivr-recarga/monto',6000,3);
					if ( $quetzales eq '' ) {
						$AGI -> verbose ("ivr-recarga.pl No ingreso monto");
						$AGI -> exec ('playback', 'ivr-recarga/datos-incorrectos');
					} else {
						if ( $quetzales > $saldo ) {
							$AGI -> exec ('playback', 'ivr-recarga/nosaldo');
						} else {
							$AGI -> exec ('playback', 'ivr-recarga/recargar' );
							$AGI -> exec ('SayNumber', $quetzales );
							$AGI -> exec ('playback', 'ivr-recarga/quetzales' );
							$AGI -> exec ('playback', 'ivr-recarga/alnumero' );
							$AGI -> exec ('SayDigits', $cliente  );
	
							$realizar = $AGI -> get_data ('ivr-recarga/confirmacion', 6000, 1 );
							if ( $realizar eq '1' || $realizar eq '2' ) {
								$AGI -> verbose ("ivr-recarga.pl Realizando recarga en $cliente con monto $quetzales" );
								
								if ( $existe eq '0' ) {
									$AGI -> verbose ("ivr-recarga.pl Creando al cliente $cliente");
									$ingresar_cliente -> execute( $cliente, random_pass(8), $cliente, $cliente, $cliente, $id_vendedor );
									$cliente_qry -> execute ( $id_vendedor, $cliente );
									$cliente_qry -> bind_col( 1, \$id_cliente );
									$cliente_qry -> fetch();
								}
								
								$minutes = floor($quetzales * 15 / 10);
								$ingresar_log -> execute ( $id_vendedor, $cliente, $minutes );
								$update_vendedor -> execute ( $quetzales, $id_vendedor, $callerid );
								$update_cliente -> execute ( $minutes, $id_cliente);

								$AGI -> exec ('playback', 'ivr-recarga/recarga-exitosa');

								if ( $realizar eq '1' ) {
									$continue = 4;
								} else {
									$continue = 1;
									$AGI -> exec ( 'Wait', 1 );
								}	
							} elsif ( $realizar eq '3' ) {
								$AGI -> verbose ("ivr-recarga.pl Finalizando sin recarga" );
								$continue = 4;
							}
						}
					}
				} else {
					$AGI -> exec ('playback', 'ivr-recarga/datos-incorrectos');
					$AGI -> verbose ("ivr-recarga.pl Cliente no validado");
				}
			}

			#Refrescar saldo.
			$vendedor_qry -> execute ( $callerid );
			$vendedor_qry -> bind_col( 1, \$id_vendedor );
			$vendedor_qry -> bind_col( 2, \$saldo );
			$vendedor_qry -> fetch();

		} while ( $continue && $continue < 4);
	} else {
		$AGI -> exec ('playback', 'ivr-recarga/nosaldo');
	}
	
} else {
	$AGI -> exec ('playback', 'ivr-recarga/datos-incorrectos');
	$AGI -> verbose ("ivr-recarga.pl No se encontro vendedor con numero $callerid");
}

$AGI -> hangup();

sub random_pass {
	my $lenpass = shift;

	my @chars = ('a'..'z','A'..'Z','0'..'9','_');
	my $pass;

	foreach ( 1..$lenpass ) {
		$pass .= $chars[rand @chars];
	}
	
	return $pass;
}
