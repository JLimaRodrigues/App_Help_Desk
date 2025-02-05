# Use a imagem base do PHP com Apache
FROM php:7.4-apache

# Instale extensões do PHP que você pode precisar
RUN docker-php-ext-install pdo pdo_mysql

# Copie os arquivos do projeto para o diretório do Apache
COPY . /var/www/html/

# Defina permissões adequadas para o diretório do projeto
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta 80 para o mundo externo
EXPOSE 80