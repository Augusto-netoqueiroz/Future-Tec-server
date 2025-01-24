@extends('day.layout')

@section('title', 'Monitor')

@section('content')
<div class="container mt-5">
    <div class="row">
        <!-- Seção de Ramais -->
        <div class="col-md-8">
            <h1 class="text-primary">Dados da Tabela Sippeers</h1>
            <!-- Container para os cards -->
            <div class="row" id="sippers-cards">
                <!-- Os cards serão adicionados aqui via JavaScript -->
            </div>
        </div>

        <!-- Seção de Filas -->
        <div class="col-md-3 d-flex justify-content-end"> <!-- Alinhado à direita -->
            <div>
                <div class="d-flex justify-content-end mb-2">
                    <div class="form-check form-switch" style="transform: scale(1.3);">
                        <input class="form-check-input" type="checkbox" id="queue-toggle">
                        <label class="form-check-label text-secondary fw-bold ms-2" for="queue-toggle">Mostrar</label>
                    </div>
                </div>
                <div id="queue-section" class="queue-section p-3 shadow-sm rounded" 
                     style="background: linear-gradient(to right, #f8f9fa, #eaecef); border: 1px solid #ddd; font-size: 0.9rem; display: none;">
                    <h3 class="text-secondary">Ligações em Fila</h3>
                    <div id="queue-table" class="queue-table bg-white rounded p-2" style="max-height: 280px; overflow-y: auto;">
                        <!-- Os dados das filas serão adicionados aqui via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.0/socket.io.min.js"></script>
<script>
    const socket = io("http://93.127.212.237:4000");

    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600).toString().padStart(2, '0');
        const mins = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
        const secs = (seconds % 60).toString().padStart(2, '0');
        return `${hrs}:${mins}:${secs}`;
    }

    socket.on('connect', () => {
        console.log('Conectado ao servidor Socket.IO');
        socket.emit('fetch-sippers');
    });

    socket.on('fetch-sippers-response', (data) => {
        const cardsContainer = document.querySelector("#sippers-cards");
        cardsContainer.innerHTML = "";

        data.forEach((sipper) => {
            const card = document.createElement("div");
            card.classList.add("col-md-6", "mb-4");
            card.innerHTML = `
                <div class="card" id="card-${sipper.id}">
                    <div class="card-body">
                        <h5 class="card-title">${sipper.name}</h5>
                        <span class="badge">${sipper.user_name || "Desconhecido"}</span>
                        <p class="card-text mt-3">
                            <strong>Status:</strong> <span class="status">${sipper.status || "Desconhecido"}</span><br>
                            <strong>Início:</strong> ${sipper.started_at || "N/A"}<br>
                            <strong>Tempo:</strong> <span class="time">${sipper.time_in_pause || "00:00:00"}</span>
                        </p>
                    </div>
                </div>
            `;

            cardsContainer.appendChild(card);
            updateCardStyle(sipper);
        });
    });

    function updateCardStyle(sipper) {
        const card = document.querySelector(`#card-${sipper.id}`);
        const timeSpan = card.querySelector('.time');
        let timeInSeconds = 0;

        switch (sipper.status) {
            case 'Ringing':
                card.classList.add('ringing');
                card.classList.remove('ring', 'call');
                startTimer(timeSpan, sipper.time_in_status || 0);
                break;
            case 'Ring':
                card.classList.add('ring');
                card.classList.remove('ringing', 'call');
                startTimer(timeSpan, sipper.time_in_status || 0);
                break;
            case 'Call':
                card.classList.add('call');
                card.classList.remove('ring', 'ringing');
                startTimer(timeSpan, sipper.time_in_status || 0);
                break;
            default:
                card.classList.remove('ring', 'ringing', 'call');
                timeSpan.textContent = sipper.time_in_pause || "00:00:00";
                break;
        }
    }

    function startTimer(element, initialTime) {
        let timeInSeconds = initialTime;
        element.textContent = formatTime(timeInSeconds);

        setInterval(() => {
            timeInSeconds++;
            element.textContent = formatTime(timeInSeconds);
        }, 1000);
    }

    socket.on('disconnect', () => {
        alert('Desconectado do servidor Socket.IO!');
    });
</script>

<style>
    .card {
        position: relative;
        background-color: #17a2b8;
        color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 15px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card.ringing {
        background-color: #ffc107; /* Amarelo para Ringing */
        animation: shake 0.3s infinite;
    }

    .card.ring {
        background-color: #007bff; /* Azul para Ring */
    }

    .card.call {
        background-color: #0056b3; /* Azul escuro para Call */
    }

    @keyframes shake {
        0%, 100% {
            transform: translateX(0);
        }
        25% {
            transform: translateX(-5px);
        }
        50% {
            transform: translateX(5px);
        }
        75% {
            transform: translateX(-5px);
        }
    }

    .time {
        font-size: 1.2rem;
        font-weight: bold;
        margin-top: 10px;
    }
</style>
@endsection
