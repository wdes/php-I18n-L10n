#!/bin/bash
brew remove mysql
brew remove mariadb
brew uninstall mysql
sudo chown -R $(whoami):admin /usr/local

brew cleanup
brew update
rm -Rf /usr/local/var/mysql 2>/dev/null
rm /etc/my.cnf 2>/dev/null
brew install mariadb
rm /etc/my.cnf 2>/dev/null
mysql.server start
ls /tmp

brew install php72
sed -i -e 's/^memory_limit = .*/memory_limit = -1/' /usr/local/etc/php/7.2/php.ini
curl https://getcomposer.org/installer | php
ln -s "$(pwd)/composer.phar" /usr/local/bin/composer
