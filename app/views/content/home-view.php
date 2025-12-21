<div class="container full-container py-5 min-h-screen items-center justify-center">
    <div class="card bg-lightinfo dark:bg-darkinfo shadow-none dark:shadow-none position-relative overflow-hidden mb-6 w-full max-w-3xl">
        <div class="card-body text-center py-10">
            <div class="items-center grid gap-6">
                <div class="col-span-9 text-center">
                    <h1 class="mb-7 text-4xl" style="color: #0058af;">Hola! <?php echo $_SESSION['nombres'] ?></h1>
                    <p class="mb-6" style="font-size: 16px;">
                        En la Clínica Zayma creemos que cada acción cuenta y cada colaborador es pieza clave en nuestra misión de servir con excelencia y calidez humana, para trascender en la vida de las personas. Este formulario no es solo una evaluación, es una oportunidad para reflexionar sobre nuestro desempeño, destacar nuestras fortalezas y trazar juntos el camino hacia la excelencia.💡 Tu compromiso nos inspira, tu crecimiento nos fortalece, y tu impacto trasciende.
                        <br><br>
                        Te invitamos a responder con sinceridad y entusiasmo, porque cada aporte nos lleva más cerca de construir el futuro que soñamos como equipo.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-6 items-center">
        <div class="col-span-12 flex">
            <div class="card">
                <div class="card-body">
                    <div class="flex flex-col">
                        <div class="-m-1.5 overflow-x-auto">
                            <div class="p-1.5 min-w-full inline-block align-middle">
                                <div class="overflow-hidden border-light-dark rounded-md text-center">
                                    <button type="button" 
                                        style="background-color: #0058AE; width: 50%; font-size: 18px;" 
                                        role="button" 
                                        onclick="showLoading('<?php echo APP_URL;?>evaluarList/')"
                                        class="btn-md inline-flex justify-center items-center gap-x-2 font-semibold rounded-md border border-transparent text-white hover:bg-primaryemphasis dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                        Iniciar Evaluación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overlay de carga -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-text">Generando formularios y colaboradores a evaluar...</div>
</div>

<script>
    function showLoading(url) {
        document.getElementById('loadingOverlay').classList.add('active');

        // Espera 3 segundos y luego redirige
        setTimeout(function() {
            window.location.href = url;
        }, 3000);
    }

    // Si la página se recarga desde caché (por ejemplo, al presionar "Atrás"), oculta la pantalla de carga
    window.addEventListener("pageshow", function(event) {
        if (event.persisted) {
            document.getElementById('loadingOverlay').classList.remove('active');
        }
    });
</script>
