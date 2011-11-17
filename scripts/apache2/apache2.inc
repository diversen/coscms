<?php

$apache_str = <<<EOD
<VirtualHost *:80>
    ServerAdmin webmaster@example.com
    ServerName  {$SERVER_NAME}

    # Indexes + Directory Root.
    DirectoryIndex index.php
    DocumentRoot {$DOCUMENT_ROOT}
  
    <Directory {$DOCUMENT_ROOT}>
        RewriteEngine on
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]
    </Directory>

<Files *.inc>
deny from all
</Files>

<Files info.php>
deny from all
allow from 127.0.0.1
</Files>

<Files *.sql>
deny from all
</Files>

<Files *.ini>
deny from all
</Files>
   
#    ErrorLog  {$APACHE_LOG_ROOT}/error.log
#    CustomLog {$APACHE_LOG_ROOT}/access.log combined
</VirtualHost>
EOD;
