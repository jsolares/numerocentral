#!/bin/bash
#
#    rsync.sh
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
#    Backup everything to a backup server, an external usb drive (if mounted), S3 and b2
#

LOGFILE="/var/log/sync.log"

DATE=$(date +%Y-%-m-%-d)
set -- "$DATE"
IFS="-"; declare -a DATES=($*); unset IFS;
YEAR=${DATES[0]}
MONTH=${DATES[1]}
DAY=${DATES[2]}
DATE=$(date --date "yesterday" +%Y-%-m-%-d)
set -- "$DATE"
IFS="-"; declare -a DATES=($*); unset IFS;
PREVYEAR=${DATES[0]}
PREVMONTH=${DATES[1]}
PREVDAY=${DATES[2]}

echo "$(date +%d%m%Y\ %T) : Finding older than 200 days backups and moving it to nosync"
find /isci/backups -type f -mtime +200 -print0 | xargs -0 mv -t /isci/backups.nosync

echo "$(date +%d%m%Y\ %T) : Starting RSYNC to backup server" >> $LOGFILE
rsync -akv -e "ssh -p 223 -i key" /var/spool/asterisk/monitor/$YEAR codevoz@192.168.1.2:monitor >> $LOGFILE 2>&1
rsync -akv -e "ssh -p 223 -i key" /var/spool/asterisk/fax codevoz@192.168.1.2: >> $LOGFILE 2>&1
rsync -akv -e "ssh -p 223 -i key" /var/spool/asterisk/facturas codevoz@192.168.1.2: >> $LOGFILE 2>&1
rsync -akv -e "ssh -p 223 -i key" /var/spool/asterisk/dpi codevoz@192.168.1.2: >> $LOGFILE 2>&1
rsync --delete -akv -e "ssh -p 223 -i key" /home/codevoz/backups codevoz@192.168.1.2: >> $LOGFILE 2>&1

echo "$(date +%d%m%Y\ %T) : Starting SYNC to Backblaze B2" >> $LOGFILE

#Backup everything but calls to backblaze b2
rclone --transfers 10 sync /iscsi/dpi Backblaze:numerocentral/dpi/
rclone --transfers 10 sync /iscsi/fax Backblaze:numerocentral/fax/
rclone --transfers 10 sync /iscsi/facturas Backblaze:numerocentral/facturas/
rclone --transfers 10 sync /iscsi/backups Backblaze:numerocentral/backups/

mountpoint -q /mnt
if [ $? == 0 ]
then
        cd /mnt
        echo "$(date +%d%m%Y\ %T) : Starting RSYNC to usb disk" >> $LOGFILE
        #rsync -akv /home/codevoz/backups /mnt/            >> $LOGFILE 2>&1
        rsync -akv /var/spool/asterisk/monitor /mnt/      >> $LOGFILE 2>&1
        rsync -akv /var/spool/asterisk/fax /mnt/          >> $LOGFILE 2>&1
        rsync -akv /var/spool/asterisk/dpi /mnt/          >> $LOGFILE 2>&1
        rsync -akv /var/spool/asterisk/facturas /mnt/     >> $LOGFILE 2>&1
else
echo "USB no montado" >> $LOGFILE
fi

#Backup everything to S3, do the folder by day to minimize running time of script
echo "$(date +%d%m%Y\ %T) : Starting SYNC to S3" >> $LOGFILE
s3cmd -r --no-progress sync /var/spool/asterisk/fax/ s3://backupnc/backups/fax/ >> $LOGFILE 2>&1
echo "$(date +%d%m%Y\ %T) : Backed up Faxes" >> $LOGFILE
s3cmd -r --no-progress sync /var/spool/asterisk/facturas/ s3://backupnc/backups/facturas/ >> $LOGFILE 2>&1
echo "$(date +%d%m%Y\ %T) : Backed up Facturas" >> $LOGFILE
s3cmd -r --no-progress sync /var/spool/asterisk/monitor/$PREVYEAR/$PREVMONTH/$PREVDAY/ s3://backupnc/backups/monitor/$PREVYEAR/$PREVMONTH/$PREVDAY/ >> $LOGFILE 2>&1
s3cmd -r --no-progress sync /var/spool/asterisk/monitor/$YEAR/$MONTH/$DAY/ s3://backupnc/backups/monitor/$YEAR/$MONTH/$DAY/ >> $LOGFILE 2>&1
echo "$(date +%d%m%Y\ %T) : Backed up calls" >> $LOGFILE
s3cmd -r --no-progress sync /home/codevoz/backups/ s3://backupnc/backups/backups/ >> $LOGFILE 2>&1
echo "$(date +%d%m%Y\ %T) : Backed up tars and sql" >> $LOGFILE

echo "$(date +%d%m%Y\ %T) : Finished, flushing caches" >> $LOGFILE
echo 3 > /proc/sys/vm/drop_caches

exit
