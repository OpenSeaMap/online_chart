
# see https://github.com/ngineered/nginx-php-fpm for details

docker run -e 'GIT_REPO=https://github.com/OpenSeaMap/online_chart.git'  -e 'GIT_EMAIL=nobody@localhost' -e 'GIT_NAME=nobody' -p 8080:80 -d richarvey/nginx-php-fpm:stable > container.id
