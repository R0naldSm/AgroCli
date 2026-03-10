// public/js/procedimientos.js
// Lógica frontend vinculada a los procedimientos almacenados de AgroCli

// ============================================================
// SP_REGISTRAR_ETAPA — Cálculo de fechas estimadas
// Refleja la lógica del procedimiento: siembra=110 días, trasplante=135 días
// ============================================================

const DIAS_CULTIVO = {
    siembra: 110,
    trasplante: 135
};

/**
 * Calcula la fecha estimada de cosecha según tipo de cultivo.
 * Replica exactamente la lógica de sp_registrar_etapa.
 */
function calcularFechaEstimadaSP(fechaInicio, tipoCultivo) {
    if (!fechaInicio || !tipoCultivo) return null;
    const dias = DIAS_CULTIVO[tipoCultivo] ?? 110;
    const fecha = new Date(fechaInicio + 'T00:00:00');
    fecha.setDate(fecha.getDate() + dias);
    return fecha.toISOString().split('T')[0];
}

/**
 * Inicializa el formulario de creación de etapa.
 * Calcula automáticamente la fecha estimada y muestra info de duración.
 */
function initFormEtapa() {
    const selectTipo    = document.getElementById('tipo_cultivo');
    const inputInicio   = document.getElementById('fecha_inicio');
    const inputEstimada = document.getElementById('fecha_fin_estimada');
    const infoDias      = document.getElementById('info-dias');

    if (!selectTipo || !inputInicio) return;

    function actualizar() {
        const tipo        = selectTipo.value;
        const fechaInicio = inputInicio.value;
        const dias        = DIAS_CULTIVO[tipo];

        if (tipo && fechaInicio && dias) {
            const fechaFin = calcularFechaEstimadaSP(fechaInicio, tipo);
            if (inputEstimada) {
                inputEstimada.value    = fechaFin;
                inputEstimada.readOnly = true;
            }
            if (infoDias) {
                infoDias.innerHTML =
                    `<span class="badge bg-info text-dark">
                        <i class="bi bi-calendar-check"></i>
                        Duración estimada: <strong>${dias} días</strong>
                        &nbsp;|&nbsp; Cosecha aprox.: <strong>${formatearFechaDisplay(fechaFin)}</strong>
                    </span>`;
            }
        } else {
            if (inputEstimada) inputEstimada.value = '';
            if (infoDias) infoDias.innerHTML = '';
        }
    }

    selectTipo.addEventListener('change', actualizar);
    inputInicio.addEventListener('change', actualizar);
    actualizar();
}

// ============================================================
// SP_REGISTRAR_APLICACION — Validación de stock en tiempo real
// Replica la verificación de stock del procedimiento almacenado
// ============================================================

/**
 * Verifica si la cantidad ingresada supera el stock disponible.
 * Muestra advertencia visual antes de que el SP rechace la operación.
 */
function validarStockProducto(selectProducto, inputCantidad, spanStock) {
    const option   = selectProducto.options[selectProducto.selectedIndex];
    const stock    = parseFloat(option && option.dataset.stock ? option.dataset.stock : 0);
    const unidad   = option && option.dataset.unidad ? option.dataset.unidad : '';
    const cantidad = parseFloat(inputCantidad.value) || 0;

    if (!option || !option.value) {
        if (spanStock) spanStock.innerHTML = '<span class="badge bg-secondary">—</span>';
        return true;
    }

    if (spanStock) {
        const cls = stock < 10 ? 'bg-danger' : stock < 30 ? 'bg-warning text-dark' : 'bg-success';
        spanStock.innerHTML = `<span class="badge ${cls}">${stock} ${unidad}</span>`;
    }

    if (cantidad > 0 && cantidad > stock) {
        inputCantidad.classList.add('is-invalid');
        let feedback = inputCantidad.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            inputCantidad.parentNode.appendChild(feedback);
        }
        feedback.textContent = 'Stock insuficiente. Disponible: ' + stock + ' ' + unidad;
        return false;
    } else {
        inputCantidad.classList.remove('is-invalid');
        const feedback = inputCantidad.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
        return true;
    }
}

/**
 * Crea una nueva fila de producto en la tabla de aplicaciones.
 * Compatible con el array productos[] que espera sp_registrar_aplicacion.
 */
function crearFilaProductoAplicacion(productos, index, datosReutilizar) {
    const tr = document.createElement('tr');

    let optionsHtml = '<option value="">Seleccione producto</option>';
    productos.forEach(function(prod) {
        const sel = datosReutilizar && datosReutilizar.id_producto == prod.id ? 'selected' : '';
        optionsHtml += '<option value="' + prod.id + '"'
            + ' data-stock="' + prod.stock + '"'
            + ' data-unidad="' + prod.unidad_medida + '"'
            + ' ' + sel + '>'
            + prod.nombre + ' (' + prod.tipo + ')'
            + '</option>';
    });

    const fechaHoy = new Date().toISOString().split('T')[0];
    const valCant  = datosReutilizar ? datosReutilizar.cantidad        : '';
    const valDosis = datosReutilizar ? datosReutilizar.dosis           : '';
    const valFecha = datosReutilizar ? datosReutilizar.fecha_aplicacion : fechaHoy;

    tr.innerHTML =
        '<td>'
        + '<select name="productos[' + index + '][id_producto]" class="form-select form-select-sm select-producto" required>'
        + optionsHtml
        + '</select>'
        + '</td>'
        + '<td>'
        + '<input type="number" name="productos[' + index + '][cantidad]" class="form-control form-control-sm input-cantidad" value="' + valCant + '" step="0.01" min="0.01" required>'
        + '</td>'
        + '<td>'
        + '<input type="text" name="productos[' + index + '][dosis]" class="form-control form-control-sm" value="' + valDosis + '" placeholder="Ej: 500ml/ha">'
        + '</td>'
        + '<td>'
        + '<input type="date" name="productos[' + index + '][fecha_aplicacion]" class="form-control form-control-sm" value="' + valFecha + '" required>'
        + '</td>'
        + '<td class="text-center span-stock"><span class="badge bg-secondary">—</span></td>'
        + '<td class="text-center">'
        + '<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila" title="Eliminar fila">'
        + '<i class="bi bi-trash"></i>'
        + '</button>'
        + '</td>';

    const selectProd  = tr.querySelector('.select-producto');
    const inputCant   = tr.querySelector('.input-cantidad');
    const spanStockEl = tr.querySelector('.span-stock');

    selectProd.addEventListener('change', function() {
        validarStockProducto(selectProd, inputCant, spanStockEl);
    });

    inputCant.addEventListener('input', function() {
        validarStockProducto(selectProd, inputCant, spanStockEl);
    });

    tr.querySelector('.btn-eliminar-fila').addEventListener('click', function() {
        tr.remove();
        verificarFilasProductos();
    });

    if (datosReutilizar) {
        setTimeout(function() { selectProd.dispatchEvent(new Event('change')); }, 0);
    }

    return tr;
}

/**
 * Verifica que quede al menos una fila de productos.
 */
function verificarFilasProductos() {
    const tbody = document.getElementById('productos-tbody');
    const aviso = document.getElementById('aviso-sin-productos');
    const filas = tbody ? tbody.querySelectorAll('tr') : [];
    if (aviso) {
        aviso.style.display = filas.length === 0 ? 'block' : 'none';
    }
}

// ============================================================
// SP_REGISTRAR_APLICACION — Reutilizar aplicaciones anteriores
// Llama a AplicacionController.php?action=reutilizar
// ============================================================

/**
 * Inicializa el botón de reutilizar aplicaciones de etapa anterior.
 */
function initReutilizarAplicaciones(productos) {
    const btnReutilizar  = document.getElementById('btn-reutilizar');
    const selectEtapaAnt = document.getElementById('etapa_anterior');
    const tbody          = document.getElementById('productos-tbody');

    if (!btnReutilizar || !selectEtapaAnt || !tbody) return;

    let contadorFilas = tbody.querySelectorAll('tr').length;

    btnReutilizar.addEventListener('click', function() {
        const etapaId = selectEtapaAnt.value;

        if (!etapaId) {
            mostrarAlertaInline('Seleccione una etapa anterior para reutilizar.', 'warning');
            return;
        }

        if (!confirm('¿Desea cargar los productos de la etapa seleccionada?\nSe agregarán a los productos actuales.')) {
            return;
        }

        btnReutilizar.disabled   = true;
        btnReutilizar.innerHTML  = '<span class="spinner-border spinner-border-sm"></span> Cargando...';

        fetch('../../controllers/AplicacionController.php?action=reutilizar&id_etapa_origen=' + etapaId)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!Array.isArray(data) || data.length === 0) {
                    mostrarAlertaInline('La etapa seleccionada no tiene aplicaciones registradas.', 'info');
                    return;
                }
                data.forEach(function(app) {
                    const fila = crearFilaProductoAplicacion(productos, contadorFilas++, app);
                    tbody.appendChild(fila);
                });
                mostrarAlertaInline('Se cargaron ' + data.length + ' producto(s) de la etapa anterior.', 'success');
                verificarFilasProductos();
            })
            .catch(function() {
                mostrarAlertaInline('Error al cargar las aplicaciones anteriores.', 'danger');
            })
            .finally(function() {
                btnReutilizar.disabled  = false;
                btnReutilizar.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Reutilizar';
            });
    });
}

// ============================================================
// SP_FINALIZAR_ETAPA — Validación del formulario de finalización
// ============================================================

/**
 * Inicializa las validaciones del formulario de finalizar etapa.
 * Muestra rendimiento estimado en tiempo real.
 */
function initFormFinalizarEtapa() {
    const formFinalizar   = document.getElementById('form-finalizar');
    const inputProduccion = document.getElementById('produccion_quintales');
    const inputFechaFin   = document.getElementById('fecha_fin_real');
    const infoRendimiento = document.getElementById('info-rendimiento');
    const hectareasEl     = document.getElementById('hectareas-lote');
    const hectareas       = hectareasEl ? parseFloat(hectareasEl.dataset.hectareas) : 0;

    if (!formFinalizar) return;

    if (inputProduccion && infoRendimiento && hectareas > 0) {
        inputProduccion.addEventListener('input', function() {
            const produccion  = parseFloat(this.value) || 0;
            const rendimiento = (produccion / hectareas).toFixed(2);
            infoRendimiento.innerHTML = produccion > 0
                ? '<span class="badge bg-info text-dark"><i class="bi bi-graph-up"></i> Rendimiento: <strong>' + rendimiento + ' qq/ha</strong></span>'
                : '';
        });
    }

    if (inputFechaFin) {
        const fechaInicioEl  = document.getElementById('fecha-inicio-etapa');
        const fechaInicioStr = fechaInicioEl ? fechaInicioEl.dataset.fecha : null;

        inputFechaFin.addEventListener('change', function() {
            if (fechaInicioStr && this.value < fechaInicioStr) {
                this.classList.add('is-invalid');
                mostrarFeedback(this, 'La fecha de cosecha no puede ser anterior al inicio del cultivo.');
            } else {
                this.classList.remove('is-invalid');
                quitarFeedback(this);
            }
        });
    }

    formFinalizar.addEventListener('submit', function(e) {
        const produccion = parseFloat(inputProduccion ? inputProduccion.value : 0) || 0;
        if (produccion <= 0) {
            e.preventDefault();
            if (inputProduccion) {
                inputProduccion.classList.add('is-invalid');
                mostrarFeedback(inputProduccion, 'Ingrese la producción obtenida en quintales.');
            }
            return;
        }
        if (!confirm('¿Confirma la finalización de esta etapa?\n\nEsta acción es IRREVERSIBLE.')) {
            e.preventDefault();
        }
    });
}

// ============================================================
// CARGA DINÁMICA — Lotes por cliente / Etapas por lote
// ============================================================

/**
 * Carga los lotes activos de un cliente vía AJAX.
 */
function cargarLotesPorCliente(idCliente, selectLote, callbackDespues) {
    if (!selectLote) return;

    if (!idCliente) {
        selectLote.innerHTML = '<option value="">Primero seleccione un cliente</option>';
        selectLote.disabled  = true;
        if (callbackDespues) callbackDespues([]);
        return;
    }

    selectLote.innerHTML = '<option value="">Cargando lotes...</option>';
    selectLote.disabled  = true;

    fetch('../../controllers/LoteController.php?action=por_cliente&id_cliente=' + idCliente)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!Array.isArray(data) || data.length === 0) {
                selectLote.innerHTML = '<option value="">Sin lotes disponibles</option>';
                return;
            }
            let html = '<option value="">Seleccione un lote</option>';
            data.forEach(function(lote) {
                html += '<option value="' + lote.id + '"'
                    + ' data-hectareas="' + lote.tamanio_hectareas + '"'
                    + ' data-temporada="' + lote.temporada + '">'
                    + lote.nombre + ' (' + lote.tamanio_hectareas + ' ha — ' + lote.temporada + ')'
                    + '</option>';
            });
            selectLote.innerHTML = html;
            selectLote.disabled  = false;
            if (callbackDespues) callbackDespues(data);
        })
        .catch(function() {
            selectLote.innerHTML = '<option value="">Error al cargar lotes</option>';
            mostrarAlertaInline('No se pudieron cargar los lotes del cliente.', 'danger');
        });
}

/**
 * Carga las etapas en proceso de un lote vía AJAX.
 */
function cargarEtapasPorLote(idLote, selectEtapa, soloEnProceso) {
    if (soloEnProceso === undefined) soloEnProceso = true;
    if (!selectEtapa) return;

    if (!idLote) {
        selectEtapa.innerHTML = '<option value="">Primero seleccione un lote</option>';
        selectEtapa.disabled  = true;
        return;
    }

    selectEtapa.innerHTML = '<option value="">Cargando etapas...</option>';
    selectEtapa.disabled  = true;

    fetch('../../controllers/EtapaController.php?action=por_lote&id_lote=' + idLote)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            const filtradas = soloEnProceso
                ? data.filter(function(e) { return e.estado === 'en_proceso'; })
                : data;

            if (!filtradas.length) {
                selectEtapa.innerHTML = '<option value="">Sin etapas activas en este lote</option>';
                mostrarAlertaInline('Este lote no tiene etapas de cultivo en proceso.', 'warning');
                return;
            }

            let html = '<option value="">Seleccione una etapa</option>';
            filtradas.forEach(function(etapa) {
                const label = ucfirst(etapa.tipo_cultivo) + ' — Inicio: ' + formatearFechaDisplay(etapa.fecha_inicio);
                html += '<option value="' + etapa.id + '"'
                    + ' data-tipo="' + etapa.tipo_cultivo + '"'
                    + ' data-inicio="' + etapa.fecha_inicio + '"'
                    + ' data-estimada="' + etapa.fecha_fin_estimada + '">'
                    + label
                    + '</option>';
            });
            selectEtapa.innerHTML = html;
            selectEtapa.disabled  = false;
        })
        .catch(function() {
            selectEtapa.innerHTML = '<option value="">Error al cargar etapas</option>';
            mostrarAlertaInline('No se pudieron cargar las etapas del lote.', 'danger');
        });
}

/**
 * Conecta los selectores en cascada: Cliente → Lote → Etapa
 */
function initCascadaClienteLoteEtapa() {
    const selectCliente = document.getElementById('id_cliente_select');
    const selectLote    = document.getElementById('id_lote');
    const selectEtapa   = document.getElementById('id_etapa_cultivo');

    if (!selectCliente) return;

    selectCliente.addEventListener('change', function() {
        if (selectEtapa) {
            selectEtapa.innerHTML = '<option value="">Primero seleccione un lote</option>';
            selectEtapa.disabled  = true;
        }
        cargarLotesPorCliente(this.value, selectLote, null);
    });

    if (selectLote && selectEtapa) {
        selectLote.addEventListener('change', function() {
            cargarEtapasPorLote(this.value, selectEtapa, true);
        });
    }
}

// ============================================================
// PLANTILLAS DE NOTIFICACIONES
// Llama a NotificacionController.php?action=plantillas
// ============================================================

function initPlantillasNotificaciones() {
    const selectPlantilla = document.getElementById('select-plantilla');
    const inputAsunto     = document.getElementById('asunto');
    const textaMensaje    = document.getElementById('mensaje');

    if (!selectPlantilla) return;

    fetch('../../controllers/NotificacionController.php?action=plantillas')
        .then(function(res) { return res.json(); })
        .then(function(plantillas) {
            let html = '<option value="">— Seleccionar plantilla —</option>';
            Object.keys(plantillas).forEach(function(key) {
                const val = plantillas[key];
                html += '<option value="' + key + '"'
                    + ' data-asunto="' + val.asunto + '"'
                    + ' data-mensaje="' + val.mensaje.replace(/"/g, '&quot;') + '">'
                    + val.asunto
                    + '</option>';
            });
            selectPlantilla.innerHTML = html;
        })
        .catch(function() {
            selectPlantilla.innerHTML = '<option value="">Error al cargar plantillas</option>';
        });

    selectPlantilla.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (!option.value) return;
        if (inputAsunto)  inputAsunto.value  = option.dataset.asunto  || '';
        if (textaMensaje) textaMensaje.value = option.dataset.mensaje || '';
        this.value = '';
    });
}

/**
 * Muestra u oculta el selector de cliente según tipo de notificación.
 */
function initTipoNotificacion() {
    const radios       = document.querySelectorAll('input[name="tipo"]');
    const panelCliente = document.getElementById('panel-cliente-notif');

    if (!radios.length || !panelCliente) return;

    radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            panelCliente.style.display = this.value === 'individual' ? 'block' : 'none';
            const selectCliente = panelCliente.querySelector('select');
            if (selectCliente) {
                selectCliente.required = this.value === 'individual';
            }
        });
    });
}

// ============================================================
// HISTORIAL DE APLICACIONES POR ETAPA (modal Bootstrap)
// ============================================================

function verHistorialAplicaciones(idEtapa) {
    const modal   = document.getElementById('modalHistorialAplicaciones');
    const tbody   = document.getElementById('historial-tbody');
    const spinner = document.getElementById('historial-spinner');

    if (!modal || !tbody) return;

    tbody.innerHTML = '';
    if (spinner) spinner.style.display = 'block';

    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    fetch('../../controllers/AplicacionController.php?action=por_etapa&id_etapa=' + idEtapa)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (spinner) spinner.style.display = 'none';

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin aplicaciones registradas</td></tr>';
                return;
            }

            data.forEach(function(app) {
                tbody.innerHTML +=
                    '<tr>'
                    + '<td>' + formatearFechaDisplay(app.fecha_aplicacion) + '</td>'
                    + '<td>' + (app.producto_nombre || '—') + '</td>'
                    + '<td>' + app.cantidad + ' ' + (app.unidad_medida || '') + '</td>'
                    + '<td>' + (app.dosis || '—') + '</td>'
                    + '<td>' + (app.metodo_aplicacion || '—') + '</td>'
                    + '</tr>';
            });
        })
        .catch(function() {
            if (spinner) spinner.style.display = 'none';
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar historial</td></tr>';
        });
}

// ============================================================
// UTILIDADES INTERNAS
// ============================================================

function formatearFechaDisplay(fechaStr) {
    if (!fechaStr) return '—';
    const partes = fechaStr.split('-');
    return partes[2] + '/' + partes[1] + '/' + partes[0];
}

function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function mostrarAlertaInline(mensaje, tipo) {
    if (!tipo) tipo = 'info';
    let contenedor = document.getElementById('alertas-procedimientos');

    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'alertas-procedimientos';
        const main = document.querySelector('.container') || document.body;
        main.prepend(contenedor);
    }

    const alerta = document.createElement('div');
    alerta.className = 'alert alert-' + tipo + ' alert-dismissible fade show mt-2';
    alerta.innerHTML = mensaje
        + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    contenedor.appendChild(alerta);

    setTimeout(function() {
        alerta.classList.remove('show');
        setTimeout(function() { alerta.remove(); }, 300);
    }, 5000);
}

function mostrarFeedback(input, mensaje) {
    let feedback = input.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        input.parentNode.appendChild(feedback);
    }
    feedback.textContent = mensaje;
}

function quitarFeedback(input) {
    const feedback = input.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = '';
    }
}

// ============================================================
// INICIALIZACIÓN GLOBAL
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initFormEtapa();
    initCascadaClienteLoteEtapa();
    initFormFinalizarEtapa();
    initPlantillasNotificaciones();
    initTipoNotificacion();

    document.querySelectorAll('[data-accion="ver-historial"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const etapaId = this.dataset.etapaId;
            if (etapaId) verHistorialAplicaciones(etapaId);
        });
    });
});

// ============================================================
// API PÚBLICA
// ============================================================
window.ProcedimientosModule = {
    calcularFechaEstimadaSP,
    validarStockProducto,
    crearFilaProductoAplicacion,
    initReutilizarAplicaciones,
    cargarLotesPorCliente,
    cargarEtapasPorLote,
    verHistorialAplicaciones,
    mostrarAlertaInline
};