# 🚀 DEPLOY NO RENDER.COM

## ✅ Por que Render?

- ✅ **PHP Nativo** - Sem limitações
- ✅ **Upload de arquivos** funciona 100%
- ✅ **SQLite** funciona perfeitamente
- ✅ **SMTP** sem restrições
- ✅ **Rate limiting** por IP funciona
- ✅ **Gratuito** para sempre
- ✅ **SSL automático**
- ✅ **Deploy via Git** automático

---

## 📋 PASSO A PASSO

### 1️⃣ Criar conta no Render

Acesse: https://render.com/

Clique em **"Get Started"** e faça login com GitHub

---

### 2️⃣ Conectar repositório

1. No dashboard, clique em **"New +"**
2. Selecione **"Web Service"**
3. Conecte sua conta GitHub
4. Selecione o repositório: `bruyen72/testedoempresa`

---

### 3️⃣ Configurar o serviço

Preencha os campos:

```
Name: tecpoint
Runtime: PHP
Branch: main
Build Command: (deixe vazio)
Start Command: php -S 0.0.0.0:$PORT
```

---

### 4️⃣ Variáveis de Ambiente

Clique em **"Advanced"** e adicione:

```env
APP_ENV = production
SMTP_SERVER = smtps.uhserver.com
SMTP_PORT = 465
SMTP_USERNAME = contato@tecpoint.net.br
SMTP_PASSWORD = tecpoint@2024B
```

---

### 5️⃣ Plano Free

Selecione:
```
Plan: Free
Auto-Deploy: Yes
```

---

### 6️⃣ Deploy!

Clique em **"Create Web Service"**

Aguarde ~3-5 minutos para o deploy completar

---

## 🌐 Sua URL será:

```
https://tecpoint-XXXXX.onrender.com
```

Você pode adicionar um **domínio customizado** depois!

---

## ✅ O QUE FUNCIONA 100%:

- ✅ Formulário de contato
- ✅ Rate limiting por IP
- ✅ Sistema de email (3 fallbacks)
- ✅ Upload de imagens/PDFs
- ✅ Painel admin
- ✅ Banco SQLite
- ✅ Todas as proteções anti-spam
- ✅ Logs completos

---

## 🔄 AUTO-DEPLOY

Toda vez que você der **push** no GitHub, o Render faz deploy automático!

```bash
git add .
git commit -m "Atualização"
git push origin main
```

→ Deploy automático em 3 minutos! 🚀

---

## 🎯 VANTAGENS DO RENDER

| Recurso | Vercel | Render |
|---------|--------|--------|
| PHP Nativo | ❌ (descontinuado) | ✅ Sim |
| Upload Files | ❌ Limitado | ✅ Funciona |
| SQLite | ❌ Não persiste | ✅ Funciona |
| SMTP | ⚠️ Limitado | ✅ Sem limites |
| Rate Limit | ⚠️ Complicado | ✅ Por IP |
| SSL Grátis | ✅ Sim | ✅ Sim |
| Deploy Git | ✅ Sim | ✅ Sim |

---

## 📊 MONITORAMENTO

No dashboard do Render você vê:

- 📈 **Logs em tempo real**
- 🔄 **Status do deploy**
- 📊 **Uso de recursos**
- 🚀 **Histórico de deploys**

---

## 🆘 TROUBLESHOOTING

### Deploy falhou?

1. Verifique os **logs** no dashboard
2. Confirme que as **variáveis de ambiente** estão corretas
3. Certifique-se que o **Start Command** é: `php -S 0.0.0.0:$PORT`

### Emails não chegam?

1. Verifique a variável `SMTP_PASSWORD`
2. Confira os logs: procure por "Email enviado"
3. Verifique `emails_backup.log` no servidor

---

## 🎉 PRONTO!

Seu site estará no ar em **~5 minutos** com:

✅ PHP completo
✅ Sistema de email funcionando
✅ Proteções anti-spam ativas
✅ Upload de arquivos
✅ SSL automático
✅ Deploy automático via Git

**Deploy no Render = 100% funcional!** 🚀
