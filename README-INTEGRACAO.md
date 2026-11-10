# PetAlert — pacote integrado

## O que foi preservado
- Backend principal do pacote de 03/11/2026: controllers, models, DAO, services, middleware e rotas.
- Frontend existente no pacote principal: `public/`, incluindo páginas, CSS, JavaScript, imagens e administração.
- Dependências Composer e estrutura de uploads existentes.

## O que foi integrado
- `config/database.php` foi substituído pela versão com leitura de `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS` do `.env`, proveniente do pacote de 09/11.
- A classe continua expondo `Database::getConnection(): PDO`, preservando o contrato utilizado pelo backend principal.
- O banco permanece `petalert`. Nenhum banco ou tabela foi criado.

## Segurança
- O `.env` original não foi incluído porque continha chaves e credenciais.
- Copie `.env.example` para `.env` e informe seus próprios dados locais.
- Não publique nem compartilhe o `.env`.
- Credenciais que estavam dentro dos ZIPs devem ser consideradas expostas e substituídas nos respectivos serviços.
- Backups SQL com dados potencialmente pessoais e arquivos temporários/logs foram omitidos do pacote.

## Instalação local (XAMPP)
1. Extraia a pasta `Backand 3 (1).0` para `C:\xampp\htdocs\`.
2. Copie `.env.example` para `.env` na raiz do projeto.
3. Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS` para o banco `petalert` já existente.
4. A recuperação de senha foi alterada para funcionar sem SMTP: o usuário confirma e-mail, CPF e data de nascimento e, após a conferência no banco, define uma nova senha.
5. Se necessário, execute `composer install` na raiz do projeto.
6. Inicie Apache e MySQL/MariaDB no XAMPP.
7. Acesse `http://localhost/Backand%203%20(1).0/public/`.

## Limites dos testes
O pacote foi verificado estruturalmente e os arquivos PHP foram submetidos à verificação de sintaxe quando o PHP CLI estava disponível. Testes funcionais de login, cadastro, CRUD e operações com banco dependem do ambiente XAMPP e do banco local; não foram simulados como se tivessem sido executados. A recuperação local exige que e-mail, CPF e data de nascimento correspondam ao mesmo registro ativo em `tb_usuario`. A senha nova é gravada em `tb_usuario.senha` usando `password_hash`; o login existente já aceita senhas com hash.


## Recuperação de senha sem envio de e-mail
- A página `public/login/recuperar-senha.html` solicita e-mail, CPF e data de nascimento.
- O backend consulta os três dados em `tb_usuario`; não cria tabela ou coluna adicional.
- Após a conferência, uma autorização temporária é mantida na sessão PHP por 10 minutos.
- A nova senha é atualizada em `tb_usuario.senha` com hash seguro e a autorização é invalidada após o uso.
- O fluxo não depende de `MAIL_HOST`, `MAIL_PORT` ou senha de aplicativo do Gmail.
- Essa validação é adequada como fluxo básico de projeto acadêmico, mas não equivale a uma verificação forte por código enviado a um canal previamente confirmado.
