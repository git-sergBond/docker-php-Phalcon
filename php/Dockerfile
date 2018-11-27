FROM ubuntu:16.04

ENV TZ=Europe/Kiev
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt update && apt -y upgrade && apt install -y software-properties-common apache2 curl\
    && rm -r /var/lib/apt/lists/* \
    && rm -rf /tmp/* \
# OR python-software-properties \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update 

RUN apt install -y php7.0 php7.0-dev \
    libpcre3-dev \
    gcc \
    php7.0-phalcon \
    make \
    && rm -r /var/lib/apt/lists/* \
    && rm -rf /tmp/* \

RUN curl -s https://packagecloud.io/install/repositories/phalcon/stable/script.deb.sh | bash
RUN echo 'extension=phalcon.so' >/etc/php/7.0/mods-available/phalcon.ini

CMD [ "/etc/init.d/apache2 start" ]