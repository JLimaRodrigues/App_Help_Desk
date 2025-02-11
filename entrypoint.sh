#!/bin/bash

# Função para verificar se o MySQL está pronto
wait_for_db() {
    echo "Aguardando o banco de dados ficar disponível..."
    while ! mysqladmin ping -h"db" -u"root" -p"root" --silent; do
        sleep 1
    done
    echo "Banco de dados disponível!"
}

# Executa a verificação
wait_for_db

# Executa o script de migrations
php /var/www/html/migrations.php

# Mantém o container rodando o Apache
exec apache2-foreground