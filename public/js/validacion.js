// public/js/validaciones.js

// Validación de cédula ecuatoriana
function validarCedulaEcuatoriana(cedula) {
    // Remover espacios y guiones
    cedula = cedula.replace(/\s/g, '').replace(/-/g, '');
    
    // Debe tener 10 dígitos
    if (cedula.length !== 10) {
        return false;
    }
    
    // Los dos primeros dígitos deben estar entre 01 y 24
    const provincia = parseInt(cedula.substring(0, 2));
    if (provincia < 1 || provincia > 24) {
        return false;
    }
    
    // El tercer dígito debe ser menor a 6
    if (parseInt(cedula[2]) > 5) {
        return false;
    }
    
    // Algoritmo de validación
    const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    let suma = 0;
    
    for (let i = 0; i < 9; i++) {
        let valor = parseInt(cedula[i]) * coeficientes[i];
        if (valor > 9) {
            valor -= 9;
        }
        suma += valor;
    }
    
    const digitoVerificador = (10 - (suma % 10)) % 10;
    
    return digitoVerificador === parseInt(cedula[9]);
}

// Validar formulario genérico
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

// Solo números
function soloNumeros(event) {
    if (!/[0-9]/.test(event.key)) {
        event.preventDefault();
    }
}

// Solo letras
function soloLetras(event) {
    if (!/[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/.test(event.key)) {
        event.preventDefault();
    }
}

// Formatear número
function formatearNumero(numero, decimales = 2) {
    return new Intl.NumberFormat('es-EC', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    }).format(numero);
}

// Validar email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validar teléfono (9 o 10 dígitos)
function validarTelefono(telefono) {
    const regex = /^[0-9]{9,10}$/;
    return regex.test(telefono);
}