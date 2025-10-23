// === BOTONES Y EFECTOS DE FORMULARIO ===
document.addEventListener('DOMContentLoaded', () => {
  const btnLogin = document.getElementById("btn__iniciar-sesion");
  const btnRegister = document.getElementById("btn__registrarse");

  const formulario_login = document.querySelector(".formulario__login");
  const formulario_register = document.querySelector(".formulario__register");
  const contenedor_login_register = document.querySelector(".contenedor__login-register");
  const caja_trasera_login = document.querySelector(".caja__trasera-login");
  const caja_trasera_register = document.querySelector(".caja__trasera-register");

  function anchoPage() {
    if (window.innerWidth > 850) {
      caja_trasera_register.style.display = "block";
      caja_trasera_login.style.display = "block";
    } else {
      caja_trasera_register.style.display = "block";
      caja_trasera_register.style.opacity = "1";
      caja_trasera_login.style.display = "none";
      formulario_login.style.display = "block";
      contenedor_login_register.style.left = "0px";
      formulario_register.style.display = "none";
    }
  }

  function iniciarSesion() {
    if (window.innerWidth > 850) {
      formulario_login.style.display = "block";
      contenedor_login_register.style.left = "10px";
      formulario_register.style.display = "none";
      caja_trasera_register.style.opacity = "1";
      caja_trasera_login.style.opacity = "0";
    } else {
      formulario_login.style.display = "block";
      contenedor_login_register.style.left = "0px";
      formulario_register.style.display = "none";
      caja_trasera_register.style.display = "block";
      caja_trasera_login.style.display = "none";
    }
  }

  function register() {
    if (window.innerWidth > 850) {
      formulario_register.style.display = "block";
      contenedor_login_register.style.left = "410px";
      formulario_login.style.display = "none";
      caja_trasera_register.style.opacity = "0";
      caja_trasera_login.style.opacity = "1";
    } else {
      formulario_register.style.display = "block";
      contenedor_login_register.style.left = "0px";
      formulario_login.style.display = "none";
      caja_trasera_register.style.display = "none";
      caja_trasera_login.style.display = "block";
      caja_trasera_login.style.opacity = "1";
    }
  }

  // Listeners UI
  window.addEventListener("resize", anchoPage);
  if (btnLogin) btnLogin.addEventListener("click", iniciarSesion);
  if (btnRegister) btnRegister.addEventListener("click", register);
  anchoPage();

  // === VALIDACIONES PERSONALIZADAS (login y registro) ===
  const forms = [formulario_login, formulario_register].filter(Boolean);

  // Helper: reglas extra para correo
  function correoValidoASCII(valor) {
    const v = (valor || '').trim();
    const asciiOnly = /^[\x00-\x7F]+$/.test(v);                         // solo ASCII
    const reEmail = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,24}$/; // formato ASCII + TLD 2–24
    const domain = v.split('@')[1] || '';
    const noDoubleDot = !/\.\./.test(domain);                            // sin doble punto en dominio
    return asciiOnly && reEmail.test(v) && noDoubleDot;
  }

  // Limpia mensajes al escribir
  forms.forEach(form => {
    form.querySelectorAll('input, select').forEach(inp => {
      inp.addEventListener('input', () => inp.setCustomValidity(''));
    });

    // Mensajes personalizados por campo
    form.addEventListener('invalid', function (e) {
      e.preventDefault();
      const f = e.target;
      let msg = "Por favor ingresa un valor válido.";

      switch (f.name) {
        // Registro
        case 'usuario':
          if (f.validity.valueMissing) msg = "El usuario es obligatorio.";
          else if (f.validity.tooShort) msg = "Mínimo 3 caracteres.";
          else if (f.validity.tooLong) msg = "Máximo 30 caracteres.";
          else if (f.validity.patternMismatch) msg = "Solo letras (incluye acentos) y números, sin espacios ni símbolos.";
          break;

        case 'nombre':
          if (f.validity.valueMissing) msg = "El nombre es obligatorio.";
          else if (f.validity.tooShort) msg = "Debe tener al menos 1 carácter.";
          else if (f.validity.tooLong) msg = "Máximo 50 caracteres.";
          else if (f.validity.patternMismatch) msg = "Solo letras (incluye acentos). Espacios simples entre nombres, sin números ni símbolos.";
          break;

        case 'app':
          if (f.validity.valueMissing) msg = "Este campo es obligatorio.";
          else if (f.validity.tooShort) msg = "Debe tener al menos 1 carácter.";
          else if (f.validity.tooLong) msg = "Máximo 30 caracteres.";
          else if (f.validity.patternMismatch) msg = "Solo letras (incluye acentos), sin espacios ni números.";
          break;
          
        case 'apm':
          if (f.validity.tooShort) msg = "Debe tener al menos 1 carácter.";
          else if (f.validity.tooLong) msg = "Máximo 30 caracteres.";
          else if (f.validity.patternMismatch) msg = "Solo letras (incluye acentos), sin espacios ni números.";
          break;

        // Login y registro
        case 'correo':
          if (f.validity.valueMissing) msg = "El correo es obligatorio.";
          else if (f.validity.tooLong) msg = "El correo es demasiado largo.";
          else if (f.validity.typeMismatch || f.validity.patternMismatch) msg = "Escribe un correo válido (ASCII). Ej: nombre@dominio.com";
          break;

        case 'contrasena':
          if (f.validity.valueMissing) msg = "La contraseña es obligatoria.";
          else if (f.validity.tooShort) msg = "Mínimo 8 caracteres.";
          else if (f.validity.tooLong) msg = "Máximo 25 caracteres.";
          else if (f.validity.patternMismatch) msg = "Debe tener al menos 1 letra y 1 número, sin espacios.";
          break;
      }

      f.setCustomValidity(msg);
      f.reportValidity();
      f.focus();
    }, true);

    // Validación extra al enviar (correo ASCII estricto)
    form.addEventListener('submit', (e) => {
      const email = form.querySelector('input[name="correo"]');
      if (email && !correoValidoASCII(email.value)) {
        e.preventDefault();
        email.setCustomValidity("Correo inválido: usa solo ASCII (sin ñ), TLD de 2–24 letras y sin '..' en el dominio.");
        email.reportValidity();
        email.focus();
        return;
      }
    });
  });
});
