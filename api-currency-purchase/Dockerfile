FROM webdevops/php-nginx:8.3-alpine
ENV WEB_DOCUMENT_ROOT=/app/public
ENV COMPOSER_ALLOW_SUPERUSER=1
#ENV PHP_DISMOD=bz2,calendar,exiif,ffi,intl,gettext,ldap,mysqli,imap,pdo_pgsql,pgsql,soap,sockets,sysvmsg,sysvsm,sysvshm,shmop,xsl,zip,gd,apcu,vips,yaml,imagick,mongodb,amqp

# Argumentos de compilação
ARG user=application
ARG uid=1000

# Configurar PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configurar supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /app

COPY . .

COPY docker/nginx/default.conf /etc/ngix/conf.d/default.conf

# Ensure all of our files are owned by the same user and group.
RUN chown -R $user:$user .

# Instalar dependências do projeto
USER $user
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Voltar para o usuário root para operações finais
USER root

# Expor porta 80
EXPOSE 80
