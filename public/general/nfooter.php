</main>
<script src="<?php echo asset('public/js/bootstrap.bundle.min.js'); ?>"></script>
<script>
    // Bootstrap 5 no expone plugins de jQuery: los tooltips se inicializan de forma nativa.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (elemento) {
            new bootstrap.Tooltip(elemento);
        });
        // Popovers (p. ej. «Ventas por producto» en los indicadores del hero).
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (elemento) {
            new bootstrap.Popover(elemento);
        });
    });
</script>
</body>
</html>
