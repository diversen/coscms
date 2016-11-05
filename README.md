
	  ___|             ___|  \  |  ___|  
	 |      _ \   __| |     |\/ |\___ \  
	 |     (   |\__ \ |     |   |      | 
	\____|\___/ ____/\____|_|  _|_____/  

# About

CosCMS is a simple modular framework for building web application or shell applications.

Modules are distrubuted as profiles. 

This is the default profile, it includes a 

* Account system
* A blog
* Gallery. 
* Disqus
* Analytics

# Demo

[Demo Site](http://coscms.os-cms.net/) 

Login with `admin` / `admin`

# Install Requirements

You will need: 

* PHP>=5.5 (Tested with PHP7.0)
* PHP extensions: PDO, PDO-mysql, GD, mbstring, intl
* MySQL>=5.5, and 
* Apache2 with mod rewrite module enabled

You can install it else where, but this is the quickstart. 

# Install

Install

    git clone https://github.com/diversen/coscms

    cd coscms
    
Update composer packages
    
    composer update

Create an apache2 host

    ./coscli.sh apache2 --en yoursite.com

Install: 

    ./coscli.sh prompt-install --in
    
You will need a: 

* MySQL user (username and password) who can create a database
* Database name to be created
* Server name (yoursite.com - in our example)

Install will proceed, annd all modules will be cloned from `git` repos. 

At last you are prompted for an email and a password. Enter email and password, but just before logging in, run the following command to set correct file perms

    sudo ./coscli.sh file --chmod-files

In order to change other settings, edit `config/config.ini`
