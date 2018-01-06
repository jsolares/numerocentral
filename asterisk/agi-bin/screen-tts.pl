#!/usr/bin/perl
#
#    screen-tts.pl
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
#    Plays back to our user who is calling them using TTS and their contact data
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

$name_query = $dbh -> prepare ("SELECT name
				  FROM contacts, groups
				 WHERE id_group = id
			    	   AND accountcode = ?
				   AND number = ?" );

$numero      = $AGI -> get_variable('ARG1');
$accountcode = $AGI -> get_variable('ARG2');

$name_query -> execute ( $accountcode, $numero );
$name_query -> bind_col( 1, \$nombre);

if ( $name_query -> fetch() ) {
	$audio = tts_cepstral( $nombre );
	$AGI -> exec ( 'playback', $audio);
	$AGI -> verbose ("Llamada de $numero, $nombre using $audio.");
} else {
	$audio = tts_cepstral( "Desconocido" );
	$AGI -> exec ( 'playback', $audio);
	$AGI -> verbose ("Llamada de $numero, no contact using $audio.");
}

exit 1;

sub tts_cepstral() {
	$text = shift;

	my $sounddir 	= "/var/lib/asterisk/sounds/tts/screen/";
	my $tmpdir	= "/tmp/";
	my $hash 	= md5_hex($text);
	my $tmpfile     = $tmpdir . "tts-$hash.wav";
	my $wavefile	= $sounddir . "tts-$hash.wav";

	unless ( -f $wavefile ) {
		open ( fileOUT, ">/tmp/text-$hash.txt" );
		print fileOUT "$text";
		close ( fileOUT );

		$exectts = "/opt/swift/bin/swift -o $tmpfile -f /tmp/text-$hash.txt";
		$result  = qx/$exectts/;

		$execsox = "sox $tmpfile -r 8000 $wavefile";
		$result = qx/$execsox/;

	}

	return "tts/screen/tts-$hash";
}

