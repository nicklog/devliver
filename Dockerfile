FROM thecodingmachine/php:7.4-v4-apache-node14

ENV APACHE_DOCUMENT_ROOT="public/"
ENV STARTUP_COMMAND_1="composer install --no-interaction --no-progress --classmap-authoritative"
ENV STARTUP_COMMAND_2="bin/console doctrine:migrations:migrate --no-interaction"

RUN touch /home/docker/.bashrc && printf '\
HISTFILE=~/bash_history\n\
PROMPT_COMMAND="history -a;history -n"\n\
umask 027\n' >> /home/docker/.bashrc

COPY . /var/www/html/
