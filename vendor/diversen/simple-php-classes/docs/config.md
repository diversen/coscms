config
======

Config class is used for setting module and main configuration. 

Main configuration is usually set in config/config.ini, but you can alter this 
in any modules.

### Format

The format is old style .ini setting. 

    my_setting_1 = 'test'
    my_setting_2 = 1232

This format has good and bad sides. The good is that a .ini file is easy 
to manipulate in programs. The bad side is that it is difficult to e.g. add
annonymous functions inside a configuration file. 

### Get a main configuration

Gets a main configuration setting from config/config.ini

    $url = config::getMainIni('url');

Returns the database url from config.ini

Depending on your demands you can put any settings in config/config.ini

### Get module configuration

When you include a module the module.ini file is read per auto. In this file
you can places settings that is only used when using your module

You get a module ini setting this way: 

    $my_setting = config::getModuleIni('my_setting');

These are usaually poor man namespaced. In e.g. the account module all
ini settings are prefixed with `account`, e.g. 

    account_logins[] = openid

### Prevent override on checkout

All places where there is configuration files there is often a .gitignore file
with a single line: 

     *.ini

This prevent you from removing any changes to your personal ini file on a 
checkout. 