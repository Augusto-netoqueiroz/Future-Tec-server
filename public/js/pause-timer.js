 
document.addEventListener('DOMContentLoaded', function () {
  const pauseButton = document.getElementById('pauseToggleButton');
  const pauseModal = new bootstrap.Modal(document.getElementById('pauseModal'));
  const pauseList = document.getElementById('pauseList');
  const confirmPauseButton = document.getElementById('confirmPauseButton');
  const onlineTimerElement = document.getElementById('onlineTimer');
  const pauseTimerElement = document.getElementById('pauseTimer');
  const currentStatusElement = document.getElementById('currentStatus');

  let onlineStartTime = null;
  let pauseStartTime = null;
  let selectedPauseId = null;

  // Função para formatar tempo
  const formatTime = (seconds) => {
    const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
    const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    return `${h}:${m}:${s}`;
  };

  // Atualiza os contadores de tempo
  const updateTimers = () => {
    const now = new Date();
    if (onlineStartTime) {
      const elapsed = Math.floor((now - onlineStartTime) / 1000);
      onlineTimerElement.textContent = `Tempo Disponível: ${formatTime(elapsed)}`;
    }
    if (pauseStartTime) {
      const elapsed = Math.floor((now - pauseStartTime) / 1000);
      pauseTimerElement.textContent = `Tempo na Pausa: ${formatTime(elapsed)}`;
    }
  };

  // Atualiza a cor do status com base no texto
  const updateStatusColor = () => {
    const statusText = currentStatusElement.textContent.trim();
    if (statusText.includes('Disponível')) {
      currentStatusElement.style.backgroundColor = '#e8f5e9'; // Verde claro
      currentStatusElement.style.color = '#4caf50'; // Texto verde escuro
    } else {
      currentStatusElement.style.backgroundColor = '#fff3e0'; // Amarelo claro
      currentStatusElement.style.color = '#ff9800'; // Texto laranja
    }
  };

  // Converte o formato 'YYYY-MM-DD HH:mm:ss' para um objeto Date
  const parseDateTime = (dateTimeString) => {
    const [datePart, timePart] = dateTimeString.split(' ');
    const [year, month, day] = datePart.split('-').map(Number);
    const [hour, minute, second] = timePart.split(':').map(Number);
    return new Date(year, month - 1, day, hour, minute, second);
  };

  // Gerencia o timer de disponibilidade
  const startOnlineTimer = (startTime = null) => {
    onlineStartTime = startTime ? parseDateTime(startTime) : new Date();
    pauseStartTime = null;
    onlineTimerElement.style.display = 'block';
    pauseTimerElement.style.display = 'none';
    currentStatusElement.textContent = 'Status: Disponível';
    pauseButton.textContent = 'Selecionar Pausa';
    updateStatusColor(); // Atualiza a cor do status
  };

  // Gerencia o timer de pausa
  const startPauseTimer = (pauseName, startTime = null) => {
    pauseStartTime = startTime ? parseDateTime(startTime) : new Date();
    onlineStartTime = null;
    pauseTimerElement.style.display = 'block';
    onlineTimerElement.style.display = 'none';
    currentStatusElement.textContent = `Status: ${pauseName}`;
    pauseButton.textContent = 'Liberar Pausa';
    updateStatusColor(); // Atualiza a cor do status
  };

  // Carrega o último log de pausa ao carregar a página
  const fetchLastPause = async () => {
    try {
      console.log('Chamando /pauses/last com user_id:', window.userId);
      const response = await fetch(`/pauses/last?user_id=${window.userId}`);
      if (!response.ok) {
        throw new Error(`Erro HTTP ${response.status}`);
      }

      const lastPause = await response.json();
      if (lastPause) {
        const { pause_name, started_at } = lastPause;

        if (pause_name === 'Disponível') {
          startOnlineTimer(started_at);
        } else {
          startPauseTimer(pause_name, started_at);
        }
      } else {
        // Nenhum log encontrado, inicializa como disponível
        startOnlineTimer();
      }
    } catch (error) {
      console.error('Erro ao buscar último log de pausa:', error);
      startOnlineTimer(); // Fallback para disponibilidade
    }
    updateStatusColor(); // Atualiza a cor do status
  };

  // Carrega as pausas no modal
  const fetchPausas = async () => {
    try {
      const response = await fetch('/pauses'); // Atualize com a rota correta
      const pausas = await response.json();

      pauseList.innerHTML = '';
      pausas.forEach((pausa) => {
        const item = document.createElement('li');
        item.classList.add('list-group-item');
        item.textContent = pausa.name;
        item.addEventListener('click', () => {
          selectedPauseId = pausa.id;
          confirmPauseButton.disabled = false;
          confirmPauseButton.textContent = `Confirmar: ${pausa.name}`;
        });
        pauseList.appendChild(item);
      });
    } catch (error) {
      console.error('Erro ao carregar pausas:', error);
      document.getElementById('errorMessage').style.display = 'block';
    }
  };

  // Confirma a pausa selecionada e envia o POST
  confirmPauseButton.addEventListener('click', async () => {
    if (selectedPauseId) {
      const pauseName = confirmPauseButton.textContent.split(': ')[1];
      startPauseTimer(pauseName);
      pauseModal.hide();

      try {
        const currentUserId = window.userId;

        const response = await fetch('/pauses/start', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
            pause_id: selectedPauseId,
            user_id: currentUserId,
          }),
        });

        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(`Erro HTTP ${response.status}: ${errorText}`);
        }

        const result = await response.json();
        console.log('Pausa iniciada com sucesso:', result);
      } catch (error) {
        console.error('Erro ao iniciar a pausa:', error);
        alert('Ocorreu um erro ao tentar iniciar a pausa. Tente novamente mais tarde.');
      }
    }
  });

  // Alterna entre pausa e disponibilidade
  pauseButton.addEventListener('click', async () => {
    if (pauseStartTime) {
      try {
        const endPauseResponse = await fetch('/pauses/end', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
            user_id: window.userId,
          }),
        });

        if (!endPauseResponse.ok) {
          throw new Error(`Erro HTTP ${endPauseResponse.status}`);
        }

        const endPauseResult = await endPauseResponse.json();
        console.log('Pausa encerrada com sucesso:', endPauseResult);

        const createAvailableLogResponse = await fetch('/pauses/start', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
            pause_id: 6,
            user_id: window.userId,
          }),
        });

        if (!createAvailableLogResponse.ok) {
          throw new Error(`Erro HTTP ${createAvailableLogResponse.status}`);
        }

        const availableLogResult = await createAvailableLogResponse.json();
        console.log('Log de "Disponível" criado com sucesso:', availableLogResult);

        startOnlineTimer();
      } catch (error) {
        console.error('Erro ao liberar pausa ou criar log de "Disponível":', error);
        alert('Erro ao liberar pausa. Tente novamente.');
      }
    } else {
      fetchPausas();
      pauseModal.show();
    }
  });

  setInterval(updateTimers, 1000);

  fetchLastPause();
});
 