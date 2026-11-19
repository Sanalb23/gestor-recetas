<?php
require_once 'conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id > 0) {
        $stmt = $conexion->prepare("DELETE FROM recetas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: index.php");
exit;
?>