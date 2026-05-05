# Development Workflow - Luxury CRM

> **Checklist OBRIGATÓRIO** antes de cada commit/push

---

## 🔄 Antes de Criar PR / Push

### 1. Testar Localmente
- [ ] Código funciona sem erros
- [ ] Funcionalidade implementada/testada
- [ ] Não quebrou funcionalidades existentes

### 2. Documentar Alterações
- [ ] MEMORIA.md atualizado com:
  - Novo ficheiro criado?
  - Ficheiro modificado? O que mudou?
  - Novo problema corrigido?
  - Nova funcionalidade adicionada?
  - Ficheiros de debug/removidos?

### 3. Limpeza de Código
- [ ] Removidos ficheiros de debug temporários?
- [ ] Comentários desnecessários removidos?
- [ ] Código segue style guide do projeto?

### 4. Git
- [ ] Commits com mensagens descritivas
- [ ] Um commit por mudança logical
- [ ] Branch atualizada com master

---

## 📝 Formato de Commit

```
<tipo>: <descrição curta>

[descrição longa opcional]
```

**Tipos:**
- `feat` - Nova funcionalidade
- `fix` - Correção de bug
- `refactor` - Refatoração
- `docs` - Documentação (inclui MEMORIA.md)
- `cleanup` - Limpeza código
- `deploy` - Deploy

**Exemplos:**
```
feat: Add export to CSV for deals
fix: Fix SQL syntax error in calendar.php  
docs: Update MEMORIA.md with new users
cleanup: Remove debug scripts
deploy: Push to production
```

---

## ⚠️ Lembretes

1. **SEMPRE** atualizar MEMORIA.md antes de push
2. **NUNCA** fazer commit de ficheiros debug (debug.php, test_*.php, etc)
3. **SEMPRE** testar antes de fazer push
4. **SEMPRE** fazer descrição clara do que mudou

---

## 🔧 Configuração (Opcional)

### Git Hook Automático
Pode criar um pre-commit hook que lembra de atualizar MEMORIA.md:

```bash
# .git/hooks/pre-commit
#!/bin/bash
echo "⚠️ LEMBRETE: Atualizou o MEMORIA.md?"
```

---

## 📂 Estrutura de Ficheiros

### Ficheiros Principais (MANTER)
- `*.php` - Páginas principais (index, login, deals, etc)
- `includes/*.php` - Componentes PHP
- `config.php` - Configuração

### Ficheiros Úteis (MANTER)
- `scripts/seed.php` - Seed dados teste
- `database_mysql.sql` - Schema BD

### Ficheiros temporários (REMOVER)
- `debug.php`, `test_*.php`, `fix*.php`
- `diagnostico.php`, `phpinfo.php`

---

**Última atualização:** 05/05/2026
**FTP Deploy:** ✅ Working - Auto-deploy on push
**Sistema:** ✅ 100% operacional com:
- Galeria de Imóveis (upload, delete, primary)
- styling de inputs (bordas douradas #c9a227)