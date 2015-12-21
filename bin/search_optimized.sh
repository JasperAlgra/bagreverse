#!/bin/sh
DATADIR="../data"
for i in $(cat $DATADIR/bulthuis.csv | sed 's/ /__/g');do
   straat=$(echo $i | cut -d ";" -f 2 | sed 's/^\([^0-9]*\).*$/\1/' | sed 's/_*$//' | sed 's/__/ /g' );
   huisnummer=$(echo $i | cut -d ";" -f 2 | sed 's/___*/__/g' | sed 's/^[^0-9]*\([0-9]*\).*$/\1/');
   postcode=$(echo $i | cut -d ";" -f 3 | sed 's/_*//g');
   if [ -n "$huisnummer" ];then
     huisnummer="AND huisnummer = '$huisnummer'"
   fi
   woonplaats=$(echo $i | cut -d ";" -f 4 | sed 's/_*$//' | sed 's/__/ /g')
   coords=$(psql -U postgres -w -d bag -tq -c "SELECT ST_X(geopunt) as x, ST_Y(geopunt) as y FROM adres WHERE postcode = '$postcode' $huisnummer LIMIT 1;" |head -n1);
   # coords=$(psql -U postgres -w -d bag -tq -c "SELECT ST_X(geopunt) as x, ST_Y(geopunt) as y FROM adres WHERE postcode = '$postcode' $huisnummer LIMIT 1;" |head -n1);
   php -r 'include("/home/eztrack/bagreverse/branches/master/lib/lib.php"); $ll = rd2wgs('$(echo $coords | cut -d "|" -f 1 | sed 's/[^0-9\.]*//g')','$(echo $coords | cut -d "|" -f 2 | sed 's/[^0-9\.]*//g')'); echo $ll["lat"].", ".$ll["lon"]."\n";'
done
