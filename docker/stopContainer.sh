#!/bin/bash
# see https://github.com/ngineered/nginx-php-fpm for details

id=$(<container.id)

docker stop $id
rm container.id
