<header class="fixed top-0 left-0 right-0 inset-x-0 z-50 flex flex-wrap md:justify-start md:flex-nowrap text-sm  bg-white dark:bg-dark shadow-md">
<div class="with-vertical w-full">
  <div class="w-full mx-auto px-4 lg:py-1 py-3 lg:px-4" aria-label="Global">
    <div class="relative md:flex md:items-center md:justify-between">
      <div class="hs-collapse  grow md:block">
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2">
            <div class="flex lg:hidden w-10 md:w-full overflow-hidden">
              <div class="brand-logo flex  items-center ">
                <a href="#" class="text-nowrap logo-img">
                  <img src="#" class="dark:hidden block rtl:hidden !w-fit" alt="Logo-zayma"/>
                </a>
              </div>
            </div>
            <div class="relative">
              <a class="xl:flex hidden text-xl icon-hover cursor-pointer text-link dark:text-darklink sidebartoggler h-10 w-10 hover:text-primary light-dark-hoverbg  justify-center items-center rounded-full" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2 relative z-[1] "></i>
              </a>
              <!--Mobile Sidebar Toggle -->
              <div class="sticky top-0 inset-x-0 xl:hidden">
                <div class="flex items-center">
                  <!-- Navigation Toggle -->
                  <a class="text-xl icon-hover cursor-pointer text-link dark:text-darklink sidebartoggler h-10 w-10 hover:text-primary light-dark-hoverbg flex justify-center items-center rounded-full"
                    data-hs-overlay="#application-sidebar-brand" aria-controls="application-sidebar-brand" aria-label="Toggle navigation">
                    <i class="ti ti-menu-2 relative z-[1] "></i>
                  </a>
                  <!-- End Navigation Toggle -->
                </div>
              </div>
              <!-- End Sidebar Toggle -->
            </div>
            <div class="lg:hidden">
              <button type="button" class="p-2 inline-flex h-10 w-10 text-link dark:text-darklink hover:text-primary light-dark-hoverbg  justify-center items-center rounded-full"
                data-hs-overlay="#navbar-offcanvas-example-menu" aria-controls="navbar-offcanvas-example-menu" aria-label="Toggle navigation">
                <i class="ti ti-apps text-xl"></i>
              </button>
            </div>
            <div>
              <a href="<?php echo APP_URL; ?>home/"
                class="header-link-btn dark:hover:text-primary">
                <i class="ti ti-mail lg:hidden lg:text-sm text-xl"></i>Inicio</a>
            </div>
            <div>
              <a href="#"
                class="header-link-btn dark:hover:text-primary">
                <i class="ti ti-calendar lg:hidden lg:text-sm text-xl"></i>Calendario</a>
            </div>
            <!--
            <div>
              <a href="#"
                class="header-link-btn dark:hover:text-primary">
                <i class="ti ti-calendar lg:hidden lg:text-sm text-xl"></i>Informes</a>
            </div>
            -->
            <div>
              <a href="#"
                class="header-link-btn dark:hover:text-primary">
                <i class="ti ti-calendar lg:hidden lg:text-sm text-xl"></i>Feedback-Prueba</a>
            </div>
			<div>
              <a href="<?php echo APP_URL; ?>seguridad/"
                class="header-link-btn dark:hover:text-primary">
                <i class="ti ti-calendar lg:hidden lg:text-sm text-xl"></i>Seguridad</a>
            </div>
			<div>
              <a href="<?php echo APP_URL; ?>reportes/"
                class="header-link-btn dark:hover:text-primary">
                <i class="ti ti-chart-bar lg:hidden lg:text-sm text-xl"></i>Reportes</a>
            </div>
          </div>

          <div class="icon-nav items-center gap-3 lg:gap-4 flex">
            <!-- Theme Toggle  -->
            <button type="button" class="hs-dark-mode-active:hidden icon-hover block hs-dark-mode group items-center font-medium hover:text-primary text-link dark:text-darklink h-10 w-10 light-dark-hoverbg  justify-center rounded-full" data-hs-theme-click-value="dark" id="dark-layout">
              <i class="ti ti-moon text-xl  text-link dark:text-darklink relative  hover:text-primary"></i>
            </button>

            <button type="button" class="hs-dark-mode-active:block icon-hover hidden hs-dark-mode group  items-center  font-medium hover:text-primary text-link dark:text-darklink h-10 w-10 light-dark-hoverbg  justify-center rounded-full" data-hs-theme-click-value="light" id="light-layout">
              <i class="ti ti-sun text-xl  text-link dark:text-darklink relative  hover:text-primary"></i>
            </button>

            <!-- Notifications DD -->
            <div class="hs-dropdown [--strategy:absolute] [--adaptive:none] sm:[--trigger:hover] sm:relative group/menu">
                <a id="hs-dropdown-hover-event-notification" class="relative hs-dropdown-toggle h-10 w-10 text-link dark:text-darklink cursor-pointer hover:bg-lightprimary  hover:text-primary dark:hover:bg-darkprimary flex justify-center items-center rounded-full group-hover/menu:bg-lightprimary group-hover/menu:text-primary">
                    <i class="ti ti-bell-ringing text-xl relative z-[1] blinking-icon"></i>
                    <div class="absolute inline-flex items-center justify-center  text-white text-[11px] font-medium  bg-primary p-[5px] rounded-full -top-[-5px] -right-[0px]">
                    </div>
                </a>
                <div class="card hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 right-0 rtl:right-auto rtl:left-0 mt-2 min-w-max top-auto w-full sm:w-[360px] hidden z-[2]" aria-labelledby="hs-dropdown-hover-event-notification">
                    <div class="flex items-center py-4 px-7 justify-between">
                        <h3 class="mb-0 card-title">Criterios de evalución</h3>
                    </div>
                    <div class="message-body max-h-[350px]" data-simplebar="">
                        <a href="javascript:void(0)" class="px-7 py-3 flex items-center light-dark-hoverbg">
                            <div class="ps-4">
                                <h5 class="text-sm">
                                  Sobresaliente
                                </h5>
                                <span>El colaborador supera las expectativas, demuestra un compromiso excepcional con este principio y sus valores, y actúa de manera proactiva, inspirando a otros con su actitud y resultados.</span>
                            </div>
                        </a>
                        <a href="javascript:void(0)" class="px-7 py-3 flex items-center light-dark-hoverbg">
                            <div class="ps-4">
                                <h5 class="text-sm">
                                  Acorde
                                </h5>
                                <span>El colaborador cumple consistentemente con las expectativas, refleja un alineamiento sólido con el principio y sus valores, y ocasionalmente toma la iniciativa para mejorar y aportar soluciones innovadoras.</span>
                            </div>
                        </a>
                        <a href="javascript:void(0)" class="px-7 py-3 flex items-center light-dark-hoverbg">
                            <div class="ps-4">
                                <h5 class="text-sm">
                                  Aceptable
                                </h5>
                                <span>El colaborador cumple con las expectativas básicas. Aunque actúa acorde al principio y sus valores, hay áreas donde podría ser más proactivo o comprometido.</span>
                            </div>
                        </a>
                        <a href="javascript:void(0)" class="px-7 py-3 flex items-center light-dark-hoverbg">
                            <div class="ps-4">
                                <h5 class="text-sm">
                                  Necesita Mejorar
                                </h5>
                                <span>El colaborador presenta algunas deficiencias en la vivencia del principio y sus valores. Si bien cumple con algunas expectativas, necesita mejorar en áreas clave para alinearse con la cultura organizacional.</span>
                            </div>
                        </a>
                        <a href="javascript:void(0)" class="px-7 py-3 flex items-center light-dark-hoverbg">
                            <div class="ps-4">
                                <h5 class="text-sm">
                                  Insuficiente
                                </h5>
                                <span>El colaborador no cumple con las expectativas. Se observan acciones que no reflejan adecuadamente el principio y sus valores, y es necesario un esfuerzo significativo para mejorar su desempeño.</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div>
              <?php echo htmlspecialchars(mb_convert_encoding($_SESSION['nombres'], 'UTF-8', 'ISO-8859-1')) ." ".   htmlspecialchars(mb_convert_encoding($_SESSION['apellidos'], 'UTF-8', 'ISO-8859-1')) ?>
            </div>

            <!-- Profile DD -->
            <div class="hs-dropdown [--strategy:absolute] [--adaptive:none] [--placement:top-left] sm:[--trigger:hover] sm:relative group/menu">
              <a id="hs-dropdown-hover-event-profile" class="relative hs-dropdown-toggle cursor-pointer align-middle rounded-full group-hover/menu:bg-lightprimary group-hover/menu:text-primary">
                <?php if($_SESSION['sexo']=='MASCULINO'){ ?>
                    <img class="object-cover w-9 h-9 rounded-full" src="<?php echo APP_URL; ?>app/views/img/profile/user-1.jpg" alt="" aria-hidden="true">
                <?php } else { ?>
                      <img class="object-cover w-9 h-9 rounded-full" src="<?php echo APP_URL; ?>app/views/img/profile/user-1.jpg" alt="" aria-hidden="true">
                <?php } ?>
              </a>
              <div class="card hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 mt-2 min-w-max top-auto right-0 rtl:right-auto rtl:left-0 w-full sm:w-[360px] hidden z-[2]" aria-labelledby="hs-dropdown-hover-event-profile">
                  <div class="card-body">
                      <div class="flex items-center pb-4 justify-between">
                          <h3 class="mb-0 card-title">Perfil de usuario</h3>
                      </div>
                      <div class="message-body max-h-[450px]" data-simplebar="">
                          <div class="">
                              <div class="flex items-center">
                                  <img src="<?php echo APP_URL; ?>app/views/img/profile/user-1.jpg" class="h-20 w-20 rounded-full object-cover" alt="profile">
                                  <div class="ml-4 rtl:mr-4 rtl:ml-auto">
                                      <h5 class="text-base">
                                      <?php echo htmlspecialchars(mb_convert_encoding($_SESSION['nombres'], 'UTF-8', 'ISO-8859-1')) ." ".   htmlspecialchars(mb_convert_encoding($_SESSION['apellidos'], 'UTF-8', 'ISO-8859-1')) ?>
                                      </h5>
                                      <span class="text-sm font-normal flex items-center text-link dark:text-darklink">
                                          <span><?php echo $_SESSION['identificacion'] ?></span>
                                      </span>
                                  </div>
                              </div>
                              <ul class="mt-10">
                                  <li class="mb-5">
                                      <a href="#" class="flex gap-3 items-center group">
                                          <span class="bg-lightgray dark:bg-darkgray h-12 w-12 flex justify-center items-center rounded-md">
                                              <img src="<?php echo APP_URL; ?>app/views/img/svgs/icon-account.svg" class="h-6 w-6">
                                          </span>
                                          <div class="">
                                              <h6 class="text-sm mb-1  group-hover:text-primary">
                                                  Mi perfil
                                              </h6>
                                              <p class="text-xs text-link dark:text-darklink font-normal">Configuraciones de la cuenta</p>
                                          </div>
                                      </a>
                                  </li>
                              </ul>
                          </div>
                      </div>
                      <div class="mt-5">
                          <a href="<?php echo APP_URL;?>logout/" class="btn btn-outline-primary block w-full">
                              Cerrar sesión
                          </a>
                      </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>
</header>