## Manual voor BAG
apt-get install -y python-pygresql python-setuptools python-dev libpq-dev libxml2 libxslt1.1 python-lxml subversion

easy_install psycopg2
easy_install argparse
easy_install GDAL

su - postgres

# createdb --owner eztrack -T postgis -E UTF8 bag

createdb --owner bag -E UTF8 bag -T template0
# createlang plpgsql bag
# psql -d bag -f /usr/share/postgresql/8.4/contrib/postgis.sql
# psql -d bag -f /usr/share/postgresql/9.1/contrib/postgis-1.5/postgis.sql
psql -d bag -f /usr/local/pgsql/share/contrib/postgis-2.0/postgis.sql

# exit postgres..

# export BAGROOT=/home/ap/projects/NLExtract/bag
# export BAGEXTRACT=/home/ap/downloads/bag

git clone https://github.com/appelflap/NLExtract.git

export BAGROOT=/home/nominatim/NLExtract/bag
export BAGEXTRACT=/home/nominatim/bag

# wget http://www.kadaster.nl/BAG/docs/Testbestand_BAG_Extract.zip -O $BAGEXTRACT/test.zip

$BAGROOT/bin/bag-extract.sh -v --dbinit

$BAGROOT/bin/bag-extract.sh -v -e $BAGEXTRACT/DNLDLXEE02-9990000000-999000006-01042011.zip
$BAGROOT/bin/bag-extract.sh -v -e $BAGEXTRACT/DNLDLXAM02-9990000000-999000000-01042011-02042011.zip
[..]

# verrijk met oude code, maar nieuw bestand
/home/nominatim/NLExtract/bag/bin/bag-extract.sh -v -e Gemeente-woonplaats-relatietabel.zip

$BAGROOT/bin/bag-extract.sh -v -q $BAGROOT/db/script/gemeente-provincie-tabel.sql


# $BAGROOT/bin/bag-extract.sh -v -q $BAGROOT/db/script/fix-foute-woonplaatscodes.sql
$BAGROOT/bin/bag-extract.sh -v -q $BAGROOT/db/script/ontdubbel.sql
$BAGROOT/bin/bag-extract.sh -v -q $BAGROOT/db/script/bag-view-actueel-bestaand.sql
$BAGROOT/bin/bag-extract.sh -v -q $BAGROOT/db/script/adres-tabel.sql

# $BAGROOT/bin/bag-extract.sh -v -q $BAGROOT/db/script/adres-reverse-geocode.sql

# Kan zijn dat deze nodig is...
# $BAGROOT/bin/bag-extract.sh -v -q /usr/src/postgis-2.0.2/spatial_ref_sys.sql
# INSERT into spatial_ref_sys (srid, auth_name, auth_srid, proj4text, srtext) values ( 28992, 'epsg', 28992, '+proj=sterea +lat_0=52.15616055555555 +lon_0=5.38763888888889 +k=0.9999079 +x_0=155000 +y_0=463000 +ellps=bessel +units=m +towgs84=565.237,50.0087,465.658,-0.406857,0.350733,-1.87035,4.0812 +no_defs', 'PROJCS["Amersfoort / RD New",GEOGCS["Amersfoort",DATUM["Amersfoort",SPHEROID["Bessel 1841",6377397.155,299.1528128,AUTHORITY["EPSG","7004"]],AUTHORITY["EPSG","6289"]],PRIMEM["Greenwich",0,AUTHORITY["EPSG","8901"]],UNIT["degree",0.01745329251994328,AUTHORITY["EPSG","9122"]],AUTHORITY["EPSG","4289"]],UNIT["metre",1,AUTHORITY["EPSG","9001"]],PROJECTION["Oblique_Stereographic"],PARAMETER["latitude_of_origin",52.15616055555555],PARAMETER["central_meridian",5.38763888888889],PARAMETER["scale_factor",0.9999079],PARAMETER["false_easting",155000],PARAMETER["false_northing",463000],AUTHORITY["EPSG","28992"],AXIS["X",EAST],AXIS["Y",NORTH]]');
# UPDATE spatial_ref_sys SET proj4text = '+proj=sterea +lat_0=52.15616055555555 +lon_0=5.38763888888889 +k=0.9999079 +x_0=155000 +y_0=463000 +ellps=bessel +units=m +towgs84=565.237,50.0087,465.658,-0.406857,0.350733,-1.87035,4.0812 +no_defs' WHERE srid = 28992;

$BAGROOT/bin/bag-extract.sh -v -q $BAGROOT/db/script/geocode/geocode-tabellen.sql
$BAGROOT/bin/bag-extract.sh -v -q $BAGROOT/db/script/geocode/geocode-functies.sql