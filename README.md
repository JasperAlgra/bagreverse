# tools:
apt-get install unzip htop bmon git


# Postgres official repo (alle versies van postgres)
# Voor ubuntu 14.04 LTS (trusty)
echo "deb http://apt.postgresql.org/pub/repos/apt/ trusty-pgdg main" > /etc/apt/sources.list.d/pgdg.list
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
apt-get update

# Repos voor postGIS, libgeo
apt-get install python-software-properties
add-apt-repository ppa:ubuntugis/ubuntugis-unstable
apt-get update


apt-get install postgis postgresql-9.1 postgresql-contrib-9.1

# Server Instrumentation, met admin pack.
sudo -u postgres psql
CREATE EXTENSION adminpack;

# Installatie controleren met ::
psql -h localhost -U postgres template1

# Enablen locale connecties in /etc/postgresql/9.1/main/pg_hba.conf

# Postgis
apt-get install postgresql-9.1-postgis-2.1

# Template DB``postgis2`` opzetten
su postgres
createdb postgis2
psql postgis2
CREATE EXTENSION postgis;
CREATE EXTENSION postgis_topology;

# optioneel: setup tools
apt-get install python-setuptools
apt-get install python-dev
apt-get install libpq-dev

# snellere XML parsing
apt-get install libxml2
apt-get install libxslt1.1
apt-get install python-lxml

# GDAL (www.gdal.org) met Python bindings voor OGR geometrie-parsing en geometrie-validatie (NLX v1.1.0 en hoger)
apt-get install gdal-bin
apt-get install python-gdal

# PostgreSQL python bibliotheek psycopg2
easy_install psycopg2

# Python package “argparse”
easy_install argparse

# test import van amsterveen testdata
wget http://data.nlextract.nl/bag/postgis/bag-amstelveen.backup
sudo -u postgres createdb -U postgres  -T postgis2 bag
sudo -u postgres pg_restore --no-owner --no-privileges -d bag bag-amstelveen.backup

# Optioneel: GIT clone van NLExtract project
git clone http://github.com/opengeogroep/NLExtract.git