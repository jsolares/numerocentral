#!/usr/bin/perl
#
#    marcador_hangup.pl
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
#    Update the status of a finished call in a campaign.
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

$update = $dbh -> prepare( "UPDATE marcador_event_detail 
			       SET status=2, status_date= now()
			     WHERE id = ? and id_event = ?" );

$id_event = $AGI -> get_variable('IDEVENT');
$id_call  = $AGI -> get_variable('IDCALL');

$update -> execute ( $id_call, $id_event );

exit 1;

sub disconnect() {
	$AGI -> hangup;
	exit 0;
}
