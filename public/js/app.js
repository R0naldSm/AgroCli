// public/js/app.js

// CONVERSIONES DE MEDIDAS
const PARADAS_POR_CUADRA = 16;
const PARADAS_POR_HECTAREA = 21;
const METROS_POR_PARADA = 21;

function paradasACuadras(paradas) {
    return paradas / PARADAS_POR_CUADRA;
}

function paradasAHectareas(paradas) {
    return paradas / PARADAS_POR_HECTAREA;
}

function cuadrasAParadas(cuadras) {
    return cuadras * PARADAS_POR_CUADRA;
}

function hectareasAParadas(hectareas) {
    return hectareas * PARADAS_POR_HECTAREA;
}

// Actualizar conversiones en tiempo real
function setupConversionesLote() {
    const inputParadas = document.getElementById('tamanio_paradas');
    const inputCuadras = document.getElementById('tamanio_cuadras');
    const inputHectareas = document.getElementById('tamanio_hectareas');
    
    if (!inputParadas) return;
    
    inputParadas.addEventListener('input', function() {
        const paradas = parseFloat(this.value) || 0;
        inputCuadras.value = paradasACuadras(paradas).toFixed(2);
        inputHectareas.value = paradasAHectareas(paradas).toFixed(2);
    });
    
    inputCuadras.addEventListener('input', function() {
        const cuadras = parseFloat(this.value) || 0;
        const paradas = cuadrasAParadas(cuadras);
        inputParadas.value = paradas.toFixed(2);
        inputHectareas.value = paradasAHectareas(paradas).toFixed(2);
    });
    
    inputHectareas.addEventListener('input', function() {
        const hectareas = parseFloat(this.value) || 0;
        const paradas = hectareasAParadas(hectareas);
        inputParadas.value = paradas.toFixed(2);
        inputCuadras.value = paradasACuadras(paradas).toFixed(2);
    });
}

// CÁLCULO DE FECHAS ESTIMADAS PARA CULTIVOS
function calcularFechaFinEstimada(fechaInicio, tipoCultivo) {
    if (!fechaInicio) return null;
    
    const dias = tipoCultivo === 'siembra' ? 110 : 135;
    const fecha = new Date(fechaInicio);
    fecha.setDate(fecha.getDate() + dias);
    
    return fecha.toISOString().split('T')[0];
}

// Setup de selector de tipo de cultivo
function setupTipoCultivo() {
    const selectTipo = document.getElementById('tipo_cultivo');
    const inputFechaInicio = document.getElementById('fecha_inicio');
    const inputFechaFin = document.getElementById('fecha_fin_estimada');
    const infoDias = document.getElementById('info-dias');
    
    if (!selectTipo || !inputFechaInicio || !inputFechaFin) return;
    
    function actualizarFechaEstimada() {
        const tipo = selectTipo.value;
        const fechaInicio = inputFechaInicio.value;
        
        if (tipo && fechaInicio) {
            const fechaFin = calcularFechaFinEstimada(fechaInicio, tipo);
            inputFechaFin.value = fechaFin;
            
            const dias = tipo === 'siembra' ? '110 días (≈4 meses)' : '135 días (≈4 meses + 2 semanas)';
            if (infoDias) {
                infoDias.textContent = `Duración estimada: ${dias}`;
            }
        }
    }
    
    selectTipo.addEventListener('change', actualizarFechaEstimada);
    inputFechaInicio.addEventListener('change', actualizarFechaEstimada);
}

// BÚSQUEDA DE CLIENTES CON AUTOCOMPLETADO
function setupBusquedaClientes() {
    const inputBusqueda = document.getElementById('buscar-cliente');
    const resultadosDiv = document.getElementById('resultados-clientes');
    const clienteIdInput = document.getElementById('id_cliente');
    
    if (!inputBusqueda) return;
    
    let timeout;
    
    inputBusqueda.addEventListener('input', function() {
        clearTimeout(timeout);
        const termino = this.value.trim();
        
        if (termino.length < 2) {
            resultadosDiv.style.display = 'none';
            return;
        }
        
        timeout = setTimeout(() => {
            buscarClientesAjax(termino, resultadosDiv, clienteIdInput, inputBusqueda);
        }, 300);
    });
    
    // Ocultar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!inputBusqueda.contains(e.target) && !resultadosDiv.contains(e.target)) {
            resultadosDiv.style.display = 'none';
        }
    });
}

function buscarClientesAjax(termino, resultadosDiv, clienteIdInput, inputBusqueda) {
    fetch(`../controllers/ClienteController.php?action=buscar&q=${encodeURIComponent(termino)}`)
        .then(response => response.json())
        .then(data => {
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
                        <small class="text-muted">${cliente.telefono || ''}</small>
                    </a>
                `;
            });
            
            resultadosDiv.innerHTML = html;
            resultadosDiv.style.display = 'block';
            
            // Agregar eventos de clic
            resultadosDiv.querySelectorAll('a').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    clienteIdInput.value = this.dataset.id;
                    inputBusqueda.value = this.dataset.nombre;
                    resultadosDiv.style.display = 'none';
                });
            });
        })
        .catch(error => console.error('Error:', error));
}

// CARGA DE LOTES POR CLIENTE
function setupLotesPorCliente() {
    const selectCliente = document.getElementById('id_cliente_select');
    const selectLote = document.getElementById('id_lote');
    
    if (!selectCliente || !selectLote) return;
    
    selectCliente.addEventListener('change', function() {
        const clienteId = this.value;
        
        if (!clienteId) {
            selectLote.innerHTML = '<option value="">Seleccione un lote</option>';
            selectLote.disabled = true;
            return;
        }
        
        fetch(`../controllers/LoteController.php?action=por_cliente&id_cliente=${clienteId}`)
            .then(response => response.json())
            .then(data => {
                let html = '<option value="">Seleccione un lote</option>';
                
                data.forEach(lote => {
                    html += `<option value="${lote.id}">${lote.nombre} (${lote.tamanio_hectareas} ha)</option>`;
                });
                
                selectLote.innerHTML = html;
                selectLote.disabled = false;
            })
            .catch(error => console.error('Error:', error));
    });
}

// REUTILIZAR APLICACIONES ANTERIORES
function setupReutilizarAplicaciones() {
    const btnReutilizar = document.getElementById('btn-reutilizar');
    const selectEtapaAnterior = document.getElementById('etapa_anterior');
    
    if (!btnReutilizar) return;
    
    btnReutilizar.addEventListener('click', function() {
        const etapaId = selectEtapaAnterior.value;
        
        if (!etapaId) {
            alert('Seleccione una etapa anterior');
            return;
        }
        
        if (confirm('¿Desea cargar los productos aplicados de la etapa seleccionada?')) {
            fetch(`../controllers/AplicacionController.php?action=listar_por_etapa&id_etapa=${etapaId}`)
                .then(response => response.json())
                .then(data => {
                    cargarProductosEnFormulario(data);
                })
                .catch(error => console.error('Error:', error));
        }
    });
}

function cargarProductosEnFormulario(aplicaciones) {
    const tbody = document.getElementById('productos-aplicados');
    tbody.innerHTML = '';
    
    aplicaciones.forEach((app, index) => {
        const fila = crearFilaProducto(app, index);
        tbody.appendChild(fila);
    });
}

function crearFilaProducto(datos, index) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="productos[${index}][id_producto]" class="form-select" required>
                <option value="${datos.id_producto}">${datos.producto_nombre}</option>
            </select>
        </td>
        <td>
            <input type="number" name="productos[${index}][cantidad]" 
                   class="form-control" value="${datos.cantidad}" step="0.01" required>
        </td>
        <td>
            <input type="text" name="productos[${index}][dosis]" 
                   class="form-control" value="${datos.dosis}">
        </td>
        <td>
            <input type="date" name="productos[${index}][fecha]" 
                   class="form-control" value="${datos.fecha_aplicacion}" required>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarFilaProducto(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    return tr;
}

function eliminarFilaProducto(btn) {
    btn.closest('tr').remove();
}

// FORMATEO DE NÚMEROS
function formatearNumero(numero, decimales = 2) {
    return new Intl.NumberFormat('es-EC', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    }).format(numero);
}

// VALIDACIONES GENERALES
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let valido = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            valido = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return valido;
}

// INICIALIZAR AL CARGAR LA PÁGINA
document.addEventListener('DOMContentLoaded', function() {
    setupConversionesLote();
    setupTipoCultivo();
    setupBusquedaClientes();
    setupLotesPorCliente();
    setupReutilizarAplicaciones();
    
    // Ocultar alertas automáticamente después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// EXPORTAR FUNCIONES PARA USO GLOBAL
window.AgriManage = {
    paradasACuadras,
    paradasAHectareas,
    cuadrasAParadas,
    hectareasAParadas,
    calcularFechaFinEstimada,
    formatearNumero,
    validarFormulario,
    eliminarFilaProducto
};