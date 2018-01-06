#!/usr/bin/perl
#
#    cron-screen-tts.pl
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
#    Generates the audio of the name for all contacts to be used for screening
#	Populates the cache periodically
#

use DBI;
use DBD::mysql;
use Digest::MD5 md5_hex;

$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

$name_query = $dbh -> prepare ("SELECT name
				  FROM contacts" );

$name_query -> execute ();
$name_query -> bind_col( 1, \$nombre);

while ( $name_query -> fetch() ) {
	$audio = tts_cepstral( $nombre );
	
	print "$audio\n";
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

