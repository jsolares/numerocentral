#!/usr/bin/perl
#
#    fax_account.pl
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
#    Get email for called number if available, this will be used to send the fax in pdf
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

$get_account = $dbh -> prepare ("select accountcode, email from users where fax=?");
$extension = $input{'extension'};

$AGI -> verbose ("Finding account for $extension");

$get_account -> execute ( $extension );
$get_account -> bind_col( 1, \$accountcode);
$get_account -> bind_col( 2, \$email);

if ( $get_account -> fetch() ) {
	$AGI -> verbose ("Got account: $accountcode and email: $email");
} else {
	$accountcode = 'admin';
	$email = 'voip@codevoz.com';
}

$AGI -> set_variable('CDR(accountcode)',$accountcode);
$AGI -> set_variable('FAXEMAIL',$email);

exit 1;
