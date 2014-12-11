#!/bin/sh
# [ Summary ]
# backup mongo database
#
# [ Useage ]
# sh mongo_dump.sh
#
# [ Etc. ] 
# @link    https://github.com/tabio/common_work 
# @auther  tabio <tabio.github@gmail.com> 
# @version 1.0 
# @license Copyright (c) 2015 tabio 
#          This software is released unser the MIT License. 
#          http://opensource.org/licenses/mit-license.php 

# life time of backup file
period=30

# backup file name
date=`date +%Y%m%d`
old=`date --date "$period days ago" +%Y%m%d`
ext=".tgz" 

# config database
dir="" 
host="localhost"
db=""

fname=$db"_"$date
fname_tgz=$db"_"$date$ext
fname_old=$db"_"$old$ext

# change directory
cd $dir

# execute mongodump
mongodump --out $fname --host $host --db $db

# compress
tar --remove-files -czf $fname_tgz $fname

# delete backup file (for life time)
if test -e $fname_old
then
  rm -f $fname_old
fi
exit 0