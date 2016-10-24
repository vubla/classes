#!/bin/bash

#
# 


# show program usage
show_usage() {
echo
echo ${0##/*}" Usage:"
echo "   Runs every test class in testadmin"
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



#cd temp/ 
cd testsystem/ 
for file in * ; do   
        if [ $opt1 == "-v" ] ; then
             echo "Testing: " $file
  
             phpunit $file
             res=$(phpunit $file| tail -n 2 | head -c 4 );
            
             if [ $res != "OK" ] ; then
                result=failed
             fi
        else
            res=$(phpunit $file | tail -n 2 | head -c 4 );
            
            if [ "$res" == "" ] ; then
            	echo "Failed in test: "$file
                echo "No output!"
                echo ""
            else
	            if [ $res != "OK" ] ; then
	                echo "Failed in test: "$file
	                phpunit $file
	                echo ""
	             
	            fi
	        fi
        fi 
            
done

if [ $opt1 == "-v" ] ; then
     if [ $result == "true" ] ; then
        echo "Everything went well"
    else 
        echo "Test failed!!!!"
    
     fi
fi


