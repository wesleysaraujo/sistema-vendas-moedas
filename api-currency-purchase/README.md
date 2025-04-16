# Sistema de Compra de Moedas

Um aplicativo web para compra de moedas estrangeiras usando moeda local, com aplicação de taxa de serviço e integração com API externa de cotação.

## 📝 Índice

- [Visão Geral](#visão-geral)
- [Tecnologias](#tecnologias)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Funcionalidades](#funcionalidades)
- [API](#api)
- [Testes](#testes)
- [Docker](#docker)
- [Licença](#licença)

## Visão Geral

O Sistema de Compra de Moedas é uma aplicação web que permite aos usuários adquirir moedas estrangeiras utilizando a moeda local. O sistema integra-se com a API AwesomeAPI para obter cotações atualizadas de câmbio, aplica taxas de serviço sobre as transações e mantém um registro histórico das operações realizadas por cada usuário.

## Tecnologias

- **Backend**: Laravel 10.x
- **Database**: MySQL ou PostgreSQL
- **Container**: Docker e Docker Compose
- **Testes**: PHPUnit
- **API Externa**: [AwesomeAPI](https://docs.awesomeapi.com.br/api-de-moedas)

## Requisitos

- Docker e Docker Compose
- Composer (opcional, caso prefira executar comandos fora do container)
- Git

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/wesleysaraujo/sistema-vendas-moeda.git
cd api-currency-exchange
```

2. Inicialize o ambiente Docker:
```bash
docker-compose up -d
```

3. Instale as dependências do Laravel:
```bash
docker-compose exec currency-exchange-app composer install
```

4. Copie o arquivo de ambiente e configure suas variáveis:
```bash
cp .env.example .env
docker-compose exec currency-exchange-app php artisan key:generate
```

5. Execute as migrações do banco de dados:
```bash
docker-compose exec currency-exchange-app php artisan migrate
```

6. Popule a tabela currencies rodando o comando:
```bash
docker-compose exec currency-exchange-app php artisan app:import-currencies
```

## ⚙️ Configuração

Abra o arquivo `.env` e configure os seguintes parâmetros:

```
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=currency_exchange
DB_USERNAME=root
DB_PASSWORD=sua_senha

# Taxa de serviço (percentual)
SERVICE_FEE_PERCENTAGE=2.5

# Configuração da API de moedas
CURRENCY_API_BASE_URL=https://economia.awesomeapi.com.br/json
```

## 📁 Estrutura do Projeto

```
app/
├── Console/
│   ├── Commands/
│   │   └── ImportCurrenciesFromAwesomeApi.php
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
|   │   │   ├── AuthController.php
│   │   │   ├── CurrenciesController.php
│   │   │   └── TransactionsController.php
│   ├── Middleware/
│   └── Requests/
├── Models/
│   ├── User.php
|   ├── Currency.php
│   └── Transaction.php
├── Services/
│   ├── CurrencyService.php
│   └── TransactionService.php
config/
database/
├── migrations/
docker/
├── php/
└── nginx/
routes/
├── api.php
└── console.php
tests/
├── Feature/
|   ├── AuthenticationTest.php
│   ├── CurrencyEndpointsTest.php
│   └── TransactionEndpointsTest.php
└── Unit/
│   ├── CurrencyServiceTest.php
    └── TransactionServiceTest.php
docker-compose.yml
Dockerfile
.env.example
```

## ✨ Funcionalidades

1. **Listagem de Moedas Disponíveis**
   - Exibição das moedas disponíveis para compra
   - Taxas de câmbio atualizadas em tempo real

2. **Compra de Moedas**
   - Cálculo do valor total incluindo taxa de serviço
   - Registro da transação no banco de dados

3. **Histórico de Transações**
   - Listagem das transações realizadas pelo usuário
   - Filtragem por período e tipo de moeda

## 🔌 API

### Endpoints Disponíveis

#### Listar Moedas Disponíveis
```
GET /api/currencies
```
Retorna a lista de moedas disponíveis e suas taxas de câmbio atuais.

#### Obter Detalhes de uma Moeda
```
GET /api/currencies/show/{code}
```
Retorna detalhes da cotação para uma moeda específica.

#### Simular Compra
```
POST /api/transactions/simulate
```
Calcula o custo total de uma compra simulada incluindo taxas.

Body:
```json
{
    "currency_code": "USD",
    "amount": 100.00
}
```

#### Realizar Compra
```
POST /api/transactions
```
Realiza uma transação de compra de moeda.

Body:
```json
{
    "currency_code": "USD",
    "amount": 100.00
}
```

#### Listar Transações do Usuário
```
GET /api/transactions
```
Retorna o histórico de transações do usuário autenticado.

## 🧪 Testes

Execute os testes automatizados com:

```bash
docker-compose exec app php artisan test
```

Tipos de testes implementados:
- Testes unitários para cálculos de transação
- Testes de integração com a API externa
- Testes de funcionalidade dos endpoints da API

## Docker

O ambiente Docker inclui:

- PHP 8.3 com extensões necessárias
- MySQL 8.0
- Nginx como servidor web
- Redis (para cache)

Para reconstruir os containers:
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE).
