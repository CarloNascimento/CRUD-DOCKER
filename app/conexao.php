<?php
$host=getenv('DB_HOST')?:'db';$user=getenv('DB_USER')?:'root';$password=getenv('DB_PASSWORD')?:'root';$database=getenv('DB_NAME')?:'crud_produtos';
$conn=new mysqli($host,$user,$password,$database);if($conn->connect_errno){die('Erro na conexão com o banco: ('.$conn->connect_errno.') '.$conn->connect_error);}$conn->set_charset('utf8mb4');
$conn->query("CREATE TABLE IF NOT EXISTS produtos (id INT AUTO_INCREMENT PRIMARY KEY,nome VARCHAR(100) NOT NULL,descricao TEXT,data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP)");
