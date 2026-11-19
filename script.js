// ==========================================================================
// Gestor de Recetas - Lógica JavaScript
// ==========================================================================

// Elementos del DOM
const recipeForm = document.getElementById('recipeForm');
const recipeList = document.getElementById('recipeList');
const emptyState = document.getElementById('emptyState');
const recipeCount = document.getElementById('recipeCount');

const formModal = document.getElementById('formModal');
const openModalBtn = document.getElementById('openModalBtn');
const closeModalBtn = document.getElementById('closeModalBtn');

const contenedorIngredientes = document.getElementById('contenedor-ingredientes');
const btnAgregarIngrediente = document.getElementById('btn-agregar-ingrediente');

const contenedorPasos = document.getElementById('contenedor-pasos');
const btnAgregarPaso = document.getElementById('btn-agregar-paso');

// Estado local de recetas (cargado desde localStorage si existe)
let recipes = [];
try {
    const saved = localStorage.getItem('recetas_app');
    if (saved) {
        recipes = JSON.parse(saved);
    }
} catch (e) {
    recipes = [];
}

// --------------------------------------------------------------------------
// Modal: Apertura y Cierre
// --------------------------------------------------------------------------
function openModal() {
    if (formModal) {
        formModal.classList.add('active');
        const firstInput = document.getElementById('name');
        if (firstInput) firstInput.focus();
    }
}

function closeModal() {
    if (formModal) {
        formModal.classList.remove('active');
    }
}

if (openModalBtn) openModalBtn.addEventListener('click', openModal);
if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);

// Cerrar al hacer clic en el fondo oscuro
if (formModal) {
    formModal.addEventListener('click', (e) => {
        if (e.target === formModal) {
            closeModal();
        }
    });
}

// Cerrar con la tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && formModal && formModal.classList.contains('active')) {
        closeModal();
    }
});

// --------------------------------------------------------------------------
// Manejo Dinámico de Ingredientes (Cantidad y Nombre separados)
// --------------------------------------------------------------------------
function crearFilaIngrediente(cantidad = '', nombre = '') {
    const div = document.createElement('div');
    div.className = 'ingredient-row';
    div.innerHTML = `
        <input type="text" name="ing_cantidades[]" class="form-control ing-cantidad" placeholder="Cantidad (ej: 200g, 2 u.)" value="${escapeHtml(cantidad)}" required>
        <input type="text" name="ing_nombres[]" class="form-control ing-nombre" placeholder="Ingrediente (ej: Harina, Tomates)" value="${escapeHtml(nombre)}" required>
        <button type="button" class="btn-remove-row" onclick="eliminarFilaIngrediente(this)" title="Eliminar fila">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
    `;
    return div;
}

if (btnAgregarIngrediente) {
    btnAgregarIngrediente.addEventListener('click', () => {
        const nuevaFila = crearFilaIngrediente();
        contenedorIngredientes.appendChild(nuevaFila);
        const inputNombre = nuevaFila.querySelector('.ing-nombre');
        const inputCant = nuevaFila.querySelector('.ing-cantidad');
        if (inputCant) inputCant.focus();
    });
}

// Eliminar fila de ingrediente
window.eliminarFilaIngrediente = function(btn) {
    const filas = contenedorIngredientes.querySelectorAll('.ingredient-row');
    if (filas.length > 1) {
        btn.closest('.ingredient-row').remove();
    } else {
        // Si es la única fila, solo limpiamos los inputs en vez de eliminarla
        const fila = filas[0];
        fila.querySelector('.ing-cantidad').value = '';
        fila.querySelector('.ing-nombre').value = '';
    }
};

// --------------------------------------------------------------------------
// Manejo Dinámico de Pasos (Numeración y Ordenamiento Automático)
// --------------------------------------------------------------------------
function actualizarNumerosPasos() {
    if (!contenedorPasos) return;
    const filas = contenedorPasos.querySelectorAll('.step-row');
    filas.forEach((fila, index) => {
        const badge = fila.querySelector('.step-badge');
        if (badge) {
            badge.textContent = index + 1;
        }
    });
}

function crearFilaPaso(texto = '') {
    const div = document.createElement('div');
    div.className = 'step-row';
    div.innerHTML = `
        <span class="step-badge">1</span>
        <input type="text" name="pasos[]" class="form-control step-input" placeholder="Ej. Lavar y cortar los ingredientes..." value="${escapeHtml(texto)}" required>
        <button type="button" class="btn-remove-row" onclick="eliminarFilaPaso(this)" title="Eliminar paso">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
    `;
    return div;
}

if (btnAgregarPaso) {
    btnAgregarPaso.addEventListener('click', () => {
        if (!contenedorPasos) return;
        const nuevaFila = crearFilaPaso();
        contenedorPasos.appendChild(nuevaFila);
        actualizarNumerosPasos();
        const input = nuevaFila.querySelector('.step-input');
        if (input) input.focus();
    });
}

// Eliminar fila de paso
window.eliminarFilaPaso = function(btn) {
    if (!contenedorPasos) return;
    const filas = contenedorPasos.querySelectorAll('.step-row');
    if (filas.length > 1) {
        btn.closest('.step-row').remove();
        actualizarNumerosPasos();
    } else {
        // Si es la única fila, solo limpiamos el input
        const fila = filas[0];
        fila.querySelector('.step-input').value = '';
    }
};

// --------------------------------------------------------------------------
// Utilidad: Escapar HTML para prevenir XSS
// --------------------------------------------------------------------------
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// --------------------------------------------------------------------------
// Renderizado de Tarjetas de Recetas (Modo Frontend / index.html)
// --------------------------------------------------------------------------
function renderRecipes() {
    if (!recipeList) return;

    // Limpiar contenedor
    recipeList.innerHTML = '';

    // Actualizar contador
    if (recipeCount) {
        recipeCount.textContent = `${recipes.length} receta${recipes.length !== 1 ? 's' : ''}`;
    }

    if (recipes.length === 0) {
        if (emptyState) {
            emptyState.classList.remove('hidden');
            recipeList.appendChild(emptyState);
        }
        return;
    }

    // Crear tarjetas
    recipes.forEach((recipe, index) => {
        const card = document.createElement('article');
        card.className = 'recipe-card';

        // Descripción opcional
        const descHtml = recipe.description ? `<p class="card-desc">"${escapeHtml(recipe.description)}"</p>` : '';

        // Renderizar lista de ingredientes
        let ingredientesHtml = '';
        if (Array.isArray(recipe.ingredients)) {
            ingredientesHtml = recipe.ingredients.map(ing => {
                const cant = ing.cantidad ? `<span class="qty-tag">${escapeHtml(ing.cantidad)}</span>` : '';
                return `<li>${cant}<span class="ing-tag-name">${escapeHtml(ing.nombre)}</span></li>`;
            }).join('');
        } else if (typeof recipe.ingredients === 'string') {
            // Compatibilidad con formato antiguo de texto
            ingredientesHtml = recipe.ingredients.split('\n').filter(l => l.trim() !== '').map(linea => {
                return `<li><span class="ing-tag-name">${escapeHtml(linea.replace(/^[-•*]\s*/, ''))}</span></li>`;
            }).join('');
        }

        // Renderizar lista de pasos
        let pasosHtml = '';
        if (Array.isArray(recipe.steps)) {
            pasosHtml = recipe.steps.map(paso => {
                const txt = typeof paso === 'string' ? paso.replace(/^\d+[\.\-\)]\s*/, '') : (paso.instruccion ? paso.instruccion.replace(/^\d+[\.\-\)]\s*/, '') : '');
                return `<li>${escapeHtml(txt)}</li>`;
            }).join('');
        } else if (typeof recipe.steps === 'string') {
            pasosHtml = recipe.steps.split('\n').filter(l => l.trim() !== '').map(linea => {
                return `<li>${escapeHtml(linea.replace(/^\d+[\.\-\)]\s*/, ''))}</li>`;
            }).join('');
        }

        card.innerHTML = `
            <div class="card-header">
                <h3 class="card-title">${escapeHtml(recipe.name)}</h3>
                <div class="card-time">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>${escapeHtml(recipe.time)}${!isNaN(recipe.time) && recipe.time !== '' ? ' min' : ''}</span>
                </div>
            </div>
            
            ${descHtml}
            
            <div class="card-body">
                <div>
                    <h4 class="section-title">Ingredientes</h4>
                    <ul class="card-ingredients-list">
                        ${ingredientesHtml}
                    </ul>
                </div>
                <div>
                    <h4 class="section-title">Pasos</h4>
                    <ol class="card-steps-list">
                        ${pasosHtml}
                    </ol>
                </div>
            </div>
            
            <div class="card-footer">
                <button type="button" onclick="deleteRecipe(${index})" class="btn-delete">Eliminar</button>
            </div>
        `;

        recipeList.appendChild(card);
    });
}

// Eliminar receta (Modo Frontend)
window.deleteRecipe = function(index) {
    if (confirm('¿Estás seguro de que deseas eliminar esta receta?')) {
        recipes.splice(index, 1);
        try {
            localStorage.setItem('recetas_app', JSON.stringify(recipes));
        } catch (e) {}
        renderRecipes();
    }
};

// --------------------------------------------------------------------------
// Manejar Envío del Formulario
// --------------------------------------------------------------------------
if (recipeForm) {
    recipeForm.addEventListener('submit', function(e) {
        // Si el formulario tiene action y se está ejecutando en servidor (PHP), permitir envío POST normal
        const action = recipeForm.getAttribute('action');
        if (action && (window.location.protocol === 'http:' || window.location.protocol === 'https:')) {
            return;
        }

        // Modo Frontend standalone (index.html o local sin servidor PHP)
        e.preventDefault();

        // Extraer ingredientes dinámicos
        const filas = contenedorIngredientes.querySelectorAll('.ingredient-row');
        const ingredientes = [];
        filas.forEach(fila => {
            const cant = fila.querySelector('.ing-cantidad').value.trim();
            const nom = fila.querySelector('.ing-nombre').value.trim();
            if (nom !== '') {
                ingredientes.push({ cantidad: cant, nombre: nom });
            }
        });

        // Extraer pasos dinámicos
        const pasos = [];
        if (contenedorPasos) {
            const filasPasos = contenedorPasos.querySelectorAll('.step-row');
            filasPasos.forEach(fila => {
                const val = fila.querySelector('.step-input').value.trim();
                if (val !== '') {
                    pasos.push(val);
                }
            });
        }

        // Crear objeto de nueva receta
        const newRecipe = {
            name: document.getElementById('name').value.trim(),
            time: document.getElementById('time').value.trim(),
            description: document.getElementById('description') ? document.getElementById('description').value.trim() : '',
            ingredients: ingredientes,
            steps: pasos
        };

        // Agregar al inicio
        recipes.unshift(newRecipe);

        // Guardar en localStorage
        try {
            localStorage.setItem('recetas_app', JSON.stringify(recipes));
        } catch (e) {}

        // Renderizar y reiniciar formulario
        renderRecipes();
        recipeForm.reset();

        // Restaurar a una sola fila de ingredientes vacía
        contenedorIngredientes.innerHTML = '';
        contenedorIngredientes.appendChild(crearFilaIngrediente());

        // Restaurar a una sola fila de pasos vacía
        if (contenedorPasos) {
            contenedorPasos.innerHTML = '';
            contenedorPasos.appendChild(crearFilaPaso());
            actualizarNumerosPasos();
        }

        closeModal();
    });
}

// --------------------------------------------------------------------------
// Inicialización
// --------------------------------------------------------------------------
// Si estamos en index.html o no hay tarjetas renderizadas por PHP en el DOM
document.addEventListener('DOMContentLoaded', () => {
    // Si el contenedor recipeList no tiene tarjetas generadas por PHP
    if (recipeList && !recipeList.querySelector('.recipe-card')) {
        renderRecipes();
    }
});
