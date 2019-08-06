#!/usr/bin/env bash

case $1 in
    restart)
        CONTAINER_EXISTS=$(docker container ls | grep 'nginx-reverse-proxy')
        NETWORK_EXISTS=$(docker network ls | grep 'reverse-proxy')
        REVERSE_PROXY_PORT=$(awk '/^reverse_proxy_port/{print $3}' /etc/nginx-reverse-proxy/config)
        if [[ ! -z "$CONTAINER_EXISTS" ]]; then
            docker stop nginx-reverse-proxy
            docker rm nginx-reverse-proxy
        fi
        if [[ -z "$NETWORK_EXISTS" ]]; then
            docker network create --driver bridge reverse-proxy
        fi
        # Extra conf.d files
        EXTRA='';
        for F in /etc/nginx-reverse-proxy/conf.d/* /etc/nginx-reverse-proxy/conf.d/.[^.]*; do
            EXTRA="${EXTRA} -v ${F}:/etc/nginx/conf.d/$(basename ${F}):ro"
        done
        docker run -d -p ${REVERSE_PROXY_PORT}:${REVERSE_PROXY_PORT} \
            --name nginx-reverse-proxy \
            --net reverse-proxy \
            -v /var/run/docker.sock:/tmp/docker.sock:ro \
            -v /etc/nginx-reverse-proxy/nginx.tmpl:/app/nginx.tmpl:ro \
            -v /etc/nginx-reverse-proxy/nginx-proxy.conf:/etc/nginx/proxy.conf:ro \
            -v /etc/nginx-reverse-proxy/nginx-proxy-503.tmpl:/app/nginx-proxy-503.tmpl:ro \
            -v /etc/nginx-reverse-proxy/docker-gen.cfg:/app/docker-gen.cfg:ro \
            -v /etc/nginx-reverse-proxy/Procfile:/app/Procfile:ro \
            ${EXTRA} \
            -e LISTENED_PORT=${REVERSE_PROXY_PORT} \
            --restart always \
            jwilder/nginx-proxy
    ;;
    enter)
        docker exec -i -t nginx-reverse-proxy /bin/bash
    ;;
    show-config)
        docker exec -i -t nginx-reverse-proxy cat /etc/nginx/conf.d/default.conf
    ;;
    *)
        echo -e "Enabled commands: \033[33mrestart\033[0m, \033[33menter\033[0m and \033[33mshow-config\033[0m"
    ;;
esac
