<?php
    use app\controllers\evaluarController;
    $insUsuario = new evaluarController();
?>

<div class="container full-container py-5">
    <div class="card bg-lightinfo dark:bg-darkinfo shadow-none dark:shadow-none position-relative overflow-hidden mb-6">
        <div class="card-body md:py-3 py-5">
            <div class=" items-center grid grid-cols-12 gap-6">
                <div class="col-span-9">
                    <h4 class="font-semibold text-xl text-dark dark:text-white mb-3">Evaluación de Desempeño</h4>
                    <p class="text-sm mt-1">
                      Buscamos destacar cómo tu talento convierte tareas en resultados extraordinarios, creando 
                      valor, inspirando a otros y dejando una huella en nuestra organización. Evaluaremos cómo 
                      tu desempeño fomenta la colaboración, contribuye a los objetivos y demuestra tu capacidad 
                      como agente de cambio al optimizar tiempo y recursos.
                    </p>
                    <?php
                        echo $insUsuario->autoEvaluacion();
                    ?>
                </div>
                <div class="lg:col-span-5 md:col-span-5 sm:col-span-12 col-span-12">
                    <div class="sm:absolute relative right-0 rtl:right-auto rtl:left-0 -bottom-8">
                        <img src="<?php echo APP_URL;?>app/views/img/backgrounds/welcome-bg.svg" alt=""
                            class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="card bg-lightinfo dark:bg-darkinfo shadow-none dark:shadow-none position-relative overflow-hidden mb-6">
        <div class="card-body md:py-3 py-5">
            <div class="items-center grid grid-cols-12 gap-6">
                <div class="col-span-9">
                    <h4 class="font-semibold text-xl text-dark dark:text-white mb-3">Listado de Colaboradores a Evaluar</h4>
                </div>
            </div>
        </div>
      </div>
      <div class="grid grid-cols-12 gap-6">
        <div class=" col-span-12 flex">
          <div class="card">
            <div class="card-body">
              <div class="flex flex-col">
                <div class="-m-1.5 overflow-x-auto">
                  <div class="p-1.5 min-w-full inline-block align-middle">
                    <div class="border overflow-hidden border-light-dark rounded-md">
                    <?php
                        echo $insUsuario->listarColaboradoresEvaluar();
                    ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  </div> 
