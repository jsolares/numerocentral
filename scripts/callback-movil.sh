#!/bin/bash
#
#    callback-movil.sh
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
#    script used for callback
#

echo "Channel: DAHDI/g1/$1" > /tmp/$1
echo "CallerID: <$2>" >> /tmp/$1

if [ "$4" == "1" ];then
        echo "Context: callback-movil-record" >> /tmp/$1
else
        echo "Context: callback-movil" >> /tmp/$1
fi
echo "Extension: $2" >> /tmp/$1
echo "Set: NUMBER=$3" >> /tmp/$1
echo "Set: IVR=$5" >> /tmp/$1
echo "Set: ORIGEN=$1" >> /tmp/$1

NOW=`date +%s`
let NOW=$NOW+4
TOUCH_TMSP=`date -d "1970-01-01 $NOW sec GMT" +%Y%m%d%H%M.%S`
touch -t $TOUCH_TMSP /tmp/$1

mv /tmp/$1 /var/spool/asterisk/outgoing
