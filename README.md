# 🍱 Lunchbox — Alê Marmitas

> Sistema completo e moderno para gestão operacional, financeira e de entregas para negócios de marmitas e alimentação.

---

### 🌐 Ambiente em Produção

A aplicação está disponível e operando em produção no endereço:
👉 **[https://alemarmitas.kolap.com.br/](https://alemarmitas.kolap.com.br/)**

---

## 📌 Visão Geral

O **Lunchbox** foi desenvolvido sob medida para atender à rotina dinâmica de produção, venda e controle financeiro de marmitas. O sistema centraliza desde o recebimento de pedidos diários e o resumo de produção na cozinha até a gestão de conta corrente de clientes (sistema de carteira/saldo com baixa automática de débitos), catálogo de produtos, relatórios de vendas, controle de acesso granular e integração com WhatsApp para mensageria.

A plataforma foi construída como uma **SPA moderna (Single Page Application)** utilizando o ecossistema Laravel + Inertia.js + Vue 3, oferecendo navegação instantânea, interface responsiva e suporte a **PWA (Progressive Web App)** para instalação em dispositivos móveis e desktops.

---

## 🚀 Funcionalidades Principais

### 🍲 1. Operação Diária & Pedidos do Dia (Cozinha em Tempo Real)
- **Fila de Pedidos em Tempo Real**: Atualização contínua (*live polling*) da esteira de pedidos do dia atual para a equipe de cozinha e atendimento.
- **Resumo de Produção (Preparo)**: Totalizador consolidado e automático de quantidades por marmita/produto a serem produzidas no dia, facilitando o planejamento da cozinha.
- **Métricas do Dia**: Visualização instantânea da quantidade total de pedidos ativos e do faturamento acumulado no dia.
- **Cards Operacionais**: Visualização rápida com dados do cliente, horário do pedido, status, itens inclusos e observações/preferências do cliente.
- **Acesso Rápido (FAB)**: Botão de ação rápida para inclusão imediata de novos pedidos.

### 📋 2. Gestão de Pedidos (Vendas & Ciclo de Vida)
- **Fluxo Completo de Pedidos**: Criação, edição, visualização detalhada e cancelamento de pedidos.
- **Itens Flexíveis**: Suporte a múltiplos produtos por pedido, com quantidades e ajuste dinâmico de valores unitários.
- **Ciclo de Estados Seguro**: Transição controlada entre status (*Pendente*, *Concluído*, *Cancelado*), com possibilidade de reabertura ou conclusão rápida com recálculo automático de totais.
- **Histórico & Filtros Avançados**: Listagem geral de pedidos com filtros por nome do cliente, intervalo de datas, múltiplos status simultâneos e paginação.

### 👥 3. Gestão de Clientes & Conta Corrente (Carteira Digital)
- **Cadastro e Manutenção**: Registro de clientes com informações de contato e vínculo ao WhatsApp.
- **Controle de Saldo (Conta Corrente)**: Cada cliente possui uma carteira interna (`balance`), onde pedidos geram débitos e pagamentos geram créditos.
- **Filtro de Inadimplência**: Visualização rápida de clientes devedores (*saldo negativo*) para facilitar cobranças e conciliações.
- **Integridade de Dados**: Proteção com *soft deletes* em cascata e bloqueio de exclusão para clientes com histórico de pedidos ativos.

### 💳 4. Gestão Financeira & Baixa Automática de Pedidos
- **Extrato Financeiro Individual**: Painel dedicado por cliente contendo saldo atualizado, histórico de pagamentos, pedidos não quitados e extrato de movimentações.
- **Sistema de Baixa Automática (`Settle from Wallet`)**: Ao registrar um pagamento ou entrada de crédito, o sistema aloca e liquida automaticamente os pedidos em aberto por ordem cronológica (dos mais antigos para os mais recentes).
- **Múltiplos Meios de Pagamento**: Registro e estorno de pagamentos via PIX, Dinheiro, Cartão, entre outros.
- **Trilha de Auditoria Financeira (Livro Razão)**: Registro imutável de todas as transações (débitos de pedidos, créditos de pagamentos, baixas e estornos) com saldo consolidado a cada operação.

### 📊 5. Relatórios Gerenciais de Vendas
- **Relatório de Vendas por Cliente**: Análise detalhada por período (mensal ou intervalo customizado) e busca por cliente.
- **Indicadores Chave (KPIs)**: Cards com Total de Vendas Faturadas, Total Recebido/Pago e Saldo Pendente a Receber no período selecionado.
- **Visão Analítica**: Tabela com quantidade de pedidos realizados, valor total comprado, total liquidado e saldo em aberto por cliente.

### 🍱 6. Catálogo de Produtos
- **Cadastro e Precificação**: Gestão de produtos/marmitas com preços padrão.
- **Controle de Exibição no Resumo**: Opção para definir quais produtos compõem o quadro de preparo da cozinha (`show_in_prep_summary`).

### 💬 7. Mensageria & Integração WhatsApp (Evolution API)
- **Arquitetura Assíncrona**: Envio de mensagens em segundo plano via filas (*background jobs*) para alta performance.
- **Rastreamento de Mensagens**: Histórico completo com estados de envio (*Queued*, *Processing*, *Sent*, *Failed*) e IDs externos da API.
- **Integração com Evolution API**: Gateway desacoplado via Service Providers para comunicação direta via WhatsApp com clientes.

### 🔐 8. Controle de Acesso (RBAC), Segurança & Auditoria
- **Autenticação Segura**: Gerenciada pelo Laravel Fortify com suporte a autenticação de dois fatores (**2FA / TOTP** via aplicativo autenticador e códigos de recuperação).
- **Controle de Permissões Granular (ACL / RBAC)**: Gestão de papéis (*roles*) e permissões específicas por recurso (usuários, clientes, pedidos, pagamentos, relatórios, etc.) via Spatie Permission.
- **Gestão de Usuários**: Criação, edição, atribuição de perfis e exclusão com proteção da própria conta logada.
- **Logs de Atividades**: Rastreabilidade e auditoria automática de alterações realizadas nos registros do sistema via Spatie Activitylog.

### 🎨 9. Experiência do Usuário (UX/UI) & PWA
- **Interface Moderna**: Componentes estilizados com Tailwind CSS e Reka UI.
- **Tema Claro / Escuro (Dark Mode)**: Suporte completo a alternância de temas com preferência de sistema ou manual.
- **Progressive Web App (PWA)**: Possibilidade de instalação como aplicativo nativo no celular ou computador.
- **Notificações em Toast**: Feedback visual imediato de ações via Vue Sonner.

---

## 🛠️ Stack Tecnológica

### Backend
- **Framework**: [Laravel 13](https://laravel.com/) (PHP 8.3+)
- **Autenticação & Segurança**: Laravel Fortify + Spatie Laravel Permission
- **Banco de Dados**: PostgreSQL 16
- **Auditoria & DTOs**: Spatie Laravel Activitylog & Spatie Laravel Data
- **Filas & Processamento**: Laravel Queue & Database Worker
- **Integrações**: Evolution API (WhatsApp Provider)

### Frontend
- **Framework**: [Vue 3](https://vuejs.org/) (Composition API, `<script setup lang="ts">`)
- **Linguagem**: [TypeScript](https://www.typescriptlang.org/)
- **Adaptador SPA**: [Inertia.js v3](https://inertiajs.com/)
- **Estilização**: [Tailwind CSS v4](https://tailwindcss.com/)
- **Componentes Base**: [Reka UI](https://reka-ui.com/) (Shadcn-vue primitives)
- **Tabelas & Ícones**: TanStack Table & Lucide Vue Next
- **Internacionalização**: `laravel-vue-i18n` (pt_BR)
- **PWA & Build Tool**: `vite-plugin-pwa` & [Vite 8](https://vitejs.dev/)

---

## 📂 Estrutura do Projeto

```text
├── app/
│   ├── Actions/            # Camada de regras de negócio (Pedidos, Pagamentos, Fortify)
│   ├── Data/               # Data Transfer Objects (DTOs) com Spatie Laravel Data
│   ├── Enums/              # Enumerações tipadas (Status de Pedidos, Pagamentos, Mensagens, etc.)
│   ├── Http/
│   │   ├── Controllers/    # Controladores da aplicação
│   │   └── Requests/       # Form Requests de validação
│   ├── Jobs/               # Background jobs (ex: envio assíncrono de WhatsApp)
│   ├── Models/             # Modelos Eloquent (Customer, Order, Payment, Transaction, Product, etc.)
│   ├── Providers/          # Service Providers e contratos de integração (Evolution/WhatsApp)
│   └── Services/           # Serviços de domínio (ex: WhatsAppService)
├── database/
│   ├── migrations/         # Migrações do banco de dados (PostgreSQL)
│   └── seeders/            # Seeders de permissões, papéis e usuários iniciais
├── resources/
│   ├── js/
│   │   ├── components/     # Componentes de UI reutilizáveis (Shadcn / Reka)
│   │   ├── composables/    # Composables Vue (filtros, permissões, etc.)
│   │   ├── layouts/        # Layouts da aplicação (Sidebar, App Layout, Auth)
│   │   ├── pages/          # Páginas Inertia.js (Orders, Customers, Payments, Reports, Users, etc.)
│   │   └── types/          # Definições de tipos TypeScript
├── routes/
│   ├── web.php             # Rotas web autenticadas e autorizadas
│   └── settings.php        # Rotas de perfil, segurança e aparência
└── docker-compose.yml      # Configuração dos containers de desenvolvimento/execução
```

---

## ⚙️ Instalação e Execução Local

### Pré-requisitos
- [Docker](https://www.docker.com/) e [Docker Compose](https://docs.docker.com/compose/) **OU**
- PHP 8.3+, Composer, Node.js (v20+) e PostgreSQL

### Opção 1: Via Docker Compose (Recomendado)

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/mateuskolap/lunchbox.git
   cd lunchbox
   ```

2. **Configure o arquivo de ambiente:**
   ```bash
   cp .env.example .env
   ```

3. **Suba os containers:**
   ```bash
   docker compose up -d
   ```

4. **Instale as dependências e inicialize o banco:**
   ```bash
   docker compose exec web composer install
   docker compose exec web php artisan key:generate
   docker compose exec web php artisan migrate --seed
   docker compose exec web npm install
   docker compose exec web npm run build
   ```

5. **Acesse:**
   - Aplicação: [http://localhost:8080](http://localhost:8080)

---

### Opção 2: Execução Manual (Local)

1. **Instale as dependências PHP e Node:**
   ```bash
   composer install
   npm install
   ```

2. **Configure o `.env` com os dados do seu PostgreSQL local e gere a chave:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Execute as migrações e seeders:**
   ```bash
   php artisan migrate --seed
   ```

4. **Inicie os servidores de desenvolvimento:**
   ```bash
   composer dev
   ```
   *(Executa simultaneamente o servidor Laravel, worker de filas, logs Pail e o Vite)*

---

## 🌐 Produção

Acesse a versão em produção do sistema em:
👉 **[https://alemarmitas.kolap.com.br/](https://alemarmitas.kolap.com.br/)**
