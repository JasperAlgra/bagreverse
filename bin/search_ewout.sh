#!/bin/bash
if [ -z "$1" ];then
  echo "no file given"
  echo "Usage: $0 /path/to/filename.csv"
  exit 0
fi

if [ -f "$1" ];then
  echo "Using file $1"
else
  echo "File not found"
  echo "Usage: $0 /path/to/filename.csv"
  exit 0
fi

for i in $(cat "$1" | sed 's/ /__/g');do
   huisnummer=$(echo $i | cut -d ";" -f 2 | sed 's/_*//g');
   toevoeging=$(echo $huisnummer | sed 's/^[0-9]+\([a-zA-Z]*\)/\1/');
   if [ -n "$toevoeging" ];then
     huisnummer=$(echo $huisnummer | sed 's/^\([0-9]*\).*/\1/');
     toevoeging="AND huisletter = '$toevoeging'"
     toevoeging="" 
   else
     echo ""
     continue;
   fi
   postcode=$(echo $i | cut -d ";" -f 1 | sed 's/_*//g');
   if [ -z "$postcode" ];then
     echo "no postcode"
     continue;
   fi
   if [ -z "$huisnummer" ];then
     echo "no huisnummer"
     continue;
   fi
   echo -n $(date "+%Y-%m-%d %H:%M:%S")" -- searching for postcode = '$postcode' AND huisnummer = '$huisnummer' $toevoeging... "
   # coords=$(psql -U postgres -w -d bag -tq -c "SELECT ST_X(geopunt) as x, ST_Y(geopunt) as y FROM adres WHERE ((openbareruimtenaam = '$straat' AND woonplaatsnaam = '$woonplaats') OR postcode = '$postcode') $huisnummer LIMIT 1;" |head -n1);
   coords=$(psql -U postgres -w -d bag -tq -c "SELECT ST_X(geopunt) as x, ST_Y(geopunt) as y FROM adres WHERE postcode = '$postcode' AND huisnummer = '$huisnummer' $toevoeging LIMIT 1;" |head -n1);
   if [ -n "$coords" ]; then
      phpcode='include("../lib/lib.php"); $ll = rd2wgs('$(echo $coords | cut -d "|" -f 1 | sed 's/[^0-9\.]*//g')','$(echo $coords | cut -d "|" -f 2 | sed 's/[^0-9\.]*//g')'); echo $ll["lat"].", ".$ll["lon"]."\n";'
#      echo "$phpcode"
      php -r "$phpcode"
   else
     echo ""
   fi
done
