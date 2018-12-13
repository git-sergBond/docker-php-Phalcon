##  Ubuntu 18.04
### Linux Apach Postgres Php
##  7.12.18         by Sergo
FROM ubuntu:18.04

##
### NOTES
##

#Installing shared extensions:     /usr/lib/php/20170718/
#Installing header files:          /usr/include/php/20170718/
#Libraries have been installed in:
#   /etc/php/cphalcon/build/php7/64bits/modules/phalcon.so

##
### Presets
##
ENV TZ=Europe/Kiev
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /etc/php/

#Refresh
RUN apt-get -y update \
    && apt-get -y upgrade

#Instruments
#->DEV TOOLS
RUN apt-get install -y git nano mc

##
### Stack LAPP
##
#->Apache
RUN apt-get -y install apache2 \
    && a2enmod rewrite
VOLUME [ "/var/log/apache2", "/var/www/" ]
EXPOSE 80
CMD /usr/sbin/apache2ctl -D FOREGROUND
#->Postgress
#RUN apt install -y postgresql libpq5 postgresql-10.0 postgresql-client-10.0 postgresql-client-common postgresql-contrib
#->Php
RUN apt install --reinstall -y software-properties-common \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php php-dev libpcre3-dev gcc make re2c

##
### Extensions
##

#->PostgreSQL
RUN apt-get install -y php-pgsql

#->Phalcon
RUN git clone git://github.com/phalcon/cphalcon.git \
    && cd cphalcon/build \
    && ./install \
    && echo 'extension=phalcon.so' >> /etc/php/7.2/mods-available/phalcon.ini \
    && phpenmod phalcon
#->Phalcon-DEV_TOOL 
RUN cd ~ \
    && git clone https://github.com/phalcon/phalcon-devtools.git \
    && cd phalcon-devtools \
    && ln -s $(pwd)/phalcon.php /usr/bin/phalcon \
    && chmod ugo+x /usr/bin/phalcon
    # FIX if [cmd not found] 
    #&& alias phalcon=/home/[USERNAME]/phalcon-devtools/phalcon.php
#-> create test project
#RUN cd /var/www/ \
#    && phalcon create-project store
ADD ./store.conf /etc/apache2/sites-available/
RUN a2dissite 000-default.conf \
    && a2ensite store

#->RESTART SERVICES
RUN /etc/init.d/apache2 restart
#->show installed extensions
RUN php -r 'print_r(get_loaded_extensions());'
