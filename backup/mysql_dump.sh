#!/bin/sh
# [ Summary ]
# backup mysql database
#
# [ Useage ]
# sh mysql_dump.sh
#
# [ Etc. ]
# @link    https://github.com/tabio/common_work
# @auther  tabio <tabio.github@gmail.com>
# @version 1.0
# @license Copyright (c) 2013 tabio
#          This software is released unser the MIT License.
#          http://opensource.org/licenses/mit-license.php

# life time of backup file
period=5

# backup file name
date=`date +%Y%m%d`
old=`date --date "$period days ago" +%Y%m%d`
ext=".gz" 

# config database
dir="" 
user="" 
password="" 
host=""
db=""

fname=$dir$db"_"$date$ext
fname_old=$dir$db"_"$old$ext

# execute mysqldump & compress
`mysqldump --opt -u$user --password=$password -h $host $db | gzip > $fname`

# delete backup file (for life time)
if test -e $fname_old
then
  rm -f $fname_old
fi
exit 0

