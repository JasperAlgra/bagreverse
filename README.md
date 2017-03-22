## install

* clone repo

* install apache + php
  Linux: apt-get install apache2 php5-pgsql php5-mysql
  Windows/xampp: extension=php_pgsql.dll (enable)

* Postgres, maak user "bag" met rechten op database bag
  sudo -u postgres createuser -SDRP bag
  psql bag
  GRANT usage ON SCHEMA public TO "bag";
  GRANT SELECT ON ALL TABLES IN SCHEMA public TO "bag";
  GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "bag";

  GRANT USAGE ON SCHEMA bagactueel TO "bag";
  GRANT ALL PRIVILEGES ON SCHEMA bagactueel TO "bag";
  GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA bagactueel TO "bag";
  
  GRANT USAGE ON SCHEMA bag_geocode TO "bag";
  GRANT ALL PRIVILEGES ON SCHEMA bag_geocode TO "bag";
  GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA bag_geocode TO "bag";
  
  GRANT USAGE ON SCHEMA bagaveen TO "bag";
  GRANT SELECT ON ALL TABLES IN SCHEMA bagaveen TO "bag";

* Link www dir naar apache
  * vhost: /bag  -> /bagreverse/www
  * symlink: ln -s ~/bagreverse/www /var/www/bag

* PEAR
  wget http://pear.php.net/go-pear.phar
  php go-pear.phar
  pear install DB
  pear install DB_pgsql

* Test:
    /reverse.php?lat=52.283971&lon=4.854669
    Result: Boogschutter 154, 1188 BX, Amstelveen
