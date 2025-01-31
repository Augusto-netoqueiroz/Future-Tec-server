<?php

?>
<style>
  form .botao__custom {
    margin: 30px auto 0;
  }

  .custom__h4 span {
    font-size: 12px;
    font-weight: 400;
    opacity: .8;
  }

  .form-group {
    margin-bottom: 25px;
  }
</style>

<div class="form__titulo">
  <div class="d-flex">
    <h4 class="custom__h4 m-0">
      <?= isset($_GET['cmp_id']) ? 'Editar' : (isset($_GET['clone_cmp_id']) ? 'Clonar' : 'Cadastrar') ?> Campanha
    </h4>
    <div class="ml-2" style="position: relative;height: fit-content;">
      <a href="#" class="btn btn-warning btn-icon btn-sm m-0 btn__info--swal"
        style="border-radius:50%;position:relative;z-index:2;height:30px;width:30px;"><i class="fas fa-info"
          style="font-size: 16px;"></i></a>
      <span class="animate-ping bg-warning"></span>
    </div>
  </div>
  <small>Campos com o símbolo <b>*</b> são obrigatórios.</small>
</div>

<form id="campanha" name="campanha" novalidate="novalidate">
    <?php if(isset($_GET['cmp_id'])){ ?>
      <input type="hidden" id="cmp_id" name="campanha[cmp_id]" value="<?= $_GET['cmp_id'] ?>">
    <?php }else if(isset($_GET['clone_cmp_id'])){ ?>
      <input type="hidden" id="clone_cmp_id" value="<?= $_GET['clone_cmp_id'] ?>">
    <?php } ?>


  <div class="row">
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_nome">Nome *</label>
        <input type="text" class="form-control" maxlength="255" id="cmp_nome"
          name="campanha[cmp_nome]"
          placeholder="Ex: Áudio Promocional Natal" required>
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_destino">Fila Destino *</label>
        <select class="form-control selectpicker" id="cmp_destino" name="campanha[cmp_destino]"
          title="Selecione a Fila Destino" data-live-search="true" required>
          <option class='dropdown-item' value="20">Fila Manual</option>
          <option class='dropdown-item' value="1">Gerar Lead</option>
          <option class='dropdown-item' value="21">LigIA (em teste)</option>
          <?php
        
          ?>
        </select>
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_equipe">Equipe Destino Fila</label>
        <select class="form-control selectpicker" id="cmp_equipe" name="campanha[cmp_equipe]"
          title="Selecione uma Equipe" data-live-search="true">
          <option class='dropdown-item' value='0' selected>Sem Equipe</option>
          <?php
           
          ?>
        </select>
      </div>
    </div>

    <?php
   
    ?>
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_audio">Áudio Inicial *</label>
        <select class="form-control selectpicker" id="cmp_audio" name="campanha[cmp_audio]"
          title="Selecione um Áudio Inicial" data-live-search="true" required>
          <option class='dropdown-item' value='28'>Fila Manual</option>
          <option class='dropdown-item' value='28'>Sem áudio - Transfere direto</option>
          <?php
          foreach ($audios as $dst) {
            $cma_nome = $dst->cma_nome;
            $cma_id = $dst->cma_id;
            echo "<option class='dropdown-item' value='$cma_id'>$cma_id - $cma_nome</option>";
          }
          ?>
        </select>
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_audio_leed">Áudio Após o Dígito *</label>
        <select class="form-control selectpicker" id="cmp_audio_leed" name="campanha[cmp_audio_leed]"
          title="Selecione um Áudio" data-live-search="true" required>
          <option class='dropdown-item' value='20'>Fila Manual</option>
          <option class='dropdown-item' value='28'>Sem áudio - Transfere direto</option>
          <?php
          foreach ($audios as $dst) {
            $cma_nome = $dst->cma_nome;
            $cma_id = $dst->cma_id;
            echo "<option class='dropdown-item' value='$cma_id'>$cma_id - $cma_nome</option>";
          }
          ?>
        </select>
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_empresa">Empresa *</label>
        <select class="form-control selectpicker" id="cmp_empresa" name="campanha[cmp_empresa]"
          title="Selecione uma Empresa" data-live-search="true" required>
          <?php
          ->load->model('Empresa', '', TRUE);
          $empresas = ->Empresa->getEmpresas();
          foreach ($empresas as $emp) {
            $emp_nome = $emp->emp_nome;
            $emp_id = $emp->emp_id;

            $isSelected = '';
            if ($emp_id === $session_usuario->usr_empresa) {
              $isSelected = 'selected';
            }
            echo "<option class='dropdown-item' value='$emp_id' $isSelected>$emp_id - $emp_nome</option>";
          }
          ?>
        </select>
      </div>
    </div>

    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_rota">Rota *</label>
        <select class="form-control selectpicker" id="cmp_rota" name="campanha[cmp_rota]" title="Selecione uma Rota"
          data-live-search="true" required>
          <?php
          ->load->model('Empresa', '', TRUE);
          $rotas = ->Empresa->getRotas();
          if ($rotas) {
            foreach ($rotas as $rts) {
              $rotas_nome = $rts->rotas_nome;
              $rotas_id = $rts->rotas_id;

              echo "<option class='dropdown-item' value='$rotas_id'>$rotas_id - $rotas_nome</option>";
            }
          } else {
            echo "<option class='dropdown-item' value='' disabled>Nenhuma rota cadastrada</option>";
          }
          ?>
        </select>
      </div>
    </div>

    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_lista">Lista *</label>
        <select class="form-control selectpicker" id="cmp_lista" name="campanha[cmp_lista]" title="Selecione uma Lista"
          data-live-search="true" required>
          <?php
          ->load->model('Listas', '', TRUE);
          $lista = ->Listas->get_listas();
          foreach ($lista as $lst) {
            $lista_nome = $lst->lista_nome;
            $lista_id = $lst->lista_id;

            echo "<option class='dropdown-item' value='$lista_id'>$lista_id - $lista_nome</option>";
          }
          ?>
        </select>
      </div>
    </div>
    <div class="col-md-4"></div>
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_agendamento_data">Agendar Início</label>
        <input class="form-control" type="datetime-local" id="cmp_agendamento_data"
          name="campanha[cmp_agendamento_data]">
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_agendamento_data">Agendar Fim</label>
        <input class="form-control" type="datetime-local" id="cmp_agendamento_data_final"
          name="campanha[cmp_agendamento_data_final]">
      </div>
    </div>

    <div class="form-group">
      <input type="checkbox" class="form-control" value=1 id="cmp_fala_nome" name="campanha[cmp_fala_nome]">
      <label for="cmp_fala_nome">Falar nome antes do áudio</label>
    </div>

    <h4 class="custom__h4 col-12 col-md-12">URA + WhatsApp <span>(Opcional)</span></h4>
    <div class="col-md-3 col-xl-2">
      <div class="form-group" style="text-align:center;">
        <input type="checkbox" class="form-control" value="true" id="cmp_whatsapp" name="campanha[cmp_whatsapp]">
        <label for="cmp_whatsapp">Enviar WhatsApp <i>(dígitos)</i></label>
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label for="cmp_instancia_wtz">Instância Whatsapp</label>
        <select class="form-control selectpicker" id="cmp_instancia_wtz" name="campanha[cmp_instancia_wtz]"
          title="Selecione uma Instância" data-live-search="true">
          <?php
          echo "<option class='dropdown-item' value='0' selected>Selecione uma opção</option>";
          ->load->model('Whatsapp', '', TRUE);
          $rotas = ->Whatsapp->get_sessions(false);
          if ($rotas) {
            foreach ($rotas as $rts) {
              $session_wtz_id = $rts->session_wtz_id;
              $session_wtz_nome = $rts->session_wtz_nome;
              $sessionOficial = !empty($rts->session_wtz_waba_id) ? 'Oficial' : '';

              echo "<option class='dropdown-item' value='$session_wtz_id' data-subtext='$sessionOficial'>$session_wtz_id - $session_wtz_nome</option>";
            }
          } else {
            echo "<option class='dropdown-item' value='0' selected disabled>Nenhuma intância cadastrada no sistema</option>";
          }
          ?>
        </select>
      </div>
    </div>

    <div class="col-md-3">
      <div class="form-group">
        <label for="cmp_cadencia">CADÊNCIA</label>
        <input type="number" class="form-control" value="60" min="0" max="1000" id="cmp_cadencia"
          name="campanha[cmp_cadencia]">
        <label for="cmp_cadencia">SEGUNDO(S)</label>
      </div>
    </div>

    <div class="col-md-12" id="cmp_modelo_whatsapp_wrapper">
      <div class="form-group">
        <label for="cmp_modelo_whatsapp">Modelo de mensagem</label>
        <select class="form-control selectpicker" id="cmp_modelo_whatsapp" name="campanha[cmp_modelo_whatsapp]"
          title="Selecione o Modelo de Mensagem" data-live-search="true">
          <?php
          echo "<option class='dropdown-item' value='0' selected disabled>Selecione uma opção</option>";
          ->load->model('Whatsapp', '', TRUE);
          $wppModelos = ->Whatsapp->buscarWPPModelos();
          if ($wppModelos) {
            foreach ($wppModelos as $modelo) {
              $wpp_modelo_id = $modelo->wpp_modelo_id;
              $wpp_modelo_nome = $modelo->wpp_modelo_nome;
              $wpp_roteiro_nome = !is_null($modelo->wpp_roteiro_nome) ? " | Inicia Bot: $modelo->wpp_roteiro_nome" : '';
              $hasBot = $wpp_roteiro_nome ? "data-icon='fas fa-robot'" : '';

              $arrContains = array(
                $modelo->wpp_modelo_texto ? 'Texto' : '',
                $modelo->wpp_modelo_imagem ? 'Imagem' : '',
                $modelo->wpp_modelo_audio ? 'Áudio' : '',
                $modelo->wpp_modelo_video ? 'Vídeo' : '',
                $modelo->wpp_modelo_documento ? 'Documento' : ''
              );
              $arrContains = implode(', ', array_filter($arrContains));
              echo "<option class='dropdown-item' value='$wpp_modelo_id' $hasBot>$wpp_modelo_id - $wpp_modelo_nome → Contém: $arrContains $wpp_roteiro_nome</option>";
            }
          }
          ?>
        </select>
      </div>
    </div>

    <div class="col-md-12" id="cmp_template_whatsapp_oficial_wrapper" style="display:none;">
      <div class="form-group">
        <label for="cmp_template_whatsapp_oficial">Modelo de mensagem oficial</label>
        <select class="form-control selectpicker" id="cmp_template_whatsapp_oficial"
          name="campanha[cmp_template_whatsapp_oficial]" title="Selecione o Modelo de Mensagem Oficial"
          data-live-search="true" disabled>
          <?php
          echo "<option class='dropdown-item' value='0' selected>Selecione uma opção</option>";
          ->load->model('Whatsapp', '', TRUE);
          $wppModelosOficial = ->Whatsapp->buscarWPPModelos();
          if ($wppModelosOficial) {
            foreach ($wppModelosOficial as $modelo) {
              $wpp_modelo_id = $modelo->wpp_modelo_id;
              $wpp_modelo_nome = $modelo->wpp_modelo_nome;
              $wpp_roteiro_nome = !is_null($modelo->wpp_roteiro_nome) ? " | Inicia Bot: $modelo->wpp_roteiro_nome" : '';
              $hasBot = $wpp_roteiro_nome ? "data-icon='fas fa-robot'" : '';

              $arrContains = array(
                $modelo->wpp_modelo_texto ? 'Texto' : '',
                $modelo->wpp_modelo_imagem ? 'Imagem' : '',
                $modelo->wpp_modelo_audio ? 'Áudio' : '',
                $modelo->wpp_modelo_video ? 'Vídeo' : '',
                $modelo->wpp_modelo_documento ? 'Documento' : ''
              );
              $arrContains = implode(', ', array_filter($arrContains));
              echo "<option class='dropdown-item' value='$wpp_modelo_id' $hasBot>$wpp_modelo_id - $wpp_modelo_nome → Contém: $arrContains $wpp_roteiro_nome</option>";
            }
          }
          ?>
        </select>
      </div>
    </div>

    <h4 class="custom__h4 col-12 col-md-12">SMS <span>(Opcional)</span></h4>
    <div class="col-md-6">
      <div class="row">
        <div class="col-md-2">
          <div class="form-group" style="text-align:center;">
            <label for="cmp_enviar_sms">Enviar SMS</label>
            <input type="checkbox" class="form-control" value=1 id="cmp_enviar_sms" name="campanha[cmp_enviar_sms]">
          </div>
        </div>
        <div class="col-md-10">
          <div class="form-group">
            <label for="cmp_msg_sms">Mensagem SMS</label>
            <textarea name="campanha[cmp_msg_sms]" id="cmp_msg_sms" class="form-control" cols="30" rows="3"
              maxlength="160" placeholder="Até 160 caracteres"></textarea>
          </div>
        </div>
      </div>
    </div>

    <h4 class="custom__h4 col-12 col-md-12">
      LigIA
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
        <path fill="currentColor"
          d="m19 1l-1.26 2.75L15 5l2.74 1.26L19 9l1.25-2.74L23 5l-2.75-1.25M9 4L6.5 9.5L1 12l5.5 2.5L9 20l2.5-5.5L17 12l-5.5-2.5M19 15l-1.26 2.74L15 19l2.74 1.25L19 23l1.25-2.75L23 19l-2.75-1.26" />
      </svg>
      <span>(para saber mais procure o nosso comercial)</span>
    </h4>
    <div class="col-md-6">
      <div class="form-group">
        <label for="cmp_openaiproduto">Qual produto você deseja vender?</label>
        <textarea name="campanha[cmp_openaiproduto]" id="cmp_openaiproduto" class="form-control" cols="30" rows="3"
          maxlength="4000" placeholder="Até 4000 caracteres"></textarea>
      </div>
    </div>

    <h4 class="custom__h4 col-12 col-md-12">Webhook <span>(Opcional)</span></h4>
    <div class="col-md-6">
      <div class="form-group">
        <label for="cmp_webhook">Link do Webhook</label>
        <input type="text" class="form-control" id="cmp_webhook" name="campanha[cmp_webhook]"
          placeholder="Ex: https://webhookurl.com.br/...">
      </div>
    </div>

    <div class="col-12 col-md-12">
      <button class="botao__custom" id="btn_salvar"><i class="fas fa-check-circle"></i> SALVAR</button>
    </div>
  </div>
</form>

<?php echo script_tag('cadastro_campanhas') ?>