@extends('day.layout')

@section('content')
    <div class="container">
        <h1>Relatório de Login/Logout</h1>

        <!-- Filtros -->
        <div class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <select id="filterUser" class="form-control">
                        <option value="">Todos os Usuários</option>
                        @foreach($users as $user)
                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" id="filterIp" class="form-control" placeholder="IP">
                </div>
                <div class="col-md-2">
                    <input type="date" id="filterDateFrom" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" id="filterDateTo" class="form-control">
                </div>
                <div class="col-md-3">
                    <button id="applyFilters" class="btn btn-primary">Filtrar</button>
                    <button id="resetFilters" class="btn btn-secondary">Limpar</button>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <table class="table table-bordered table-striped" id="logsTable">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>IP</th>
                    <th>Login</th>
                    <th>Logout</th>
                    <th>Duração</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="user-name">{{ $log->user_name }}</td>
                        <td class="ip-address">{{ $log->ip_address }}</td>
                        <td class="login-time">{{ $log->login_time }}</td>
                        <td>{{ $log->logout_time ?? 'Ainda Logado' }}</td>
                        <td>{{ gmdate("H:i:s", $log->session_duration) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Paginação Nativa do Laravel -->
        <div>
            {{ $logs->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Script para Filtragem em JS -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const filterUser = document.getElementById("filterUser");
            const filterIp = document.getElementById("filterIp");
            const filterDateFrom = document.getElementById("filterDateFrom");
            const filterDateTo = document.getElementById("filterDateTo");
            const applyFilters = document.getElementById("applyFilters");
            const resetFilters = document.getElementById("resetFilters");
            const tableRows = document.querySelectorAll("#logsTable tbody tr");

            applyFilters.addEventListener("click", function () {
                const userValue = filterUser.value.toLowerCase();
                const ipValue = filterIp.value.toLowerCase();
                const dateFromValue = filterDateFrom.value;
                const dateToValue = filterDateTo.value;

                tableRows.forEach(row => {
                    const userName = row.querySelector(".user-name").textContent.toLowerCase();
                    const ipAddress = row.querySelector(".ip-address").textContent.toLowerCase();
                    const loginTime = row.querySelector(".login-time").textContent;

                    let showRow = true;

                    if (userValue && !userName.includes(userValue)) {
                        showRow = false;
                    }

                    if (ipValue && !ipAddress.includes(ipValue)) {
                        showRow = false;
                    }

                    if (dateFromValue || dateToValue) {
                        const loginDate = new Date(loginTime);
                        const fromDate = dateFromValue ? new Date(dateFromValue) : null;
                        const toDate = dateToValue ? new Date(dateToValue) : null;

                        if (fromDate && loginDate < fromDate) {
                            showRow = false;
                        }

                        if (toDate && loginDate > toDate) {
                            showRow = false;
                        }
                    }

                    row.style.display = showRow ? "" : "none";
                });
            });

            resetFilters.addEventListener("click", function () {
                filterUser.value = "";
                filterIp.value = "";
                filterDateFrom.value = "";
                filterDateTo.value = "";
                tableRows.forEach(row => row.style.display = "");
            });
        });
    </script>
@endsection
