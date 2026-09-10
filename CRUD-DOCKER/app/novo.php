<?php
include "conexao.php";

if($_POST){
    $nome=$_POST["nome"];
    $desc=$_POST["descricao"];
    $conn->query("INSERT INTO produtos(nome,descricao) VALUES('$nome','$desc')");
    header("Location:index.php");
}
?>

<form method="post">
Nome: <input name="nome"><br>
Descrição: <textarea name="descricao"></textarea><br>
<button>Cadastrar</button>
</form>
