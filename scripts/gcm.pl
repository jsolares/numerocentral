#!/usr/bin/perl
#
#    gcm.pl
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
#    Script used to send a message to a phone using the android app
#	for incomming calls
#

use DBI;
use DBD::mysql;
use WWW::Google::Cloud::Messaging;

my $api_key = '';
my $gcm = WWW::Google::Cloud::Messaging->new(api_key => $api_key);

$collapse_key = "Llamadas";
$database = "numerocentral";
$hostname = "localhost";
$user     = "root";
$password = "";

die "Usage: $0 ACCOUNTCODE CONTACT\n" if @ARGV < 2;

$DSN = "DBI:mysql:database=$database;host=$hostname";
$dbh = DBI->connect ($DSN, $user, $password) or disconnect();

$accountcode = shift;
$number = shift;
$ivr = shift;

if ( $accountcode eq '24584715' ) {
	die "Pablo Pintor no quiere gcm\n";
}

$registration = $dbh -> prepare ("SELECT deviceID
				    FROM android_devices
				   WHERE accountcode = ?");

$contactdata = $dbh -> prepare ("SELECT name
				   FROM groups,
					contacts
				  WHERE id_group = id
				    AND accountcode = ?
				    AND number = ?");
if ( length($ivr) eq '4' ) {
	$registration -> execute($accountcode . "-" . $ivr);
} else {
	$registration -> execute($accountcode);
}

$registration -> bind_col(1, \$deviceID);

if ( $registration -> fetch() ) {

	$message = '';
	$contactdata -> execute($accountcode, $number);
	$contactdata -> bind_col(1, \$name);

	if ( $contactdata -> fetch() ) {
		$message = $name. ' (' . $number . ')';
	} else {
		$message = $number;
	}

	$message = "Tiene una llamada de " . $message;

	my $res = $gcm->send({
      		registration_ids => [ $deviceID ],
      		collapse_key     => $collapse_key,
		time_to_live	 => 15,
      		data             => {
        		message => $message,
      		},
	});

	die $res->error unless $res->is_success;

	my $results = $res->results;
	while (my $result = $results->next) {
		if ($result->has_canonical_id) {
		}
      	}
}
