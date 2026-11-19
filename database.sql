-- Crear la base de datos (opcional)
CREATE DATABASE IF NOT EXISTS gestor_recetas;
USE gestor_recetas;

-- Tabla principal de recetas
CREATE TABLE IF NOT EXISTS recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    tiempo_estimado INT COMMENT 'Tiempo estimado en minutos',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de ingredientes (Relación 1 a Muchos con recetas)
CREATE TABLE IF NOT EXISTS ingredientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receta_id INT NOT NULL,
    cantidad VARCHAR(50) NOT NULL COMMENT 'Ej: 200g, 2 tazas, 1 unidad',
    nombre VARCHAR(100) NOT NULL COMMENT 'Ej: harina leudante, leche, huevo',
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE
);

-- Tabla de pasos (Relación 1 a Muchos con recetas)
CREATE TABLE IF NOT EXISTS pasos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receta_id INT NOT NULL,
    orden INT NOT NULL COMMENT 'Orden cronológico (1, 2, 3...)',
    instruccion TEXT NOT NULL,
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE
);
