<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Minha Aplicação')</title>

    <!-- Incluindo CSS do tema Saul -->
    <link href="/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />

    <!-- FontAwesome para ícones adicionais, se necessário -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navbar do Tema Saul -->
    <div class="d-flex flex-column flex-root">
        <div class="page">
            <div class="d-flex flex-row flex-column-fluid page">
                <!-- Menu Lateral -->
                <aside class="aside aside-left d-flex flex-column" id="kt_aside">
                    <!-- Logo -->
                    <div class="aside-logo flex-column-auto" id="kt_aside_logo">
                        <a href="{{ url('/') }}">
                            <img alt="Logo" src="/assets/media/logos/logo-light.png" class="logo-default max-h-50px" />
                        </a>
                    </div>

                    <!-- Menu -->
                    <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
                        <div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">
                            <ul class="menu-nav">
                                <li class="menu-item" aria-haspopup="true">
                                    <a href="{{ url('/') }}" class="menu-link">
                                        <i class="menu-icon fas fa-home"></i>
                                        <span class="menu-text">Home</span>
                                    </a>
                                </li>
                                <li class="menu-item" aria-haspopup="true" @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
                                    <a href="{{ route('dashboard') }}" class="menu-link">
                                        <i class="menu-icon fas fa-chart-line"></i>
                                        <span class="menu-text">Dashboard</span>
                                    </a>
                                </li>
                                <li class="menu-item" aria-haspopup="true" @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
                                    <a href="{{ route('call-events') }}" class="menu-link">
                                        <i class="menu-icon fas fa-tachometer-alt"></i>
                                        <span class="menu-text">Monitoramento</span>
                                    </a>
                                </li>
                                <li class="menu-item menu-item-submenu" aria-haspopup="true" data-menu-toggle="hover">
                                    <a href="javascript:;" class="menu-link menu-toggle">
                                        <i class="menu-icon fas fa-file-alt"></i>
                                        <span class="menu-text">Relatórios</span>
                                        <i class="menu-arrow"></i>
                                    </a>
                                    <div class="menu-submenu">
                                        <ul class="menu-subnav">
                                            <li class="menu-item" aria-haspopup="true">
                                                <a href="{{ route('relatorios.ligacoes') }}" class="menu-link">
                                                    <i class="menu-icon fas fa-phone"></i>
                                                    <span class="menu-text">Ligações</span>
                                                </a>
                                            </li>
                                            <li class="menu-item" aria-haspopup="true">
                                                <a href="{{ route('login-report.index') }}" class="menu-link">
                                                    <i class="menu-icon fas fa-file-alt"></i>
                                                    <span class="menu-text">Login/Logout</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="menu-item" aria-haspopup="true">
                                    <a href="{{ route('painel-atendimento') }}" class="menu-link">
                                        <i class="menu-icon fas fa-headset"></i>
                                        <span class="menu-text">Painel de Atendimento</span>
                                    </a>
                                </li>
                                <li class="menu-item menu-item-submenu" aria-haspopup="true" data-menu-toggle="hover">
                                    <a href="javascript:;" class="menu-link menu-toggle">
                                        <i class="menu-icon fas fa-phone"></i>
                                        <span class="menu-text">Telefonia</span>
                                        <i class="menu-arrow"></i>
                                    </a>
                                    <div class="menu-submenu">
                                        <ul class="menu-subnav">
                                            <li class="menu-item" aria-haspopup="true">
                                                <a href="{{ route('ramais.index') }}" class="menu-link">
                                                    <i class="menu-icon fas fa-phone-alt"></i>
                                                    <span class="menu-text">Ramais</span>
                                                </a>
                                            </li>
                                            <li class="menu-item" aria-haspopup="true">
                                                <a href="{{ route('filas.index') }}" class="menu-link">
                                                    <i class="menu-icon fas fa-users"></i>
                                                    <span class="menu-text">Filas</span>
                                                </a>
                                            </li>
                                            <li class="menu-item" aria-haspopup="true">
                                                <a href="{{ route('rotas.index') }}" class="menu-link">
                                                    <i class="menu-icon fas fa-route"></i>
                                                    <span class="menu-text">Rotas</span>
                                                </a>
                                            </li>
                                            <li class="menu-item" aria-haspopup="true">
                                                <a href="{{ route('troncos.index') }}" class="menu-link">
                                                    <i class="menu-icon fas fa-network-wired"></i>
                                                    <span class="menu-text">Troncos</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="menu-item menu-item-submenu" aria-haspopup="true" data-menu-toggle="hover" @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
                                    <a href="javascript:;" class="menu-link menu-toggle">
                                        <i class="menu-icon fas fa-cogs"></i>
                                        <span class="menu-text">Administração</span>
                                        <i class="menu-arrow"></i>
                                    </a>
                                    <div class="menu-submenu">
                                        <ul class="menu-subnav">
                                            <li class="menu-item" aria-haspopup="true">
                                                <a href="{{ route('users.index') }}" class="menu-link">
                                                    <i class="menu-icon fas fa-users"></i>
                                                    <span class="menu-text">Usuários</span>
                                                </a>
                                            </li>
                                            <li class="menu-item" aria-haspopup="true">
                                                <a href="{{ route('permissions.index') }}" class="menu-link">
                                                    <i class="menu-icon fas fa-key"></i>
                                                    <span class="menu-text">Permissões</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Logout -->
                    <div class="aside-footer d-flex flex-column align-items-center">
                        <a href="{{ route('logout') }}" class="btn btn-sm btn-danger font-weight-bold" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </aside>

                <!-- Conteúdo Principal -->
                <div class="d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper">
                    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                        <div class="container-fluid">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts do Tema Saul -->
    <script src="/assets/plugins/global/plugins.bundle.js"></script>
    <script src="/assets/js/scripts.bundle.js"></script>

    <!-- Scripts adicionais -->
    <script>
        // Aqui você pode adicionar qualquer script específico necessário para sua aplicação
    </script>
</body>
</html>
