# InEvent Weather Challenge

> Aplicação de previsão climática desenvolvida como desafio técnico para processo seletivo da InEvent.

![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=flat-square&logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)
![TypeScript](https://img.shields.io/badge/TypeScript-5.9-3178C6?style=flat-square&logo=typescript)
![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=flat-square&logo=vite)

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Funcionalidades](#-funcionalidades)
- [Tecnologias Utilizadas](#-tecnologias-utilizadas)
- [Arquitetura](#-arquitetura)
- [Pré-requisitos](#-pré-requisitos)
- [Instalação e Execução](#-instalação-e-execução)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [API Endpoints](#-api-endpoints)
- [Decisões Técnicas](#-decisões-técnicas)
- [Melhorias Futuras](#-melhorias-futuras)
- [Autor](#-autor)

---

## 🎯 Sobre o Projeto

Este projeto foi desenvolvido como parte do desafio técnico do processo seletivo da **InEvent**. O objetivo era criar uma aplicação web que consome a API do [OpenWeatherMap](https://openweathermap.org/api) para exibir informações climáticas de forma intuitiva e visualmente atraente.

A aplicação foi inspirada no design e funcionalidades do [Weather.com](https://weather.com), oferecendo uma experiência completa de consulta climática com:

- Dashboard interativo com clima atual
- Previsão por hora (próximas 24 horas)
- Previsão estendida de 5 dias
- Índice de qualidade do ar
- Sistema de autenticação

---

## ✨ Funcionalidades

### Autenticação
- Login com validação de e-mail
- Persistência de sessão via localStorage
- Proteção de rotas autenticadas
- Logout com redirecionamento

### Dashboard de Clima
- **Clima Atual**: Temperatura, sensação térmica, condição climática com ícone
- **Detalhes Meteorológicos**: Umidade, velocidade e direção do vento, horário do nascer e pôr do sol
- **Previsão por Hora**: Scroll horizontal mostrando as próximas 24 horas com temperatura, ícone e probabilidade de chuva
- **Previsão de 5 Dias**: Lista com temperaturas mínima/máxima, condição do tempo e probabilidade de precipitação
- **Qualidade do Ar**: Índice AQI com classificação visual e componentes (PM2.5, PM10, O₃, NO₂)
- **Informações Adicionais**: Pressão atmosférica, visibilidade e nebulosidade

### Busca
- Pesquisa de qualquer cidade do mundo
- Carregamento automático de São Paulo como cidade padrão

---

## 🛠 Tecnologias Utilizadas

### Frontend
| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| Vue.js | 3.5 | Framework JavaScript progressivo |
| TypeScript | 5.9 | Superset tipado do JavaScript |
| Vite | 7.x | Build tool e dev server |
| Vue Router | 4.x | Roteamento SPA |
| CSS3 | - | Estilização com variáveis CSS e glassmorphism |

### Backend
| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| PHP | 8.x | Linguagem server-side |
| Guzzle HTTP | 7.10 | Cliente HTTP para consumo de APIs |
| PHP dotenv | 5.6 | Gerenciamento de variáveis de ambiente |

### APIs Externas
- [OpenWeatherMap Current Weather](https://openweathermap.org/current) - Clima atual
- [OpenWeatherMap 5 Day Forecast](https://openweathermap.org/forecast5) - Previsão 5 dias
- [OpenWeatherMap Air Pollution](https://openweathermap.org/api/air-pollution) - Qualidade do ar

---

## 🏗 Arquitetura

A aplicação segue uma arquitetura **cliente-servidor** com separação clara de responsabilidades:

```
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│                 │      │                 │      │                 │
│    Frontend     │ ───► │     Backend     │ ───► │  OpenWeatherMap │
│    (Vue.js)     │      │     (PHP)       │      │      API        │
│                 │      │                 │      │                 │
└─────────────────┘      └─────────────────┘      └─────────────────┘
     SPA + Router         REST API + CORS          Weather Data
```

### Fluxo de Dados
1. Usuário faz login no frontend
2. Frontend solicita dados climáticos ao backend
3. Backend consulta a API do OpenWeatherMap
4. Backend processa e formata os dados
5. Frontend renderiza as informações

---

## 📦 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- **Node.js** >= 20.19.0 ou >= 22.12.0
- **PHP** >= 8.0
- **Composer** (gerenciador de dependências PHP)
- **Chave de API** do OpenWeatherMap ([obter gratuitamente](https://openweathermap.org/api))

---

## 🚀 Instalação e Execução

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/inevent_weather_challenge.git
cd inevent_weather_challenge
```

### 2. Configuração do Backend

```bash
# Acesse a pasta do backend
cd inevent_weather_backend

# Instale as dependências
composer install

# Configure as variáveis de ambiente
cp .env.example .env
# Edite o arquivo .env e adicione sua chave da API:
# OPENWEATHER_API_KEY=sua_chave_aqui

# Inicie o servidor PHP
php -S localhost:8000 -t public
```

### 3. Configuração do Frontend

```bash
# Em outro terminal, acesse a pasta do frontend
cd inevent_weather_frontend

# Instale as dependências
npm install

# Configure as variáveis de ambiente (opcional)
cp .env.example .env
# O arquivo já vem configurado para localhost:8000

# Inicie o servidor de desenvolvimento
npm run dev
```

### 4. Acesse a aplicação

Abra o navegador e acesse: **http://localhost:5173**

#### Credenciais de Acesso
- **E-mail**: Qualquer e-mail válido (ex: `teste@email.com`)
- **Senha**: Mínimo 6 caracteres (ex: `123456`)

---

## 📁 Estrutura do Projeto

```
inevent_weather_challenge/
│
├── inevent_weather_backend/          # API Backend (PHP)
│   ├── public/
│   │   └── index.php                 # Entry point e roteamento
│   ├── src/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php    # Autenticação
│   │   │   ├── WeatherController.php # Clima atual
│   │   │   ├── ForecastController.php# Previsão 5 dias
│   │   │   └── AirQualityController.php # Qualidade do ar
│   │   └── Services/
│   │       └── OpenWeatherService.php # Integração com API
│   ├── .env                          # Variáveis de ambiente
│   └── composer.json                 # Dependências PHP
│
├── inevent_weather_frontend/         # SPA Frontend (Vue.js)
│   ├── src/
│   │   ├── assets/
│   │   │   └── styles/
│   │   │       └── main.css          # Estilos globais + variáveis CSS
│   │   ├── composables/
│   │   │   └── useAuth.ts            # Composable de autenticação
│   │   ├── config/
│   │   │   └── api.ts                # Configuração da API
│   │   ├── router/
│   │   │   └── index.ts              # Configuração de rotas
│   │   ├── services/
│   │   │   ├── auth.ts               # Serviço de autenticação
│   │   │   └── weather.ts            # Serviço de clima
│   │   ├── views/
│   │   │   ├── LoginView.vue         # Tela de login
│   │   │   └── DashboardView.vue     # Dashboard principal
│   │   ├── App.vue                   # Componente raiz
│   │   └── main.ts                   # Entry point
│   ├── .env                          # Variáveis de ambiente
│   ├── package.json                  # Dependências Node.js
│   └── vite.config.ts                # Configuração Vite
│
└── README.md                         # Este arquivo
```

---

## 🔌 API Endpoints

### Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/auth/login` | Realiza login do usuário |

**Request Body:**
```json
{
  "email": "usuario@email.com",
  "password": "senha123"
}
```

**Response:**
```json
{
  "success": true,
  "user": {
    "email": "usuario@email.com",
    "name": "usuario"
  },
  "token": "base64_encoded_token"
}
```

### Clima

| Método | Endpoint | Parâmetros | Descrição |
|--------|----------|------------|-----------|
| GET | `/api/weather` | `city` | Retorna clima atual |
| GET | `/api/forecast` | `city` | Retorna previsão de 5 dias |
| GET | `/api/air-quality` | `lat`, `lon` | Retorna qualidade do ar |

**Exemplo - Clima Atual:**
```
GET /api/weather?city=São Paulo
```

**Response:**
```json
{
  "city": "São Paulo",
  "country": "BR",
  "coord": { "lat": -23.5505, "lon": -46.6333 },
  "temperature": 25,
  "feels_like": 26,
  "humidity": 65,
  "pressure": 1015,
  "wind": { "speed": 12, "direction": "NE" },
  "weather": {
    "main": "Clouds",
    "description": "nublado",
    "icon": "04d"
  }
}
```

---

## 💡 Decisões Técnicas

### Frontend

1. **Vue 3 com Composition API**: Escolhido pela reatividade eficiente e melhor organização de código através de composables.

2. **TypeScript**: Adicionado para garantir type safety e melhor experiência de desenvolvimento com autocomplete.

3. **CSS com Variáveis**: Sistema de design consistente usando CSS custom properties para cores, espaçamentos e transições.

4. **Glassmorphism**: Design moderno com efeito de vidro fosco nos cards para visual elegante.

5. **Sem biblioteca de UI**: Optei por CSS puro para demonstrar habilidades de estilização e manter o bundle leve.

### Backend

1. **PHP Puro**: Estrutura simples e direta, sem framework, focando na funcionalidade essencial.

2. **Service Layer**: Separação da lógica de negócio em `OpenWeatherService` para melhor manutenibilidade.

3. **CORS Configurado**: Headers adequados para permitir requisições do frontend.

4. **Tratamento de Erros**: Respostas padronizadas com códigos HTTP apropriados.

### Segurança

1. **Validação de E-mail**: Verificação de formato válido no backend.
2. **Token Base64**: Simulação de autenticação (em produção, usar JWT).
3. **Variáveis de Ambiente**: Chaves de API não expostas no código.

---

## 🔮 Melhorias Futuras

- [ ] Implementar JWT para autenticação real
- [ ] Adicionar testes unitários e E2E
- [ ] Cache de requisições à API do OpenWeatherMap
- [ ] Geolocalização automática do usuário
- [ ] PWA com suporte offline
- [ ] Modo escuro/claro
- [ ] Gráficos de temperatura ao longo do dia
- [ ] Notificações de alertas climáticos
- [ ] Histórico de cidades pesquisadas
- [ ] Suporte a múltiplos idiomas

---

## 👨‍💻 Autor

**Felipe Bufelli**

- GitHub: [@felipebufelli](https://github.com/felipebufelli)

---

## 📄 Licença

Este projeto foi desenvolvido exclusivamente para fins de avaliação técnica no processo seletivo da InEvent.

---

<p align="center">
  Desenvolvido com ❤️ para o desafio técnico InEvent
</p>
