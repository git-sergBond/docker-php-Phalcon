FROM php:7.2-apache

#RUN apt-get update \
#&& apt-get install -y software-properties-common 

#RUN apt-get install libpcre3-dev \
   # gcc \
   # make 

RUN docker-php-ext-install \
    pdo_pgsql \
    && a2enmod rewrite
RUN apt-get install php7.0-phalcon 
    
RUN curl -s https://packagecloud.io/install/repositories/phalcon/stable/script.deb.sh | bash
RUN echo 'extension=phalcon.so' > /etc/php/7.0/mods-available/phalcon.ini

