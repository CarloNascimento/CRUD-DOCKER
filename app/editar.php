<?php
include "conexao.php";
$id=$_GET["id"];

if($_POST){
$conn->query("UPDATE produtos SET nome='{$_POST['nome']}', descricao='{$_POST['descricao']}' WHERE id=$id");
header("Location:index.php");
}

$p=$conn->query("SELECT * FROM produtos WHERE id=$id")->fetch_assoc();
?>

<form method="post">
Nome: <input name="nome" value="<?= $p['nome'] ?>"><br>
Descrição: <textarea name="descricao"><?= $p['descricao'] ?></textarea><br>
<button>Salvar</button>
</form>
