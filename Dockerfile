# Use a imagem base do PHP com Apache
FROM php:7.4-apache

# Instale extensões do PHP que você pode precisar
RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    zlib1g-dev \
    libzip-dev \
    default-mysql-client \
    mariadb-client && \  
    docker-php-ext-install pdo pdo_mysql zip gd && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Copie os arquivos do projeto para o diretório do Apache
COPY . /var/www/html/

# Configura permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod +x /var/www/html/entrypoint.sh

# Define entrypoint e comando
ENTRYPOINT ["/var/www/html/entrypoint.sh"]

# Defina permissões adequadas para o diretório do projeto
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta 80 para o mundo externo
EXPOSE 80