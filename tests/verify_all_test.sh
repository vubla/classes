#!/bin/bash

#
# 


# show program usage
show_usage() {
echo
echo ${0##/*}" Usage:"
echo "   Runs every test class in testadmin, testapi, testclasses, testlogin, and testmodules"
echo "   No output means everything was good :)"
echo "${0##/} [options]"
echo "  -v  Verbose. "
exit
}

# Minimum number of arguments needed by this program
MINARGS=0

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

./make_config.sh $(cat ../../../testname)

./verify_lang_test.sh
./verify_admin_test.sh $1
./verify_api_test.sh $1
./verify_classes_test.sh $1
./verify_login_test.sh $1
sudo ./verify_system_test.sh $1
#sudo ./verify_modules_test.sh $1


