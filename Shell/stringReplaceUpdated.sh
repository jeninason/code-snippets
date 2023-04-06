#!/bin/bash

# Prompt the user for the input folder
echo -e "Input folder: \c"
read folder

# Validate the input folder
if [ ! -d "$folder" ]; then
  echo "Error: Input folder does not exist or is not a directory."
  exit 1
fi

# Prompt the user for the search string
echo -e "Search string: \c"
read search

# Prompt the user for the replacement string
echo -e "Replacement string: \c"
read replace

# Confirm with the user before executing the script
echo "The script will search for files containing '$search' in the folder '$folder' and replace it with '$replace'."
echo -n "Are you sure you want to continue? (y/n) "
read confirm
if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
  echo "Aborted."
  exit
fi

# Escape special characters in the search and replacement strings
search=$(sed 's/[\*\.&]/\\&/g' <<< "$search")
replace=$(sed 's/[\*\.&]/\\&/g' <<< "$replace")

# Search for files and replace the search string with the replacement string
find "$folder" -type f -exec sed -i "s/$search/$replace/g" {} \;

# Check if the command succeeded
if [ $? -eq 0 ]; then
  echo "Done."
else
  echo "Error: Failed to search and replace in files."
  exit 1
fi
