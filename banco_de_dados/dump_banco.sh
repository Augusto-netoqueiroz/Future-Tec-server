#!/bin/bash

# Configurações do banco
USUARIO="root"
SENHA="MKsx2377!@"
BANCO="asterisk"
PASTA="/var/www/projetolaravel/banco_de_dados"
ARQUIVO="dump.sql"

# Gera o dump
mysqldump -u $USUARIO -p$SENHA $BANCO > $PASTA/$ARQUIVO

# Log de execução
echo "$(date '+%Y-%m-%d %H:%M:%S') - Dump realizado com sucesso!" >> $PASTA/backup.log

