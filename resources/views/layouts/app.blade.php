<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Minha Aplicação')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .navbar {
            display: flex;
            flex-direction: column;
            width: 250px;
            height: 100vh;
            background-color: #004a70;
            color: #fff;
            position: fixed;
        }
        .navbar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background-color: #006687;
        }
        .navbar-header img {
            width: 100px;
        }
        .navbar .menu {
            flex-grow: 1;
            padding: 10px;
        }
        .navbar .menu-item {
            display: flex;
            align-items: center;
            padding: 10px;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .navbar .menu-item:hover {
            background-color: #005f80;
        }
        .navbar .menu-item i {
            margin-right: 10px;
        }
        .navbar .logout {
            margin: 20px auto;
            width: 90%;
            background-color: #d9534f;
            color: #fff;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .navbar .logout:hover {
            background-color: #c9302c;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        /* Estilos para os submenus */
        .submenu {
            display: none;
            background-color: #003953;
            margin-left: 15px;
            border-radius: 5px;
        }
        .submenu .menu-item {
            padding-left: 30px;
        }
        .submenu.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-header">
            <img src="https://via.placeholder.com/100x50" alt="Logo">
        </div>
        <div class="menu">
            <a href="{{ url('/') }}" class="menu-item"><i class="fas fa-home"></i> Home</a>
            <a href="{{ route('call-events') }}" class="menu-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="#" class="menu-item"><i class="fas fa-chart-line"></i> Monitoramento</a>
             
            <!-- Submenu "Telefonia" com ícone ajustado -->
            <a href="#" class="menu-item" id="relatorios-menu"><i class="fas fa-file-alt"></i> Relatórios</a>
              <div class="submenu" id="submenu-relatorios">
                 <a href="{{ route('relatorios.ligacoes') }}" class="menu-item"><i class="fas fa-phone"></i> Ligações</a>
              </div>
            <a href="{{ route('painel-atendimento') }}" class="menu-item"><i class="fas fa-headset"></i> Painel de Atendimento</a>
            <a href="#" class="menu-item"><i class="fas fa-address-book"></i> Lista de contato</a>
            <a href="#" class="menu-item"><i class="fas fa-network-wired"></i> URA</a>
            
            <!-- Submenu "Telefonia" com ícone ajustado -->
               <a href="#" class="menu-item" id="telefonia-menu"><i class="fas fa-phone"></i> Telefonia</a>
            <div class="submenu" id="submenu-telefonia">
            <!-- Ícone atualizado para "fa-phone-alt" -->
             <a href="{{ route('ramais.index') }}" class="menu-item"><i class="fas fa-phone-alt"></i> Ramais</a>
            </div>

            <!-- Administração com submenu -->
            <a href="#" class="menu-item" id="admin-menu"><i class="fas fa-cogs"></i> Administração</a>
            <div class="submenu" id="submenu">
                <a href="{{ route('users.index') }}" class="menu-item"><i class="fas fa-users"></i> Usuários</a>
            </div>
        </div>
        <a href="{{ route('logout') }}" class="logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
    <div class="content">
        @yield('content')
    </div>

    <!-- Inclusão do Bootstrap 5 JS e dependências -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>

    <script>
        // Script para abrir/fechar o submenu Administração
        document.getElementById('admin-menu').addEventListener('click', function(event) {
            event.preventDefault();
            const submenu = document.getElementById('submenu');
            submenu.classList.toggle('show');
        });

        // Script para abrir/fechar o submenu Telefonia
        document.getElementById('telefonia-menu').addEventListener('click', function(event) {
            event.preventDefault();
            const submenuTelefonia = document.getElementById('submenu-telefonia');
            submenuTelefonia.classList.toggle('show');
        });
        // Script para abrir/fechar o submenu relatórios
        document.getElementById('relatorios-menu').addEventListener('click', function(event) {
            event.preventDefault();
            const submenuRelatorios = document.getElementById('submenu-relatorios');
            submenuRelatorios.classList.toggle('show');
        });
    </script>
</body>
</html>
