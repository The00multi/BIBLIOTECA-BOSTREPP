document.querySelectorAll('.alert-auto').forEach(alerta => {
    window.setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 4000);
});
