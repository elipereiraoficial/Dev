# 🛡️ LUXURY CRM - AGENT SQUAD

> Sistema de automação para desenvolvimento de sistemas

## 📋 AGENTS DISPONÍVEIS

### 1. 🔴 AUDITOR Agent
**Comando:** `/auditor` ou `Task(description="auditor")`

**Funções:**
- Verificar vulnerabilidades de segurança (SQL Injection, XSS, CSRF)
- Analisar performance de código
- Verificar follow-up de convenções (PSR standards)
- Checklist de segurança OWASP

### 2. 🔵 TESTER Agent
**Comando:** `/tester` ou `Task(description="tester")`

**Funções:**
- Testar funcionalidades após cada mudança
- Verificar se código compila/tem erros de sintaxe
- Testar fluxos de utilizador (create, read, update, delete)
- Verificar integração com BD

### 3. 🟢 PAIN TESTER Agent
**Comando:** `/pain` ou `Task(description="pain-tester")`

**Funções:**
- Simular uso real do sistema
- Testar edge cases (dados inválidos, vazios, limites)
- Verificar UX/UI problems
- Testar stress (muitos dados, muitas requisições)

### 4. 🟡 DEPLOY Agent
**Comando:** `/deploy` ou `Task(description="deploy")`

**Funções:**
- Executar upgrades de BD automaticamente
- Executar scripts SQL necessários
- Fazer push para GitHub
- Verificar se deploy foi bem-sucedido

### 5. 🟣 DOCS Agent
**Comando:** `/docs` ou `Task(description="docs")`

**Funções:**
- Atualizar MEMORIA.md automaticamente
- Manter WORKFLOW.md atualizado
- Criar documentação técnica
- Gerar changelogs

---

## 🎮 COMO USAR

### Após cada mudança/faturação:

**1. Auditor** (sempre primeiro):
```
Analise o código em [ficheiro] para vulnerabilidades de segurança
```

**2. Tester** (antes de fazer commit):
```
Teste a funcionalidade de [módulo] e verifique se funciona
```

**3. Pain Tester** (teste final):
```
Simule uso real do sistema e encontre problemas
```

**4. Deploy** (quando precisa de BD):
```
Execute o upgrade de BD e verifique se funcionou
```

**5. Docs** (antes de push):
```
Atualize a documentação com as mudanças feitas
```

---

## ⚙️ AUTOMATIONS

### Auto-Run after every commit:
1. ✅ Auditor → verifica segurança
2. ✅ Docs → atualiza MEMORIA.md
3. ✅ Tester → valida funcionamento

---

## 📁 Project Context

- **Project:** Luxury CRM (PHP + MySQL)
- **URL:** https://crm.elipereira.com
- **Repo:** https://github.com/elipereiraoficial/Dev
- **Stack:** PHP 8.2, MySQL, TailwindCSS

## ⚠️ REGRA OBRIGATÓRIA - Deploy

**SEMPRE que houver qualquer alteração no código, executar:**
1. `git add -A`
2. `git commit -m "mensagem"`
3. `git push`

O deploy para Hostinger é AUTOMÁTICO via GitHub Actions após cada push.

**NUNCA fazer alterações manuais na Hostinger** - todas as mudanças devem ser feitas via código e commit.

---

## 🚀 Future Projects

Este sistema de agents pode ser usado para:
- ✅ Novos módulos do CRM
- ✅ Novos projetos PHP/Python/Node
- ✅ APIs e integrações
- ✅ Apps mobile

---

**Last Updated:** 05/05/2026 - Sistema 100% operacional com:
- Galeria de Imóveis (upload, delete, primary)
- styling de inputs (bordas douradas)
- Scripts de seed (apply_seed.php)