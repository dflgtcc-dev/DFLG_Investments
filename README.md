# DFLG Investments

**Dream. Fund. Learn. Grow.**

Gestão financeira pessoal com gamificação: registre, planeje, simule investimentos e ganhe XP pelo hábito.

![PHP 8.2](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)
![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)
![Projeto Integrador](https://img.shields.io/badge/Projeto-Integrador-1f8f5f)

**[Acessar o sistema online](https://dflg-investments.ethiodeploy.app/)**

---

## Sobre o projeto

O **DFLG Investments** é um sistema web de gestão financeira pessoal. A maioria dos aplicativos do tipo vira só um depósito de dados e acaba abandonada; aqui o usuário é incentivado a voltar. Ele ganha **XP**, sobe de **nível**, mantém uma **sequência de acessos** e disputa um **ranking**.

Além do controle de receitas e despesas, o sistema oferece metas, compras parceladas, limites de gasto por categoria, extratos em PDF e 10 calculadoras de investimento.

> O XP premia **hábitos que o sistema consegue verificar** (acessar, usar relatórios, completar o perfil), nunca os valores registrados. Lançar mais gastos não rende pontos, então não há vantagem em inserir dados falsos.

O sistema **não se conecta a bancos nem faz pagamentos ou transferências**. O usuário registra tudo manualmente.

Projeto Integrador do curso Técnico em Desenvolvimento de Sistemas, Instituto Federal do Paraná (Foz do Iguaçu).

## Telas do sistema

**Página inicial**

![Página inicial](docs/screenshots/home.png)

**Dashboard**

![Dashboard](docs/screenshots/dashboard.png)

**Minhas Finanças**

![Minhas Finanças](docs/screenshots/financas.png)

**Metas**

![Metas](docs/screenshots/metas.png)

**Parcelamentos**

![Parcelamentos](docs/screenshots/parcelamentos.png)

**Calculadoras**

![Calculadoras](docs/screenshots/calculadoras.png)

**Ranking**

![Ranking](docs/screenshots/ranking.png)

## Sumário

1. [Teste rápido (sem instalar nada)](#1-teste-rápido-sem-instalar-nada)
2. [Funcionalidades](#2-funcionalidades)
3. [Gamificação](#3-gamificação)
4. [Painel administrativo](#4-painel-administrativo)
5. [Instalação local](#5-instalação-local)
6. [Roteiro sugerido para avaliar o sistema](#6-roteiro-sugerido-para-avaliar-o-sistema)
7. [Segurança e qualidade](#7-segurança-e-qualidade)
8. [Tecnologias e arquitetura](#8-tecnologias-e-arquitetura)
9. [Contribuindo](#9-contribuindo)
10. [Equipe](#10-equipe)

---

## 1. Teste rápido (sem instalar nada)

Acesse **<https://dflg-investments.ethiodeploy.app/>**.

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Usuário comum | crie a sua em **Cadastrar** (`/cadastrar`) | a que você definir |
| Administrador chefe | `admin@dflg.com` | `admin123` |

- O login é feito pelo **e-mail**.
- O cadastro público cria sempre um usuário comum.
- Para ver o sistema por inteiro, crie uma conta comum, use-a por um tempo e depois entre como administrador. Veja o [roteiro sugerido](#6-roteiro-sugerido-para-avaliar-o-sistema).

> O ambiente online é de demonstração e é compartilhado. Não registre dados financeiros reais nele.

### Perfis de usuário

| Perfil | O que acessa |
| --- | --- |
| **Usuário comum** | Finanças, metas, parcelamentos, limites, relatórios, calculadoras, ranking e perfil. É o único perfil com XP, nível e sequência. |
| **Administrador** | Painel administrativo (indicadores e gestão de contas) e o próprio perfil. Não acessa as telas financeiras e não participa da gamificação. |

---

## 2. Funcionalidades

| Módulo | O que faz |
| --- | --- |
| **Cadastro e login** | Criação de conta, entrada por e-mail e senha, saída da sessão |
| **Dashboard** | Saldo, receitas, despesas, gráficos, limites, metas, parcelas e últimos lançamentos de um mês |
| **Minhas Finanças** | Receitas e despesas, com filtros, busca e ordenação |
| **Metas** | Metas financeiras com aporte e resgate |
| **Parcelamentos** | Compras parceladas, lançadas parcela a parcela |
| **Categorias e limites** | Limite mensal de gastos por categoria, com alerta |
| **Relatórios** | Extrato mensal e anual, na tela e em PDF |
| **Calculadoras** | 10 calculadoras de investimento e planejamento |
| **Ranking** | Classificação semanal, mensal e geral |
| **Perfil** | Dados pessoais, foto, senha, preferências, tema claro/escuro e exclusão de conta |
| **Painel administrativo** | Indicadores de uso e gestão de contas |

### Cadastro e login

- Campos: nome (até 100 caracteres), **nickname** único (até 25), e-mail único e válido, telefone opcional (com DDD), data de nascimento e senha.
- Senha de **8 a 72 caracteres**, guardada criptografada.
- É preciso ter **pelo menos 16 anos**.
- Contas **bloqueadas** não conseguem entrar.
- Quem já está logado e abre a página inicial é levado direto ao seu painel.

### Dashboard

Mostra a situação financeira de **um mês** (o atual ou outro dentro do histórico do usuário):

- **Saldo** e resumo do mês, com **comparação com o período anterior** (o mês atual é comparado com os mesmos dias do mês anterior).
- **Nível, sequência de acessos** e barra de progresso de XP até o próximo nível.
- **Para onde foi seu dinheiro:** despesas por categoria (as seis maiores, e as demais agrupadas em "Outras categorias").
- **Receitas × despesas** dos últimos 6 meses.
- **Indicadores:** gasto médio por dia, maior despesa do mês, estimativa de despesas até o fim do mês (só aparece depois do dia 7, porque nos primeiros dias a média engana) e quanto da receita sobrou.
- **Limites do mês**, **metas em andamento** (a mais próxima do prazo primeiro), **parcelas do mês** e **últimos lançamentos**.

### Minhas Finanças (receitas e despesas)

- Cadastro, edição e exclusão com **tipo**, **valor** (maior que zero), **categoria**, **data** e **descrição**.
- A categoria precisa ser do mesmo tipo do lançamento.
- O topo mostra **total de receitas, total de despesas e saldo** da lista filtrada.
- **Filtros combináveis:** tipo, categoria, origem (lançadas por mim, parcelas ou metas), período, valor mínimo e máximo, e busca por texto. Filtros inválidos são descartados com um aviso.
- **Ordenação:** mais recentes, mais antigas, maior valor ou menor valor.
- Lançamentos criados por **parcelas** ou **metas** só são alterados pela tela de origem.
- Aportes e resgates de metas movem o saldo, mas **não entram nas somas** de receitas e despesas.

| Receitas | Despesas |
| --- | --- |
| Salário, Freelance, Rendimentos, Outras receitas, Resgate de metas | Alimentação, Moradia, Transporte, Saúde, Educação, Lazer, Compras, Contas e assinaturas, Outras despesas, Metas |

### Metas financeiras

- Cada meta tem **nome**, **tipo** (Economizar, Comprar algo ou Investir), **valor alvo**, **data limite** (sempre depois de hoje) e, opcionalmente, o **valor já guardado**.
- **Aportar:** transfere dinheiro do saldo para a meta, como **despesa** na categoria "Metas". Não pode passar do que falta para concluir nem do saldo disponível.
- **Resgatar:** devolve dinheiro ao saldo, como **receita** em "Resgate de metas". Não pode passar do valor guardado.
- **Editar:** o valor alvo nunca pode ficar abaixo do que já foi guardado.
- **Excluir:** todo o valor guardado volta para o saldo.
- A categoria "Metas" só é usada pelos aportes: não pode ser escolhida em lançamentos nem parcelamentos.

### Parcelamentos

- Cadastro com **descrição**, **categoria de despesa**, **tag** opcional, **quantidade de parcelas** (2 a 420), **valor da parcela** e **data da primeira parcela**.
- O sistema calcula o valor total e acompanha quantas parcelas já foram pagas.
- **Lançar parcela:** lança a próxima da sequência como despesa. Parcela vencida entra na data do vencimento; parcela antecipada entra com a data de hoje, então nunca fica lançamento no futuro. Clique duplo não lança a mesma parcela duas vezes.
- **Desfazer:** remove a última parcela lançada.
- Não é possível reduzir a quantidade de parcelas para menos do que já foi paga.
- Ao excluir o parcelamento, as parcelas já lançadas permanecem como despesas comuns.
- **Lembrete de vencimento:** um aviso no topo do dashboard, de Minhas Finanças e de Parcelamentos mostra as parcelas que vencem nos próximos **3 dias** ou que já venceram e ainda não foram lançadas. O aviso tem um **X** para fechar (volta no dia seguinte, ou se outra parcela entrar na janela) e some sozinho quando a parcela é lançada.

### Categorias e limites de gastos

- Define um **limite mensal** para cada categoria de despesa. Campo vazio remove o limite.
- O sistema compara o limite com o gasto do mês e **avisa quando a categoria passa do limite**. Gastar exatamente o valor do limite não conta como excesso.
- Se algum valor do formulário for inválido, **nada é gravado**.
- Os limites também aparecem no dashboard.
- O aviso de limite excedido tem um **X** para fechar. Ele não volta no mesmo mês, a menos que outra categoria ultrapasse o limite.

### Relatórios (extratos)

- **Extrato mensal:** saldo inicial, cada lançamento com o **saldo logo após ele**, totais e saldo final.
- **Extrato anual:** uma linha por mês, com o **saldo acumulado**.
- Anos disponíveis: do ano do primeiro registro do usuário até o atual.
- **Download em PDF** do mesmo extrato que está na tela.

### Calculadoras de investimento

Cada calculadora valida os dados, mostra o resultado na tela e aceita taxas **mensais ou anuais** (a conversão é feita pelo sistema e exibida). Os prazos são limitados a 100 anos.

| Calculadora | Para que serve |
| --- | --- |
| Juros compostos com aportes | Quanto o dinheiro rende com valor inicial e aportes mensais |
| Aporte para atingir uma meta | Quanto guardar por mês para chegar ao valor desejado |
| Tempo para atingir uma meta | Em quanto tempo se chega ao valor desejado com os aportes |
| Independência financeira | Patrimônio necessário para viver de renda e prazo para chegar lá |
| Planejamento de aposentadoria | Patrimônio e renda mensal estimados na aposentadoria |
| Reserva de emergência | Valor da reserva e tempo para montá-la |
| Valor real (inflação) | Quanto a inflação reduz o poder de compra ao longo do tempo |
| Comparador de cenários | Compara dois investimentos e indica qual termina com mais dinheiro |
| Quanto meu investimento rendeu | Rentabilidade e taxa média de um investimento já feito |
| Juros simples | Calcula juros simples e compara com juros compostos |

### Ranking

- Três visões: **Semanal** (segunda a domingo), **Mensal** (mês do calendário) e **Geral**.
- **Pódio** (1º a 3º) e lista dos **20 primeiros**, com nickname, nível e XP. Quem está fora do top 20 vê a própria posição à parte.
- **Desempate:** nível, depois XP (do período, nas visões semanal e mensal), depois maior sequência e, por fim, cadastro mais antigo.
- Só aparecem usuários comuns **ativos** com a opção "Aparecer no ranking" ligada.
- Nas visões semanal e mensal, entra quem ganhou XP no período.

### Perfil

- Dados, nível, XP total, sequência atual e **maior sequência**.
- Edição de nome, nickname, e-mail, telefone e data de nascimento.
- **Foto de perfil** em JPG, PNG ou WebP de até 2 MB, que pode ser trocada ou removida.
- **Alterar senha:** exige a senha atual e uma nova, diferente, com confirmação.
- **Preferências:** aparecer ou não no ranking e **modo claro ou escuro** (salvo na conta).
- **Excluir conta:** exige confirmação explícita e é **definitiva**. Apaga lançamentos, metas, parcelamentos, limites e histórico de XP.

---

## 3. Gamificação

| Ação | XP | Frequência |
| --- | --- | --- |
| Primeiro acesso do dia | 5 | uma vez por dia |
| Usar as calculadoras | 10 | uma vez por dia |
| Completar o perfil (com foto) | 20 | uma única vez |
| Definir as preferências | 10 | uma única vez |
| Abrir os relatórios pela primeira vez | 15 | uma única vez |
| Sequência de 7 dias | 25 | uma única vez |
| Sequência de 30 dias | 100 | uma única vez |
| Sequência de 100 dias | 300 | uma única vez |
| Sequência de 1000 dias | 5000 | uma única vez |

**Níveis:** para passar do nível *n* para o *n + 1* são necessários *n × 100* XP (100 XP no nível 1, 200 no nível 2, e assim por diante).

**Sequência (streak):** conta os dias seguidos de acesso. Ficou um dia sem entrar, ela recomeça do primeiro dia, mas XP e nível são mantidos. O sistema guarda também a **maior sequência** já alcançada. Cada marco (7, 30, 100 e 1000 dias) rende XP **uma única vez na vida**, mesmo que a sequência seja quebrada e refeita.

**Notificação de XP:** ao ganhar pontos ou subir de nível, aparece um aviso na tela, exibido uma única vez.

---

## 4. Painel administrativo

O administrador entra direto no painel.

- **Indicadores:** total de usuários comuns (ativos e bloqueados), quantidade de administradores, novos cadastros nos últimos 7 e 30 dias e **engajamento** por tempo desde o último acesso: *ativos* (até 7 dias), *em risco* (8 a 30) e *inativos* (mais de 30).
- **Gestão de contas:** lista com busca, filtro por status e paginação; edição de dados (a gamificação não é editável); redefinição de senha; bloqueio e desbloqueio; exclusão com confirmação.
- Um usuário bloqueado com a sessão aberta **perde o acesso no próximo clique** e vê um aviso na tela de login.

**Permissões**

| Ação | Administrador chefe | Administrador comum |
| --- | --- | --- |
| Ver e gerenciar usuários comuns | Sim | Sim |
| Ver e gerenciar outros administradores | Sim | Não |
| Criar novos administradores | **Somente o chefe** | Não |
| Excluir contas | **Somente o chefe** | Não |
| Agir sobre a própria conta pelo painel | Não | Não |
| Ser alterado, bloqueado ou excluído por outro administrador | Nunca (o chefe é protegido) | Sim, pelo chefe |

O administrador altera a própria senha e os próprios dados pela tela de **Perfil**.

---

## 5. Instalação local

### Requisitos

- PHP **8.2** ou superior, com as extensões `pdo`, `pdo_mysql` e `mbstring`
- MySQL (ou MariaDB)
- Apache com `mod_rewrite` (recomendado), ou o servidor embutido do PHP para testes

### Passo a passo

**1. Baixe o projeto**

```bash
git clone <URL-DO-REPOSITORIO>
cd DFLG_Investments
```

**2. Crie o banco de dados (vazio)**

```bash
mysql -u root -p -e "CREATE DATABASE dflg CHARACTER SET utf8mb4;"
```

No primeiro acesso, o `DatabaseInitializer` percebe que o banco está vazio e cria sozinho as tabelas, as categorias padrão e a conta do administrador chefe (`admin@dflg.com`). Se preferir, é possível importar o esquema à mão (por exemplo, no phpMyAdmin): `mysql -u root -p dflg < app/database/scripts/script.sql`.

Em bancos que já existem, as atualizações de estrutura entre versões também são aplicadas automaticamente.

**3. Informe a conexão com o banco**

A conexão é lida da variável de ambiente `DATABASE_URL`, no formato:

```
mysql://USUARIO:SENHA@HOST:PORTA/NOME_DO_BANCO
```

Exemplo no Linux/macOS:

```bash
export DATABASE_URL="mysql://root:minhasenha@127.0.0.1:3306/dflg"
```

No Apache, defina a variável no virtual host com `SetEnv DATABASE_URL "mysql://..."`.

> As constantes `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS` do `Config.php` não são usadas pela conexão atual.

**4. Ajuste o endereço do sistema**

Em `app/config/Config.php`, troque `URL_BASE` pelo endereço onde o projeto vai rodar, **sem barra no final**:

```php
define('URL_BASE', 'http://localhost:8000');
```

**5. Suba o servidor**

*Opção A, servidor embutido do PHP (mais simples para testar):*

```bash
php -S localhost:8000 -t public
```

Abra <http://localhost:8000>.

*Opção B, Apache em subpasta:* copie o projeto para `htdocs/DFLG` (ou `/var/www/html/DFLG`), mantenha o `.htaccess` da raiz, que já usa `RewriteBase /DFLG/`, e defina `URL_BASE` como `http://localhost/DFLG`. Se usar outro nome de pasta, altere o `RewriteBase`.

### Observações

- **Fotos de perfil** são salvas em `public/assets/uploads/perfil`. A pasta é criada automaticamente, mas o servidor precisa de permissão de escrita em `public/assets`.
- **Erros detalhados:** em `Config.php`, `DEV_ENVIRONMENT` em `true` exibe os erros do PHP na tela. Mantenha `false` em produção.
- **Troque a senha do administrador** (`admin123`) antes de publicar o sistema em qualquer ambiente real.
- O fuso horário padrão é `America/Sao_Paulo`, definido em `Config.php`.

---

## 6. Roteiro sugerido para avaliar o sistema

1. **Página inicial:** veja a apresentação do sistema e as dúvidas frequentes.
2. **Cadastro:** crie uma conta de usuário comum e entre. Note a notificação de **5 XP** pelo primeiro acesso do dia.
3. **Perfil:** envie uma foto (+20 XP) e salve as preferências (+10 XP). Teste o **modo escuro**.
4. **Minhas Finanças:** cadastre uma receita e algumas despesas em categorias diferentes. Experimente filtros, busca e ordenação.
5. **Categorias:** defina um limite baixo e lance uma despesa que o ultrapasse. Observe o **alerta** e o quadro "Limites do mês" no dashboard.
6. **Metas:** crie uma meta, faça um **aporte** (veja a despesa em "Metas" e o saldo diminuir) e depois um **resgate**.
7. **Parcelamentos:** cadastre uma compra parcelada, use **Lançar parcela** e depois **Desfazer**. Veja as parcelas em "A pagar neste mês" no dashboard.
8. **Dashboard:** confira gráficos, indicadores e a comparação com o mês anterior. Troque o mês exibido.
9. **Relatórios:** abra o extrato mensal (+15 XP), o anual e baixe o **PDF**.
10. **Calculadoras:** use qualquer uma (+10 XP) e teste o comparador de cenários.
11. **Ranking:** veja as três visões. Desligue "Aparecer no ranking" no perfil e confira que você deixa de aparecer.
12. **Administrador:** saia e entre com `admin@dflg.com`. Veja os indicadores, bloqueie a conta de teste e tente entrar com ela, depois desbloqueie. Crie um novo administrador (ação exclusiva do chefe).

---

## 7. Segurança e qualidade

- **Senhas criptografadas** (bcrypt) e sessão renovada a cada login.
- **Validação no servidor** de todos os formulários, com mensagens claras. Dados de filtros e formulários nunca são tratados como confiáveis.
- **Consultas protegidas** contra injeção de SQL (PDO com parâmetros).
- **Controle de acesso por perfil:** o usuário comum não acessa o painel administrativo e o administrador não acessa as telas financeiras.
- **Contas bloqueadas ou excluídas** perdem o acesso mesmo com a sessão aberta.
- **Operações atômicas** na concessão de XP: histórico e total de pontos são gravados juntos, ou nenhum dos dois.
- **Proteção contra duplicidade**, como lançar a mesma parcela duas vezes.
- **Falha do banco de dados:** o usuário vê uma tela de aviso clara, e o detalhe técnico vai só para o log.
- **Interface responsiva** (Bootstrap), com tema claro e escuro.

---

## 8. Tecnologias e arquitetura

| Camada | Tecnologia |
| --- | --- |
| Linguagem | PHP 8.2, sem framework |
| Banco de dados | MySQL |
| Interface | HTML, CSS, JavaScript, Bootstrap 5 e Bootstrap Icons |
| PDF | FPDF |
| Servidor | Apache com `mod_rewrite` |

O projeto segue uma arquitetura em camadas (MVC com *services* e *repositories*), com roteador e autoload próprios:

```
app/
├── config/         configurações do sistema (Config.php)
├── core/           roteador, autoload e controller base
├── controllers/    recebem as requisições e escolhem a tela
├── services/       regras de negócio (XP, metas, limites, ranking, calculadoras...)
├── repositories/   acesso ao banco de dados
├── models/         entidades (Usuario, Meta, Transacao, Parcelamento...)
├── helpers/        upload de imagem, validador, geração do PDF do extrato
├── libs/           FPDF
├── views/          telas em PHP
└── database/       conexão, migrações e scripts/script.sql
public/
├── index.php       ponto de entrada e tabela de rotas
└── assets/         CSS, JavaScript e imagens
```

**Principais tabelas:** `usuarios`, `usuario_comum`, `administradores`, `categorias`, `registro_financeiro`, `metas`, `parcelamentos`, `limites_categoria` e `historico_xp`.

---

## 9. Contribuindo

Sugestões e correções são bem-vindas.

**Encontrou um problema ou tem uma ideia?** Abra uma *issue* no repositório e descreva:

- o que você esperava e o que aconteceu;
- os passos para reproduzir (tela, dados informados);
- a versão do PHP e do MySQL, se for um erro de instalação.

**Quer alterar o código?**

1. Faça um *fork* do repositório e crie uma branch a partir da principal:
   ```bash
   git checkout -b minha-alteracao
   ```
2. Siga a organização em camadas do projeto:
   - o **controller** só recebe a requisição e escolhe a tela;
   - as **regras de negócio** ficam em `services/`;
   - o **acesso ao banco** fica em `repositories/`, sempre com consultas preparadas (PDO com parâmetros);
   - novas rotas entram em `public/index.php`.
3. Valide no servidor qualquer dado vindo de formulário ou filtro.
4. Se a alteração mexer no banco, crie uma migração em `app/database/DatabaseInitializer.php`, para não quebrar instalações existentes.
5. Teste o fluxo afetado, como usuário comum e como administrador, quando fizer sentido.
6. Faça commits pequenos, com mensagens claras, e abra um *pull request* explicando o que mudou e por quê.

**Nunca envie para o repositório** senhas, strings de conexão reais (`DATABASE_URL`) nem fotos de perfil de usuários (`public/assets/uploads/`).

---

## 10. Equipe

Projeto Integrador do curso Técnico em Desenvolvimento de Sistemas, Instituto Federal do Paraná, Foz do Iguaçu, 2026.

- Daniel Medeiros de Sá
- Felipe Lenz Soares dos Santos
- Gabriel Guilhen
- Lucas Bordini Ragnini

**Orientadores:** Ana Carla Arruda de Holanda e Jefferson de Oliveira Chaves
