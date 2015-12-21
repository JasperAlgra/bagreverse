## install
* clone repo
* install apache + php + pear
  wget http://pear.php.net/go-pear.phar
  php go-pear.phar
  pear install DB
* Link www dir naar apache
  * vhost: /bag  -> /bagreverse/www
  * symlink: ln -s ~/bagreverse/www /var/www/bag


