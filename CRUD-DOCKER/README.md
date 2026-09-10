# CRUD Produtos - PHP + Docker Compose

## Correção da conexão com o MySQL

O problema principal era que a imagem `php:8.3-apache` não instala a extensão `mysqli` por padrão. O arquivo `Dockerfile` agora instala essa extensão.

Também foi adicionado um `healthcheck` no MySQL para que o PHP só dependa do banco depois que ele estiver pronto.

## Executar

Na pasta do projeto:

```bash
docker compose down
docker compose up -d --build
```

Depois acesse:

http://localhost:8080

## Se o banco antigo estiver causando conflito

Se você já executou uma versão anterior e alterou usuário/senha/banco, pode recriar o volume:

```bash
docker compose down -v
docker compose up -d --build
```

Atenção: `down -v` apaga os dados do banco armazenados nesse volume.

## Estrutura

- `app/` - arquivos PHP
- `Dockerfile` - instala PHP/Apache + mysqli
- `docker-compose.yml` - PHP + MySQL
