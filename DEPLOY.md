# 🚀 DEPLOY PARA PRODUÇÃO - UOL HOST

## 📋 CHECKLIST PRÉ-DEPLOY

- [x] ✅ Rate Limiting implementado (3s JS + 10s PHP)
- [x] ✅ Proteção anti-spam por IP
- [x] ✅ Validação de campos e email
- [x] ✅ Sanitização HTML (XSS protection)
- [x] ✅ Sistema de email com 3 fallbacks
- [x] ✅ Logs de erro e backup

---

## 🔧 CONFIGURAÇÕES SMTP

**Servidor:** `smtps.uhserver.com:465`
**Usuário:** `contato@tecpoint.net.br`
**Senha:** `tecpoint@2024B`

Credenciais estão em `index.php` linhas 15-18.

---

## 📤 COMO FAZER UPLOAD

### Via FTP:

1. **Conecte ao servidor UOL Host** via FTP
2. **Navegue até** `public_html/`
3. **Faça upload de TODOS os arquivos**, exceto:
   - ❌ `emails_sent.log`
   - ❌ `emails_backup.log`
   - ❌ `rate_limit_*.txt`
   - ❌ `*.db`
   - ❌ `vendor/` (se existir)

---

## ✅ COMO O SISTEMA FUNCIONA EM PRODUÇÃO

### **Método 1: mail() nativa** (Prioridade)
- UOL Host tem servidor SMTP configurado
- Usa função `mail()` nativa do PHP
- Mais rápido e confiável no servidor

### **Método 2: SMTP Socket** (Fallback 1)
- Se `mail()` falhar, tenta conexão direta SMTP
- Socket SSL/TLS na porta 465
- Autenticação manual

### **Método 3: Backup em arquivo** (Fallback 2)
- Se ambos falharem, salva em `emails_backup.log`
- Você não perde NENHUM email
- Retorna sucesso ao usuário

---

## 🛡️ PROTEÇÕES ANTI-SPAM ATIVAS

### **JavaScript (Cliente):**
- ⏱️ Cooldown de 3 segundos entre envios
- 🔒 Botão desabilitado durante envio
- ✅ Validação de campos obrigatórios

### **PHP (Servidor):**
- 🚫 Rate limit de 10 segundos por IP
- 📊 HTTP 429 (Too Many Requests)
- 📝 Log de IP em cada email
- 🔍 Validação de email com `filter_var()`
- 🛡️ Sanitização HTML com `htmlspecialchars()`

---

## 📊 MONITORAMENTO

### **Arquivos de log:**
```
emails_backup.log - Emails que falharam no envio
error_log - Erros do PHP
```

### **Verificar se há emails em backup:**
```bash
cat public_html/emails_backup.log
```

Se houver emails, envie manualmente ou verifique a configuração SMTP.

---

## 🔍 TESTE PÓS-DEPLOY

1. Acesse `https://tecpoint.net.br`
2. Vá até o formulário de contato
3. Preencha e envie
4. Verifique o email em `contato@tecpoint.net.br`
5. Tente enviar novamente rapidamente (deve bloquear)

---

## 🆘 TROUBLESHOOTING

### **Problema:** Emails não chegam

**Solução 1:** Verifique `emails_backup.log`
```bash
tail -20 public_html/emails_backup.log
```

**Solução 2:** Verifique error_log do servidor
```bash
tail -50 public_html/error_log
```

**Solução 3:** Teste credenciais SMTP manualmente

---

### **Problema:** Rate limit muito agressivo

**Ajustar em:**
- `static/js/index.js` linha 4: `SUBMIT_COOLDOWN = 3000` (3s)
- `index.php` linha 1276: `if ($time_diff < 10)` (10s)

---

### **Problema:** Usuários reclamando de bloqueio

**Limpar rate limit de um IP específico:**
```bash
rm public_html/rate_limit_*.txt
```

---

## 📞 SUPORTE

**Logs importantes:**
- `error_log` - Erros PHP
- `emails_backup.log` - Emails em backup

**Contato técnico:** Bruno Ruthes
**Email:** brunoruthes92@gmail.com

---

## ✅ STATUS FINAL

| Item | Status |
|------|--------|
| Anti-spam | ✅ Ativo |
| Rate limiting | ✅ Ativo |
| Validação | ✅ Ativo |
| Email SMTP | ✅ Configurado |
| Fallback | ✅ Ativo |
| Logs | ✅ Habilitados |

**Sistema pronto para PRODUÇÃO!** 🚀
