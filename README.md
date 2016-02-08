
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

# Requirements

You will need: 

* PHP>=5.5, PDO, 
* MySQL>=5.5, and 
* Apache2 with mod rewrite module enabled

You can install it else where, but this is the quickstart. 

# Install

Install

    git clone https://github.com/diversen/coscms

    cd coscms
    
Update composer packages
    
	composer update

Create an apache2 host (it should also work with other web servers)

	./coscli.sh apache2 --en yoursite.com

Install: 

    ./coscli prompt-install --in
    
You will need a: 

* MySQL username and password who can create a database
* Database name to be created
* Server name (yoursite.com - in our example)

Tested with Apache2 on Debian systems, and Windows. 
Should work anywhere, even though it is build on Debian systems.  

# Demo

[Demo Site](http://demo.coscms.org/) 

Login with admin / admin. A simple demo with a blog (blog module),
and a CMS (content module) and a comment module (comment), 
and a few more modules. 

# Homepage

[Main site](http://www.coscms.org)

# Extending: 

[Extend](http://www.coscms.org/content/article/view/40/Extend)

[Web Module](http://www.coscms.org/content/article/view/27/Web-Module-Guide)

[Shell Module Guide](http://www.coscms.org/content/article/view/60/Shell-Module-Guide)

Enjoy!
