<script src="<?php echo asset('public/js/bootstrap.bundle.min.js'); ?>"></script>
<script>
    // Bootstrap 5 no expone plugins de jQuery: los tooltips se inicializan de forma nativa.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (elemento) {
            new bootstrap.Tooltip(elemento);
        });
    });
</script>
</body>
</html>
