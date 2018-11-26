FROM ubuntu

ENV TZ=Europe/Kiev
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
&& apt-get install -y apache2 \
    software-properties-common \ 
# OR python-software-properties \
&& add-apt-repository ppa:ondrej/php \
&& apt-get update 

RUN apt-get install -y php7.0-dev \
    libpcre3-dev \
    gcc \
    php7.0-phalcon \
    make 

RUN curl -s https://packagecloud.io/install/repositories/phalcon/stable/script.deb.sh | bash
RUN echo 'extension=phalcon.so' >/etc/php/7.0/mods-available/phalcon.ini
