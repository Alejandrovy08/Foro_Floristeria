<?php
    /*
    Plantilla para el cierre visual de la parte inferior de la web que se encarga de lo siguiente:
    1-Mostrar el copyright e identidad 
    2-Ofrecer un enlace directo de retorno al inicio
    3-Aplicar filas y columnas flexibles de bootstrap que permiten la responsividad
    */
    if (!empty($hideSiteChrome)) {
        return;
    }
?>
<footer class="site-footer">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <p class="mb-0 small">Floristería Yerga — Foro y atención al cliente</p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
                <a href="/vista/home.php" class="small">Inicio</a>
            </div>
        </div>
    </div>
</footer>