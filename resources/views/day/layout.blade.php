<!DOCTYPE html>

<html lang="en">
	<!--begin::Head-->
	<head>
		<base href="{{ url('/') }}"/>
		<title>Future Tec Server</title>
		<meta charset="utf-8" />
	 
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />
		<!--begin::Fonts(mandatory for all pages)-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--end::Fonts-->
		<!-- CSS do Bootstrap -->
		<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
		<!--begin::Vendor Stylesheets(used for this page only)-->
		<link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
		<!--end::Vendor Stylesheets-->
		<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
		<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
		<script>
			// Frame-busting to prevent site from being loaded within a frame without permission (click-jacking) 
			if (window.top != window.self) { window.top.location.replace(window.self.location.href); }
		</script>
		<!-- Bootstrap JS -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/js/bootstrap.bundle.min.js"></script>
		<!-- JS do Bootstrap -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
		<meta name="csrf-token" content="{{ csrf_token() }}">
	</head>
	<!--end::Head-->
</html>

	<!--Script para autenticar a rota-->
	@if (!Auth::check())
    <script>
        window.location.href = "{{ route('login') }}";
    </script>
	<!--Script para autenticar a rota-->

	@endif

	<link rel="stylesheet" href="{{ asset('css/pause-style.css') }}">


	<!--begin::Body-->
	<body id="kt_app_body" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" data-kt-app-aside-enabled="true" data-kt-app-aside-fixed="true" data-kt-app-aside-push-toolbar="true" data-kt-app-aside-push-footer="true" class="app-default">
		<!--begin::Theme mode setup on page load-->
		<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
		<!--end::Theme mode setup on page load-->
		<!--begin::App-->
		
			<!--begin::Page-->
					
					<!--Grupo de bloqueio-->
					@php
    				$cargosBloqueados = ['Operador', 'Supervisor']; // Lista de cargos que não devem ver o menu
					@endphp
					<!--Grupo de bloqueio-->


				<!--begin::Header-->
				<div id="kt_app_header" class="app-header d-flex flex-column flex-stack">
					<!--begin::Header main-->
					<div class="d-flex align-items-center flex-stack flex-grow-1">
						<div class="app-header-logo d-flex align-items-center flex-stack px-lg-11 mb-2" id="kt_app_header_logo">
							<!--begin::Sidebar mobile toggle-->
							<div class="btn btn-icon btn-active-color-primary w-35px h-35px ms-3 me-2 d-flex d-lg-none" id="kt_app_sidebar_mobile_toggle">
								<i class="ki-duotone ki-abstract-14 fs-2">
									<span class="path1"></span>
									<span class="path2"></span>
								</i>
							</div>
							<!--end::Sidebar mobile toggle-->
							<!--begin::Logo-->
							<a href="{{ route('home') }}" class="app-sidebar-logo">
								<img alt="Logo" src="assets/media/logos/default.svg" class="h-100px theme-light-show" />
								<img alt="Logo" src="assets/media/logos/default-dark.svg" class="h-100px theme-dark-show" />
							</a>
							<!--end::Logo-->
							<!--begin::Sidebar toggle-->
							<div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-sm btn-icon btn-color-warning me-n2 d-none d-lg-flex" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="app-sidebar-minimize">
								<i class="ki-duotone ki-exit-left fs-2x rotate-180">
									<span class="path1"></span>
									<span class="path2"></span>
								</i>
							</div>
							<!--end::Sidebar toggle-->
						</div>

						
						<!--begin::Navbar-->
						<div class="app-navbar flex-grow-1 justify-content-end" id="kt_app_header_navbar">
						<div class="app-navbar-item d-flex align-items-stretch me-2 me-lg-0">
								<!--begin::Search-->
								<div id="kt_header_search" class="header-search d-flex align-items-center w-lg-350px" data-kt-search-keypress="true" data-kt-search-min-length="2" data-kt-search-enter="enter" data-kt-search-layout="menu" data-kt-search-responsive="true" data-kt-menu-trigger="auto" data-kt-menu-permanent="true" data-kt-menu-placement="bottom-start">
									<!--begin::Tablet and mobile search toggle-->
									 
									<!--end::Tablet and mobile search toggle-->
									
										
										<!--begin::Spinner-->
										<span class="search-spinner position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-5" data-kt-search-element="spinner">
											<span class="spinner-border h-15px w-15px align-middle text-gray-400"></span>
										</span>
										<!--end::Spinner-->
										<!--begin::Reset-->
										<span class="search-reset btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4" data-kt-search-element="clear">
											<i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
										<!--end::Reset-->
									</form>
									<!--end::Form-->
									
							</div>
							
							<!--Script para atualizar sessão-->
							
							<!--
							@if(session('success'))
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								{{ session('success') }}
								<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							-->
							@endif
							<!--Script para atualizar sessão-->
						
							<!-- Botão para selecionar a pausa -->
							<div class="d-flex justify-content-center justify-content-lg-end">
								<button id="pauseToggleButton" class="btn btn-primary">Selecionar Pausa</button>
							</div>
									</div>
									</div>
							<!-- Botão para selecionar a pausa -->

							<!-- Modal de Pausas -->
							<div id="pauseModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="pauseModalLabel" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
									<h5 class="modal-title" id="pauseModalLabel">Escolher Pausa</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
									<ul id="pauseList" class="list-group">
										<!-- Lista de pausas carregada via JavaScript -->
									</ul>
									<div id="errorMessage" class="text-danger mt-2" style="display: none;">Erro ao carregar pausas!</div>
									</div>
									<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
									<button type="button" class="btn btn-primary" id="confirmPauseButton" disabled>Confirmar</button>
									</div>
								</div>
								</div>
							</div>
							<!-- Início contador do tempo de pausa-->
							<div id="statusContainer" class="mt-3">
								<div id="onlineTimer" style="font-size: 12px; font-weight: bold; color: #4caf50; padding: 4px 8px; border: 1px solid #ddd; border-radius: 5px; background-color: #e8f5e9; margin-bottom: 4px; max-width: 170px; text-align: center;">
									Tempo Disponível: 00:00:00
								</div>
								<div id="pauseTimer" style="display: none; font-size: 12px; font-weight: bold; color: #ff9800; padding: 4px 8px; border: 1px solid #ddd; border-radius: 5px; background-color: #fff3e0; margin-bottom: 4px; max-width: 170px; text-align: center;">
									Tempo na Pausa: 00:00:00
								</div>
								<div id="currentStatus" style="font-size: 12px; font-weight: bold; color: #ffffff; padding: 4px 8px; border: 1px solid #ddd; border-radius: 5px; background-color: #4caf50; max-width: 170px; text-align: center;">
									Status: Disponível
								</div>
							</div>
							<!-- Fim contador do tempo de pausa-->
							<div class="app-navbar-item ms-3 ms-lg-4 me-lg-2" id="kt_header_user_menu_toggle">
								<!--begin::Menu wrapper-->
								<!--begin::Menu wrapper-->
								<div class="cursor-pointer symbol symbol-30px symbol-lg-40px" 
									data-kt-menu-trigger="{default: 'click', lg: 'hover'}" 
									data-kt-menu-attach="parent" 
									data-kt-menu-placement="bottom-end">
									@if(Auth::check() && Auth::user()->avatar)
										<img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" />
									@else
										<img src="/path/to/default-avatar.png" alt="Usuário" />
									@endif
								</div>
								<!--begin::User account menu-->
								<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
									<!--begin::Menu item-->
									<div class="menu-item px-3">
										<div class="menu-content d-flex align-items-center px-3">
											<!--begin::Avatar-->
											<div class="menu-item px-3">
											<div class="menu-content d-flex align-items-center px-3">
												<!-- Verificar se o usuário está autenticado -->
												@if (Auth::check())
													<!-- Avatar -->
													<div class="symbol symbol-50px me-5">
													<img alt="{{ Auth::user()->name }}" src="{{ Storage::url('avatars/' . Auth::user()->avatar) }}" />

													</div>
													<!-- Username -->
													<div class="d-flex flex-column">
														<div class="fw-bold d-flex align-items-center fs-5">
															{{ Auth::user()->name }}
														</div>
														<a href="#" class="fw-semibold text-muted text-hover-primary fs-7">
															{{ Auth::user()->email }}
														</a>
													</div>
												@else
													<!-- Caso o usuário não esteja autenticado -->
													<div class="d-flex flex-column">
														<div class="fw-bold d-flex align-items-center fs-5">
															Usuário não autenticado
														</div>
														<a href="{{ route('login') }}" class="fw-semibold text-muted text-hover-primary fs-7">
															Fazer login
														</a>
													</div>
												@endif
											</div>
										</div>

										</div>
									</div>
									<!--end::Menu item-->
									<!--begin::Menu separator-->
									<div class="separator my-2"></div>
									<!--end::Menu separator-->
									<!--begin::Menu item-->
									<div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
										<a href="#" class="menu-link px-5">
											<span class="menu-title position-relative">Modo
											<span class="ms-5 position-absolute translate-middle-y top-50 end-0">
												<i class="ki-duotone ki-night-day theme-light-show fs-2">
													<span class="path1"></span>
													<span class="path2"></span>
													<span class="path3"></span>
													<span class="path4"></span>
													<span class="path5"></span>
													<span class="path6"></span>
													<span class="path7"></span>
													<span class="path8"></span>
													<span class="path9"></span>
													<span class="path10"></span>
												</i>
												<i class="ki-duotone ki-moon theme-dark-show fs-2">
													<span class="path1"></span>
													<span class="path2"></span>
												</i>
											</span></span>
										</a>
										<!--begin::Menu-->
										<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
											<!--begin::Menu item-->
											<div class="menu-item px-3 my-0">
												<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-night-day fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
															<span class="path3"></span>
															<span class="path4"></span>
															<span class="path5"></span>
															<span class="path6"></span>
															<span class="path7"></span>
															<span class="path8"></span>
															<span class="path9"></span>
															<span class="path10"></span>
														</i>
													</span>
													<span class="menu-title">Claro</span>
												</a>
											</div>
											<!--end::Menu item-->
											<!--begin::Menu item-->
											<div class="menu-item px-3 my-0">
												<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-moon fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
													</span>
													<span class="menu-title">Escuro</span>
												</a>
											</div>
											<!--end::Menu item-->
											<!--begin::Menu item-->
											<div class="menu-item px-3 my-0">
												<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-screen fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
															<span class="path3"></span>
															<span class="path4"></span>
														</i>
													</span>
													<span class="menu-title">Sistema</span>
												</a>
											</div>
											<!--end::Menu item-->
										</div>
										<!--end::Menu-->
									</div>
									<!--end::Menu item-->
									
									<!--begin::opção de pausa-->
									<div class="d-flex align-items-center">
									

									<form id="pauseToggleForm" action="{{ route('users.togglePause') }}" method="POST" style="display: none;">
										@csrf
									</form>
								</div>

									<!--begin::opção de pausa-->
									<!--begin::Menu item-->
									<div class="menu-item px-5">
										<form method="POST" action="{{ route('logout') }}" style="display: inline;">
											@csrf
											<button type="submit" class="menu-link px-5" style="background: none; border: none; padding: 0; color: inherit; font: inherit; cursor: pointer;">
												Log Out
											</button>
										</form>
									</div>
									<!--end::Menu item-->
								</div>
								<!--end::User account menu-->
								<!--end::Menu wrapper-->
							</div>
							<!--end::User menu-->
							<!--begin::Action-->
							<div class="app-navbar-item ms-3 ms-lg-4 me-lg-6">
							
							</div>
							<!--end::Action-->
							<!--begin::Header menu toggle-->
							<div class="app-navbar-item ms-3 ms-lg-4 ms-n2 me-3 d-flex d-lg-none">
								<div class="btn btn-icon btn-custom btn-color-gray-600 btn-active-color-primary w-35px h-35px w-md-40px h-md-40px" id="kt_app_aside_mobile_toggle">
									<i class="ki-duotone ki-burger-menu-2 fs-2">
										<span class="path1"></span>
										<span class="path2"></span>
										<span class="path3"></span>
										<span class="path4"></span>
										<span class="path5"></span>
										<span class="path6"></span>
										<span class="path7"></span>
										<span class="path8"></span>
										<span class="path9"></span>
										<span class="path10"></span>
									</i>
								</div>
							</div>
							<!--end::Header menu toggle-->
						</div>
						<!--end::Navbar-->
					</div>
					<!--end::Header main-->
					<!--begin::Separator-->
					<div class="app-header-separator"></div>
					<!--end::Separator-->
				</div>
				<!--end::Header-->
				<!--begin::Wrapper-->
				<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
					<!--begin::Sidebar-->
					<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
						<!--begin::Main-->
						<div class="d-flex flex-column justify-content-between h-100 hover-scroll-overlay-y my-2 d-flex flex-column" id="kt_app_sidebar_main" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_main" data-kt-scroll-offset="5px">
							<!--begin::Sidebar menu-->
							<div id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false" class="flex-column-fluid menu menu-sub-indention menu-column menu-rounded menu-active-bg mb-7">
								<!--begin:Menu item-->
								<div data-kt-menu-trigger="click" class="menu-item 
								{{ request()->routeIs('dashboard.index', 'monitor.index', 'painel-atendimento') 
					 			? 'here show' : '' }} menu-accordion">

									<!--begin:Menu link-->
									<span class="menu-link">
										<span class="menu-icon">
											<i class="ki-duotone ki-element-11 fs-1">
												<span class="path1"></span>
												<span class="path2"></span>
												<span class="path3"></span>
												<span class="path4"></span>
											</i>
										</span>
										<span class="menu-title">Dashboards</span>
										<span class="menu-arrow"></span>
									</span>
									<!--end:Menu link-->
									<!--begin:Menu sub-->
									<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu item-->
										<div class="menu-item">
											<!--begin:Menu link-->
											<a class="menu-link" href="{{ route('dashboard.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Dashboard</span>
											</a>
											<!--end:Menu link-->
										</div>
										<!--end:Menu item-->
										<!--begin:Menu item-->
										<div class="menu-item">
											<!--begin:Menu link-->
											<a class="menu-link" href="{{ route('monitor.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Monitoramento</span>
											</a>
											<!--end:Menu link-->
										</div>
										<!--end:Menu item-->

										<!--begin:Menu item Painel de atendimento-->
										<div class="menu-item">
											<!--begin:Menu link-->
											<a class="menu-link" href="{{ route('painel-atendimento') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Painel de Atendimento</span>
											</a>
											<!--end:Menu link-->
										</div>
										<!--end:Menu item Painel de atendimento-->
									

									
										</div>
									<!--end:Menu sub Painel de atendimento-->
								</div>
								<!--end:Menu item-->



								<!-- Início do Menu Telefonia -->
								<div data-kt-menu-trigger="click" class="menu-item 
								{{ request()->routeIs('filas.index', 'troncos.index', 'ramais.index' , 'rotas.index') 
					 			? 'here show' : '' }} menu-accordion">
									<!-- Título do Menu -->
									<span class="menu-link">
										<span class="menu-icon">
											<!-- Ícone do Menu -->
											<i class="ki-duotone ki-phone fs-1">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
										<span class="menu-title">Telefonia</span>
										<span class="menu-arrow"></span>
									</span>

									<!-- Opções do Menu -->
									<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('ramais.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Ramais</span>
											</a>
										</div>


										<!-- inicio submenu troncos -->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('troncos.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Troncos</span>
											</a>
										</div>
										
										<!-- inicio submenu Filas -->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('filas.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Filas</span>
											</a>
										</div>
										<!-- inicio submenu Filas -->

										<!-- inicio submenu Rotas -->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('rotas.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Rotas</span>
											</a>
										</div>
										<!-- inicio submenu Rotas -->
										<!-- fim submenu troncos -->
									</div>
								</div>
								<!-- Fim do Menu Telefonia -->



								<!-- Início do Menu Campanhas -->
								<div data-kt-menu-trigger="click" class="menu-item 
								{{ request()->routeIs('campaign.index', 'campaign.channels', 'report.index') 
					 			? 'here show' : '' }} menu-accordion">
									<!-- Título do Menu -->
									<span class="menu-link">
										<span class="menu-icon">
											<!-- Ícone do Menu -->
											<i class="ki-duotone ki-graph-4">
											<span class="path1"></span>
											<span class="path2"></span>
											</i>
										</span>
										<span class="menu-title">Campanhas</span>
										<span class="menu-arrow"></span>
									</span>

									<!-- Opções do Menu -->
									<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('campaign.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Campanhas</span>
											</a>
										</div>
									</div>

									<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('campaign.channels') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Monitor</span>
											</a>
										</div>
									</div>

									
									<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('report.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Relatório</span>
											</a>
										</div>
									</div>

									

								</div>
								<!-- Fim do Menu Campanhas -->		
							

											
								<!-- Início do Menu Tickets -->
								<div data-kt-menu-trigger="click" class="menu-item 
								{{ request()->routeIs('glpi.showCreateTicketForm' , 'glpi.tickets') 
					 			? 'here show' : '' }} menu-accordion">
									<!-- Título do Menu -->
									<span class="menu-link">
										<span class="menu-icon">
											<!-- Ícone do Menu -->
											<i class="ki-duotone ki-some-files fs-1">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
										<span class="menu-title">Tickets</span>
										<span class="menu-arrow"></span>
									</span>


									<!-- Opções do Menu -->
									<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('glpi.showCreateTicketForm') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Criar</span>
											</a>
										</div>
										</div>

										<!-- Opções do Menu -->
										<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('glpi.tickets') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Tickets</span>
											</a>
										</div>
										</div>


										</div>

								<!-- Início do Menu Relatórios -->
								<div data-kt-menu-trigger="click" class="menu-item 
								{{ request()->routeIs('login-report.index', 'relatorios.ligacoes', 'relatorio.pausas', 'relatorios.index') 
					 			? 'here show' : '' }} menu-accordion">
									<!-- Título do Menu -->
									<span class="menu-link">
										<span class="menu-icon">
											<!-- Ícone do Menu -->
											<i class="ki-duotone ki-some-files fs-1">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
										<span class="menu-title">Relatórios</span>
										<span class="menu-arrow"></span>
									</span>

									<!-- Opções do Menu -->
									<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('login-report.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Login/Logout</span>
											</a>
										</div>

									
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('relatorio.pausas') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Pausas</span>
											</a>
										 </div>
										


										<!-- inicio submenu Ligação -->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('relatorios.ligacoes') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Ligações</span>
											</a>
										</div>

										<!-- inicio submenu Atividade -->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('relatorios.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Atividade</span>
											</a>
										</div>

									</div>
								</div>
								<!-- Fim do Menu Relatórios -->

										
										
								<!-- Início do Menu Administração -->
								@if(Auth::check() && !in_array(Auth::user()->cargo, $cargosBloqueados))
								<div data-kt-menu-trigger="click" class="menu-item 
								{{ request()->routeIs('users.index', 'Pausas.inicio','empresas.create') 
					 			? 'here show' : '' }} menu-accordion">
									<!-- Título do Menu -->
									<span class="menu-link">
										<span class="menu-icon">
											<!-- Ícone do Menu -->
											<i class="ki-duotone ki-gear">
											<span class="path1"></span>
											<span class="path2"></span>
											</i>
										</span>
										<span class="menu-title">Administração</span>
										<span class="menu-arrow"></span>
									</span>

									<!-- Opções do Menu -->
									<div class="menu-sub menu-sub-accordion">
										<!--begin:Menu link-->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('users.index') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Usuários</span>
											</a>
										</div>


										<!-- inicio submenu pausas -->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('Pausas.inicio') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Pausas</span>
											</a>
										</div>

										<!-- inicio submenu Empresas -->
										<div class="menu-item">
											<a class="menu-link" href="{{ route('empresas.create') }}">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Criar empresa</span>
											</a>
										</div>
										@endif
									</div>
								</div>
								<!-- Fim do Menu Administração -->		
							</div>
							<!--end::Sidebar menu-->
						</div>
						<!--end::Main-->
					</div>
					<!--end::Sidebar-->
					<!--begin::Main-->
					<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
						<!--begin::Content wrapper-->
						<div class="d-flex flex-column flex-column-fluid">
							<!--begin::Toolbar-->
							<div id="kt_app_toolbar" class="app-toolbar pt-3">
								<!--begin::Toolbar container-->
								<div id="kt_app_toolbar_container" class="app-container container-fluid d-flex align-items-stretch">
									<!--begin::Toolbar wrapper-->
									<div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
									</div>
									<!--end::Toolbar wrapper-->
								</div>
								<!--end::Toolbar container-->
							</div>
							<!--end::Toolbar-->
							<!--Conteudo blade-->
							<div id="kt_app_content" class="app-content flex-column-fluid">
								@yield('content') 
							</div>
							<!--Conteudo blade-->
							<!--end::Content wrapper-->
							<!--begin::Footer-->
						<div id="kt_app_footer" class="app-footer d-flex align-items-center justify-content-center justify-content-md-between flex-column flex-md-row py-3 px-4 border-top">
							<!--begin::Copyright-->
							<div class="text-dark order-2 order-md-1 text-center text-md-start">
								<span class="text-muted fw-semibold me-1">2025&copy;</span>
								<a href="https://fttelecom.cloud" target="_blank" class="text-gray-800 text-hover-primary fw-bold">Future Tec</a>
							</div>
							<!--end::Copyright-->
						</div>
						<!--end::Footer-->

					</div>
					<!--end:::Main-->
		<!--end::App-->
		<!--begin::Drawers-->
		<!--begin::Activities drawer-->
		<div id="kt_activities" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="activities" data-kt-drawer-activate="true" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'300px', 'lg': '900px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_activities_toggle" data-kt-drawer-close="#kt_activities_close">
			<div class="card shadow-none border-0 rounded-0">
				<!--begin::Header-->
				<div class="card-header" id="kt_activities_header">
					<h3 class="card-title fw-bold text-dark">Activity Logs</h3>
					<div class="card-toolbar">
						<button type="button" class="btn btn-sm btn-icon btn-active-light-primary me-n5" id="kt_activities_close">
							<i class="ki-duotone ki-cross fs-1">
								<span class="path1"></span>
								<span class="path2"></span>
							</i>
						</button>
					</div>
				</div>
				<!--end::Header-->
				<!--begin::Body-->
				<div class="card-body position-relative" id="kt_activities_body">
					<!--begin::Content-->
					<div id="kt_activities_scroll" class="position-relative scroll-y me-n5 pe-5" data-kt-scroll="true" data-kt-scroll-height="auto" data-kt-scroll-wrappers="#kt_activities_body" data-kt-scroll-dependencies="#kt_activities_header, #kt_activities_footer" data-kt-scroll-offset="5px">				
					</div>
					<!--end::Modal body-->
				</div>
				<!--end::Modal content-->
			</div>
			<!--end::Modal dialog-->
		</div>
		<!--end::Modal - Invite Friend-->
		<!--end::Modals-->
		<!--begin::Javascript-->
		<script>var hostUrl = "assets/";</script>
		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		<script src="assets/plugins/global/plugins.bundle.js"></script>
		<script src="assets/js/scripts.bundle.js"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Vendors Javascript(used for this page only)-->
	 
	
		
	<script src="{{ asset('js/pause-timer.js') }}"></script>

	<script>
    @if(Auth::check())
        window.userId = {{ Auth::user()->id }};
    @else
        window.location.href = "{{ route('login') }}";  // Redireciona para a página de login
    @endif
    </script>

	</body>
	<!--end::Body-->
</html>