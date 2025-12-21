<?php
if(isset($_GET['message'])){
    $message = $_GET['message'];

    // Mostrar el mensaje con la notificación
    echo "<script>
        Swal.fire({
            icon: 'success', 
            title: 'Notificación', 
            text: '" . $message . "',
            willClose: () => {
                // Eliminar el parámetro 'message' de la URL
                history.replaceState(null, '', window.location.pathname);
            }
        }).then(() => {
            // Redirigir después de mostrar la notificación
            window.location.href = 'http://10.2.202.204/GestionHumana/login/';
        });
    </script>";
}
?>


<main class="h-screen w-full">
  <div class="h-screen w-full">
    <div class="xl:flex justify-center w-full">
      <div class="xl:w-4/6 w-full">
        <div class="card-body p-5 xl:h-screen" style="background-image: url('<?php echo APP_URL;?>app/views/img/backgrounds/fondo.png'); background-size: cover; background-position: center;">
        </div>
      </div>

      <!-- Formulario de Login y Activación -->
      <div class="xl:w-2/6 w-full">
        <div class="card-body flex justify-center items-center bg-base-100 h-screen">
          <div class="max-w-[400px] sm:px-6 w-full">
            
            <!-- Formulario de Login -->
            <div id="loginForm">
              <h2 class="font-bold text-2xl">Evaluación de Desempeño</h2>
              <p class="mb-7">Gestión Humana y Cultura- Bienvenido</p>
              <form action="" method="POST" autocomplete="OFF">
                <div class="flex flex-col gap-4 mt-7">
                  <div>
                    <label class="text-dark dark:text-darklink font-semibold mb-2 block">Número de Identificación</label>
                    <input type="number" class="form-control py-2" name="identificacion" maxlength="20" pattern="[a-zA-Z0-9]{4,20}" required/>
                  </div>
                  <div>
                    <label class="text-dark dark:text-darklink font-semibold mb-2 block">Contraseña</label>
                    <input type="password" class="form-control py-2" name="password" maxlength="20" required/>
                  </div>
                  <div>
                    <div class="flex justify-between my-2">
                      <div>
                        <label class="cursor-pointer label flex items-center">
                          <input type="checkbox" class="border-bordergray w-4 h-4 rounded-sm text-primary dark:border-darkborder bg-transparent dark:checked:bg-primary dark:checked:border-primary focus:ring-0 focus:ring-offset-0" checked id="checkbox1">
                          <span class="label-text ms-2">Recuerda este dispositivo</span>
                        </label>
                      </div>
                    </div>
                  </div>
                  <button class="btn btn-md py-3" type="submit">Iniciar Sesión</button>
                  <div class="text-center mt-2.5">
                    <span class="text-base font-medium">¿Validaste tu acceso? <a href="#" class="text-primary font-medium text-sm ms-2" onclick="mostrarActivacion()">Activar mi cuenta</a></span>
                  </div>
                </div>
                <!-- Campo oculto para diferenciar el formulario -->
                <input type="hidden" name="formType" value="login">
              </form>
            </div>

            <!-- Formulario de Activación de Cuenta -->
            <div id="activationForm" style="display: none;">
              <h2 class="font-bold text-2xl">Activar Cuenta</h2>
              <p class="mb-7 mt-5">Por favor ingrese tu numero de identificación y contraseña para activar su cuenta.</p>
              <form action="" method="POST" autocomplete="OFF">
                <div class="flex flex-col gap-4 mt-7">
                  <div>
                    <label class="text-dark dark:text-darklink font-semibold mb-2 block">Numero de Identificación</label>
                    <input type="number" class="form-control py-2" name="identificacion" required />
                  </div>
                  <div>
                    <label class="text-dark dark:text-darklink font-semibold mb-2 block">Contraseña</label>
                    <input type="password" class="form-control py-2" name="password" required />
                  </div>
                  <button class="btn btn-md py-3" type="submit">Activar Cuenta</button>
                  <div class="text-center mt-2.5">
                    <span class="text-base font-medium">¿Ya tienes cuenta? <a href="#" class="text-primary font-medium text-sm ms-2" onclick="mostrarLogin()">Volver al Login</a></span>
                  </div>
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
</main>

<!-- Scripts -->
<script>
  // Función para mostrar el formulario de activación
  function mostrarActivacion() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('activationForm').style.display = 'block';
  }

  // Función para mostrar el formulario de login
  function mostrarLogin() {
    document.getElementById('activationForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
  }
</script>

<?php
  if(isset($_POST['formType'])){
    if($_POST['formType'] == 'login' && isset($_POST['identificacion']) && isset($_POST['password'])){
      $loginController->iniciarSesionController();
    } elseif($_POST['formType'] == 'activation' && isset($_POST['identificacion']) && isset($_POST['password'])){
      $loginController->activarCuentaController();
    }
  }
?>