
	  ___|             ___|  \  |  ___|  
	 |      _ \   __| |     |\/ |\___ \  
	 |     (   |\__ \ |     |   |      | 
	\____|\___/ ____/\____|_|  _|_____/  

# About

CosCMS is a simple modular framework for building web application or shell applications.

Modules are distrubuted as profiles. 

This is the default profile, it includes a 

* Account system
* Content / CMS system (with epub, mobi and pdf export options using pandoc)
* A blog
* Gallery. 
* Disqus
* Analytics
* And some other modules 

# Demo

[Demo Site](http://demo.coscms.org/) 

Login with `admin` / `admin`

# Install Requirements

You will need: 

* PHP>=5.5
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

* MySQL username and password who can create a database
* Database name to be created
* Server name (yoursite.com - in our example)

Install will proceed, an clone all modules from .git repos. 

At last you are prompted for a email and a password. Enter email and password, but just before logging in, run the following command to set correct file perms

    sudo ./coscli.sh file --chmod-files

[Other install methods](http://www.coscms.org/content/article/view/72/Install)

# Homepage

[Main site](http://www.coscms.org)

# Extending: 

[Extend](http://www.coscms.org/content/article/view/40/Extend)

[Web Module](http://www.coscms.org/content/article/view/27/Web-Module-Guide)

[Shell Module Guide](http://www.coscms.org/content/article/view/60/Shell-Module-Guide)

Enjoy!
