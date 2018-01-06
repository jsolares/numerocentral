#!/usr/bin/perl 
#
#    transpagos.pl
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
#    Integration with Transpagos webapp for reloading time for
#	Claro, Tigo and Telefonica in Guatemala
#

use SOAP::Lite;
#use SOAP::Lite +trace;
use DBI;
use DBD::mysql;
use Switch;
use DateTime::HiRes qw();
#use Term::ANSIColor;

sleep(60);

$|++;

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";

$cajaID  = '';
$cajaIP  = '';
$serie   = '';
$usuario = '';

our $DSN = "DBI:mysql:database=$database;host=$hostname";
our $dbh = DBI->connect_cached ($DSN, $user, $password) or die "Error DB!";

$ENV{PERL_LWP_SSL_VERIFY_HOSTNAME} = 0;

our $conn = SOAP::Lite
    -> uri('http://tempuri.org')
    -> proxy('https://www.transpagos.com/transpagos.asmx', ssl_opts => [ SSL_verify_mode => 0 ])
    -> default_ns('http://tempuri.org/');
#    -> default_ns('http://tempuri.org');

$conn->{_transport}->{_proxy}->{ssl_opts}->{verify_hostname} = 0;
$conn->on_action(sub{join '',@_ });

$pCajaId   = SOAP::Data->new(name => 'pCajaId', value => $cajaID, attr => {type => undef, 'xsi:type' => undef});
$pIpCaja   = SOAP::Data->new(name => 'pIpCaja', value => $cajaIP, attr => {type => undef, 'xsi:type' => undef});
$pSerie    = SOAP::Data->new(name => 'pSerie', value => $serie, attr => {type => undef, 'xsi:type' => undef});
$pUsuario  = SOAP::Data->new(name => 'pUsuario', value => $usuario, attr => {type => undef, 'xsi:type' => undef});
$pValor    = SOAP::Data->new(name => 'pValor', value => '0', attr => {type => undef, 'xsi:type' => undef});

$timestr = DateTime::HiRes->now(time_zone => 'local')->strftime('%F %T.%5N');
print "$timestr Iniciando script\n";

while() {
	$dbh = DBI->connect_cached ($DSN, $user, $password) or die "Error DB!";

	$pagos  = $dbh -> prepare( "SELECT id, accountcode, uid, empresa, producto, celular, monto, estado FROM transpagos WHERE estado = 0" );
	$update_pagos = $dbh -> prepare( "UPDATE transpagos SET estado = ?, transaccion = ?, result = ?, proceso = now() where id = ?");
	$update_saldo = $dbh -> prepare( "UPDATE saldo SET saldo_qtz=saldo_qtz+? where uid=?");
	$check  = $dbh -> prepare( "SELECT count(*) from users where accountcode = ? and uid = ?" );

	$pagos -> execute();
	$pagos -> bind_col( 1, \$id_pago );
	$pagos -> bind_col( 2, \$accountcode );
	$pagos -> bind_col( 3, \$uid );
	$pagos -> bind_col( 4, \$empresa );
	$pagos -> bind_col( 5, \$producto );
	$pagos -> bind_col( 6, \$celular );
	$pagos -> bind_col( 7, \$monto );
	$pagos -> bind_col( 8, \$estado );
	while ( $pagos -> fetch() ) {
		# Verificar que uid y account hagan match.
		$check -> execute( $accountcode, $uid );
		$check -> bind_col( 1, \$qty );
		$check -> fetch();
		
		if ( $qty ) {
			$process = 0; #Verificar que el monto y el producto hagan match:
			switch ( $empresa ) {
				case 1 { #Claro
					if ( $producto eq '110760' && int($monto) eq '5'  ) { $process = 1; }
					if ( $producto eq '110453' && int($monto) eq '10' ) { $process = 1; }
					if ( $producto eq '110454' && int($monto) eq '25' ) { $process = 1; }
					if ( $producto eq '110455' && int($monto) eq '50' ) { $process = 1; }
				}
				case 2 { #Telefonica
					if ( $producto eq '107672' && int($monto) eq '5'  ) { $process = 1; }
					if ( $producto eq '107673' && int($monto) eq '10' ) { $process = 1; }
					if ( $producto eq '107675' && int($monto) eq '25' ) { $process = 1; }
					if ( $producto eq '107676' && int($monto) eq '50' ) { $process = 1; }
				}
				case 3 { #Tigo
					if ( $producto eq '110762' && int($monto) eq '5'  ) { $process = 1; }
					if ( $producto eq '107802' && int($monto) eq '10' ) { $process = 1; }
					if ( $producto eq '107803' && int($monto) eq '25' ) { $process = 1; }
					if ( $producto eq '107804' && int($monto) eq '50' ) { $process = 1; }
				}
			}

			if ( $process eq 1 ) {
				$pProducto = SOAP::Data->new(name => 'pProducto', value => $producto, attr => {type => undef, 'xsi:type' => undef});
				$pNumero   = SOAP::Data->new(name => 'pNumero', value => $celular, attr => {type => undef, 'xsi:type' => undef}); 
				$pFactura  = SOAP::Data->new(name => 'pFactura', value => $id_pago, attr => {type => undef, 'xsi:type' => undef});

				if ( $estado eq '0' ) {
					$params = SOAP::Data->type('complex')->value($pCajaId, $pIpCaja, $pProducto, $pNumero, $pValor, $pSerie, $pFactura, $pUsuario);
	
					$result = $conn -> PagoServicioConValor($params)->result;
					( $codigo, $desc, $transaccion ) = split (/\|/, $result, 3);

					switch ( $codigo ) {
						case '00' { #aprobado
							$update_pagos -> execute(2, $transaccion, $desc, $id_pago);
						}
						case '98' { #reintentar
							$update_saldo -> execute($monto, $uid);
							#regresar saldo qtz.
							$update_pagos -> execute(1, $transaccion, $desc, $id_pago);
						}
						else { #cancelado
							$update_saldo -> execute($monto, $uid);
							#regresar saldo qtz.
							$update_pagos -> execute(3, $transaccion, $desc, $id_pago);
						}
					}

					$timestr = DateTime::HiRes->now(time_zone => 'local')->strftime('%F %T.%5N');
					print "$timestr $codigo : $desc : $transaccion\n";

				} elsif ( $estado eq '1' ) {
					$params = SOAP::Data->type('complex')->value($pCajaId, $pIpCaja, $pSerie, $pFactura);

					$result = $conn -> VerificaTransaction($params)->result;
				
					$timestr = DateTime::HiRes->now(time_zone => 'local')->strftime('%F %T.%5N');
					print "$result\n";
				}
			} else {
				$timestr = DateTime::HiRes->now(time_zone => 'local')->strftime('%F %T.%5N');
				print "$timestr Valores invalidos hay que regresar el saldo.\n";
			}
		} else {
			$timestr = DateTime::HiRes->now(time_zone => 'local')->strftime('%F %T.%5N');
			print "$timestr No hay match $accountcode y $uid.\n";
			$update_pagos -> execute( -1, $id_pago );
		}
	}
	sleep(10);
}

$dbh -> disconnect;
