// public/js/validacion.js
// Validaciones completas para todos los formularios de AgroCli

// ============================================================
// VALIDACIÓN DE CÉDULA ECUATORIANA
// ============================================================

/**
 * Valida una cédula ecuatoriana usando el algoritmo oficial.
 * Provincias válidas: 01-24. Tercer dígito < 6.
 */
function validarCedulaEcuatoriana(cedula) {
    cedula = cedula.replace(/\s/g, '').replace(/-/g, '');

    if (cedula.length !== 10) return false;
    if (!/^\d{10}$/.test(cedula)) return false;

    const provincia = parseInt(cedula.substring(0, 2));
    if (provincia < 1 || provincia > 24) return false;

    if (parseInt(cedula[2]) > 5) return false;

    const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    let suma = 0;

    for (let i = 0; i < 9; i++) {
        let valor = parseInt(cedula[i]) * coeficientes[i];
        if (valor > 9) valor -= 9;
        suma += valor;
    }

    const digitoVerificador = (10 - (suma % 10)) % 10;
    return digitoVerificador === parseInt(cedula[9]);
}

// ============================================================
// VALIDACIÓN DE TELÉFONO ECUATORIANO
// ============================================================

/**
 * Valida número de teléfono ecuatoriano.
 * Acepta 9 dígitos (celular sin 0) o 10 dígitos (con 0 inicial).
 * Acepta también números fijos de 7-8 dígitos locales.
 */
function validarTelefono(telefono) {
    const limpio = telefono.replace(/\s/g, '').replace(/-/g, '');
    return /^[0-9]{9,10}$/.test(limpio);
}

// ============================================================
// VALIDACIÓN DE EMAIL
// ============================================================

function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
}

// ============================================================
// VALIDACIÓN DE CAMPOS NUMÉRICOS
// ============================================================

/**
 * Valida que un valor sea un número positivo mayor que cero.
 */
function validarNumeroPositivo(valor) {
    const num = parseFloat(valor);
    return !isNaN(num) && num > 0;
}

/**
 * Valida que un valor sea un número dentro de un rango.
 */
function validarRango(valor, min, max) {
    const num = parseFloat(valor);
    return !isNaN(num) && num >= min && num <= max;
}

// ============================================================
// VALIDACIÓN DE FECHAS
// ============================================================

/**
 * Valida que la fecha no sea futura.
 */
function validarFechaNoFutura(fechaStr) {
    if (!fechaStr) return false;
    const fecha = new Date(fechaStr + 'T00:00:00');
    const hoy   = new Date();
    hoy.setHours(0, 0, 0, 0);
    return fecha <= hoy;
}

/**
 * Valida que fechaFin sea posterior o igual a fechaInicio.
 */
function validarRangoFechas(fechaInicioStr, fechaFinStr) {
    if (!fechaInicioStr || !fechaFinStr) return false;
    return new Date(fechaFinStr) >= new Date(fechaInicioStr);
}

// ============================================================
// VALIDACIÓN DE FORMULARIOS GENÉRICA
// ============================================================

/**
 * Valida todos los campos required de un formulario.
 * Aplica clases Bootstrap is-invalid / is-valid.
 * Retorna true si todo es válido.
 */
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const campos = form.querySelectorAll('input[required], select[required], textarea[required]');
    let valido   = true;

    campos.forEach(function(campo) {
        if (!campo.value.trim()) {
            marcarInvalido(campo, 'Este campo es obligatorio.');
            valido = false;
        } else {
            marcarValido(campo);
        }
    });

    return valido;
}

// ============================================================
// VALIDACIÓN FORMULARIO CLIENTE
// ============================================================

/**
 * Agrega validaciones en tiempo real al formulario de clientes.
 * Cubre: cédula, teléfono, email.
 */
function initValidacionCliente() {
    const form = document.getElementById('form-cliente');
    if (!form) return;

    // Cédula — validar al salir del campo
    const inputCedula = document.getElementById('cedula') || form.querySelector('[name="cedula"]');
    if (inputCedula) {
        inputCedula.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                marcarInvalido(this, 'La cédula es obligatoria.');
            } else if (!validarCedulaEcuatoriana(this.value)) {
                marcarInvalido(this, 'Cédula ecuatoriana inválida. Verifique los 10 dígitos.');
            } else {
                marcarValido(this);
            }
        });

        // Solo permite números mientras escribe
        inputCedula.addEventListener('keypress', soloNumeros);
        inputCedula.setAttribute('maxlength', '10');
    }

    // Teléfono
    const inputTelefono = form.querySelector('[name="telefono"]');
    if (inputTelefono) {
        inputTelefono.addEventListener('blur', function() {
            if (this.value.trim() !== '' && !validarTelefono(this.value)) {
                marcarInvalido(this, 'Teléfono inválido. Debe tener 9 o 10 dígitos.');
            } else {
                marcarValido(this);
            }
        });
        inputTelefono.addEventListener('keypress', soloNumeros);
    }

    // Email
    const inputEmail = form.querySelector('[name="email"]');
    if (inputEmail) {
        inputEmail.addEventListener('blur', function() {
            if (this.value.trim() !== '' && !validarEmail(this.value)) {
                marcarInvalido(this, 'Correo electrónico inválido.');
            } else {
                marcarValido(this);
            }
        });
    }

    // Nombres y apellidos — solo letras
    const camposTexto = form.querySelectorAll('[name="nombre"], [name="apellido"]');
    camposTexto.forEach(function(campo) {
        campo.addEventListener('keypress', soloLetras);
        campo.addEventListener('blur', function() {
            if (!this.value.trim()) {
                marcarInvalido(this, 'Este campo es obligatorio.');
            } else {
                marcarValido(this);
            }
        });
    });

    // Submit — validación completa
    form.addEventListener('submit', function(e) {
        let ok = true;

        if (inputCedula && !validarCedulaEcuatoriana(inputCedula.value)) {
            e.preventDefault();
            marcarInvalido(inputCedula, 'Cédula ecuatoriana inválida.');
            ok = false;
        }

        if (inputTelefono && inputTelefono.value.trim() && !validarTelefono(inputTelefono.value)) {
            e.preventDefault();
            marcarInvalido(inputTelefono, 'Teléfono inválido.');
            ok = false;
        }

        if (inputEmail && inputEmail.value.trim() && !validarEmail(inputEmail.value)) {
            e.preventDefault();
            marcarInvalido(inputEmail, 'Correo electrónico inválido.');
            ok = false;
        }

        if (!ok) {
            // Scroll al primer error
            const primerError = form.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return ok;
    });
}

// ============================================================
// VALIDACIÓN FORMULARIO LOTE
// ============================================================

function initValidacionLote() {
    const form = document.getElementById('form-lote');
    if (!form) return;

    // Tamaño en paradas — solo números positivos
    const inputParadas = document.getElementById('tamanio_paradas');
    if (inputParadas) {
        inputParadas.addEventListener('blur', function() {
            if (!validarNumeroPositivo(this.value)) {
                marcarInvalido(this, 'El tamaño debe ser un número mayor a 0.');
            } else {
                marcarValido(this);
            }
        });
    }

    form.addEventListener('submit', function(e) {
        let ok = true;

        const paradas = document.getElementById('tamanio_paradas');
        if (paradas && !validarNumeroPositivo(paradas.value)) {
            e.preventDefault();
            marcarInvalido(paradas, 'El tamaño del lote debe ser mayor a 0.');
            ok = false;
        }

        const cliente = form.querySelector('[name="id_cliente"]');
        if (cliente && !cliente.value) {
            e.preventDefault();
            marcarInvalido(cliente, 'Debe seleccionar un cliente.');
            ok = false;
        }

        const temporada = form.querySelector('[name="temporada"]');
        if (temporada && !temporada.value) {
            e.preventDefault();
            marcarInvalido(temporada, 'Debe seleccionar una temporada.');
            ok = false;
        }

        if (!ok) {
            const primerError = form.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return ok;
    });
}

// ============================================================
// VALIDACIÓN FORMULARIO PRODUCTO
// ============================================================

function initValidacionProducto() {
    const form = document.getElementById('form-producto');
    if (!form) return;

    // Precio unitario
    const inputPrecio = form.querySelector('[name="precio_unitario"]');
    if (inputPrecio) {
        inputPrecio.addEventListener('blur', function() {
            if (!validarNumeroPositivo(this.value)) {
                marcarInvalido(this, 'El precio debe ser mayor a 0.');
            } else {
                marcarValido(this);
            }
        });
    }

    // Stock inicial
    const inputStock = form.querySelector('[name="stock"]');
    if (inputStock) {
        inputStock.addEventListener('blur', function() {
            const val = parseFloat(this.value);
            if (isNaN(val) || val < 0) {
                marcarInvalido(this, 'El stock no puede ser negativo.');
            } else {
                marcarValido(this);
            }
        });
    }

    form.addEventListener('submit', function(e) {
        let ok = validarFormulario('form-producto');

        if (inputPrecio && !validarNumeroPositivo(inputPrecio.value)) {
            e.preventDefault();
            marcarInvalido(inputPrecio, 'El precio debe ser mayor a 0.');
            ok = false;
        }

        if (!ok) e.preventDefault();
        return ok;
    });
}

// ============================================================
// VALIDACIÓN FORMULARIO ETAPA
// ============================================================

function initValidacionEtapa() {
    const form = document.getElementById('form-etapa');
    if (!form) return;

    const inputFechaInicio = form.querySelector('[name="fecha_inicio"]');

    if (inputFechaInicio) {
        inputFechaInicio.addEventListener('blur', function() {
            if (!this.value) {
                marcarInvalido(this, 'La fecha de inicio es obligatoria.');
            } else {
                marcarValido(this);
            }
        });
    }

    form.addEventListener('submit', function(e) {
        let ok = true;

        const selectLote = document.getElementById('id_lote') || form.querySelector('[name="id_lote"]');
        if (selectLote && !selectLote.value) {
            e.preventDefault();
            marcarInvalido(selectLote, 'Debe seleccionar un lote.');
            ok = false;
        }

        const selectTipo = form.querySelector('[name="tipo_cultivo"]');
        if (selectTipo && !selectTipo.value) {
            e.preventDefault();
            marcarInvalido(selectTipo, 'Debe seleccionar el tipo de cultivo.');
            ok = false;
        }

        if (!ok) {
            const primerError = form.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return ok;
    });
}

// ============================================================
// VALIDACIÓN FORMULARIO APLICACIÓN
// ============================================================

function initValidacionAplicacion() {
    const form = document.getElementById('form-aplicacion');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        let ok = true;

        const selectEtapa = document.getElementById('id_etapa_cultivo');
        if (selectEtapa && !selectEtapa.value) {
            e.preventDefault();
            marcarInvalido(selectEtapa, 'Debe seleccionar la etapa de cultivo.');
            ok = false;
        }

        // Verificar que haya al menos un producto
        const filas = document.querySelectorAll('#productos-tbody tr');
        if (filas.length === 0) {
            e.preventDefault();
            mostrarAlertaValidacion('Debe agregar al menos un producto para registrar la aplicación.');
            ok = false;
        }

        // Verificar que ninguna cantidad supere el stock
        let stockOk = true;
        document.querySelectorAll('.select-producto, .input-cantidad').forEach(function(el) {
            if (el.classList.contains('is-invalid')) {
                stockOk = false;
            }
        });

        if (!stockOk) {
            e.preventDefault();
            mostrarAlertaValidacion('Hay productos con cantidad superior al stock disponible.');
            ok = false;
        }

        if (!ok) {
            const primerError = form.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return ok;
    });
}

// ============================================================
// VALIDACIÓN FORMULARIO NOTIFICACIÓN
// ============================================================

function initValidacionNotificacion() {
    const form = document.getElementById('form-notificacion');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        let ok = true;

        const inputAsunto = form.querySelector('[name="asunto"]');
        if (inputAsunto && !inputAsunto.value.trim()) {
            e.preventDefault();
            marcarInvalido(inputAsunto, 'El asunto es obligatorio.');
            ok = false;
        }

        const textaMensaje = form.querySelector('[name="mensaje"]');
        if (textaMensaje && !textaMensaje.value.trim()) {
            e.preventDefault();
            marcarInvalido(textaMensaje, 'El mensaje es obligatorio.');
            ok = false;
        }

        // Si es notificación individual, verificar cliente
        const radioIndividual = form.querySelector('input[name="tipo"][value="individual"]');
        if (radioIndividual && radioIndividual.checked) {
            const selectCliente = form.querySelector('[name="id_cliente"]');
            if (selectCliente && !selectCliente.value) {
                e.preventDefault();
                marcarInvalido(selectCliente, 'Debe seleccionar un cliente para notificación individual.');
                ok = false;
            }
        }

        if (!ok) {
            const primerError = form.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return ok;
    });
}

// ============================================================
// FILTROS DE TECLADO — Solo números / Solo letras
// ============================================================

function soloNumeros(event) {
    if (!/[0-9]/.test(event.key)
        && event.key !== 'Backspace'
        && event.key !== 'Tab'
        && event.key !== 'Delete'
        && event.key !== 'ArrowLeft'
        && event.key !== 'ArrowRight') {
        event.preventDefault();
    }
}

function soloLetras(event) {
    if (!/[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/.test(event.key)
        && event.key !== 'Backspace'
        && event.key !== 'Tab'
        && event.key !== 'Delete') {
        event.preventDefault();
    }
}

// ============================================================
// HELPERS INTERNOS — Marcar válido / inválido
// ============================================================

function marcarInvalido(campo, mensaje) {
    campo.classList.remove('is-valid');
    campo.classList.add('is-invalid');

    let feedback = campo.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        campo.parentNode.appendChild(feedback);
    }
    feedback.textContent = mensaje || 'Campo inválido.';
}

function marcarValido(campo) {
    campo.classList.remove('is-invalid');
    campo.classList.add('is-valid');

    const feedback = campo.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = '';
    }
}

function mostrarAlertaValidacion(mensaje) {
    let alerta = document.getElementById('alerta-validacion-global');

    if (!alerta) {
        alerta = document.createElement('div');
        alerta.id        = 'alerta-validacion-global';
        alerta.className = 'alert alert-danger alert-dismissible fade show mt-2';

        const contenedor = document.querySelector('.card-body') || document.querySelector('.container') || document.body;
        contenedor.prepend(alerta);
    }

    alerta.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> ' + mensaje
        + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';

    alerta.scrollIntoView({ behavior: 'smooth', block: 'center' });

    setTimeout(function() {
        alerta.classList.remove('show');
        setTimeout(function() { alerta.remove(); }, 300);
    }, 6000);
}

// ============================================================
// FORMATO VISUAL
// ============================================================

/**
 * Formatea un número al estilo es-EC (puntos de miles, coma decimal).
 */
function formatearNumero(numero, decimales) {
    if (decimales === undefined) decimales = 2;
    return new Intl.NumberFormat('es-EC', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    }).format(numero);
}

// ============================================================
// INICIALIZACIÓN GLOBAL
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initValidacionCliente();
    initValidacionLote();
    initValidacionProducto();
    initValidacionEtapa();
    initValidacionAplicacion();
    initValidacionNotificacion();

    // Auto-ocultar alertas flash de PHP ($_SESSION['success/error'])
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            alert.classList.remove('show');
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });

    // Limpiar estado is-valid/is-invalid al resetear formularios
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('reset', function() {
            form.querySelectorAll('.is-valid, .is-invalid').forEach(function(el) {
                el.classList.remove('is-valid', 'is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(function(fb) {
                fb.textContent = '';
            });
        });
    });
});

// ============================================================
// API PÚBLICA
// ============================================================
window.ValidacionModule = {
    validarCedulaEcuatoriana,
    validarTelefono,
    validarEmail,
    validarNumeroPositivo,
    validarRango,
    validarFechaNoFutura,
    validarRangoFechas,
    validarFormulario,
    marcarInvalido,
    marcarValido,
    soloNumeros,
    soloLetras,
    formatearNumero
};