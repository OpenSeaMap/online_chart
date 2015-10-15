#!/bin/bash
# see https://github.com/ngineered/nginx-php-fpm for details

id=$(<container.id)
echo $id
docker exec -t -i $id /usr/bin/pull
