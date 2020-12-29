FROM thecodingmachine/php:8.0-v4-apache-node14

ENV TEMPLATE_PHP_INI="production"

ENV CRON_USER_1="docker" \
    CRON_SCHEDULE_1="* * * * *" \
    CRON_COMMAND_1="bin/console app:queue:execute"

ENV APACHE_DOCUMENT_ROOT="public/"

RUN touch /home/docker/.bashrc && printf '\
HISTFILE=~/bash_history\n\
PROMPT_COMMAND="history -a;history -n"\n\
umask 027\n' >> /home/docker/.bashrc

COPY . /var/www/html/
COPY ./docker/app/startup.sh /etc/container/startup.sh
RUN sudo chmod +x /etc/container/startup.sh
