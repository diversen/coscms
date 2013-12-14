### About

Very simple github API for PHP using OAuth

### A github app

Make an github app under your github account -> edit profile -> apps

For my example I used: 

URL:
    
    http://cos/test/github/example/github.php

Callback URL:

    http://cos/test/github/example/callback.php

### Example without composer

Clone the source or download it into a web directory

    git clone git://github.com/diversen/simple-php-github-api.git github

copy config.php-dist to config.php

    cp config.php-dist config.php

Edit the 3. constants according to your setup. 

    define('GITHUB_ID', 'github_id');
    define('GITHUB_SECRET', 'github_secret');
    define('GITHUB_CALLBACK_URL', 'http://cos/test/github/callback.php');

Test it: 

go to http://cos/test/github/github.php (or your web path). 

This example will show the user his basic profile info.
This could be used to make e.g. a login system. 

### More github API info

For full listing of all API calls check: 

http://developer.github.com/

I have not tested all calls - but you should be able to use all. E.g. POST,
or PATCH.

### Composer specifics

You can include the lib into a vendor library

edit you `composer.json` file

add the following to repos (after I added this to packagist I believe you
don't need it anymore):

    {
        "type": "vcs",
        "url": "https://github.com/diversen/simple-php-github-api"
    }



add the following to the require section: 

    "diversen/simple-php-github-api": "1.0.2"
