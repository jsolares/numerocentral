#!/usr/bin/perl
#
#    transfer.pl
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
#    Used for transferring a call to another option in the IVR menu
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

$get_account = $dbh -> prepare ("select number from ivr_option where keypad=? and accountcode=?");
$extension = $input{'extension'};
$accountcode= $AGI -> get_variable("ACCOUNT");

$AGI -> verbose ("Finding number for $extension \@ $accountcode");

$get_account -> execute ( $extension, $accountcode);
$get_account -> bind_col( 1, \$number);

if ( $get_account -> fetch() ) {
	$AGI -> verbose ("Got number: $number");
} else {
	$AGI -> hangup;
}

$AGI -> set_variable('NUMBER',$number);

exit 1;
