FROM  phpmicroservice/docker_php:cli72_swoole_phalcon

MAINTAINER      Dongasai "1514582970@qq.com"

RUN apt update;apt install -y vim
COPY . /var/www/html/
ENV TCP_SERVER_HOST="pms_proxy"
ENV TCP_SERVER_PORT="9502"
EXPOSE 9502
WORKDIR /var/www/html/
CMD php start.php

