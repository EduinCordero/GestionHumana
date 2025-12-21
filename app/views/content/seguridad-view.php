<div class="container full-container py-5 min-h-screen items-center justify-center">
    <div class="grid grid-cols-12 gap-6 items-center">
        <div class="col-span-12 flex">
            <div class="card">
                <div class="card-body">
                    <div class="flex flex-col">
                        <div class="-m-1.5 overflow-x-auto">
                            <div class="p-1.5 min-w-full inline-block align-middle">
                                <div class="overflow-hidden border-light-dark rounded-md text-center">
									<!-- Formulario de Activación de Cuenta -->
									<div id="activationForm">
										<div class="card bg-lightinfo dark:bg-darkinfo shadow-none dark:shadow-none position-relative overflow-hidden mb-6">
											<div class="card-body md:py-3 py-5">
												<h2 class="font-bold text-2xl">Actualizar credenciales</h2>
											</div>
										</div> 
									  <p class="mb-7 mt-5">Por favor ingrese tu numero de identificación y nueva contraseña.</p>
									  <form action="" method="POST" autocomplete="OFF">
										<div class="flex flex-col gap-4 mt-7">
										  <div>
											<label class="text-dark dark:text-darklink font-semibold mb-2 block">Numero de Identificación</label>
											<input type="number" class="form-control py-2" name="identificacion" value="<?php echo $_SESSION['identificacion'] ?>" required readonly />
										  </div>
										  <div>
											<label class="text-dark dark:text-darklink font-semibold mb-2 block">Nueva Contraseña</label>
											<input type="password" class="form-control py-2" name="password" required />
										  </div>
										  <button class="btn btn-md py-3" type="submit">Guardar datos</button>
										</div>
										<!-- Campo oculto para diferenciar el formulario -->
										<input type="hidden" name="formType" value="activation">
									  </form>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
  if(isset($_POST['formType'])){
    if(isset($_POST['identificacion']) && isset($_POST['password'])){
      $loginController->ActualizarCuentaController();
	  }
  }
?>