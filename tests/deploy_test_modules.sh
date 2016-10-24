#!/bin/bash

show_usage() {
echo
echo ${0##/*}" Usage:"
echo "   Deploys modules"
echo "   "
echo "${0##/} [options]"
echo "  "
exit
}

# Minimum number of arguments needed by this program
MINARGS=2

# show usage if '-h' or  '--help' is the first argument or no argument is given
case $1 in
    "-h"|"--help") show_usage ;;
esac

# get the number of command-line arguments given
ARGC=$#

# check to make sure enough arguments were given or exit
if [[ $ARGC -lt $MINARGS ]] ; then
 echo "Too few arguments given (Minimum:$MINARGS)"
 echo
 show_usage
fi




BASE_PATH=/var/www/$1$2$3.crawler.vubla.com


rm -r $BASE_PATH/modules
#svn export file:///var/lib/svn/modules/trunk/$1 $BASE_PATH/modules
cp -r ../../../modules/$1  $BASE_PATH/modules

rm -r $BASE_PATH/temp
cp -r $BASE_PATH/modules/$2/ $BASE_PATH/temp
cp -r $BASE_PATH/modules/common/* $BASE_PATH/temp
cd $BASE_PATH/temp

zip -r  $BASE_PATH/htdocs/module.zip *
cd $BASE_PATH/htdocs 
unzip -o module.zip 
