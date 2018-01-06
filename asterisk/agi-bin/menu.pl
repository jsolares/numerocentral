#!/usr/bin/perl
#
#    menu.pl
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
#    AGI for menu to give to some users when dialing in
#

use Asterisk::AGI;

$AGI = new Asterisk::AGI;
%input = $AGI->ReadParse();
$AGI->setcallback(\&disconnect);

$AGI -> answer();
$AGI -> exec ( 'playback', 'silence/1' );
$option = $AGI->get_data('mensaje-Menu',6000,1);

if ( $option eq '1' ) {
	$AGI -> exec ('goto', 'favoritos' );
} elsif ( $option eq '2' ) {
	$AGI -> exec ('goto', 'callback' );
} elsif ( $option eq '3' ) {
	$AGI -> exec ('goto', 'disa' );
} elsif ( $option eq '4' ) {
	$AGI -> exec ('goto', 'saldo' );
} elsif ( $option eq '5' ) {
	$AGI -> exec ('goto', 'lastcall' );
} elsif ( $option eq '6' ) {
	$AGI -> exec ('goto', 'voicemail' );
} elsif ( $option eq '7' ) {
	$AGI -> exec ('goto', 'memo' );
} elsif ( $option eq '0' ) {
	$AGI -> exec ('goto', 'menu' );
}
