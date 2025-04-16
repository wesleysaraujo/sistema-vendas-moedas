# Sistema de Compra de Moedas

Um aplicativo web para compra de moedas estrangeiras usando moeda local, com aplicação de taxa de serviço e integração com API externa de cotação.
A aplicação é composta por uma API escrita em Laravel 12 e um Fronend desenvolvido em Vuejs com Nuxt.

## Instalação da API

1. Clone o repositório:
```bash
git clone https://github.com/wesleysaraujo/sistema-vendas-moedas.git
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

## Instalação do frontend
```bash
cd frontend-currency-exchange
````
# Instale as dependências
npm install

# Configure as variáveis de ambiente
cp .env.example .env
```

## Configuração

Edite o arquivo `.env` e configure as variáveis necessárias:

```
NUXT_PUBLIC_API_BASE_URL=http://localhost:3000/
NUXT_PUBLIC_SERVICE_FEE=2
```

## Desenvolvimento

```bash
# Inicie o servidor de desenvolvimento
npm run dev
# ou
yarn dev
```

A aplicação estará disponível em `http://localhost:3000`.

## Build para Produção

```bash
# Construa a aplicação para produção
npm run build
# ou
yarn build

# Inicie a aplicação em modo de produção
npm run start
# ou
yarn start
```
