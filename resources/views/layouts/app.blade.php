<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Minha Aplicação')</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">

    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #004a70;
            color: white;
            display: flex;
            flex-direction: column;
            width: 212px; /* Reduzido em 15% */
            height: 100vh;
            position: fixed;
            transition: width 0.3s ease;
            top: 0;
            bottom: 0;
        }

        .navbar.collapsed {
            width: 60px; /* Reduzido em 15% */
        }

        .navbar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background-color: #006687;
        }

        .navbar.collapsed .navbar-header {
            display: none;
        }

        .menu {
            flex-grow: 1;
            padding: 10px;
            overflow-y: auto;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 10px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s, justify-content 0.3s;
            font-size: 0.8rem; /* Reduzido em 30% */
        }

        .menu-item:hover {
            background-color: #005f80;
        }

        .menu-item i {
            margin-right: 10px;
            transition: margin-right 0.3s;
            font-size: 1rem; /* Reduzido em 30% */
        }

        .navbar.collapsed .menu-item {
            justify-content: center;
        }

        .navbar.collapsed .menu-item i {
            margin-right: 0;
        }

        .navbar.collapsed .menu-item span {
            display: none;
        }

        .logout {
            margin: 20px auto;
            width: 80%; /* Ajustado para 80% */
            background-color: #d9534f;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.8rem; /* Reduzido em 30% */
        }

        .logout i {
            margin-right: 5px;
            font-size: 0.9rem; /* Reduzido em 30% */
        }

        .logout:hover {
            background-color: #c9302c;
        }

        .navbar.collapsed .logout {
            font-size: 0.7rem;
            padding: 6px;
            justify-content: center;
        }

        .content {
            margin-left: 212px; /* Ajustado para 212px */
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .content.collapsed {
            margin-left: 60px; /* Ajustado para 60px */
        }

        .submenu {
            display: none;
            margin-left: 15px;
            border-radius: 5px;
        }

        .submenu .menu-item {
            padding-left: 30px;
        }

        .submenu.show {
            display: block;
        }

        /* Toggle Button */
        .toggle-button {
            position: fixed;
            top: 15px;
            left: 15px;
            background-color: #004a70;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
            font-size: 0.9rem; /* Reduzido em 30% */
        }

        .toggle-button:hover {
            background-color: #006687;
        }

        /* Responsividade para telas pequenas */
        @media (max-width: 768px) {
            .navbar {
                width: 60px;
            }

            .navbar.collapsed {
                width: 60px;
            }

            .content {
                margin-left: 60px;
            }

            .menu-item span {
                display: none;
            }

            .navbar.collapsed .menu-item i {
                margin-right: 0;
            }

            .logout {
                font-size: 0.7rem;
                padding: 6px;
                justify-content: center;
                margin: 10px 0;
            }

            .logout i {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <button class="toggle-button" id="toggleSidebar"><i class="fas fa-bars"></i></button>

    <div class="navbar" id="sidebar">
        <div class="navbar-header">
            <img src="https://via.placeholder.com/100x50" alt="Logo">
        </div>
        <div class="menu">
            <a href="{{ url('/') }}" class="menu-item"><i class="fas fa-home"></i> <span>Home</span></a>

            <a href="{{ route('dashboard') }}" class="menu-item" @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
                <i class="fas fa-chart-line"></i> <span>Dashboard</span></a>

            <a href="{{ route('call-events') }}" class="menu-item" @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
            <i class="fas fa-tachometer-alt"></i> <span>Monitoramento</span></a>

            <a href="#" class="menu-item" id="relatorios-menu" @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
            <i class="fas fa-file-alt"></i> <span>Relatórios</span></a>
            <div class="submenu" id="submenu-relatorios">
                <a href="{{ route('relatorios.ligacoes') }}" class="menu-item"><i class="fas fa-phone"></i> <span>Ligações</span></a>
                <a href="{{ route('login-report.index') }}" class="menu-item"><i class="fas fa-file-alt"></i> <span>Login/Logout</span></a>
            </div>
            <a href="{{ route('painel-atendimento') }}" class="menu-item"><i class="fas fa-headset"></i> <span>Painel de Atendimento</span></a>
            <a href="#" class="menu-item" @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
                <i class="fas fa-address-book"></i> <span>Lista de Contato</span></a>
            <a href="#" class="menu-item" id="telefonia-menu"
                @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
                <i class="fas fa-phone"></i> <span>Telefonia</span></a>
            <div class="submenu" id="submenu-telefonia">
                <a href="{{ route('ramais.index') }}" class="menu-item"><i class="fas fa-phone-alt"></i> <span>Ramais</span></a>
                <a href="{{ route('filas.index') }}" class="menu-item"><i class="fas fa-users"></i> <span>Filas</span></a>
                <a href="{{ route('rotas.index') }}" class="menu-item"><i class="fas fa-route"></i> <span>Rotas</span></a>
                <a href="{{ route('troncos.index') }}" class="menu-item"><i class="fas fa-network-wired"></i> <span>Troncos</span></a>
            </div>
            <a href="#" class="menu-item" id="admin-menu" 
            @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
            <i class="fas fa-cogs"></i> <span>Administração</span>
            </a>
            <div class="submenu" id="submenu-admin" 
                @if(auth()->check() && auth()->user()->cargo == 'Operador') style="display: none;" @endif>
                <a href="{{ route('users.index') }}" class="menu-item"><i class="fas fa-users"></i> <span>Usuários</span></a>
                <a href="{{ route('permissions.index') }}" class="menu-item"><i class="fas fa-users"></i> <span>Permissões</span></a>
            </div>
        </div>
        <a href="{{ route('logout') }}" class="logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
    <div class="content" id="mainContent">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>

    <!-- JavaScript para funcionalidade -->
    <script>
        // Função para abrir/fechar submenus
        function toggleSubmenu(menuId) {
            const submenu = document.getElementById(menuId);
            const submenus = document.querySelectorAll('.submenu');

            // Fechar todos os submenus
            submenus.forEach(sub => {
                if (sub !== submenu) {
                    sub.classList.remove('show');
                }
            });

            // Alternar a visibilidade do submenu clicado
            submenu.classList.toggle('show');
        }

        document.getElementById('admin-menu').addEventListener('click', function(event) {
            event.preventDefault();
            toggleSubmenu('submenu-admin');
        });

        document.getElementById('telefonia-menu').addEventListener('click', function(event) {
            event.preventDefault();
            toggleSubmenu('submenu-telefonia');
        });

        document.getElementById('relatorios-menu').addEventListener('click', function(event) {
            event.preventDefault();
            toggleSubmenu('submenu-relatorios');
        });

        // Toggle Sidebar
        const toggleButton = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        toggleButton.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
        });
    </script>
</body>
</html>
