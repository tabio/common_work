#!/bin/bash
# [ Summary ]
# copy image files in target dirctory
#
# [ Useage ]
# find_img.bash [target directory]
#
# [ Etc. ]
# @link    https://github.com/tabio/common_work
# @auther  tabio <tabio.github@gmail.com>
# @version 1.0
# @license Copyright (c) 2013 tabio
#          This software is released unser the MIT License.
#           http://opensource.org/licenses/mit-license.php

dir=$1

# args check
if [ "${dir}" = ""  ]; then
  echo '[ warning ] args1 => target dirctory path'
  exit 1
fi

# copy dirctory
cpdir='./copy'
if [ ! -e ${cpdir}  ]; then
  `/bin/mkdir copy`
fi

# copy file
if [ ! -e ${dir} ]; then
  echo "[ error ] no exist ${dir}"
else
  list=$(find ${dir} -type f -regex ".*\(jpg\|jpeg\|gif\|bmp\|png\)")
  for tmp in ${list[@]};do
    fname="${cpdir}/$(basename ${tmp})"
    if [ -f $fname ]; then
      echo "duplicate ${fname}"
    else
      $(cp $tmp ${cpdir})
    fi
  done
fi

exit 0
