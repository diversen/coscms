### Sqlite

There is script here which transforms a mysql database to a sqlite
I was curroious to see if this would work. And the script worked on 5.1

https://gist.github.com/esperlu/943776


    scripts/mysql2sqlite -u root -ppassword coscms | sqlite3 sqlite/database.sqlite
   chmod me:group sqlite/database.sqlite
   change url in config/config.ini

