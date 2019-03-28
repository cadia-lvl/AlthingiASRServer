#! /bin/bash
# Description: Gets today's date then retrieves all the words that have been 
#confirmed since the last time this script was run, then saves it to the 
#confirmedWords dir with a filename of CURRENT_DATE.txt 
# Usage as a cron job run daily after working hrs from the lirfa directory
# assumes this is run from the project root
. ./path.sh
dateConfirmed=$(date +%F)
fileConfirmed="$confirmedWordsDir""$dateConfirmed"".txt"
if [[ $(date +%u) -eq 1 ]]; then
#check if monday, if so do special stuff
    startDate=$(date --date="2 days ago" +%F)
else
    startDate=$dateConfirmed
fi
curl -sk 'https://YOUR-DOMAIN-HERE/lirfa/api/confirmWords/?pronunciation=1&startDate='"$startDate" -o $fileConfirmed
if [[ -f "${fileConfirmed}" && ! -s "${fileConfirmed}" ]] 
then
    rm "$fileConfirmed"
    echo "removed empty file: " "$fileConfirmed"
fi
