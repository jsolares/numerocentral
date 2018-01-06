#!/usr/bin/perl
#
#    favoritos.pl
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
#    For users with favorite contacts ask for the code and call that contact.
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

$favorites = $dbh -> prepare ("SELECT keypad,
				      number,
				      name
				 FROM contacts,
				      favorites
				WHERE contacts.id_contact = favorites.id_contact
				  AND accountcode = ?");

$accountcode = $AGI -> get_variable('CDR(accountcode)');

$favorites -> execute ( $accountcode );
$favorites -> bind_col( 1, \$keypad );
$favorites -> bind_col( 2, \$number );
$favorites -> bind_col( 3, \$name );

my %numbers;
my %names;
$rows = 0;

while ( $favorites -> fetch() ) {
	$rows++;
	$numbers{$keypad} = $number;
	$names{$keypad} = $name;
}

if ( $rows eq '0' ) {
	$AGI -> exec ('playback', 'no-hay-favoritos');
	$AGI -> hangup;
	exit 1;
}

$try_codigo = 0;
while ( $try_codigo < 3 ) {
	$option = $AGI -> get_data ('mensaje-Codigo-Contacto', 6000, 1);

	if ( exists($numbers{$option})) {
		$try_codigo = 3;
		$AGI -> set_variable ( "FAVNUMBER", $numbers{$option} );
		$AGI -> exec ('goto', 'favback' );
	}
	$try_codigo++;
}

exit 1;

sub disconnect() {
	$AGI -> hangup;
	exit 0;
}
