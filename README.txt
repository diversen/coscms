Coscms is a small and simple CMS system / framework for building all kind of websites. Easy to use, administer, and extend with modules (both web and shell modules), templates. Inspired by other PHP systems, like Drupal. Aims is modularity, easy install, setup, administration. It uses quite a few PEAR other packages.  Export websites as profiles. Easy to install and use with Debian systems (like Ubuntu 10.04  or Debian 6.0.0 - tested versions).  

INSTALL: 

You will need a system with PHP5.3. You can use versions lower but they have issues with the system (better parsing of ini files in PHP5.3) 

REQUIREMENTS

On Ubuntu (on Debian you will need todo a su root):  

	#!/bin/bash

	# Required system packages (it is PHP so it will work with others)

	sudo aptitude install \
	apache2 \
	mysql-server \
	libapache2-mod-php5 \
	php5 \
	php5-mysql \
	php5-mcrypt \
	php5-gd \
	php5-cli \
	git-core \
	php-pear \
	
	# You will need rewrite enabled. 
        
        sudo a2enmod rewrite

	# Install some usefull pear packages

	sudo pear install Pager
	sudo pear install Console_Commandline
	sudo pear install Text_Highlighter-0.7.1
	sudo pear install Validate-0.8.4
	sudo pear install Mail
	sudo pear install Mail_Mime
	sudo pear install HTML_Safe-0.10.1
	sudo pear install Image_Transform-0.9.3
	sudo pear install Net_SMTP
	sudo pear install Cache_Lite

	# install markdown (used in a couple of filters)

	sudo pear channel-discover pear.michelf.com
	sudo pear install michelf/MarkdownExtra

	# install facebook php-sdk (only needed if you want users to be able to use facebook as registration method)

	sudo pear channel-discover pearhub.org
	sudo pear install pearhub/facebook

	# For automatic documentation. Install:
	sudo pear install PhpDocumentor

	# for aksimet
	sudo pear install Net_URL2-0.3.1
	sudo pear install HTTP_Request2-0.5.2
	sudo pear install Services_Akismet2-0.3.1

	# Install Zend Framework (Need if you want to use openID)

	sudo aptitude install zendframework

	#

	echo "Remember to check if Zend/ directory exists in your php_include_path"
	echo "Or move Zend/ to default include_path (only used for open id so far.)"
	echo "But always good to have!"
	echo ""
	echo "Zend lib should be placed in /usr/share/php/Zend"


