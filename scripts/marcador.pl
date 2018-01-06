#!/usr/bin/perl
#
#    marcador.pl
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
#    Dialer, used for customer campaings to their customers
#	uses $maxcalls channels at most
#

use DBI;
use Time::HiRes qw( usleep );
use Date::Format;
use Audio::Wav;
use POSIX;

$|++;

$hostname = "localhost";
$database = "numerocentral";
$username = "root";
$password = "";
$maxcalls = 4;

$dbh = DBI->connect("dbi:mysql:dbname=$database;host=$hostname", "$username", "$password");


$marcador_audio  = $dbh -> prepare ( "select recording, amd from marcador_audio where accountcode = ?");
$marcador_events = $dbh -> prepare ( "SELECT id_event, 
					     marcador_event.accountcode, 
					     name 
					FROM marcador_event, marcador_audio 
				       WHERE marcador_event.accountcode = marcador_audio.accountcode 
					 AND status in (0,1) 
					 AND time_to_sec(TIMEDIFF(inicio, now())) < 0 
					 AND time_to_sec(subtime(hora_inicio,time(now()))) < 0 
					 AND time_to_sec(subtime(hora_fin,time(now()))) > 0;");
$marcador_update = $dbh -> prepare ( "update marcador_event set status=? where id_event=?");
$marcador_number = $dbh -> prepare ( "select id, number from marcador_event_detail where status = 0 and id_event = ? LIMIT 10" );
$marcador_qty    = $dbh -> prepare ( "select count(*) from marcador_event_detail where status = 0 and id_event = ?" );
$marcador_numupd = $dbh -> prepare ( "update marcador_event_detail set status=1, status_date=now() where id = ?");
$marcador_status = $dbh -> prepare ( "select status from marcador_event where id_event = ?" );
$marcador_get_saldo = $dbh -> prepare ( "SELECT saldo_minutos
					   FROM saldo, users
					  WHERE saldo.uid = users.uid and accountcode = ?" );

$pass = 0;
do {
	open FILE, ">>/var/log/marcador.log";
	$get_calls = "/usr/sbin/asterisk -rx \"core show channels verbose\" | grep marcador | wc -l";
	$calls = qx/$get_calls/;
	chomp($calls);
	usleep(500000);

	logit("Going for pass #$pass, with $calls call(s).");
	if ( $calls <= $maxcalls ) {
		$marcador_events -> execute();
		$marcador_events -> bind_col(1, \$id_event);
		$marcador_events -> bind_col(2, \$accountcode);
		$marcador_events -> bind_col(3, \$name);

		while ( $marcador_events -> fetch() ) {
			$event_calls = 0;
			$marcador_update -> execute ( 1, $id_event );

			$marcador_qty -> execute ( $id_event );
			$marcador_qty -> bind_col(1, \$qtycalls);
			$marcador_qty -> fetch();

			if ( !$qtycalls ) {
				$marcador_update -> execute ( 2, $id_event );
				next;
			}

			$marcador_audio -> execute ( $accountcode );
			$marcador_audio -> bind_col(1, \$audio);
			$marcador_audio -> bind_col(2, \$useamd);
			$marcador_audio -> fetch();

			if ( $useamd eq '1' )  {
				$useamd = "YES";
			} else {
				$useamd = "NO";
			}

			$audio_file = '/var/lib/asterisk/sounds/marcador/' . $audio . '.wav';
			$wav = new Audio::Wav;
			$read_file = $wav -> read ( $audio_file );
			$duration_sec = $read_file -> length_seconds();
			$duration = ceil($duration/60);
			if ( $duration_sec > 0 && $duration == 0 ) {
				$duration = 1;
			}

			$marcador_get_saldo -> execute ( $accountcode );
			$marcador_get_saldo -> bind_col( 1, \$saldo_minutos );
			$marcador_get_saldo -> fetch();

			$get_calls = "/usr/sbin/asterisk -rx \"core show channels verbose\" | grep marcador | wc -l";
			$calls = qx/$get_calls/;
			chomp($calls);
			usleep(500000);

			logit("Found event $id_event, $name for $accountcode, audio length : $duration_sec, $duration min(s). saldo : $saldo_minutos, calls: $calls");

			if (( $calls <= $maxcalls)  && ( $saldo_minutos > $duration )) {
				$marcador_number -> execute ( $id_event );
				$marcador_number -> bind_col(1, \$id_call);
				$marcador_number -> bind_col(2, \$number);

				while ( $marcador_number -> fetch() ) {
					$marcador_status -> execute( $id_event );
					$marcador_status -> bind_col(1, \$event_status );
					$marcador_status -> fetch();
					if ( $event_status eq '3' ) {
						logit("Event canceled, passing over call $id_event, $number");
					} else {
						if ( (length($number) == 8) && ($calls <= $maxcalls) && ( $saldo_minutos > $duration ) ) {
							open ( fileOUT, ">/tmp/$id_call.call" );
							print fileOUT "Channel: DAHDI/g1/$number\n";
							print fileOUT "CallerID: $accountcode\n";
							print fileOUT "MaxRetries: 2\n";
							print fileOUT "RetryTime: 60\n";
							print fileOUT "WaitTime: 30\n";
							print fileOUT "Account: $accountcode\n";
							print fileOUT "Context: marcador\n";
							print fileOUT "Extension: $accountcode\n";
							print fileOUT "Priority: 1\n";
							print fileOUT "Set: OLDCALLERID=$number\n";
							print fileOUT "Set: DIALEDNUMBER=$number\n";
							print fileOUT "Set: USEAMD=$useamd\n";
							print fileOUT "Set: IDEVENT=$id_event\n";
							print fileOUT "Set: IDCALL=$id_call\n";
							print fileOUT "Archive: Yes\n";
							close (fileOUT);

							$initiatecall = "mv /tmp/$id_call.call /var/spool/asterisk/outgoing";
							$result = qx/$initiatecall/;

							$marcador_numupd -> execute ( $id_call );
							$saldo_minutos -= $duration;

							logit("Making call for $id_event, $number, calls: $calls");
						}
					}
					usleep(5000000);
					$get_calls = "/usr/sbin/asterisk -rx \"core show channels verbose\" | grep marcador | wc -l";
					$calls = qx/$get_calls/;
					chomp($calls);
					usleep(500000);
				}
			}
			sleep (3);
		}
		sleep(3);
	}
	$pass++;
	close FILE;
	usleep(25000000);
} while ( $pass < 1000 );


exit 1;

sub logit{
        @lt = localtime(time);
        print FILE strftime("%e-%m-%Y %T",,@lt) . " ";
        print FILE "$_[0]\n";
}
