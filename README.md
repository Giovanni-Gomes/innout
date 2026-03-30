# InNOut

Sistema web de registro de ponto e acompanhamento de horas trabalhadas, desenvolvido em Laravel.

## Apenas desenvolvimento

Este projeto está preparado para **portfolio e ambiente local**. Comportamentos que não devem existir em produção sem revisão:

- **Simulação de horário** no registo de ponto (formulário "Simular Ponto") só aparece e só é aceite pelo servidor quando `APP_ENV=local`. Fora disso, usa-se sempre a hora real do servidor.
- **Dados fictícios** de batidas: não há rota web; use apenas o comando Artisan abaixo. Ele **remove todos os registos** da tabela `working_hours` e gera histórico de exemplo para cada utilizador **não administrador**. Exige `APP_ENV=local`.

```powershell
php artisan innout:demo-data
```

- Para qualquer deploy real: altere palavras-passe predefinidas, use `APP_DEBUG=false`, HTTPS e políticas de segurança adequadas.

## Funcionalidades

- Autenticacao com Laravel Breeze
- Registro de pontos diarios (`entrada/saida`)
- Relatorio mensal por usuario
- Area administrativa (`/admin`)
- Seeder com utilizador administrador e funcionário de demonstração

## Stack

- PHP 8.2
- Laravel 9.52
- Composer 2
- Node.js + npm
- Laravel Mix 6 + Tailwind CSS
- MySQL 8 (desenvolvimento local com **XAMPP** ou **Docker Compose**)

## Requisitos

- PHP 8.2+ com extensoes comuns do Laravel (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `zip`)
- Composer
- Node.js (recomendado >= 18)
- npm
- **MySQL** acessivel (via XAMPP ou container do Compose)
- Para a opcao Docker: [Docker](https://docs.docker.com/get-docker/) e Docker Compose v2

## Como rodar

### Passos comuns (dependencias e ambiente)

1. Instalar dependencias PHP

```powershell
composer install
```

2. Instalar dependencias front-end

```powershell
npm install
```

3. Criar arquivo de ambiente

```powershell
Copy-Item .env.example .env
```

---

### Opcao A: PHP na maquina + MySQL do XAMPP

1. No XAMPP, inicie o **MySQL** e crie um banco (por exemplo `innout`) no phpMyAdmin ou cliente MySQL.

2. Ajuste o `.env` para apontar para o MySQL local (exemplo tipico do XAMPP; a senha do `root` pode ser vazia ou a que voce definiu):

```env
APP_NAME=InNOut
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=innout
DB_USERNAME=root
DB_PASSWORD=
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

3. Gerar chave da aplicacao e preparar o banco:

```powershell
php artisan key:generate
php artisan migrate --seed --force
```

4. Compilar assets e subir o servidor:

```powershell
npm run dev
php artisan serve --host=127.0.0.1 --port=8000
```

Acesse: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

### Opcao B: Docker Compose (app + MySQL)

O ficheiro `docker-compose.yml` sobe o MySQL 8 e a aplicacao Laravel num container `app`, com variaveis de base de dados ja alinhadas (`DB_HOST=mysql`, base `innout`, utilizador `root`, palavra-passe `secret` — igual ao definido no servico MySQL).

Na raiz do projeto:

```powershell
docker compose up --build
```

- A aplicacao fica em [http://localhost:8000](http://localhost:8000)
- O entrypoint do container executa `migrate` ao arranque. Para popular o utilizador administrador (seeder), num segundo terminal:

```powershell
docker compose exec app php artisan db:seed --force
```

Para gerar batidas fictícias (só com `APP_ENV=local` no container):

```powershell
docker compose exec app php artisan innout:demo-data
```

Para alterar CSS/JS, continue a usar `npm run dev` na sua maquina (as dependencias Node nao estao na imagem Docker).

Se correr o PHP localmente mas quiser so o MySQL no Docker, use `DB_HOST=127.0.0.1` e `DB_PASSWORD=secret` no `.env`, com a porta `3306` exposta pelo Compose.

## Credenciais iniciais

Após `php artisan migrate --seed`:

- **Administrador** — email: `admin@cod3r.com.br`, senha: `password`
- **Funcionário (demo)** — email: `funcionario@cod3r.com.br`, senha: `password`

## Comandos uteis

```powershell
php artisan config:clear
php artisan route:list
php artisan migrate:fresh --seed
php artisan innout:demo-data
```

## Observacoes

- Se o Composer mostrar aviso relacionado a `zip`, habilite `extension=zip` no `php.ini`.
- Com Docker, detalhes sobre `DB_HOST` e cache de configuracao estao comentados no `.env.example`.
