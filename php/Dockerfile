FROM ubuntu:latest

ENV TZ=Europe/Kiev
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get -y upgrade
#&& apt-get -y upgrade && apt-get install -y apt-utils software-properties-common apache2 && add-apt-repository ppa:ondrej/php && apt-get update 

#RUN apt-get install -y php7.0-dev libpcre3-dev gcc php7.0-phalcon make 
RUN apt-get install -y php php-dev libpcre3-dev gcc
RUN apt-get install -y apache2
RUN apt-get install -y git && git clone https://github.com/phalcon/cphalcon && cd cphalcon/build && ./install
RUN echo 'extension=phalcon.so' >> /etc/php/7.2/mods-available/phalcon.ini
RUN phpenmod phalcon
RUN apt-get install -y nano
EXPOSE 80
WORKDIR /etc/php/7.2
CMD apachectl -D FOREGROUND