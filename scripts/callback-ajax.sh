#!/bin/bash
#
#    callback-ajax.sh
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
#    Callback / webcall file used
#

die () {
    echo >&2 "$@"
    exit 1
}

[ "$#" -eq 4 ] || die "4 arguments required, $# provided"

echo "Channel: Local/$1@webcall-nc" > /tmp/$1
echo "CallerID: <$2>" >> /tmp/$1
echo "Context: webcall" >> /tmp/$1
echo "Extension: $3" >> /tmp/$1
echo "Set: NUMBER=$2" >> /tmp/$1
echo "Set: IVR=$4" >> /tmp/$1
echo "Set: ORIGEN=$3" >> /tmp/$1

NOW=`date +%s`
let NOW=$NOW+2
TOUCH_TMSP=`date -d "1970-01-01 $NOW sec GMT" +%Y%m%d%H%M.%S`
touch -t $TOUCH_TMSP /tmp/$1

mv /tmp/$1 /var/spool/asterisk/outgoing
