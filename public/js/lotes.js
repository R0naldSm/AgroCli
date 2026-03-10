// public/js/lotes.js

/**
 * Funcionalidades específicas para el módulo de Lotes
 */

// Constantes de conversión
const PARADAS_POR_CUADRA = 16;
const PARADAS_POR_HECTAREA = 21;
const METROS_POR_PARADA = 21;

// Conversiones de medidas
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

// Inicializar conversiones automáticas
function inicializarConversionesMedidas() {
    const inputParadas = document.getElementById('tamanio_paradas');
    const inputCuadras = document.getElementById('tamanio_cuadras');
    const inputHectareas = document.getElementById('tamanio_hectareas');
    
    if (!inputParadas) return;
    
    // Convertir desde paradas
    inputParadas.addEventListener('input', function() {
        const paradas = parseFloat(this.value) || 0;
        
        if (inputCuadras) {
            inputCuadras.value = paradasACuadras(paradas).toFixed(2);
        }
        
        if (inputHectareas) {
            inputHectareas.value = paradasAHectareas(paradas).toFixed(2);
        }
        
        actualizarVisualizacionMedidas(paradas);
    });
    
    // Convertir desde cuadras
    if (inputCuadras) {
        inputCuadras.addEventListener('input', function() {
            const cuadras = parseFloat(this.value) || 0;
            const paradas = cuadrasAParadas(cuadras);
            
            inputParadas.value = paradas.toFixed(2);
            
            if (inputHectareas) {
                inputHectareas.value = paradasAHectareas(paradas).toFixed(2);
            }
            
            actualizarVisualizacionMedidas(paradas);
        });
    }
    
    // Convertir desde hectáreas
    if (inputHectareas) {
        inputHectareas.addEventListener('input', function() {
            const hectareas = parseFloat(this.value) || 0;
            const paradas = hectareasAParadas(hectareas);
            
            inputParadas.value = paradas.toFixed(2);
            
            if (inputCuadras) {
                inputCuadras.value = paradasACuadras(paradas).toFixed(2);
            }
            
            actualizarVisualizacionMedidas(paradas);
        });
    }
}

// Actualizar visualización de medidas
function actualizarVisualizacionMedidas(paradas) {
    const visualizacion = document.getElementById('visualizacion-medidas');
    
    if (!visualizacion) return;
    
    const metros = paradas * METROS_POR_PARADA;
    const cuadras = paradasACuadras(paradas);
    const hectareas = paradasAHectareas(paradas);
    
    visualizacion.innerHTML = `
        <div class="alert alert-info">
            <h6><i class="bi bi-rulers"></i> Resumen de Medidas:</h6>
            <div class="row">
                <div class="col-md-3">
                    <strong>${paradas.toFixed(2)}</strong><br>
                    <small>Paradas</small>
                </div>
                <div class="col-md-3">
                    <strong>${cuadras.toFixed(2)}</strong><br>
                    <small>Cuadras</small>
                </div>
                <div class="col-md-3">
                    <strong>${hectareas.toFixed(2)}</strong><br>
                    <small>Hectáreas</small>
                </div>
                <div class="col-md-3">
                    <strong>${metros.toFixed(0)}</strong><br>
                    <small>Metros</small>
                </div>
            </div>
        </div>
    `;
}

// Cargar lotes por cliente
function cargarLotesPorCliente(idCliente, selectDestino) {
    if (!idCliente) {
        if (selectDestino) {
            selectDestino.innerHTML = '<option value="">Seleccione un lote</option>';
            selectDestino.disabled = true;
        }
        return;
    }
    
    fetch(`../controllers/LoteController.php?action=por_cliente&id_cliente=${idCliente}`)
        .then(response => response.json())
        .then(data => {
            if (!selectDestino) return;
            
            let html = '<option value="">Seleccione un lote</option>';
            
            data.forEach(lote => {
                html += `<option value="${lote.id}" 
                         data-hectareas="${lote.tamanio_hectareas}"
                         data-temporada="${lote.temporada}">
                    ${lote.nombre} (${lote.tamanio_hectareas} ha - ${lote.temporada})
                </option>`;
            });
            
            selectDestino.innerHTML = html;
            selectDestino.disabled = false;
            
            // Disparar evento personalizado
            selectDestino.dispatchEvent(new Event('lotesActualizados'));
        })
        .catch(error => {
            console.error('Error al cargar lotes:', error);
            if (selectDestino) {
                selectDestino.innerHTML = '<option value="">Error al cargar lotes</option>';
            }
        });
}

// Inicializar selector de cliente para cargar lotes
function inicializarSelectorClienteLotes() {
    const selectCliente = document.getElementById('id_cliente_select');
    const selectLote = document.getElementById('id_lote');
    
    if (!selectCliente || !selectLote) return;
    
    selectCliente.addEventListener('change', function() {
        cargarLotesPorCliente(this.value, selectLote);
    });
}

// Validar formulario de lote
function validarFormularioLote() {
    const form = document.getElementById('form-lote');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let valido = true;
        
        // Validar que tenga al menos una medida
        const paradas = parseFloat(document.getElementById('tamanio_paradas')?.value) || 0;
        
        if (paradas <= 0) {
            e.preventDefault();
            alert('El tamaño del lote debe ser mayor a 0');
            valido = false;
        }
        
        // Validar cliente seleccionado
        const cliente = document.querySelector('[name="id_cliente"]')?.value;
        if (!cliente) {
            e.preventDefault();
            alert('Debe seleccionar un cliente');
            valido = false;
        }
        
        // Validar temporada seleccionada
        const temporada = document.querySelector('[name="temporada"]')?.value;
        if (!temporada) {
            e.preventDefault();
            alert('Debe seleccionar una temporada');
            valido = false;
        }
        
        return valido;
    });
}

// Calcular área aproximada del lote
function calcularAreaAproximada() {
    const paradas = parseFloat(document.getElementById('tamanio_paradas')?.value) || 0;
    
    if (paradas <= 0) return;
    
    const metrosCuadrados = (paradas * METROS_POR_PARADA) * (paradas * METROS_POR_PARADA);
    const hectareas = paradasAHectareas(paradas);
    
    return {
        metrosCuadrados: metrosCuadrados,
        hectareas: hectareas,
        paradas: paradas
    };
}

// Mostrar información de temporada
function mostrarInfoTemporada() {
    const selectTemporada = document.querySelector('[name="temporada"]');
    const infoDiv = document.getElementById('info-temporada');
    
    if (!selectTemporada || !infoDiv) return;
    
    selectTemporada.addEventListener('change', function() {
        const temporada = this.value;
        
        if (temporada === 'invierno') {
            infoDiv.innerHTML = `
                <div class="alert alert-primary">
                    <i class="bi bi-cloud-rain"></i>
                    <strong>Temporada de Invierno:</strong>
                    Ideal para cultivos que requieren mayor humedad.
                    Mayor disponibilidad de agua natural.
                </div>
            `;
        } else if (temporada === 'verano') {
            infoDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-sun"></i>
                    <strong>Temporada de Verano:</strong>
                    Requiere mayor riego artificial.
                    Mayor intensidad solar y temperaturas elevadas.
                </div>
            `;
        } else {
            infoDiv.innerHTML = '';
        }
    });
}

// Exportar lotes a CSV
function exportarLotesCSV() {
    const tabla = document.querySelector('#tabla-lotes');
    if (!tabla) return;
    
    let csv = [];
    const filas = tabla.querySelectorAll('tr');
    
    filas.forEach(fila => {
        const cols = fila.querySelectorAll('td, th');
        const row = Array.from(cols).map(col => {
            let text = col.textContent.trim();
            text = text.replace(/"/g, '""');
            return `"${text}"`;
        });
        csv.push(row.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `lotes_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Filtrar lotes por temporada
function filtrarPorTemporada(temporada) {
    const filas = document.querySelectorAll('#tabla-lotes tbody tr');
    
    filas.forEach(fila => {
        if (!temporada || temporada === 'todas') {
            fila.style.display = '';
        } else {
            const temporadaLote = fila.dataset.temporada;
            fila.style.display = temporadaLote === temporada ? '' : 'none';
        }
    });
}

// Inicializar filtros de lotes
function inicializarFiltrosLotes() {
    const filtroTemporada = document.getElementById('filtro-temporada');
    
    if (filtroTemporada) {
        filtroTemporada.addEventListener('change', function() {
            filtrarPorTemporada(this.value);
        });
    }
}

// Mostrar mapa del lote (si tiene coordenadas)
function mostrarMapaLote(latitud, longitud) {
    // Placeholder para integración futura con Google Maps o Leaflet
    console.log(`Mostrar mapa en: ${latitud}, ${longitud}`);
}

// Calcular estadísticas del lote
function calcularEstadisticasLote(idLote) {
    fetch(`../controllers/LoteController.php?action=estadisticas&id=${idLote}`)
        .then(response => response.json())
        .then(data => {
            mostrarEstadisticasEnPanel(data);
        })
        .catch(error => {
            console.error('Error al calcular estadísticas:', error);
        });
}

// Mostrar estadísticas en panel
function mostrarEstadisticasEnPanel(data) {
    const panel = document.getElementById('panel-estadisticas');
    if (!panel) return;
    
    panel.innerHTML = `
        <div class="card">
            <div class="card-body">
                <h6>Estadísticas del Lote</h6>
                <p>Total Etapas: <strong>${data.total_etapas}</strong></p>
                <p>Producción Total: <strong>${data.produccion_total} qq</strong></p>
                <p>Rendimiento Promedio: <strong>${data.rendimiento_promedio} qq/ha</strong></p>
            </div>
        </div>
    `;
}

// Inicializar todo al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    inicializarConversionesMedidas();
    inicializarSelectorClienteLotes();
    validarFormularioLote();
    mostrarInfoTemporada();
    inicializarFiltrosLotes();
    
    // Botón exportar CSV
    const btnExportar = document.getElementById('btn-exportar-lotes-csv');
    if (btnExportar) {
        btnExportar.addEventListener('click', exportarLotesCSV);
    }
});

// Exportar funciones para uso global
window.LotesModule = {
    paradasACuadras,
    paradasAHectareas,
    cuadrasAParadas,
    hectareasAParadas,
    cargarLotesPorCliente,
    calcularAreaAproximada,
    exportarLotesCSV,
    mostrarMapaLote,
    calcularEstadisticasLote
};