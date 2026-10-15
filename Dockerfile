FROM hhvm/hhvm-proxygen:latest

WORKDIR /var/www
COPY . /var/www

EXPOSE 80
