// Validación de formularios mientras se completan.
// Se aplica a todo <form data-validar>. Cuando el usuario sale de un campo (blur),
// se revisa: si hay un error se marca en ROJO con el mensaje debajo, y si está bien se marca en VERDE.
// OJO: esto es solo para comodidad del usuario. El servidor (procesos/) vuelve a validar todo.

document.querySelectorAll('form[data-validar]').forEach(function (form) {
    const campos = form.querySelectorAll('input');

    campos.forEach(function (campo) {
        // Al salir del campo: lo marcamos como "tocado" y lo validamos
        campo.addEventListener('blur', function () {
            campo.dataset.tocado = 'si';
            validarCampo(campo);
        });

        // Mientras escribe: si ya lo había tocado, el error se actualiza (y desaparece al corregirlo)
        campo.addEventListener('input', function () {
            // Campos con data-solo-numeros (el DNI): si escribe algo que no sea un número, se borra al instante
            if ('soloNumeros' in campo.dataset) {
                campo.value = campo.value.replace(/[^0-9]/g, '');
            }
            if (campo.dataset.tocado === 'si') {
                validarCampo(campo);
            }
            // Si cambia la contraseña, hay que revisar de nuevo "repetir contraseña"
            const repetir = form.querySelector('[data-igual-a="' + campo.id + '"]');
            if (repetir && repetir.dataset.tocado === 'si') {
                validarCampo(repetir);
            }
        });
    });

    // Al enviar: validamos todo. Si algo está mal, no se envía y vamos al primer error
    form.addEventListener('submit', function (evento) {
        let primerError = null;
        campos.forEach(function (campo) {
            campo.dataset.tocado = 'si';
            if (!validarCampo(campo) && primerError === null) {
                primerError = campo;
            }
        });
        if (primerError !== null) {
            evento.preventDefault();
            primerError.focus();
        }
    });
});

// Revisa un campo, muestra u oculta su error y devuelve true si está bien
function validarCampo(campo) {
    // "Repetir contraseña": tiene que ser igual al campo indicado en data-igual-a
    if (campo.dataset.igualA) {
        const original = document.getElementById(campo.dataset.igualA);
        campo.setCustomValidity(campo.value !== original.value ? 'Las contraseñas no coinciden.' : '');
    }

    const mensaje = mensajeDeError(campo);
    let aviso = campo.parentElement.querySelector('.invalid-feedback');

    // Si todavía no existe el <div> del mensaje, lo creamos justo debajo del input
    if (!aviso) {
        aviso = document.createElement('div');
        aviso.className = 'invalid-feedback';
        campo.insertAdjacentElement('afterend', aviso);
    }

    if (mensaje === '') {
        // Está bien: sacamos el rojo y ponemos el verde (salvo que el campo diga data-sin-verde)
        campo.classList.remove('is-invalid');
        if (!('sinVerde' in campo.dataset)) {
            campo.classList.add('is-valid');
        }
        aviso.textContent = '';
        return true;
    }
    // Está mal: sacamos el verde, ponemos el rojo y escribimos el error debajo
    campo.classList.remove('is-valid');
    campo.classList.add('is-invalid');
    aviso.textContent = mensaje;
    return false;
}

// Traduce lo que detectó el navegador (required, pattern, minlength, etc.) a un mensaje en español
function mensajeDeError(campo) {
    const v = campo.validity;
    if (v.valid) return '';
    if (v.valueMissing) return 'Este campo es obligatorio.';
    if (v.typeMismatch && campo.type === 'email') return 'Ingresá un email válido, por ejemplo nombre@mail.com.';
    if (v.patternMismatch) return campo.dataset.mensaje || 'El formato no es válido.';
    if (v.tooShort) return 'Tiene que tener al menos ' + campo.minLength + ' caracteres.';
    if (v.rangeOverflow) return campo.dataset.mensaje || 'El valor es demasiado alto.';
    if (v.rangeUnderflow) return campo.dataset.mensaje || 'El valor es demasiado bajo.';
    if (v.badInput) return 'El valor no es válido.';
    if (v.customError) return campo.validationMessage;
    return 'Revisá este campo.';
}
