#!/usr/bin/perl
#
#    incomming.pl
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
#    Incomming call agi, sets several variables including flags for recording, etc.
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

$connplay_ivr = 0;

$phones = $dbh -> prepare( "SELECT number
			      FROM nc_mynumber
			     WHERE accountcode = ?
			  ORDER BY id
			     LIMIT 4" );

$modes  = $dbh -> prepare( "SELECT mode,
				   dialmode,
				   screen,
				   record,
				   blockanon,
				   playrecording,
				   connplay,
				   missemail,
				   disabled 
			      FROM incomming_prefs
			     WHERE accountcode = ?" );

$recordsel = $dbh -> prepare( "SELECT record,
				      id_plan,
				      email
				 FROM plans, users
				WHERE id_plan = plans.id
			       	  AND accountcode = ?" );

$bloqueo = $dbh -> prepare( "SELECT blocked
			       FROM contacts, groups
			      WHERE contacts.id_group = groups.id
				AND accountcode = ?
				AND contacts.number = ?" );

$ivrooaudio = $dbh -> prepare( "SELECT recording
				FROM ivr_ooaudio
			       WHERE accountcode = ?");


$ivraudio = $dbh -> prepare( "SELECT recording,
				     hora_inicio,
				     hora_fin
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
$recordsel -> bind_col(3,\$email);
$recordsel -> fetch();

$AGI -> verbose("Email : $email");
$AGI -> set_variable("EMAIL", $email);
$AGI -> set_variable("CALLBACK",0);
$AGI -> set_variable("SENDEMAIL",0);

$phones -> execute($accountcode);
$phones -> bind_col(1,\$number);

my @myphones;
my %ivr_options = ();

$mohtype = 'tr';

$ismine = 0;
$isivr  = 0;
$hasfour = 0;

# Set flag for manually configured IVR
$ivrmanual = ( $accountcode eq '24584700' );

if ( $phones -> fetch() ) {
	$i = 0;
	do {
		$AGI -> verbose ($number . " - " . $callerid);
		if ( $number eq $callerid ) {
			$ismine = 1;
		}
		if ( $number eq '59790489' ) {
			$AGI -> verbose ("Skipping number $number");
		} else {
			$myphones[$i++] = $number;
		}
	} while ( $phones -> fetch() );
} else {
	# No tiene numeros asignados, Error!!
	if ( $ivrmanual || $id_plan eq '5' || $id_plan eq '6' || $id_plan eq '3' || $id_plan eq '4' || $id_plan eq '15' || $id_plan eq '16' || $id_plan eq '17' || $id_plan eq '18' ) {
		$isivr = 1;
	} else {
		$AGI -> verbose ("No tiene numeros!");
		$AGI -> set_variable("SENDEMAIL", 1);
		$AGI -> exec ('goto', '25' );
		exit;
	}
}

if ( $ivrmanual || $id_plan eq '5' || $id_plan eq '6' || $id_plan eq '3' || $id_plan eq '4' || $id_plan eq '15' || $id_plan eq '16' || $id_plan eq '17' || $id_plan eq '18' ) {
	$isivr = 1;
}

if ( $callerid !~ /^502/ ) {
	if ( length($callerid) > 8 ) {
		if ( $accountcode eq '24584700' ) {
			#Allow international incomming call, due to local regulations we have to block these.
			$AGI -> verbose ("Llamada internacional permitida - $accountcode");
		} else {
			#Block international incomming call, due to local regulations we have to block these.
			$AGI -> verbose ("Llamada internacional a colgar");
		        $AGI -> set_variable("SENDEMAIL", 1);
		        $AGI -> exec ('goto', '25' );
		        exit;
		}
	}
} else {
	#Strip leading 502 from callerid, 502 is *our* country code.
	$callerid =~ s/^502//g;
	$AGI -> set_variable("OLDCALLERID",$callerid);
}

$modes -> execute($accountcode);
$modes -> bind_col(1,\$mode);
$modes -> bind_col(2,\$dialmode);
$modes -> bind_col(3,\$screen);
$modes -> bind_col(4,\$record);
$modes -> bind_col(5,\$blockanon);
$modes -> bind_col(6,\$playrecord);
$modes -> bind_col(7,\$connplay);
$modes -> bind_col(8,\$missemail);
$modes -> bind_col(9,\$disabled);

if ( $callerid eq '' && $blockanon eq '1') {
	$AGI -> hangup();
	$AGI -> verbose ("Bloqueando anonimos!");
	exit -1;
}

$manual = 0;

#manual incomming number with audio playback before connecting call.
if ( $accountcode eq '24584711' ) {
	$isivr = 1;
	$manual = 1;

	$AGI -> set_variable("MISSEMAIL", "$missemail");
	$AGI -> exec("Playback", "ivr/announcement-menu" );
	$dialstr = "DAHDI/g1/24584700,60,$mohtype";
	$AGI -> set_variable("DIALSTR0", $dialstr);
}

#Fully manual IVR with out of order (outside of work ours) and conference room
if ( $accountcode eq '24584700' ) {
	$isivr = 1;
	$manual = 1;
	$try = 0;

	$AGI -> set_variable("MISSEMAIL", "$missemail");

	$ivroption -> execute($accountcode);
        $ivroption -> bind_col(1, \$ivr_keypad);
        $ivroption -> bind_col(2, \$ivr_number);
        $i = 0;

	$ivraudio -> execute($accountcode);
	$ivraudio -> bind_col(1, \$ivr_recording);
	$ivraudio -> bind_col(2, \$hora_inicio );
	$ivraudio -> bind_col(3, \$hora_fin );
	$ivraudio -> fetch();
		
	$now = time();
	($hinicio, $minicio, $sinicio) = split (":", $hora_inicio);
	($hfin, $mfin, $sfin ) = split (":", $hora_fin);
	($sactual,$mactual,$hactual,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($now);

	$actual = $hactual*60*60 + $mactual*60 + $sactual;
	$inicio = $hinicio*60*60 + $minicio*60 + $sinicio;
	$fin = $hfin*60*60 + $mfin*60 + $sfin;

	$AGI -> verbose ("-- $hora_inicio a $hora_fin  : $actual , $inicio, $fin --");			

	$outoftime = 0;

	if ( $inicio < $fin ) { #Hora actual debe de estar dentro del rango.
		if ( $actual > $fin || $actual < $inicio ) { #ver si hay audio fuera de horario
			$AGI -> verbose ("Llamada fuera de horario");
			$outoftime = 1;
			$AGI -> set_variable("SENDEMAIL", 1);
			$AGI -> exec("Playback", "ivroo/24584700-menu");
			$AGI -> exec("Goto","26");
			exit;
		}
	} else { #Hora actual debe de estar fuera del rango.
		if ( $actual < $inicio && $actual > $fin ) { #ver si hay audio fuera de horario
			$AGI -> verbose ("Llamada fuera de horario");
			$outoftime = 1;
			$AGI -> set_variable("SENDEMAIL", 1);
			$AGI -> exec("Playback", "ivroo/24584700-menu");
			$AGI -> exec("Goto","26");
			exit;
		}
	}
        while ( $ivroption -> fetch() ) {
        	$ivr_options{$ivr_keypad} = $ivr_number;
	
		$AGI -> verbose ($ivr_keypad . ":" . $ivr_number . " - " . $callerid . " - " . $id_plan) ;

		if ( $ivr_number eq $callerid ) {
			$ismine = 1;
		}
	}

	$modes -> fetch();
	if ( $disabled eq '1' ) {
		if ( $ismine eq '0' ) {
			$AGI -> set_variable("SENDEMAIL", 1);
			$AGI -> answer ();
	
			while ( $try < 2 && $dialstr eq '' ) {
				$try++;
				$option = $AGI -> get_data('ivr/24584700-disabled',6000,1);
				if ( $option eq '1' ) {
					$try = 3;
					$AGI -> exec("Playback", "ivr/24584700-1" );
					$AGI -> exec("Playback", "ivr/24584700-1" );
					exit;
				} elsif ( $option eq '2' ) {
					$try = 3;
					$AGI -> exec("Playback", "ivr/24584700-2" );
					$AGI -> exec ('goto', '25' );
				}
			}
			exit;
		} else {
		}
	} else {
		$ivr_conference = "";

		$conferences = $dbh -> prepare ( "SELECT substr(confno,9,1) FROM conferences WHERE confno like '$accountcode%'");
		$conferences -> execute();
		$conferences -> bind_col(1, \$ivr_conference);
		$conferences -> fetch();	

		if ( $ismine eq '0' ) {
			$AGI -> answer ();
	
			while ( $try < 3 && $dialstr eq '' ) {
				$option = $AGI -> get_data('ivr/24584700-menu',6000,1);
				$AGI -> verbose ("Option $option");
				if ( $option eq '0' ) {
					$AGI -> set_variable("IVR", "0");
					$dialstr = "DAHDI/g1/24584711,90,$mohtype";
				} elsif ( $option eq '1' ) {
					$AGI -> set_variable("IVR", "1");
					$dialstr = "DAHDI/g1/24584711,90,$mohtype";
				} elsif ( $option eq '2' ) {
					$AGI -> set_variable("IVR", "2");
					$dialstr = "DAHDI/g1/24584711,90,$mohtype";
				} elsif ( $option eq '3' ) {
					$AGI -> set_variable("IVR", "3");
					$dialstr = "DAHDI/g1/24584711,90,$mohtype";
				} else {
					if ( $ivr_conference eq '' ) {
						$AGI -> set_variable("IVR", "3");
						$dialstr = "DAHDI/g1/24584711,90,$mohtype";
					} else {	
       	        	                 	if ( $option eq $ivr_conference) {
							$AGI -> verbose ("Cuarto de conferencia $ivr_conference");
							$AGI -> set_variable ("CONFNO", $ivr_conference );
							$AGI -> exec ('goto', 'conference' );
							exit;
						} else {
							$AGI -> set_variable("IVR", "3");
							$dialstr = "DAHDI/g1/24584711,90,$mohtype";
						}
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
	}
}

#IVR programmed configuration, sometime it would be nice to move all manual configurations here
if (( $isivr eq '1') && $manual eq '0' ) {
	$modes -> fetch();
	$ivraudio -> execute($accountcode);
	$ivraudio -> bind_col(1, \$ivr_recording);
	$ivraudio -> bind_col(2, \$hora_inicio );
	$ivraudio -> bind_col(3, \$hora_fin );

	if ( $disabled eq '1' && $ismine eq '0' ) {
		$AGI -> set_variable("SENDEMAIL", 1);

		if ( -e "/var/lib/asterisk/sounds/disabled/$accountcode.wav" ) {
			$AGI -> exec ("Playback", "disabled/$accountcode");
			$AGI -> exec ('goto', '26' );
		} else {
			$AGI -> exec ('goto', '25' );
		}
		exit;
	}

	if ( $ivraudio -> fetch() || $id_plan eq '16' ) {
		$ivroption -> execute($accountcode);
		$ivroption -> bind_col(1, \$ivr_keypad);
		$ivroption -> bind_col(2, \$ivr_number);

		$i = 0;

		while ( $ivroption -> fetch() ) {
			$ivr_options{$ivr_keypad} = $ivr_number;

			if (length($ivr_keypad) > 1) {
				$hasfour = 1;
			}

			$AGI -> verbose ($ivr_keypad . ":" . $ivr_number . " - " . $callerid . " - " . $id_plan) ;

			if ( $ivr_number eq $callerid ) {
				$ismine = 1;
			}
			$myphones[$i++] = $number;
		}

		$modes -> fetch();

		$AGI -> set_variable("MISSEMAIL", "$missemail");

		if ( $id_plan eq '16' ) {
			if ( $ismine eq '0' ) {
				if ( ! $ivr_options{0} eq '' ) {
					$dialstr = "DAHDI/g1/" . $ivr_options{0} . ",60,$mohtype";
					if ( $screen eq '1' ) {
						$dialstr .= "M(screen^$callerid^$accountcode)";
					}
					$AGI -> set_variable("DIALSTR0", $dialstr);
					$AGI -> set_variable("IVR", "0");
					$try = 3;

					if ( $connplay eq '1' ) {
						$connplay_ivr = 1;
						if ( -e "/var/lib/asterisk/sounds/connect-$accountcode.wav" ) {
							$AGI -> exec("Playback", "connect-$accountcode" );
						} else {
							$AGI -> exec("Playback", "mensaje-localizando-usuario" );
						}
					}

				}
			}
		} else {
			if ( $ismine eq '0' ) {

				$now = time();
				($hinicio, $minicio, $sinicio) = split (":", $hora_inicio);
				($hfin, $mfin, $sfin ) = split (":", $hora_fin);
				($sactual,$mactual,$hactual,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($now);

				$actual = $hactual*60*60 + $mactual*60 + $sactual;
				$inicio = $hinicio*60*60 + $minicio*60 + $sinicio;
				$fin = $hfin*60*60 + $mfin*60 + $sfin;

				$AGI -> verbose ("-- $hora_inicio a $hora_fin  : $actual , $inicio, $fin --");			

				$audio = "ivr/$ivr_recording";
				$outoftime = 0;

				if ( $inicio < $fin ) { #Hora actual debe de estar dentro del rango.
					if ( $actual > $fin || $actual < $inicio ) { #ver si hay audio fuera de horario
						$AGI -> verbose ("Llamada fuera de horario");
						$ivrooaudio -> execute($accountcode);
						$ivrooaudio -> bind_col(1, \$ivr_oorecording);
						if ( $ivrooaudio -> fetch() ) {
							$audio = "ivroo/$ivr_oorecording";
						} else {
							$audio = "HorarioFueraOficina";
						}

						$outoftime = 1;
						$AGI -> set_variable("SENDEMAIL", 1);
						$AGI -> exec("Playback", "$audio");
						$AGI -> exec("Goto","26");
						exit;
					}
				} else { #Hora actual debe de estar fuera del rango.
					if ( $actual < $inicio && $actual > $fin ) { #ver si hay audio fuera de horario
						$AGI -> verbose ("Llamada fuera de horario");
						$ivrooaudio -> execute($accountcode);
						$ivrooaudio -> bind_col(1, \$ivr_oorecording);
						if ( $ivrooaudio -> fetch() ) {
							$audio = "ivroo/$ivr_oorecording";
						} else {
							$audio = "HorarioFueraOficina";
						}

						$outoftime = 1;
						$AGI -> set_variable("SENDEMAIL", 1);
						$AGI -> exec("Playback", "$audio");
						$AGI -> exec("Goto","26");
						exit;
					}
				}

				$ivr_conference = "";

				$conferences = $dbh -> prepare ( "SELECT substr(confno,9,1) FROM conferences WHERE confno like '$accountcode%'");
				$conferences -> execute();
				$conferences -> bind_col(1, \$ivr_conference);
				$conferences -> fetch();	

				$AGI -> answer();
				$try = 0;
				while ( $try < 3 ) {
					if ( $hasfour ) {
						$option_ivr = $AGI -> get_data("$audio",3500,4);
					} else {
						$option_ivr = $AGI -> get_data("$audio",3000,1);
					}
					if ( ! $ivr_options{$option_ivr} eq '' ) {
						if ( $id_plan eq '17' || $id_plan eq '18' ) {
							$productoplan = "/var/lib/asterisk/sounds/ivr/$accountcode-" . $option_ivr ;
							$AGI -> verbose ( "$accountcode - $productoplan" );
							if ( -e "$productoplan.wav" ) {
								$tryproducto = 0;
								while ( $tryproducto < 3 ) {
									$option_producto  = $AGI -> get_data("$productoplan",4000,1);
									if ( $option_producto eq '0' ) {
										$dialstr = "DAHDI/g1/" . $ivr_options{$option_ivr} . ",60,$mohtype";
										if ( $screen eq '1' ) {
											$dialstr .= "M(screen^$callerid^$accountcode)";
										}
										$AGI -> set_variable("DIALSTR0", $dialstr);
										$AGI -> set_variable("IVR", $option_ivr);

										$recordsel -> execute("$accountcode-" . $option_ivr);
										$recordsel -> bind_col(1,\$global_record);
										$recordsel -> bind_col(2,\$id_plan);
										$recordsel -> bind_col(3,\$email);

										if( $recordsel -> fetch() ) {
											if ( length($email) > 4 ) {
												$AGI -> verbose("Extension Email : $email");	
												$AGI -> set_variable("EMAIL", $email);
											}
										}
										
										$tryproducto = 3;
										$try = 3;
									} else {
										$tryproducto = 0;
									}
									$tryproducto++; 
								}
							}
		
						} else {
							$dialstr = "DAHDI/g1/" . $ivr_options{$option_ivr} . ",60,$mohtype";
							if ( $screen eq '1' ) {
								$dialstr .= "M(screen^$callerid^$accountcode)";
							}
							$AGI -> set_variable("DIALSTR0", $dialstr);
							$AGI -> set_variable("IVR", $option_ivr);

							$recordsel -> execute("$accountcode-" . $option_ivr);
							$recordsel -> bind_col(3,\$email);

							if( $recordsel -> fetch() ) {
								if ( length($email) > 4 ) {
									$AGI -> verbose("Extension Email : $email");	
									$AGI -> set_variable("EMAIL", $email);
								}
							}

							$try = 3;

							if ( $connplay eq '1' ) {
							$connplay_ivr = 1;
							if ( -e "/var/lib/asterisk/sounds/connect-$accountcode.wav" ) {
								$AGI -> exec("Playback", "connect-$accountcode" );
							} else {
								$AGI -> exec("Playback", "mensaje-localizando-usuario" );
							}
							}
						}
					} else {
						if ( $option_ivr eq '' ) {
							if ( ! $ivr_options{0} eq '' ) {
								$option_ivr = 0;
							} elsif ( ! $ivr_options{1} eq '' ) {
								$option_ivr = 1;
							} elsif ( ! $ivr_options{2} eq '' ) {
								$option_ivr = 2;
							} elsif ( ! $ivr_options{3} eq '' ) {
								$option_ivr = 3;
							} elsif ( ! $ivr_options{4} eq '' ) {
								$option_ivr = 4;
							} elsif ( ! $ivr_options{5} eq '' ) {
								$option_ivr = 5;
							} elsif ( ! $ivr_options{6} eq '' ) {
								$option_ivr = 6;
							} elsif ( ! $ivr_options{7} eq '' ) {
								$option_ivr = 7;
							} elsif ( ! $ivr_options{8} eq '' ) {
								$option_ivr = 8;
							} elsif ( ! $ivr_options{9} eq '' ) {
								$option_ivr = 9;
							}
						
							$dialstr = "DAHDI/g1/" . $ivr_options{$option_ivr} . ",60,$mohtype";
							if ( $screen eq '1' ) {
								$dialstr .= "M(screen^$callerid^$accountcode)";
							}
							$AGI -> set_variable("DIALSTR0", $dialstr);
							$AGI -> set_variable("IVR", $option_ivr);
							$try = 3;

							if ( $connplay eq '1' ) {
							$connplay_ivr = 1;
							if ( -e "/var/lib/asterisk/sounds/connect-$accountcode.wav" ) {
								$AGI -> exec("Playback", "connect-$accountcode" );
							} else {
								$AGI -> exec("Playback", "mensaje-localizando-usuario" );
							}
							}
						}
					}
					if ( $ivr_conference eq '' ) {
					} else {
						if ( $option_ivr eq $ivr_conference) {
							$AGI -> verbose ("Cuarto de conferencia $ivr_conference");
							$AGI -> set_variable ("CONFNO", $ivr_conference );
							$AGI -> exec ('goto', 'conference' );
							exit;
						}
					
					}
					$try++;
				}
			}
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
$modes -> bind_col(8,\$missemail);
$modes -> bind_col(9,\$disabled);

if ( $modes -> fetch() ) {
	$AGI -> set_variable("MISSEMAIL", "$missemail");

	if ( $disabled eq '1' && $ismine eq '0' ) {
		$AGI -> set_variable("SENDEMAIL", 1);

		if ( -e "/var/lib/asterisk/sounds/disabled/$accountcode.wav" ) {
			$AGI -> exec ("Playback", "disabled/$accountcode");
			$AGI -> exec ('goto', '26' );
		} else {
			$AGI -> exec ('goto', '25' );
		}
		exit;
	}

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

	if ( $playrecord eq '1' && $ismine eq '0' ) {
		if ( -e "/var/lib/asterisk/sounds/alert-record.wav" ) {
			$AGI -> exec("Playback", "alert-record");
		} 
	}

	if ( $playrecord eq '2' && $ismine eq '0' ) {
		if ( -e "/var/lib/asterisk/sounds/control/$accountcode.wav" ) {
			$AGI -> exec("Playback", "control/$accountcode");
		} elsif ( -e "/var/lib/asterisk/sounds/ControlCalidad.wav" ) {
			$AGI -> exec("Playback", "ControlCalidad");
		} 
	}

	if ( $dialmode eq '1' && $ismine eq '0' && $isivr eq '0') {
		$AGI -> set_variable("DIALSERIAL","1");
		for ( $i = 0; $i < @myphones; $i++ ) {
			$AGI->set_variable("DIALSTR" . $i,"DAHDI/g1/" . $myphones[$i] . ",60," . $mohtype );
			if ( $myphones[$i] =~ m/^00503/ ) {
				$number = $myphones[$i];
				$number =~ s/^00503/503/;
				$AGI->set_variable("DIALSTR" . $i,"SIP/" . $number . "\@red,60," . $mohtype );
				$AGI->set_variable("CALLERID(num)" , "502" . $accountcode );
			}
		}
	} elsif ( $dialmode eq '2' && $ismine eq '0' && $isivr eq '0') {
		$dialstr = "";
		if ( scalar(@myphones) > 1 ) {
			$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . ",60," . $mohtype;
			if ( scalar(@myphones) > 2 ) {
				$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . "&DAHDI/g1/" . $myphones[2] . ",60," . $mohtype;
				if ( scalar(@myphones) > 3 ) {
					$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . "&DAHDI/g1/" . $myphones[2] . "&DAHDI/g1/" . $myphones[3] . ",60," . $mohtype;
				}
			}
		} else {
			$dialstr = "DAHDI/g1/" . $myphones[0] . ",60," . $mohtype;
			if ( $myphones[0] =~ m/^00503/ ) {
				$number = $myphones[0];
				$number =~ s/^00503/503/;
				$dialstr = "SIP/" . $number . "\@red,60," . $mohtype ;
				$AGI->set_variable("CALLERID(num)" , "502" . $accountcode );
			}
		}
		if ( $accountcode eq '24584711' ) {
			$dialstr = "IAX2/jsolares&" . $dialstr;
		}
		$AGI -> set_variable("DIALSTR0", $dialstr);
	}	

	if ( $screen eq '1' && $dialmode eq '2' && $ismine eq '0' && $isivr eq '0') {
		if ( scalar(@myphones) > 1 ) {
			$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . ",60," . $mohtype . "M(screen^$callerid^$accountcode)";
			if ( scalar(@myphones) > 2 ) {
				$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . "&DAHDI/g1/" . $myphones[2] . ",60," . $mohtype . "M(screen^$callerid^$accountcode)";
				if ( scalar(@myphones) > 3 ) {
					$dialstr = "DAHDI/g1/" . $myphones[0] . "&DAHDI/g1/" . $myphones[1] . "&DAHDI/g1/" . $myphones[2] . "&DAHDI/g1/" . $myphones[3] . ",60," . $mohtype . "M(screen^$callerid^$accountcode)";
				}
			}
		} else {
			$dialstr = "DAHDI/g1/" . $myphones[0] . ",60," . $mohtype . "M(screen^$callerid^$accountcode)";
			if ( $myphones[0] =~ m/^00503/ ) {
				$number = $myphones[0];
				$number =~ s/^00503/503/;
				$dialstr = "SIP/" . $number . "\@red,60," . $mohtype . "M(screen^$callerid^$accountcode)";
				$AGI->set_variable("CALLERID(num)" , "502" . $accountcode );
			}
		}
		$AGI -> set_variable("DIALSTR0", $dialstr);
	}

	if ( $connplay eq '1' && $ismine eq '0' && $connplay_ivr eq '0' ) {
		if ( -e "/var/lib/asterisk/sounds/connect-$accountcode.wav" ) {
			$AGI -> exec("Playback", "connect-$accountcode" );
		} else {
			$AGI -> exec("Playback", "mensaje-localizando-usuario" );
		}
	}

	if ( $ismine ) {
		if ( $mode eq '1' ) { 
			$AGI -> set_variable("CALLBACK",1);
			$AGI -> exec ('goto', 'callback' );
		}
		if ( $mode eq '2' ) { 
			$AGI -> exec ('goto', 'disa' );
		}
		if ( $mode eq '3' ) { 
			$AGI -> exec ('goto', 'voicemail' );
		}
		if ( $mode eq '4' ) { 
			$AGI -> exec ('goto', 'menu' );
		}
	}
}

exit 1;

sub disconnect() {
	$AGI -> hangup;
	exit 0;
}
