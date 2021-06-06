FROM docker.io/bitnami/laravel:7

COPY ./container /container

ENTRYPOINT ["/container/entrypoint.sh"]
CMD [ "php", "artisan", "serve", "--host=0.0.0.0", "--port=3000" ]