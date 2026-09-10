# CRUD de Produtos com PHP, MySQL e Docker Compose

Aplicação web acadêmica para cadastrar, listar, editar e excluir produtos, desenvolvida em PHP e integrada a um banco de dados MySQL. O ambiente utiliza Docker Compose para executar a aplicação e o banco em containers separados.

O projeto demonstra as quatro operações do CRUD, a comunicação entre serviços pela rede Docker e a persistência dos dados em um volume.

## Funcionalidades

- **Cadastrar:** formulário que envia nome e descrição pelo método POST.
- **Listar:** página inicial que apresenta ID, nome, descrição e links para editar e excluir cada produto.
- **Editar:** formulário preenchido com os dados atuais do produto, com atualização via POST.
- **Excluir:** remoção do produto pelo link Excluir. Na implementação atual, a exclusão é imediata e não solicita confirmação.
- **Registrar a data:** o banco preenche automaticamente a data de cadastro; esse campo não aparece na listagem atual.

## Tecnologias

| Tecnologia | Uso no projeto |
| --- | --- |
| PHP 8.3 | Processamento das páginas e das operações do CRUD |
| Apache | Servidor HTTP incluído na imagem `php:8.3-apache` |
| MySQL 8.0 | Armazenamento dos produtos, usando a imagem oficial `mysql:8.0` |
| MySQLi | Extensão PHP utilizada por `app/conexao.php` para acessar o banco |
| Docker Compose | Configuração dos serviços, variáveis, volumes, porta e rede |
| HTML | Formulários e apresentação dos registros |

## Estrutura do projeto

```text
CRUD-DOCKER/
├── docker-compose.yml
├── README.md
└── app/
    ├── conexao.php
    ├── index.php
    ├── novo.php
    ├── editar.php
    └── excluir.php
```

| Arquivo | Responsabilidade |
| --- | --- |
| `docker-compose.yml` | Define o ambiente da aplicação e do banco |
| `app/conexao.php` | Lê as variáveis de ambiente, conecta ao MySQL e cria a tabela caso ela não exista |
| `app/index.php` | Consulta e lista todos os produtos |
| `app/novo.php` | Exibe o formulário e insere um produto |
| `app/editar.php` | Busca o produto pelo ID e atualiza seus dados |
| `app/excluir.php` | Exclui o produto pelo ID e redireciona para a listagem |

## Pré-requisitos

- Docker instalado e em execução, configurado para containers Linux.
- Docker Compose disponível pelo comando `docker compose` ou `docker-compose`.
- Git, caso o projeto seja obtido por clonagem.
- Porta `8080` disponível e acesso à internet para baixar as imagens na primeira execução.

No Windows, o Docker Desktop pode fornecer Docker e Compose. Não é necessário instalar PHP, Apache ou MySQL diretamente no computador.

Confira o ambiente:

```bash
docker --version
docker compose version
```

## Como executar

Execute os comandos desta seção dentro da pasta que contém `docker-compose.yml`.

### 1. Obter o projeto

Se recebeu o arquivo ZIP, extraia-o e abra um terminal na pasta `CRUD-DOCKER`.

Para obter pelo GitHub:

```bash
git clone https://github.com/CarloNascimento/CRUD-DOCKER.git CRUD-DOCKER
cd CRUD-DOCKER
```

Repositório público: [CarloNascimento/CRUD-DOCKER](https://github.com/CarloNascimento/CRUD-DOCKER).

### 2. Iniciar os containers

```bash
docker compose up -d
```

O enunciado utiliza a grafia abaixo, equivalente em ambientes que oferecem o comando com hífen:

```bash
docker-compose up -d
```

Use a grafia disponível na sua instalação também nos demais comandos. A opção `-d` executa os containers em segundo plano.

### 3. Conferir e preparar a extensão MySQLi

O código utiliza `new mysqli(...)`. O Compose atual aponta para `php:8.3-apache`, mas não inclui uma etapa de instalação dessa extensão. Portanto, iniciar os containers, isoladamente, não basta para assegurar o funcionamento do CRUD.

Confira a disponibilidade:

```bash
docker compose exec app php --ri mysqli
```

Se a extensão não estiver presente, instale-a manualmente no container e reinicie a aplicação:

```bash
docker compose exec app docker-php-ext-install mysqli
docker compose restart app
docker compose exec app php --ri mysqli
```

Essa preparação altera somente o container existente. Ela permanece após um simples `restart`, mas precisa ser repetida se o container `app` for removido e recriado, por exemplo após `docker compose down` seguido de `up -d`. O projeto atual não contém um Dockerfile nem uma imagem personalizada com essa instalação incorporada.

### 4. Aguardar o banco de dados

Na primeira execução, o MySQL pode levar algum tempo para inicializar. Consulte:

```bash
docker compose ps
docker compose logs db
```

Aguarde a mensagem de disponibilidade do servidor MySQL. O `depends_on` define a ordem de inicialização dos serviços; ele não garante que o banco já esteja pronto para aceitar conexões.

### 5. Acessar a aplicação

Abra [http://localhost:8080](http://localhost:8080).

Selecione **Cadastrar produto**, preencha nome e descrição e envie o formulário. O produto aparecerá na página inicial, com os links **Editar** e **Excluir**.

## Banco de dados e criação da tabela

O banco se chama `crud_produtos`. A imagem MySQL cria esse banco durante a primeira inicialização de um volume vazio, a partir da variável `MYSQL_DATABASE`.

A tabela `produtos` é criada pelo próprio `app/conexao.php`, quando uma página inclui esse arquivo e consegue estabelecer a conexão. O código executa:

```sql
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    descricao TEXT,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

| Campo | Tipo | Finalidade |
| --- | --- | --- |
| `id` | `INT`, chave primária e incremento automático | Identifica cada produto |
| `nome` | `VARCHAR(100)` | Nome do produto |
| `descricao` | `TEXT` | Descrição do produto |
| `data_cadastro` | `DATETIME` | Data e hora preenchidas automaticamente pelo banco |

A entidade possui três campos além do ID. Não há arquivo SQL separado no pacote nem uma etapa manual de criação da tabela. `IF NOT EXISTS` evita recriar uma tabela existente, mas não atualiza sua estrutura caso o código seja modificado posteriormente.

## Explicação do Docker Compose

### Serviço `app`

- `image: php:8.3-apache`: utiliza uma imagem pronta com PHP e Apache.
- `container_name: crud-php-app`: define o nome do container da aplicação.
- `ports: 8080:80`: encaminha a porta `8080` do computador para a porta `80` do Apache.
- `volumes: ./app:/var/www/html`: disponibiliza a pasta local `app` no diretório servido pelo Apache. Alterações nos arquivos PHP são refletidas no container.
- `environment`: fornece as configurações que o PHP lê usando `getenv()`.
- `depends_on: db`: inicia o serviço do banco antes da aplicação, sem verificar sua prontidão.
- `networks: minha-rede`: conecta a aplicação à rede compartilhada com o banco.

### Serviço `db`

- `image: mysql:8.0`: utiliza a imagem oficial do MySQL 8.0.
- `container_name: crud-mysql-db`: define o nome do container do banco.
- `environment`: configura a senha inicial do administrador e o nome do banco.
- `volumes: mysql-data:/var/lib/mysql`: armazena os arquivos do MySQL em um volume persistente.
- `networks: minha-rede`: permite a comunicação com a aplicação pela rede interna.

O serviço `db` não publica uma porta no computador. A aplicação o acessa internamente pelo nome `db`, na porta padrão do MySQL.

### Variáveis de ambiente

| Serviço | Variável | Valor atual | Função |
| --- | --- | --- | --- |
| `app` | `DB_HOST` | `db` | Endereço do serviço MySQL na rede interna |
| `app` | `DB_USER` | `root` | Usuário utilizado na conexão |
| `app` | `DB_PASSWORD` | `root` | Senha utilizada na conexão |
| `app` | `DB_NAME` | `crud_produtos` | Banco utilizado pelo PHP |
| `db` | `MYSQL_ROOT_PASSWORD` | `root` | Senha configurada para o administrador na inicialização |
| `db` | `MYSQL_DATABASE` | `crud_produtos` | Banco criado na inicialização |

Todas as variáveis estão diretamente no `docker-compose.yml`. O projeto não utiliza arquivo `.env`. As credenciais apresentadas são as configurações didáticas do pacote e não devem ser reutilizadas em um ambiente público.

### Volume e persistência

`mysql-data` é um volume nomeado, declarado na seção `volumes`. Os registros permanecem após reiniciar os containers e também após `docker compose down`, desde que o volume não seja removido.

O Compose pode acrescentar o nome do projeto ao nome real do volume. A montagem `./app:/var/www/html`, por sua vez, é um vínculo com uma pasta do computador, não o volume de dados do MySQL.

### Rede personalizada

`minha-rede` utiliza o driver `bridge`. Os dois serviços participam dessa rede, permitindo que o PHP encontre o banco pelo nome `db`. Por isso, `DB_HOST` usa `db` em vez de `localhost`, que dentro do container da aplicação apontaria para ele próprio.

## Comandos úteis

Ver os serviços e consultar a saída dos containers:

```bash
docker compose ps
docker compose logs app
docker compose logs db
```

Parar e retomar os containers existentes:

```bash
docker compose stop
docker compose start
```

Encerrar e remover os containers e a rede, preservando o volume do banco:

```bash
docker compose down
```

Não acrescente `-v` ao comando `down` se deseja manter os produtos: essa opção também remove os volumes declarados. Após recriar o container da aplicação, confira novamente a extensão MySQLi.

## Roteiro de verificação e apresentação

1. Obter uma cópia nova do projeto em uma máquina com Docker.
2. Seguir os passos de execução, incluindo a preparação do MySQLi se necessária.
3. Abrir a página inicial e cadastrar um produto de teste.
4. Confirmar que o produto aparece na listagem com os dados informados.
5. Editar o nome e a descrição e verificar a atualização.
6. Executar `docker compose restart`, aguardar o banco e confirmar a persistência do produto.
7. Excluir o produto de teste e confirmar sua remoção.
8. Explicar os serviços `app` e `db`, as variáveis, a porta, o volume e a rede.

Este é um roteiro de validação a executar, não um registro de testes aprovados. A revisão que originou este README foi estática; os containers não foram executados no ambiente de revisão.

## Aprendizados e decisões técnicas

1. **Separação de serviços:** PHP/Apache e MySQL executam em containers distintos, com responsabilidades definidas.
2. **Configuração por variáveis:** `getenv()` permite que a conexão PHP use os valores do Compose, sem fixar as credenciais no arquivo de conexão.
3. **Comunicação por nome:** a rede compartilhada permite resolver `db` sem depender do endereço IP do container.
4. **Persistência independente do container:** o volume mantém os dados mesmo quando o container do banco é substituído, desde que o volume seja preservado.
5. **Dependências da aplicação:** uma imagem com PHP e Apache não garante a disponibilidade de toda extensão exigida pelo código; é necessário conferir o MySQLi.
6. **Ordem não significa prontidão:** iniciar o MySQL antes do PHP não assegura uma conexão imediata durante a primeira inicialização.

## Limitações e pontos de atenção

- O código concatena entradas do usuário nas consultas SQL e apresenta dados sem escape HTML. Também não possui validação consistente dos campos, autenticação ou proteção contra requisições forjadas. A exclusão utiliza GET. A implementação requer correções antes de uso público ou com dados reais.
- A imagem da aplicação não incorpora a preparação do MySQLi; a etapa manual deve ser considerada na demonstração em uma máquina limpa.
- O Compose possui comentários, mas algumas linhas ainda não estão explicadas. O enunciado exige comentários em todas as linhas; a explicação deste README não substitui essa exigência.
- A tabela é criada automaticamente pelo PHP. O PDF cita essa possibilidade na seção do README, mas também proíbe scripts de inicialização automática. É necessário esclarecer a abrangência dessa restrição para a entrega; este documento descreve o comportamento efetivamente implementado.
- O histórico deste repositório registra a importação do CRUD e a inclusão da documentação em `main`. Ele não reconstrói as etapas de desenvolvimento anteriores ao ZIP, nem comprova a execução do sistema.

## Referências técnicas

- [Imagem oficial do PHP e instalação de extensões](https://hub.docker.com/_/php).
- [Ordem de inicialização dos serviços no Docker Compose](https://docs.docker.com/compose/how-tos/startup-order/).

## Origem do projeto

O código foi importado do pacote `CRUD-DOCKER.zip` fornecido pelos autores, substituindo os arquivos PHP vazios da estrutura inicial. A aplicação e o Compose foram organizados na raiz para que os comandos deste README possam ser executados logo após a clonagem. A autoria da dupla foi preservada.

## Autores

- Carlos Gabriel Alves Nascimento
- Wesley Kenji Ito Hidehira
