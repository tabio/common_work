#!/bin/sh
# [ Summary ]
#   Get access ranking list for content id
#
# [ Useage ]
#   /bin/sh access_rank_list.sh [target content id]
#
# [ Etc. ]
# @link    https://github.com/tabio/common_work
# @auther  tabio <tabio.github@gmail.com>
# @version 1.0
# @license Copyright (c) 2014 tabio
#          This software is released unser the MIT License.
#          http://opensource.org/licenses/mit-license.php

# Check content id
if [ $# -le 0 ]; then
  echo "[warning] /bin/sh $0 [content id]\n"
  exit 1
else
  CID=$1
fi

# Set params
HOSTS=("host1" "host2")

## file name
DATE=`date '+%Y%m' -d '1 months ago'`
NOW=`date '+%Y%m%d_%H%M%S'`
BPATH="/home/hoge/"
OPATH=$BPATH"cid_${CID}_$NOW.tsv"
TPATH=$BPATH"cid_${CID}_$NOW.tmp"

## log file
LOG_DIR="/var/log/http"

# Make ranking file list
for host in ${HOSTS[@]}
do
  log=$LOG_DIR"/"$host"-"${DATE}"-all.log.gz"
  `less $log | awk '$7 ~ /\/'$CID'(\?.+)*$/ {print $1}' | sort >> $TPATH`
done

`less $TPATH | sort | uniq -c | sort -nk 1 | sort -nk 1 | awk 'BEGIN {OFS="\t"} {print $1,$2}' > $OPATH`
`rm -f $TPATH`

# Add network domain info from ipaddress
`/usr/bin/php add_domain_info.php $OPATH`

exit 0


