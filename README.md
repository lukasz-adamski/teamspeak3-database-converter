# TeamSpeak3 Database Converter
Program designed to convert TeamSpeak3 SQLite database to MySQL format.

## Installation guide
To use this program you need PHP with [PHP Data Objects][1] (PDO) extension installed that supports mysql and sqlite drivers.

### How to install on linux?
```bash
sudo apt install php5-cli php5-pdo php5-mysql php5-sqlite
```

## How to use?
1. You need to install clean version of TeamSpeak3 server and setup its instance to work with MySQL (MariaDB) databases. To do this you need to find another guide. After setup you need to run server which will install clean MySQL schema into database given in configuration.
2. If you have installed clean TeamSpeak 3 MySQL schema succesffully. You can run this converter:
```bash
php converter.php ts3db_maria.ini ts3server.sqlitedb
```

3. The conversion process may take a while, so you need to wait to the end of data processing.

[1]: http://php.net/manual/en/book.pdo.php
