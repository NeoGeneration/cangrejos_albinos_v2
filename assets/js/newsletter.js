/**
 * JavaScript para gestionar el formulario de newsletter
 */
document.addEventListener('DOMContentLoaded', function() {
    // Buscar todos los formularios de newsletter
    const newsletterForms = document.querySelectorAll('form.newsletter-form');
    
    newsletterForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Obtener el campo de email
            const emailInput = this.querySelector('input[type="text"], input[type="email"]');
            if (!emailInput) return;
            
            const email = emailInput.value.trim();
            
            // Validación básica
            if (!email || !isValidEmail(email)) {
                showMessage(form, 'Por favor, introduce un email válido', 'error');
                return;
            }
            
            // Desactivar el botón durante el envío
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.dataset.originalText = submitButton.innerHTML;
                submitButton.innerHTML = 'Enviando...';
            }
            
            // Crear FormData
            const formData = new FormData();
            formData.append('email', email);
            
            // Enviar solicitud AJAX
            fetch('process_newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(form, data.message, 'success');
                    form.reset();
                } else {
                    showMessage(form, data.message || 'Ha ocurrido un error. Inténtalo de nuevo.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage(form, 'Ha ocurrido un error. Inténtalo de nuevo.', 'error');
            })
            .finally(() => {
                // Restaurar el botón
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.dataset.originalText;
                }
            });
        });
    });
    
    // Función para validar email
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Función para mostrar mensajes de respuesta
    function showMessage(form, message, type) {
        // Buscar o crear contenedor de mensajes
        let messageContainer = form.querySelector('.newsletter-message');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.className = 'newsletter-message';
            form.appendChild(messageContainer);
        }
        
        // Añadir mensaje
        messageContainer.innerHTML = message;
        messageContainer.className = 'newsletter-message ' + (type === 'success' ? 'success' : 'error');
        
        // Ocultar mensaje después de un tiempo
        setTimeout(() => {
            messageContainer.style.opacity = '0';
            setTimeout(() => {
                if (type === 'success') {
                    messageContainer.remove();
                } else {
                    messageContainer.style.opacity = '1';
                }
            }, 500);
        }, 5000);
    }
});