# ✦ Lumière Decor

![SENAI-SP](https://img.shields.io/badge/SENAI--SP-Projeto%20Integrador-red?style=flat-square)
![SCRUM](https://img.shields.io/badge/Metodologia-SCRUM-C9A84C?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.x-484F89?style=flat-square&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![Status](https://img.shields.io/badge/Status-Em%20desenvolvimento-3fb950?style=flat-square)

Plataforma web premium de **aluguel de decoração para eventos** — casamentos, formaturas, aniversários, corporativo e pacotes temáticos (incluindo o kit *Vem de Hexa* 🏆).

---

## 📌 Sobre o Projeto

O **Lumière Decor** é um sistema desenvolvido como Projeto Integrador do curso Técnico em Desenvolvimento de Sistemas do **SENAI-SP**, 3° Termo, 1° Semestre de 2026.

A plataforma permite que clientes naveguem por um catálogo digital de itens decorativos, realizem reservas, calculem orçamentos automaticamente e gerenciem suas contratações. Administradores possuem painel exclusivo para CRUD de itens e controle de devoluções.

> 💡 O projeto evoluiu de uma ideia inicial chamada **Vem de Hexa** (festas de futebol) para uma plataforma premium multi-evento. O conceito original foi reaproveitado como pacote temático especial dentro do catálogo.

---

## 🔗 Links

- 🎨 **Protótipo Figma:** [Visualizar protótipo](https://www.figma.com/proto/8P2FXyGWFaBuydGI1oBkIN/Lumiere?node-id=3-2&t=JdVlyrOH26MZQ4UH-1)
- 📊 **Apresentação (Canva):** [Visualizar slides](https://canva.link/fbrf1wp9jqc02w4)

---

## ⚙️ Funcionalidades

- 🔐 Autenticação com dois perfis: **admin** e **cliente**
- 🛍️ Catálogo de itens decorativos com filtros por categoria de evento
- ➕ Cadastro, edição e exclusão de itens (somente admin)
- 📅 Sistema de aluguel e devolução com atualização de status
- 🧮 Calculadora automática de orçamento por tipo de item e dias
- 📦 Pacotes temáticos prontos, incluindo o kit **Vem de Hexa** ⚽
- 📱 Interface responsiva (mobile, tablet e desktop)

---

## 🛠️ Tecnologias

| Tecnologia | Uso |
|---|---|
| PHP 8 | Backend, autenticação, lógica de negócios e CRUD |
| Bootstrap 5 | Layout responsivo e componentes visuais |
| JavaScript ES6+ | Interatividade, filtros e calculadora de orçamento |
| JSON | Persistência de dados (usuarios.json / itens.json) |
| Composer (PSR-4) | Autoloading de classes |
| Bootstrap Icons | Ícones da interface |
| Figma | Prototipagem de alta fidelidade |

---

## 👥 Equipe

| Nome | Papel |
|---|---|
| Pedro Oliveira | Product Owner |
| Isabella Radael | Scrum Master |
| Nicolas Fernandes | Desenvolvedor |
| João Pedro Tomazini | Desenvolvedor |
| Evellyn Silva | Desenvolvedora |
| Giovana Alves | Desenvolvedora |

---

## 📅 Cronograma

| Data | Sprint | Foco |
|---|---|---|
| 22/05/2026 | Sprint 1 | Planejamento, identidade visual, Figma, Backlog, 5W2H |
| 28/05/2026 | Sprint 2 | Documentação, site, slides, Kanban, Sprint Planning/Retro |
| 29/05/2026 | Sprint 2 | Revisões, integração frontend, testes de responsividade |
| 12/06/2026 | Sprint 3 | Backend PHP, CRUD, entrega final e apresentação |

> Horário de trabalho: **07:45 – 16:45**

---

## 📁 Estrutura do Projeto

```
lumiere-decor/
├── index.php            # Página principal
├── login.php            # Tela de autenticação
├── template.php         # Layout base reutilizável
├── logout.php           # Encerramento de sessão
├── data/
│   ├── usuarios.json    # Usuários e senhas criptografadas
│   └── itens.json       # Itens decorativos do catálogo
├── src/
│   ├── Auth.php         # Lógica de autenticação
│   ├── Item.php         # CRUD de itens
│   └── Aluguel.php      # Lógica de aluguel/devolução
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
└── composer.json        # Autoloading PSR-4
```

---

## 🚀 Como Rodar Localmente

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/lumiere-decor.git
```

2. Instale as dependências:
```bash
composer install
```

3. Inicie o servidor com XAMPP (Apache) apontando para a pasta do projeto

4. Acesse `http://localhost/lumiere-decor` no navegador

---

## 🔑 Credenciais de Teste

| Usuário | Senha | Perfil |
|---|---|---|
| admin | admin123 | Administrador |
| cliente | cliente123 | Cliente |

---

<p align="center">✦ Lumière Decor  ·  SENAI-SP  ·  2026  ·  Técnico em Desenvolvimento de Sistemas ✦</p>
