#!/bin/bash

#
# 


# show program usage
show_usage() {
echo
echo ${0##/*}" Usage:"
echo "   Runs every test class in testclasses"
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


opt1="nothing"
if [[ $ARGC -gt 0 ]] ; then

opt1=$1 
fi


result=true

cd ../locale
./generate_file_list.sh
./generate_pot_file.sh
./update_language.sh en > /dev/null

msgfmt -C  vubla_en.po

