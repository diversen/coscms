### Sqlite

There is script here which transforms a mysql database to a sqlite
I was curroious to see if this would work. And the script worked on 5.1

https://gist.github.com/esperlu/943776

    mkdir sqlite

    scripts/mysql2sqlite -u root -ppassword coscms | sqlite3 sqlite/database.sqlite
   
    sudo chown -R you:www-data sqlite
    sudo chmod -R 777 sqlite

change database url in config/config.ini
   
    url = "sqlite:/home/dennis/www/coscms/sqlite/database.sql"
