#!/bin/bash

show_usage() {
echo
echo ${0##/*}" Usage:"
echo "   Removes modules"
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



here=${pwd}

BASE_PATH=/var/www/$1$2$3.crawler.vubla.com
echo $BASE_PATH

rm -r $BASE_PATH/htdocs/*
cp -rP $BASE_PATH/std/* $BASE_PATH/htdocs/
cp $BASE_PATH/std/.htaccess $BASE_PATH/htdocs/.htaccess

mysql -umagento -pTrekant01 --database="magento_$2$3" -e "DELETE FROM core_resource WHERE code  =  'vubla_setup'"

mysql -umagento -pTrekant01 --database="magento_$2$3" -e "DELETE FROM api_role WHERE role_name  =  'vubla'"

mysql -umagento -pTrekant01 --database="magento_$2$3" -e "DELETE FROM api_user WHERE username  =  'vubla'"

cd $BASE_PATH/htdocs/
chmod -R 777 .
./mage mage-setup
cd $here