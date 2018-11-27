сборка
удаление контейров, образова, ...
дамп
удаление бд


conf.php

sudo apt-get install postgresql postgresql-contrib

sudo -u postgres psql
CREATE USER bro4you_parser WITH PASSWORD '123456';
CREATE DATABASE bro4you_parser;
GRANT ALL ON DATABASE bro4you_parser TO bro4you_parser;
migtration
sudo -u postgres psql bro4you_parser < ./db/dump.sql


<Directory /home/dev/Phpstormprojects/>
    Options Indexes FollowSymLinks
    AllowOverride None
    Require all granted
</Directory>
<VirtualHost *:80>
ServerName phalcon-projectname
DocumentRoot /var/www/projectname/
</VirtualHost>