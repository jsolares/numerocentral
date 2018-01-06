#!/bin/bash
#
#    convertomp3.sh
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
#    Uses lame to convert the asterisk recording to an mp3 file
#

if [ $1 = "" ]; then
	exit 1
fi

if [ -f "/var/spool/asterisk/monitor/$1.wav" ]; then
	lame -S /var/spool/asterisk/monitor/$1.wav /var/spool/asterisk/monitor/$1.mp3
	unlink /var/spool/asterisk/monitor/$1.wav
fi
