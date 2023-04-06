#!/bin/bash
echo -e "input folder: \c"
read folder
echo -e "search string: \c"
read search
echo -e "replacement string: \c"
read replace
find $folder -type f -exec sed -i 's/'"$search"'/'"$replace"'/g' {} \; > /dev/null 2>&1
if [ $? -eq 0 ]
then
echo "done"
else  echo "nope"
exit
fi
