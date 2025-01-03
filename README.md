Future-Tec Server - Sistema de Call Center

Este é um sistema desenvolvido em Laravel, com foco em integração de call center e banco de dados. O projeto permite gerenciar chamadas, gravações e eventos associados de forma eficiente.

Funcionalidades

Integração com o Asterisk via AMI (Asterisk Manager Interface).

Gerenciamento de gravações de chamadas.

Monitoramento em tempo real com Laravel Echo.

APIs para manipulação de dados de chamadas e usuários.

Tecnologias Utilizadas

Laravel (framework PHP)

MySQL (banco de dados)

Asterisk (telefonia)

PHP 8.3

Composer (gerenciamento de dependências)

Node.js e NPM (frontend e eventos em tempo real)

Instalação

Para rodar o projeto, siga os passos abaixo:

Clone o repositório:

git clone https://github.com/seu-usuario/future-tec-server.git
cd future-tec-server

Instale as dependências do backend:

composer install

Instale as dependências do frontend:

npm install

Configure o ambiente:

Renomeie o arquivo .env.example para .env.

Edite o arquivo .env com as credenciais do banco de dados e configurações do Asterisk.

Configure o banco de dados:

php artisan migrate --seed

Gere a chave da aplicação:

php artisan key:generate

Inicie o servidor backend:

php artisan serve

Inicie o servidor Laravel Echo (se aplicável):

laravel-echo-server start

Observações

Certifique-se de que o Asterisk esteja configurado corretamente para comunicação via AMI.

Configure permissões adequadas para o diretório storage e gravacoes.

Contato

Desenvolvedor: [Seu Nome]

Email: [seu.email@example.com]

Telefone: [Seu Telefone]
