#!/bin/sh
aptitude install apache2-mpm-prefork
aptitude install mysql-server
aptitude install libapache2-mod-php5

# enable mod rewrite
a2enmod rewrite

# enable default image transformation tool
# GD
aptitude install php5-gd

# install php5 module for mysql
aptitude install php5-mysql

# default configuration uses memcache. If you don't install this
# you will have to change your config/config.ini file after install
# and comment out: session_handler = 'memcache'
aptitude install memcached

# install the php5 module for memcache
aptitude install php5-memcache

# install apc cache for storing all files in memory
aptitude install php-apc

# for doing development - all modules can (should) be installed with
# the {coscli.sh git} commands
aptitude install git

# We need some pear packages. Install the base package
aptitude install php-pear
