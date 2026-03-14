# 💰 Controle Financeiro

> Tenha controle total sobre suas finanças pessoais de forma simples e visual.

![Version](https://img.shields.io/badge/version-v0.9.0-7C3AED?style=flat) ![Status](https://img.shields.io/badge/status-em%20ajustes-yellow?style=flat) ![PHP](https://img.shields.io/badge/PHP-8+-7C3AED?style=flat&logo=php)

🔗 **[Acessar o projeto](https://cf.jonasnunes.com.br)**

---

## 📸 Sobre

O **Controle Financeiro** é um projeto pessoal com dois objetivos claros: aprender PHP na prática e ter uma ferramenta própria para acompanhar meus gastos mensais. Não existe a intenção de competir com os inúmeros apps financeiros que já existem por aí — e que provavelmente são melhores em muitos aspectos. A ideia é simples: construir algo útil para a minha realidade enquanto evoluo como desenvolvedor. Cada funcionalidade implementada aqui representa um conceito novo aprendido, um bug resolvido e um passo a mais no entendimento de como aplicações web funcionam de verdade.

---

## ✨ Funcionalidades

- 🔐 **Autenticação completa** — cadastro, login e recuperação de senha por e-mail
- 📊 **Dashboard** — visão geral do mês com gráfico de evolução de gastos
- 💸 **Gestão de gastos** — cadastro, edição e exclusão com filtros por categoria, mês e ano
- 🏷️ **Categorias personalizadas** — crie categorias com ícone emoji e cor
- ⚙️ **Configurações** — atualize seu perfil, salário mensal e orçamento por categoria
- 🔴 **Alerta de orçamento** — aviso visual quando o gasto ultrapassa o limite definido
- 📱 **Totalmente responsivo** — funciona bem em desktop e mobile

---

## 🛠️ Ferramentas utilizadas

| Ferramenta | Por quê? |
|---|---|
| **PHP 8+** | A linguagem principal do projeto e o motivo pelo qual ele existe — queria aprender PHP construindo algo real, não apenas seguindo tutoriais |
| **MySQL + PDO** | Escolhi PDO em vez de mysqli por ser mais seguro e flexível, e porque queria aprender prepared statements desde o início |
| **HTML + CSS puro** | Sem frameworks de CSS por enquanto — queria entender o layout na base antes de adicionar abstrações como Tailwind, que está nos planos futuros |
| **JavaScript vanilla** | Mesma lógica do CSS — preferi escrever as interações sem depender de bibliotecas para entender o que está acontecendo de verdade |
| **Chart.js** | Única exceção à regra acima, pois gerar gráficos do zero fugiria do foco principal do projeto |
| **PHPMailer** | Para o fluxo de recuperação de senha funcionar de verdade, precisava de um envio de e-mail confiável via SMTP |
| **Git + GitHub** | Versionamento com fluxo de branches desde o início — parte essencial do aprendizado, não só do projeto |

---

## 🎨 Design

O projeto segue uma identidade visual escura inspirada em apps financeiros modernos como o Nubank.

| Elemento | Cor |
|---|---|
| Fundo | `#0F0F1A` |
| Cards | `#1E1E2E` |
| Destaque | `#7C3AED` |
| Texto secundário | `#9090A0` |
| Texto principal | `#F5F5F5` |

---

## 🚀 Como acessar

O projeto está disponível em produção e pode ser acessado pelo link abaixo. Sinta-se à vontade para criar uma conta e explorar as funcionalidades.

👉 **[cf.jonasnunes.com.br](https://cf.jonasnunes.com.br)**

---

## 📁 Estrutura do projeto

```
controlefinanceiro/
├── assets/          → CSS, JS e imagens
├── includes/        → Configurações, banco de dados e PHPMailer
├── pages/           → Páginas da aplicação
└── index.php        → Redirecionamento inicial
```

---

## 👨‍💻 Autor

Desenvolvido por **Jonas Nunes**

[![GitHub](https://img.shields.io/badge/GitHub-Ohnilrac-7C3AED?style=flat&logo=github)](https://github.com/Ohnilrac)
