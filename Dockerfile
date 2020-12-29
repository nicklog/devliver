FROM thecodingmachine/php:8.0-v4-apache-node14

ENV TEMPLATE_PHP_INI="production"

ENV CRON_USER_1="docker" \
    CRON_SCHEDULE_1="* * * * *" \
    CRON_COMMAND_1="bin/console app:queue:execute"
    
ENV STARTUP_COMMAND_1="bin/console cache:clear" \
    STARTUP_COMMAND_2="bin/console doctrine:migrations:migrate --no-interaction" 

ENV APACHE_DOCUMENT_ROOT="public/"

RUN touch /home/docker/.bashrc && printf '\
HISTFILE=~/bash_history\n\
PROMPT_COMMAND="history -a;history -n"\n\
umask 027\n' >> /home/docker/.bashrc

COPY . /var/www/html/
RUN sudo chown -R docker:docker /var/www/html/

RUN composer install --no-dev --no-interaction --no-progress --classmap-authoritative && \
    yarn install && \
    yarn prod && \
    sudo rm -rf assests docker docs node_modules tests
