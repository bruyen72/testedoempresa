# 🚀 TecPoint - Site Corporativo

Sistema completo de site corporativo com proteções anti-spam e gerenciamento de produtos/serviços.

## 🎯 Funcionalidades

- ✅ **Sistema de Contato** com proteção anti-spam
- ✅ **Rate Limiting** (3s client + 10s server por IP)
- ✅ **Catálogo de Produtos** com imagens múltiplas
- ✅ **Gerenciamento de Serviços**
- ✅ **Painel Admin** protegido
- ✅ **Sistema de Email** com 3 fallbacks
- ✅ **Integração Instagram** feed automático
- ✅ **Upload de Arquivos** (imagens, PDFs)
- ✅ **Banco de Dados** SQLite/PostgreSQL compatível

## 🛡️ Segurança

### Anti-Spam
- Rate limiting por IP (10 segundos)
- Cooldown JavaScript (3 segundos)
- Validação de campos obrigatórios
- Sanitização HTML (XSS protection)
- Log de IP em todos os emails

### Autenticação
- Painel admin protegido
- Senhas hasheadas (bcrypt)
- Sessões seguras

## 📧 Sistema de Email

### Método 1: mail() nativa
Função PHP nativa (ideal para servidores compartilhados)

### Método 2: SMTP Socket
Conexão SSL/TLS direta ao servidor SMTP

### Método 3: Backup em arquivo
Salva em `emails_backup.log` se tudo falhar

## 🔧 Configuração

### Variáveis de Ambiente (Opcional)

```bash
APP_ENV=production
DATABASE_URL=sqlite:local.db
SMTP_SERVER=smtps.uhserver.com
SMTP_PORT=465
SMTP_USERNAME=contato@tecpoint.net.br
SMTP_PASSWORD=sua_senha_aqui
```

### Admin Padrão

```
Usuário: admin
Senha: admin123
```

**⚠️ ALTERE A SENHA após primeiro login!**

## 📦 Instalação

### Local (PHP Built-in Server)

```bash
cd public_html
php -S localhost:8080
```

Acesse: `http://localhost:8080`

### Servidor Compartilhado (UOL Host, Hostgator, etc)

1. Faça upload dos arquivos via FTP
2. Aponte o domínio para a pasta `public_html/`
3. Pronto! O sistema está rodando

### Vercel

1. Push para GitHub
2. Importe o repositório na Vercel
3. Configure variáveis de ambiente
4. Deploy automático

## 📂 Estrutura de Arquivos

```
public_html/
├── index.php              # Aplicação principal
├── static/
│   ├── css/              # Estilos
│   ├── js/               # Scripts
│   ├── uploads/          # Arquivos enviados
│   └── *.png             # Imagens estáticas
├── templates/            # Templates HTML
│   ├── index.html
│   ├── produtos.html
│   ├── servicos.html
│   ├── contato.html
│   └── admin/           # Templates admin
├── local.db             # Banco SQLite (criado automaticamente)
├── .gitignore
└── README.md
```

## 🎨 Tecnologias

- **Backend:** PHP 8.3+
- **Frontend:** HTML5, CSS3, JavaScript ES6+
- **Banco:** SQLite (local) / PostgreSQL (produção)
- **Email:** SMTP nativo / socket SSL
- **Upload:** Sistema próprio com validação

## 📱 Responsivo

- ✅ Desktop (1920px+)
- ✅ Laptop (1366px)
- ✅ Tablet (768px)
- ✅ Mobile (320px+)

## 🔍 SEO

- Meta tags otimizadas
- Títulos semânticos
- Alt text em imagens
- URLs amigáveis
- Sitemap (a implementar)

## 📊 Logs

### Logs de Email
- `emails_backup.log` - Emails que falharam
- `error_log` - Erros PHP

### Rate Limiting
- `rate_limit_*.txt` - Timestamps por IP

## 🚀 Deploy

### GitHub + Vercel

```bash
# Inicializar repositório
git init
git add .
git commit -m "Sistema completo TecPoint"
git branch -M main

# Adicionar remote
git remote add origin https://github.com/bruyen72/testedoempresa.git

# Push
git push -u origin main
```

### Vercel Dashboard

1. Importe o repositório
2. Configure:
   - **Framework Preset:** Other
   - **Build Command:** (deixe vazio)
   - **Output Directory:** `.`
3. Adicione variáveis de ambiente
4. Deploy!

## 🆘 Troubleshooting

### Emails não chegam?
1. Verifique `emails_backup.log`
2. Confira credenciais SMTP
3. Teste em outro servidor

### Erro 500?
1. Verifique permissões (755 pastas, 644 arquivos)
2. Confira `error_log`
3. Verifique versão PHP (mínimo 7.4)

### Rate limit bloqueando muito?
Ajuste em:
- `static/js/index.js` linha 4: `SUBMIT_COOLDOWN`
- `index.php` linha 1276: `if ($time_diff < 10)`

## 📄 Licença

Propriedade de **TecPoint Soluções em Comunicação e Tecnologia**

---

## 👨‍💻 Desenvolvedor

**Bruno Ruthes Pinheiro de Oliveira**
- Email: brunoruthes92@gmail.com
- GitHub: [@bruyen72](https://github.com/bruyen72)

---

## 🎯 Status do Projeto

✅ **Sistema 100% funcional e pronto para produção**

- [x] Frontend responsivo
- [x] Backend PHP completo
- [x] Sistema de email
- [x] Proteção anti-spam
- [x] Painel admin
- [x] Upload de arquivos
- [x] Banco de dados
- [x] Logs e debugging
- [x] Documentação completa

---

**Última atualização:** 01/10/2025
