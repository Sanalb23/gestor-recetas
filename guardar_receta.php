<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tiempo = intval($_POST['tiempo'] ?? 0);

    if (!empty($nombre)) {
        $conexion->begin_transaction();

        try {
            // 1. Insertar receta principal
            $stmt = $conexion->prepare("INSERT INTO recetas (nombre, descripcion, tiempo_estimado) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $nombre, $descripcion, $tiempo);
            $stmt->execute();
            $receta_id = $stmt->insert_id;
            $stmt->close();

            // 2. Insertar ingredientes con cantidad y nombre separados
            if (!empty($_POST['ing_cantidades']) && !empty($_POST['ing_nombres'])) {
                $cantidades = $_POST['ing_cantidades'];
                $nombres = $_POST['ing_nombres'];

                $stmt_ing = $conexion->prepare("INSERT INTO ingredientes (receta_id, cantidad, nombre) VALUES (?, ?, ?)");
                for ($i = 0; $i < count($cantidades); $i++) {
                    $cant = trim($cantidades[$i] ?? '');
                    $nom = trim($nombres[$i] ?? '');
                    if ($nom !== '') {
                        $stmt_ing->bind_param("iss", $receta_id, $cant, $nom);
                        $stmt_ing->execute();
                    }
                }
                $stmt_ing->close();
            }

            // 3. Insertar pasos ordenados automáticamente
            if (!empty($_POST['pasos']) && is_array($_POST['pasos'])) {
                $stmt_paso = $conexion->prepare("INSERT INTO pasos (receta_id, orden, instruccion) VALUES (?, ?, ?)");
                $orden = 1;
                foreach ($_POST['pasos'] as $instruccion) {
                    $inst = trim($instruccion);
                    if ($inst !== '') {
                        $stmt_paso->bind_param("iis", $receta_id, $orden, $inst);
                        $stmt_paso->execute();
                        $orden++;
                    }
                }
                $stmt_paso->close();
            }

            $conexion->commit();
            header("Location: index.php");
            exit;

        } catch (Exception $e) {
            $conexion->rollback();
            die("Error al guardar la receta: " . $e->getMessage());
        }
    }
}

header("Location: index.php");
exit;
?>