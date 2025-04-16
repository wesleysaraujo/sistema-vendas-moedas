# Sistema de Vendas de Moedas - Frontend Nuxt

## Visão Geral

Este projeto é um sistema de vendas de moedas desenvolvido com Nuxt.js. A aplicação permite aos usuários comprar diferentes tipos de moedas, visualizar taxas em tempo real, confirmar transações após um resumo detalhado e acessar um histórico completo de suas transações anteriores.

## Tecnologias Utilizadas

- **Nuxt.js 3**: Framework baseado em Vue.js para aplicações universais
- **Vue 3**: Framework JavaScript progressivo
- **Tailwind CSS**: Framework de CSS utilitário
- **Axios**: Cliente HTTP

## Requisitos

- Node.js (v16+)
- NPM ou Yarn
- API de backend configurada

## Instalação

```bash
# Clone o repositório
git clone https://github.com/wesleysaraujo/sistema-vendas-moedas.git

# Entre no diretório do projeto
cd sistema-vendas-moedas/frontend

# Instale as dependências
npm install

# Configure as variáveis de ambiente
cp .env.example .env
```

## Configuração

Edite o arquivo `.env` e configure as variáveis necessárias:

```
NUXT_PUBLIC_API_BASE_URL=http://localhost:3000/api
NUXT_PUBLIC_AUTH_SECRET=sua-chave-secreta
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


## Componentes Principais

### Formulário de Compra de Moedas

Utilizamos componentes shadcn-vue para criar uma experiência de compra fluida:

- `Select` e `ComboBox` para seleção de moedas
- `Form` e `InputField` para entrada de montante
- `Card` para o resumo da compra
- `Button` para confirmação e cancelamento

## Integração com API

A comunicação com o backend é realizada através de endpoints REST:

- `GET /api/currencies` - Lista todas as moedas disponíveis
- `POST /api/transactions` - Realiza uma nova compra
- `Post /api/transactions/simulate` - Realiza uma simulação de compra para capturar a taxa atualizada
- `GET /api/transactions` - Obtém o histórico de transações

## Contribuição

1. Faça um fork do projeto
2. Crie sua branch de feature (`git checkout -b feature/nova-funcionalidade`)
3. Faça commit das alterações (`git commit -m 'Adiciona nova funcionalidade'`)
4. Faça push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo LICENSE para detalhes.

## Contato

Para mais informações, entre em contato com [wesleyserafimdev@gmail.com](mailto:wesleyserafimdev@gmail.com).
