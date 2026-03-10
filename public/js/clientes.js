// public/js/clientes.js

/**
 * Funcionalidades específicas para el módulo de Clientes
 */

// Búsqueda en tiempo real de clientes
function inicializarBusquedaClientes() {
    const inputBusqueda = document.getElementById('busqueda');
    
    if (!inputBusqueda) return;
    
    inputBusqueda.addEventListener('input', function(e) {
        const termino = e.target.value.toLowerCase();
        const filas = document.querySelectorAll('#tabla-clientes tbody tr');
        
        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            fila.style.display = texto.includes(termino) ? '' : 'none';
        });
        
        // Mostrar mensaje si no hay resultados
        const filasVisibles = Array.from(filas).filter(f => f.style.display !== 'none');
        actualizarMensajeResultados(filasVisibles.length);
    });
}

// Actualizar contador de resultados
function actualizarMensajeResultados(cantidad) {
    let mensaje = document.getElementById('mensaje-resultados');
    
    if (!mensaje) {
        mensaje = document.createElement('div');
        mensaje.id = 'mensaje-resultados';
        mensaje.className = 'alert alert-info mt-2';
        const tabla = document.querySelector('#tabla-clientes');
        if (tabla) {
            tabla.parentNode.insertBefore(mensaje, tabla.nextSibling);
        }
    }
    
    if (cantidad === 0) {
        mensaje.textContent = 'No se encontraron clientes con ese criterio de búsqueda';
        mensaje.style.display = 'block';
    } else {
        mensaje.style.display = 'none';
    }
}

// Autocompletar cliente por cédula
function inicializarAutocompletarCliente() {
    const inputCedula = document.getElementById('buscar-cedula');
    const resultadosDiv = document.getElementById('resultados-cedula');
    
    if (!inputCedula) return;
    
    let timeout;
    
    inputCedula.addEventListener('input', function() {
        clearTimeout(timeout);
        const cedula = this.value.trim();
        
        if (cedula.length < 3) {
            if (resultadosDiv) resultadosDiv.style.display = 'none';
            return;
        }
        
        timeout = setTimeout(() => {
            buscarClientePorCedula(cedula, resultadosDiv);
        }, 300);
    });
    
    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!inputCedula.contains(e.target) && resultadosDiv && !resultadosDiv.contains(e.target)) {
            resultadosDiv.style.display = 'none';
        }
    });
}

// Buscar cliente por cédula (AJAX)
function buscarClientePorCedula(cedula, resultadosDiv) {
    fetch(`../controllers/ClienteController.php?action=buscar_cedula&cedula=${encodeURIComponent(cedula)}`)
        .then(response => response.json())
        .then(data => {
            if (!resultadosDiv) return;
            
            if (data.length === 0) {
                resultadosDiv.innerHTML = '<div class="list-group-item">No se encontraron clientes</div>';
                resultadosDiv.style.display = 'block';
                return;
            }
            
            let html = '';
            data.forEach(cliente => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action" 
                       data-id="${cliente.id}"
                       data-nombre="${cliente.nombre} ${cliente.apellido}"
                       data-cedula="${cliente.cedula}">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${cliente.nombre} ${cliente.apellido}</h6>
                            <small>${cliente.cedula}</small>
                        </div>
                        <small class="text-muted">
                            ${cliente.telefono ? '<i class="bi bi-telephone"></i> ' + cliente.telefono : ''}
                        </small>
                    </a>
                `;
            });
            
            resultadosDiv.innerHTML = html;
            resultadosDiv.style.display = 'block';
            
            // Agregar eventos de clic
            resultadosDiv.querySelectorAll('a').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    seleccionarCliente(this.dataset);
                    resultadosDiv.style.display = 'none';
                });
            });
        })
        .catch(error => {
            console.error('Error al buscar cliente:', error);
        });
}

// Seleccionar cliente desde autocompletar
function seleccionarCliente(data) {
    const inputCedula = document.getElementById('buscar-cedula');
    const inputClienteId = document.getElementById('id_cliente_hidden');
    const displayNombre = document.getElementById('cliente-seleccionado');
    
    if (inputCedula) inputCedula.value = data.cedula;
    if (inputClienteId) inputClienteId.value = data.id;
    if (displayNombre) {
        displayNombre.innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                <strong>${data.nombre}</strong> - Cédula: ${data.cedula}
            </div>
        `;
    }
    
    // Disparar evento personalizado para otros scripts
    document.dispatchEvent(new CustomEvent('clienteSeleccionado', { 
        detail: data 
    }));
}

// Exportar clientes a CSV
function exportarClientesCSV() {
    const tabla = document.querySelector('#tabla-clientes');
    if (!tabla) return;
    
    let csv = [];
    const filas = tabla.querySelectorAll('tr');
    
    filas.forEach(fila => {
        const cols = fila.querySelectorAll('td, th');
        const row = Array.from(cols).map(col => {
            let text = col.textContent.trim();
            // Escapar comillas
            text = text.replace(/"/g, '""');
            return `"${text}"`;
        });
        csv.push(row.join(','));
    });
    
    // Crear archivo y descargar
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `clientes_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Validar formulario de cliente
function validarFormularioCliente() {
    const form = document.getElementById('form-cliente');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let valido = true;
        
        // Validar cédula
        const cedula = document.getElementById('cedula');
        if (cedula && !validarCedulaEcuatoriana(cedula.value)) {
            e.preventDefault();
            cedula.classList.add('is-invalid');
            valido = false;
        }
        
        // Validar teléfono si existe
        const telefono = document.querySelector('[name="telefono"]');
        if (telefono && telefono.value && !validarTelefono(telefono.value)) {
            e.preventDefault();
            telefono.classList.add('is-invalid');
            alert('Número de teléfono inválido. Debe tener 9 o 10 dígitos.');
            valido = false;
        }
        
        // Validar email si existe
        const email = document.querySelector('[name="email"]');
        if (email && email.value && !validarEmail(email.value)) {
            e.preventDefault();
            email.classList.add('is-invalid');
            alert('Correo electrónico inválido.');
            valido = false;
        }
        
        return valido;
    });
}

// Mostrar historial del cliente
function cargarHistorialCliente(idCliente) {
    fetch(`../controllers/ClienteController.php?action=historial&id=${idCliente}`)
        .then(response => response.json())
        .then(data => {
            mostrarHistorialEnModal(data);
        })
        .catch(error => {
            console.error('Error al cargar historial:', error);
        });
}

// Mostrar modal con historial
function mostrarHistorialEnModal(data) {
    // Crear modal dinámicamente si no existe
    let modal = document.getElementById('modalHistorial');
    if (!modal) {
        modal = crearModalHistorial();
    }
    
    // Llenar contenido
    const contenido = document.getElementById('historial-contenido');
    if (contenido && data.historial) {
        let html = '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>Fecha</th><th>Lote</th><th>Producción</th></tr></thead><tbody>';
        
        data.historial.forEach(item => {
            html += `<tr>
                <td>${item.fecha}</td>
                <td>${item.lote}</td>
                <td>${item.produccion} qq</td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        contenido.innerHTML = html;
    }
    
    // Mostrar modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Crear modal de historial
function crearModalHistorial() {
    const modalHTML = `
        <div class="modal fade" id="modalHistorial" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Historial del Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="historial-contenido">
                        Cargando...
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    return document.getElementById('modalHistorial');
}

// Filtrar clientes por diversos criterios
function inicializarFiltrosAvanzados() {
    const btnFiltros = document.getElementById('btn-filtros-avanzados');
    const panelFiltros = document.getElementById('panel-filtros');
    
    if (btnFiltros && panelFiltros) {
        btnFiltros.addEventListener('click', function() {
            panelFiltros.style.display = panelFiltros.style.display === 'none' ? 'block' : 'none';
        });
    }
}

// Inicializar todo al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    inicializarBusquedaClientes();
    inicializarAutocompletarCliente();
    validarFormularioCliente();
    inicializarFiltrosAvanzados();
    
    // Botón exportar CSV
    const btnExportar = document.getElementById('btn-exportar-csv');
    if (btnExportar) {
        btnExportar.addEventListener('click', exportarClientesCSV);
    }
});

// Exportar funciones para uso global
window.ClientesModule = {
    buscarClientePorCedula,
    seleccionarCliente,
    exportarClientesCSV,
    cargarHistorialCliente
};