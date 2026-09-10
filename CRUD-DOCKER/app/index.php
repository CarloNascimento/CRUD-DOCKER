<?php include "conexao.php"; ?>
<h1>CRUD Produtos</h1>
<a href="novo.php">Cadastrar produto</a>
<hr>

<?php
$result = $conn->query("SELECT * FROM produtos");
while($p = $result->fetch_assoc()){
    echo "<p>
    {$p['id']} - {$p['nome']} - {$p['descricao']}
    <a href='editar.php?id={$p['id']}'>Editar</a>
    <a href='excluir.php?id={$p['id']}'>Excluir</a>
    </p>";
}
?>
