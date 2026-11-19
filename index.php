<?php
$recetas = [];
$error_db = null;

if (file_exists('conexion.php')) {
    try {
        @include_once 'conexion.php';
        if (isset($conexion) && !$conexion->connect_error) {
            $sql_recetas = "SELECT * FROM recetas ORDER BY creado_en DESC";
            $res_recetas = $conexion->query($sql_recetas);

            if ($res_recetas && $res_recetas->num_rows > 0) {
                while ($receta = $res_recetas->fetch_assoc()) {
                    $receta_id = (int)$receta['id'];

                    // Obtener ingredientes
                    $res_ing = $conexion->query("SELECT cantidad, nombre FROM ingredientes WHERE receta_id = $receta_id");
                    $ingredientes = [];
                    if ($res_ing) {
                        while ($ing = $res_ing->fetch_assoc()) {
                            $ingredientes[] = $ing;
                        }
                    }

                    // Obtener pasos
                    $res_pasos = $conexion->query("SELECT orden, instruccion FROM pasos WHERE receta_id = $receta_id ORDER BY orden ASC");
                    $pasos = [];
                    if ($res_pasos) {
                        while ($paso = $res_pasos->fetch_assoc()) {
                            $pasos[] = $paso;
                        }
                    }

                    $receta['ingredientes'] = $ingredientes;
                    $receta['pasos'] = $pasos;
                    $recetas[] = $receta;
                }
            }
        }
    } catch (Exception $e) {
        $error_db = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Recetas</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Encabezado -->
    <header class="header">
        <div class="header-content">
            <div class="header-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h1>Mis Recetas</h1>
            </div>
        </div>
    </header>

    <!-- Contenedor Principal -->
    <main class="main-container">
        
        <!-- Encabezado de la lista y Botón Agregar -->
        <div class="controls-bar">
            <div class="controls-title">
                <h2>Mis Creaciones</h2>
                <span id="recipeCount" class="count-badge"><?= count($recetas) ?> receta<?= count($recetas) !== 1 ? 's' : '' ?></span>
            </div>
            <button id="openModalBtn" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Agregar receta
            </button>
        </div>

        <!-- Modal del Formulario -->
        <div id="formModal" class="modal-overlay">
            <div class="modal-content" id="modalContent">
                
                <button type="button" id="closeModalBtn" class="btn-close" aria-label="Cerrar modal">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <h2 class="modal-header">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Receta
                </h2>
                
                <form id="recipeForm" action="guardar_receta.php" method="POST">
                    <!-- Nombre -->
                    <div class="form-group">
                        <label for="name" class="form-label">Nombre de la receta *</label>
                        <input type="text" id="name" name="nombre" required placeholder="Ej. Ensalada Fresca de Verano" class="form-control">
                    </div>

                    <!-- Tiempo Estimado -->
                    <div class="form-group">
                        <label for="time" class="form-label">Tiempo estimado (en minutos) *</label>
                        <div class="input-with-icon">
                            <input type="number" id="time" name="tiempo" required min="1" placeholder="Ej. 30" class="form-control">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Descripción Opcional -->
                    <div class="form-group">
                        <label for="description" class="form-label">Descripción (Opcional)</label>
                        <textarea id="description" name="descripcion" rows="2" placeholder="Una breve introducción al plato..." class="form-control"></textarea>
                    </div>

                    <!-- Ingredientes (Selector de Cantidad y Nombre separados) -->
                    <div class="form-group">
                        <label class="form-label">Ingredientes *</label>
                        <div id="contenedor-ingredientes" class="dynamic-ingredients-container">
                            <div class="ingredient-row">
                                <input type="text" name="ing_cantidades[]" class="form-control ing-cantidad" placeholder="Cantidad (ej: 200g, 2 u.)" required>
                                <input type="text" name="ing_nombres[]" class="form-control ing-nombre" placeholder="Ingrediente (ej: Harina, Tomates)" required>
                                <button type="button" class="btn-remove-row" onclick="eliminarFilaIngrediente(this)" title="Eliminar fila">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" id="btn-agregar-ingrediente" class="btn-add-row">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Añadir ingrediente
                        </button>
                    </div>

                    <!-- Pasos a seguir (Dinámicos) -->
                    <div class="form-group">
                        <label class="form-label">Pasos a seguir *</label>
                        <div id="contenedor-pasos" class="dynamic-steps-container">
                            <div class="step-row">
                                <span class="step-badge">1</span>
                                <input type="text" name="pasos[]" class="form-control step-input" placeholder="Ej. Lavar y cortar los ingredientes..." required>
                                <button type="button" class="btn-remove-row" onclick="eliminarFilaPaso(this)" title="Eliminar paso">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" id="btn-agregar-paso" class="btn-add-row">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Añadir paso
                        </button>
                    </div>

                    <!-- Botón Enviar -->
                    <button type="submit" class="btn-primary btn-full">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                        Guardar Receta
                    </button>
                </form>
            </div>
        </div>

        <!-- Contenedor Grid de Recetas -->
        <section>
            <div id="recipeList" class="recipes-grid">
                <?php if (empty($recetas)): ?>
                    <!-- Estado Vacío por defecto -->
                    <div id="emptyState" class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3>Aún no hay recetas</h3>
                        <p>Pulsa en "Agregar receta" para crear tu primer plato.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recetas as $receta): ?>
                        <article class="recipe-card">
                            <div class="card-header">
                                <h3 class="card-title"><?= htmlspecialchars($receta['nombre']) ?></h3>
                                <div class="card-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span><?= htmlspecialchars($receta['tiempo_estimado']) ?> min</span>
                                </div>
                            </div>

                            <?php if (!empty($receta['descripcion'])): ?>
                                <p class="card-desc">"<?= htmlspecialchars($receta['descripcion']) ?>"</p>
                            <?php endif; ?>

                            <div class="card-body">
                                <div>
                                    <h4 class="section-title">Ingredientes</h4>
                                    <ul class="card-ingredients-list">
                                        <?php foreach ($receta['ingredientes'] as $ing): ?>
                                            <li>
                                                <?php if (!empty($ing['cantidad'])): ?>
                                                    <span class="qty-tag"><?= htmlspecialchars($ing['cantidad']) ?></span>
                                                <?php endif; ?>
                                                <span class="ing-tag-name"><?= htmlspecialchars($ing['nombre']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div>
                                    <h4 class="section-title">Pasos</h4>
                                    <ol class="card-steps-list">
                                        <?php foreach ($receta['pasos'] as $p): 
                                            $instruccion = preg_replace('/^\d+[\.\-\)]\s*/', '', $p['instruccion']);
                                        ?>
                                            <li><?= htmlspecialchars($instruccion) ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                            </div>

                            <div class="card-footer">
                                <a href="eliminar_receta.php?id=<?= (int)$receta['id'] ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar esta receta?');" class="btn-delete">Eliminar</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- Script de lógica de la app -->
    <script src="script.js"></script>
</body>
</html>
