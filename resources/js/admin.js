// Confirmar antes de eliminar (para formularios con class="form-eliminar")
document.querySelectorAll('.form-eliminar').forEach(form => {
    form.addEventListener('submit', e => {
        if (!confirm('¿Seguro que quieres eliminar este registro?')) {
            e.preventDefault();
        }
    });
});

// Alertas con class="alert-auto" se cierran solas a los 4 segundos
document.querySelectorAll('.alert-auto').forEach(alerta => {
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 4000);
});
