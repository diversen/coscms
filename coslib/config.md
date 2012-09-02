config
======

Config class is used for setting module and main configuration. 

Main configuration is usually set in config/config.ini

### config::getMainIni($key)

Gets a main configuration setting from config/config.ini

    $url = config::getMainIni('url');

returns the database url from config.ini