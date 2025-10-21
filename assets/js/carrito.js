// Función para actualizar el contador del carrito en el header
function actualizarContadorCarrito() {
    $.ajax({
        url: './ajax/actualizar_carrito.php',
        type: 'POST',
        data: {
            action: 'obtener_carrito'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.resumen) {
                const totalItems = response.resumen.total_items;
                const badge = $('#carrito-badge');
                
                if (totalItems > 0) {
                    badge.text(totalItems).show();
                } else {
                    badge.hide();
                }
            }
        },
        error: function() {
            console.log('Error al obtener información del carrito');
        }
    });
}

// Función para mostrar alertas toast
function mostrarToast(mensaje, tipo = 'success') {
    // Crear toast
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${tipo} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${tipo === 'success' ? 'check' : 'exclamation-triangle'} me-2"></i>
                    ${mensaje}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Crear contenedor si no existe
    if (!$('#toast-container').length) {
        $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
    }
    
    // Agregar toast
    const toast = $(toastHtml);
    $('#toast-container').append(toast);
    
    // Mostrar toast
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();
    
    // Remover toast después de que se oculte
    toast[0].addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

// Función mejorada para mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    mostrarToast(mensaje, tipo);
}

// Función para actualizar contador del carrito después de agregar productos
function actualizarContadorCarrito(count) {
    const badge = $('#carrito-badge');
    if (count > 0) {
        badge.text(count).show();
        badge.addClass('animate__animated animate__pulse');
        setTimeout(() => {
            badge.removeClass('animate__animated animate__pulse');
        }, 1000);
    } else {
        badge.hide();
    }
}

// Inicializar contador al cargar la página
$(document).ready(function() {
    actualizarContadorCarrito();
});
