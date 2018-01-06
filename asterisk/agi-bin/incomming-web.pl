#!/usr/bin/perl
#
#    incomming-web.pl 
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
#    Incomming web call AGI
#

use Asterisk::AGI;
use DBI;
use DBD::mysql;

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

$AGI = new Asterisk::AGI;
%input = $AGI->ReadParse();
$AGI->setcallback(\&disconnect);

$accountcode = $AGI -> get_variable('CDR(accountcode)');
$callerid    = $AGI -> get_variable('OLDCALLERID');
$option_ivr  = int($AGI -> get_variable('IVR'));

$connplay_ivr = 0;

$phones = $dbh -> prepare( "SELECT number
			      FROM nc_mynumber
			     WHERE accountcode = ?
			  ORDER BY id
			     LIMIT 3" );

$modes  = $dbh -> prepare( "SELECT mode,
				   dialmode,
				   screen,
				   record,
				   blockanon,
				   playrecording,
				   connplay
			      FROM incomming_prefs
			     WHERE accountcode = ?" );

$recordsel = $dbh -> prepare( "SELECT record,
				      id_plan
				 FROM plans, users
				WHERE id_plan = plans.id
			       	  AND accountcode = ?" );

$bloqueo = $dbh -> prepare( "SELECT blocked
			       FROM contacts, groups
			      WHERE contacts.id_group = groups.id
				AND accountcode = ?
				AND contacts.number = ?" );

$ivraudio = $dbh -> prepare( "SELECT recording
				FROM ivr_audio
			       WHERE accountcode = ?");

$ivroption = $dbh -> prepare( "SELECT keypad,
				      number
				 FROM ivr_option
				WHERE accountcode = ?");

$bloqueo -> execute($accountcode, $callerid);
$bloqueo -> bind_col(1,\$blocked_contact);
if ( $bloqueo -> fetch() ) {
	if ( $blocked_contact ) {
		$AGI -> hangup();
	}
}

$recordsel -> execute($accountcode);
$recordsel -> bind_col(1,\$global_record);
$recordsel -> bind_col(2,\$id_plan);
$recordsel -> fetch();

$phones -> execute($accountcode);
$phones -> bind_col(1,\$number);

my @myphones;
my %ivr_options = ();

$mohtype = 'tr';

$isivr  = 0;
if ( $phones -> fetch() ) {
	$i = 0;
	do {
		$AGI -> verbose ($number . " - " . $callerid);
		$myphones[$i++] = $number;
	} while ( $phones -> fetch() );
} else {
	# No tiene numeros asignados, Error!!
	if ( $accountcode eq '24584680' || $id_plan eq '5' || $id_plan eq '6' || $id_plan eq '3' || $id_plan eq '4' || $id_plan eq '15' ) {
		$isivr = 1;
	} else {
		$AGI -> verbose ("No tiene numeros!");
		$AGI -> hangup();
		exit -1;
	}
}

if ( $accountcode eq '24584680' || $id_plan eq '5' || $id_plan eq '6' || $id_plan eq '3' || $id_plan eq '4' || $id_plan eq '15' ) {
	$isivr = 1;
}

#if ( $ismine eq '0' ) {
#	for ( $i = 0; $i < scalar(@myphones); $i++ ) {
#		$AGI -> verbose ("/var/lib/asterisk/agi-bin/send_mqtt.php " . $myphones[$i] . " $callerid");
#		system("/var/lib/asterisk/agi-bin/send_mqtt.php " . $myphones[$i] . " $callerid");
#	}
#}

$modes -> execute($accountcode);
$modes -> bind_col(1,\$mode);
$modes -> bind_col(2,\$dialmode);
$modes -> bind_col(3,\$screen);
$modes -> bind_col(4,\$record);
$modes -> bind_col(5,\$blockanon);
$modes -> bind_col(6,\$playrecord);
$modes -> bind_col(7,\$connplay);

if ( $callerid eq '' && $blockanon eq '1') {
	$AGI -> hangup();
	$AGI -> verbose ("Bloqueando anonimos!");
	exit -1;
}

$manual = 0;

if ( $accountcode eq '24584680' ) {
	$isivr = 1;
	$manual = 1;
	$try = 0;

	$AGI -> answer();

	while ( $try < 3 && $dialstr eq '' ) {
		$option = $AGI -> get_data('ivr/CutinoIntro',6000,1);
		$AGI -> verbose ("Option $option");
		if ( $option eq '1' ) {
			$option_en = $AGI -> get_data('ivr/CutinoIngles',6000,1);

			if ( $option_en eq '1' ) {
				$dialstr = "DAHDI/g1/51481211,60,$mohtype";
			} elsif ( $option_en eq '2' ) {
				$dialstr = "DAHDI/g1/51484651,60,$mohtype";
			} elsif ( $option_en eq '3' ) {
				$dialstr = "DAHDI/g1/51481446,60,$mohtype";
			}
		} elsif ( $option eq '2' ) {
			$option_es = $AGI -> get_data('ivr/CutinoEspanol',6000,1);

			if ( $option_es eq '1' ) {
				$dialstr = "DAHDI/g1/51481211,60,$mohtype";
			} elsif ( $option_es eq '2' ) {
				$dialstr = "DAHDI/g1/51484651,60,$mohtype";
			} elsif ( $option_es eq '3' ) {
				$dialstr = "DAHDI/g1/51481446,60,$mohtype";
			} elsif ( $option_es eq '0' ) {	
				$dialstr = "DAHDI/g1/51481211,60,$mohtype";
			}
		}
		$try++;
	}
	if ( $dialstr eq '' ) {
		#Error
		$AGI -> hangup;
		exit -1;
	} else {
		$AGI -> set_variable("DIALSTR0", $dialstr);
	}
}

if (( $isivr eq '1') && $manual eq '0' ) {
	$ivraudio -> execute($accountcode);
	$ivraudio -> bind_col(1, \$ivr_recording);
	if ( $ivraudio -> fetch() ) {
		$ivroption -> execute($accountcode);
		$ivroption -> bind_col(1, \$ivr_keypad);
		$ivroption -> bind_col(2, \$ivr_number);

		$i = 0;

		while ( $ivroption -> fetch() ) {
			$ivr_options{$ivr_keypad} = $ivr_number;

			$AGI -> verbose ($ivr_keypad . ":" . $ivr_number . " - " . $callerid);

			$myphones[$i++] = $number;
		}

		$modes -> fetch();

		if ( ! $ivr_options{$option_ivr} eq '' ) {
			$dialstr = "DAHDI/g1/" . $ivr_options{$option_ivr} . ",60,$mohtype";
			$AGI -> set_variable("DIALSTR0", $dialstr);
			$AGI -> set_variable("IVR", $option_ivr);
			$try = 3;
		} else {
			exit;
		}
	} else {
		#No Hay Grabacion
		exit;
	}
}

$modes -> execute($accountcode);
$modes -> bind_col(1,\$mode);
$modes -> bind_col(2,\$dialmode);
$modes -> bind_col(3,\$screen);
$modes -> bind_col(4,\$record);
$modes -> bind_col(5,\$blockanon);
$modes -> bind_col(6,\$playrecord);
$modes -> bind_col(7,\$connplay);

if ( $modes -> fetch() ) {
	$mohtype = 'tr';
	if ( $accountcode eq '24584711' ) {
		$mohtype = "tm(" . $accountcode . ')';
	}
	if ( $global_record && ($record eq '1' || $record eq '3')) {
		$AGI->set_variable("RECORDOUT","1");
	} else {
		$AGI->set_variable("RECORDOUT","0");
	}
	if ( $global_record && ($record eq '1' || $record eq '2')) {
		$AGI->set_variable("RECORD","1");
	} else {
		$AGI->set_variable("RECORD","0");
	}
	if ( $accountcode eq '24584655' ) {
		$AGI->set_variable("RECORDOUT","1");
		$AGI->set_variable("RECORD","1");
	}

	if ( $dialmode eq '1' && $isivr eq '0') {
		$AGI -> set_variable("DIALSERIAL","1");
		for ( $i = 0; $i < @myphones; $i++ ) {
			$AGI->set_variable("DIALSTR" . $i,"DAHDI/g1/" . $myphones[$i] . ",60," . $mohtype );
		}
	} elsif ( $dialmode eq '2' && $isivr eq '0') {
		$dialstr = "";
		if ( scalar(@myphones) > 1 ) {
			$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . ",60," . $mohtype;
			if ( scalar(@myphones) > 2 ) {
				$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . "&DAHDI/g1/" . $myphones[2] . ",60," . $mohtype;
			}
		} else {
			$dialstr = "DAHDI/g1/" . $myphones[0] . ",60," . $mohtype;
		}
		if ( $accountcode eq '24584711' ) {
#			$dialstr = "IAX2/jsolares&" . $dialstr;
		}
		$AGI -> set_variable("DIALSTR0", $dialstr);
	}	

	if ( $screen eq '1' && $dialmode eq '2' && $isivr eq '0') {
		if ( scalar(@myphones) > 1 ) {
			$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . ",60," . $mohtype . "M(screen^$callerid)";
			if ( scalar(@myphones) > 2 ) {
				$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . "&DAHDI/g1/" . $myphones[2] . ",60," . $mohtype . "M(screen^$callerid)";
			}
		} else {
			$dialstr = "DAHDI/g1/" . $myphones[0] . ",60," . $mohtype . "M(screen^$callerid)";
		}
		$AGI -> set_variable("DIALSTR0", $dialstr);
	}
}

exit 1;

sub disconnect() {
	$AGI -> hangup;
	exit 0;
}
