FROM ubuntu:latest
MAINTAINER igor
RUN apt-get update
RUN apt-get install -y nginx
ADD ./nginx.conf /etc/nginx/
EXPOSE 80

CMD [nginx]