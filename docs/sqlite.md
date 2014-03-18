### Sqlite

It is easy to convert CosCMS to sqlite or postgresql project using the ruby tool sequel. 

install sequel, sqlite, mysql:

    sudo aptitude install ruby-sequel
    sudo aptitude install libsqlite3-ruby
    sudo aptitude install libsmysql-ruby

sequel mysql://root:password@localhost/default -C sqlite://sqlite/database.sql

change database url in config/config.ini

    url = "sqlite:/home/dennis/www/default/sqlite/database.sql"    

Make sure sqlite/databse.sql is writable and readable: 

    chmod -R 777 sqlite
