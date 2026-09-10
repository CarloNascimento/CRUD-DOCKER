<?php
include "conexao.php";
$conn->query("DELETE FROM produtos WHERE id=".$_GET["id"]);
header("Location:index.php");
?>
