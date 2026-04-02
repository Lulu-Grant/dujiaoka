FROM webdevops/php-nginx:7.4
COPY . /app
WORKDIR /app
RUN [ "sh", "-c", "composer install --ignore-platform-reqs --no-interaction" ]
RUN [ "sh", "-c", "chmod -R 777 /app" ]
