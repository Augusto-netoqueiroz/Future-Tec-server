<div class="app-navbar flex-grow-1 justify-content-end" id="kt_app_header_navbar">
    <div class="app-navbar flex-grow-1 justify-content-end" id="kt_app_header_navbar">
        <!-- Barra de Pesquisa -->
        <div class="app-navbar-item d-flex align-items-stretch flex-lg-grow-1 me-2 me-lg-0">
            <div id="kt_header_search" class="header-search d-flex align-items-center w-lg-350px" data-kt-search-keypress="true" data-kt-search-min-length="2" data-kt-search-enter="enter" data-kt-search-layout="menu" data-kt-search-responsive="true" data-kt-menu-trigger="auto" data-kt-menu-permanent="true" data-kt-menu-placement="bottom-start">
                <!-- Formulário de Busca -->
                <form data-kt-search-element="form" class="d-none d-lg-block w-100 position-relative mb-5 mb-lg-0" autocomplete="off">
                    <i class="ki-duotone ki-magnifier search-icon fs-2 text-gray-500 position-absolute top-50 translate-middle-y ms-5"></i>
                    <input type="text" class="search-input form-control form-control border-0 h-lg-40px ps-13" name="search" placeholder="Search..." data-kt-search-element="input" />
                    <span class="search-reset btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4" data-kt-search-element="clear">
                        <i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0"></i>
                    </span>
                </form>
            </div>
        </div>
    
        <!-- Notificações -->
        <div class="app-navbar-item me-lg-1">
            <div class="btn btn-icon btn-custom btn-color-gray-600 btn-active-color-primary w-35px h-35px w-md-40px h-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-graph-3 fs-1"></i>
            </div>
            <!-- Menu de Notificações -->
            <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true" id="kt_menu_notifications">
                <!-- Cabeçalho -->
                <div class="d-flex flex-column bgi-no-repeat rounded-top" style="background-image:url('assets/media/misc/menu-header-bg.jpg')">
                    <h3 class="text-white fw-semibold px-9 mt-10 mb-6">Notifications</h3>
                    <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-semibold px-9">
                        <li class="nav-item"><a class="nav-link text-white pb-4" data-bs-toggle="tab" href="#kt_topbar_notifications_1">Alerts</a></li>
                        <li class="nav-item"><a class="nav-link text-white pb-4 active" data-bs-toggle="tab" href="#kt_topbar_notifications_2">Updates</a></li>
                        <li class="nav-item"><a class="nav-link text-white pb-4" data-bs-toggle="tab" href="#kt_topbar_notifications_3">Logs</a></li>
                    </ul>
                </div>
            </div>
        </div>
    
        <!-- Links Rápidos -->
        <div class="app-navbar-item">
            <div class="btn btn-icon btn-custom btn-color-gray-600 btn-active-color-primary w-35px h-35px w-md-40px h-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-notification-status fs-1"></i>
            </div>
        </div>
    
        <!-- Menu do Usuário -->
        <div class="app-navbar-item ms-3 ms-lg-4 me-lg-2" id="kt_header_user_menu_toggle">
            <div class="cursor-pointer symbol symbol-30px symbol-lg-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                <img src="{{ asset('assets/media/avatars/300-2.jpg') }}" alt="user" />
            </div>
            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                <div class="menu-item px-3">
                    <div class="menu-content d-flex align-items-center px-3">
                        <div class="symbol symbol-50px me-5">
                            <img alt="Logo" src="{{ asset('assets/media/avatars/300-2.jpg') }}" />
                        </div>
                        <div class="d-flex flex-column">
                            <div class="fw-bold d-flex align-items-center fs-5">Jane Cooper</div>
                            <a href="#" class="fw-semibold text-muted fs-7">jane@kt.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>