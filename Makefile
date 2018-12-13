include .env
##
### manage containers
##
#fix
#sudo chown -R $USER <directory_project>
create-dirs:
	sudo mkdir ${MY_SITES}
	sudo mkdir ${MY_SITES}/${MY_BACKEND}
	sudo mkdir ${MY_SITES}/${MY_FRONTEND}
	sudo mkdir ${MY_PHP_LOGS}
	sudo mkdir ${MY_DB_LOGS}
dc-up-build:
	sudo docker-compose up --build
dc-build:
	sudo docker-compose build
dc-up:
	sudo docker-compose up
dc-del-all:
	sudo docker rm $(sudo docker ps -aq)
	sudo docker rmi $(sudo docker images -q)

##
### install's
##
test-install:
	sudo docker-compose exec apache sh -c 'cd /var/www/ && phalcon create-project store'

reinstall-backend:
	sudo rm -rf ${MY_SITES}/${MY_BACKEND} \
	&& git clone https://github.com/RastCorp/backendPhalcon ${MY_SITES}/${MY_BACKEND} \
	&& cat ${MY_CONTAINERS}/apache/config.php > ./sites/store/app/config/config.php \
	&& docker-compose up composer

reinstall-frontend:
	sudo rm -rf ${MY_SITES}/${MY_FRONTEND} \
	&& git clone https://github.com/RastCorp/WebClientSPA ${MY_SITES}/${MY_FRONTEND} \
	&& cd ${MY_SITES}/${MY_FRONTEND} \
	&& sudo npm install \
	&& sudo npm install -g 

upload-frontend:
	cd ${MY_SITES}/${MY_FRONTEND} \
	&& sudo npm run build
	#del files in backend
	#copy all files

##
### Data base
##
clear-db:
	sudo docker-compose exec postgresql sh -c 'dropdb postgres'
create-db:
	sudo docker-compose exec postgresql sh -c 'psql postgres < ${DC_DB_DUMP}'
scheme-db:
	#
dump-sd:
	#

##
### open console in container
##
console-php:
	sudo docker-compose exec apache bash
console-db:
	sudo docker-compose exec postgresql bash
