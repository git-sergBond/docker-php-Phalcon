FROM ubuntu:18.04
ENV TZ=Europe/Kiev
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get -y update \
    && apt-get -y upgrade
RUN apt install --reinstall -y software-properties-common \
    && apt-get update
RUN apt-key adv --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys B97B0AFCAA1A47F044F244A07FCC7D46ACCC4CF8
RUN echo "deb http://apt.postgresql.org/pub/repos/apt/ precise-pgdg main" > /etc/apt/sources.list.d/pgdg.list
RUN apt-get update && apt-get install -y postgresql-10 postgresql-client-10 postgresql-contrib-10
USER postgres
RUN /etc/init.d/postgresql start \
    &&  psql --command "CREATE USER docker WITH SUPERUSER PASSWORD 'docker';" \
    &&  createdb -O docker docker
RUN echo "host all  all    0.0.0.0/0  md5" >> /etc/postgresql/10/main/pg_hba.conf
RUN echo "listen_addresses='*'" >> /etc/postgresql/10/main/postgresql.conf
EXPOSE 5432
CMD ["/usr/lib/postgresql/10/bin/postgres", "-D", "/var/lib/postgresql/10/main", "-c","config_file=/etc/postgresql/10/main/postgresql.conf"]
VOLUME  ["/etc/postgresql", "/var/log/postgresql", "/var/lib/postgresql"]