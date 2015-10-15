
# see https://github.com/ngineered/nginx-php-fpm for details

locationOfScript=$(dirname "$(readlink -e "$0")")

docker run -p 8080:80 -v $locationOfScript/../:/usr/share/nginx/html -d richarvey/nginx-php-fpm:stable > container.id
