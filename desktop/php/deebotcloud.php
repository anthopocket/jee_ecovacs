<?php
/* plugins/deebotcloud/desktop/php/deebotcloud.php */
if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('deebotcloud');
$pluginId = $plugin->getId();
$eqLogics = eqLogic::byType($pluginId);

$mqtt_host = config::byKey('mqtt_host', $pluginId, '127.0.0.1');
$mqtt_port = config::byKey('mqtt_port', $pluginId, '1883');
$mqtt_user = config::byKey('mqtt_user', $pluginId, '');
$mqtt_pass = config::byKey('mqtt_pass', $pluginId, '');

sendVarToJS('eqType', $pluginId);
?>

<div class="row row-overflow">

  <!-- ====== Colonne gauche : liste des équipements ====== -->
  <div class="col-xs-12 col-sm-4 col-md-3">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-primary btn-sm" id="bt_addEqLogic">
          <i class="fa fa-plus-circle"></i> {{Ajouter}}
        </a>
        <li class="filter" style="margin-top:10px;">
          <input class="filter form-control input-sm" placeholder="{{Rechercher}}" />
        </li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '">';
          echo '<a>' . $eqLogic->getHumanName(true, false) . '</a>';
          echo '</li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <!-- ====== Colonne droite : contenu ====== -->
  <div class="col-xs-12 col-sm-8 col-md-9">

    <div class="input-group pull-right" style="margin-bottom:10px;">
      <span class="input-group-btn">
        <a class="btn btn-default btn-sm" id="bt_showPluginTab">
          <i class="fa fa-plug"></i> {{Onglet Plugin}}
        </a>
        <a class="btn btn-success btn-sm eqLogicAction" data-action="save">
          <i class="fa fa-check"></i> {{Sauvegarder l’équipement}}
        </a>
        <a class="btn btn-warning btn-sm eqLogicAction" data-action="remove">
          <i class="fa fa-trash"></i> {{Supprimer l’équipement}}
        </a>
      </span>
    </div>

    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation" class="active"><a href="#eqtab" role="tab" data-toggle="tab"><i class="fa fa-cube"></i> {{Équipement}}</a></li>
      <li role="presentation"><a href="#commandtab" role="tab" data-toggle="tab"><i class="fa fa-list"></i> {{Commandes}}</a></li>
      <li role="presentation"><a href="#plugintab" role="tab" data-toggle="tab"><i class="fa fa-cogs"></i> {{Plugin}}</a></li>
    </ul>

    <div class="tab-content" style="padding-top:20px;">

      <!-- ====== Onglet Équipement ====== -->
      <div role="tabpanel" class="tab-pane active eqLogic" id="eqtab" style="display:none;" data-page="deebotcloud">
        <form class="form-horizontal">

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom de l’équipement}}</label>
            <div class="col-sm-7">
              <input class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Mon Deebot}}" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Objet parent}}</label>
            <div class="col-sm-7">
              <select class="eqLogicAttr form-control" data-l1key="object_id">
                <?php
                foreach (jeeObject::buildTree(null, false) as $object) {
                  echo '<option value="' . $object->getId() . '">' . $object->getHumanName(true) . '</option>';
                }
                ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Catégories}}</label>
            <div class="col-sm-7">
              <?php
              foreach (class_exists('jeedom') ? jeedom::getConfiguration('eqLogic:category') : array() as $key => $value) {
                echo '<label class="checkbox-inline">';
                echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" /> ' . __($value['name'], __FILE__);
                echo '</label>';
              }
              ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Activer}}</label>
            <div class="col-sm-1">
              <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />
            </div>
            <label class="col-sm-3 control-label">{{Visible}}</label>
            <div class="col-sm-1">
              <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />
            </div>
          </div>

          <fieldset>
            <legend>{{Paramètres de connexion (cet équipement)}}</legend>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{DID du robot}}</label>
              <div class="col-sm-4">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="did"
                       placeholder="{{ex: 12345678901234567890}}" />
              </div>
              <div class="col-sm-5 help-block">
                {{Obligatoire. Le plugin publie vers}} <code>deebot/&lt;DID&gt;/set</code>.
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{MQTT Hôte}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mqtt_host" placeholder="127.0.0.1" value="<?php echo htmlspecialchars($mqtt_host); ?>"/>
              </div>
              <label class="col-sm-2 control-label">{{MQTT Port}}</label>
              <div class="col-sm-2">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mqtt_port" placeholder="1883" value="<?php echo htmlspecialchars($mqtt_port); ?>"/>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{MQTT Utilisateur}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mqtt_user" value="<?php echo htmlspecialchars($mqtt_user); ?>"/>
              </div>
              <label class="col-sm-2 control-label">{{MQTT Mot de passe}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" type="password" data-l1key="configuration" data-l2key="mqtt_pass" value="<?php echo htmlspecialchars($mqtt_pass); ?>"/>
              </div>
            </div>
          </fieldset>

        </form>
      </div>

      <!-- ====== Onglet Commandes ====== -->
      <div role="tabpanel" class="tab-pane" id="commandtab">
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width:60px;">#</th>
              <th style="min-width:250px;">{{Nom}}</th>
              <th style="min-width:120px;">{{Type}}</th>
              <th style="min-width:120px;">{{Sous-type}}</th>
              <th style="min-width:120px;">{{Paramètres}}</th>
              <th style="width:100px;">{{Actions}}</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <!-- ====== Onglet Plugin (config MQTT + découverte) ====== -->
      <div role="tabpanel" class="tab-pane" id="plugintab">
        <form class="form-horizontal">

          <fieldset>
            <legend>{{Configuration MQTT (globale plugin)}}</legend>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Hôte}}</label>
              <div class="col-sm-3">
                <input id="cfg_mqtt_host" class="form-control" value="<?php echo htmlspecialchars($mqtt_host); ?>" placeholder="127.0.0.1"/>
              </div>

              <label class="col-sm-2 control-label">{{Port}}</label>
              <div class="col-sm-2">
                <input id="cfg_mqtt_port" class="form-control" value="<?php echo htmlspecialchars($mqtt_port); ?>" placeholder="1883"/>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Utilisateur}}</label>
              <div class="col-sm-3">
                <input id="cfg_mqtt_user" class="form-control" value="<?php echo htmlspecialchars($mqtt_user); ?>"/>
              </div>

              <label class="col-sm-2 control-label">{{Mot de passe}}</label>
              <div class="col-sm-3">
                <input id="cfg_mqtt_pass" class="form-control" type="password" value="<?php echo htmlspecialchars($mqtt_pass); ?>"/>
              </div>
            </div>

            <div class="form-group">
              <div class="col-sm-9 col-sm-offset-3">
                <a class="btn btn-success" id="bt_save_mqtt">
                  <i class="fa fa-save"></i> {{Enregistrer la configuration MQTT}}
                </a>
              </div>
            </div>
          </fieldset>

          <fieldset>
            <legend>{{Découverte automatique (topics retained)}} <code>deebot/+/state</code></legend>
            <div class="form-group">
              <div class="col-sm-9 col-sm-offset-3">
                <a class="btn btn-primary" id="bt_deebot_discover">
                  <i class="fa fa-search"></i> {{Lancer la découverte}}
                </a>
                <span id="deebot_discover_spinner" style="margin-left:10px; display:none;">
                  <i class="fa fa-spinner fa-spin"></i> {{Scan en cours (2–3 s)…}}
                </span>
              </div>
            </div>

            <div class="form-group">
              <div class="col-sm-12">
                <table class="table table-bordered" id="tb_deebot_discovered" style="display:none;">
                  <thead>
                    <tr>
                      <th>{{DID}}</th>
                      <th>{{Nom (si fourni)}}</th>
                      <th>{{Batterie}}</th>
                      <th>{{Statut brut}}</th>
                      <th style="width:150px;">{{Action}}</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </fieldset>

        </form>
      </div>

    </div><!-- /.tab-content -->

  </div><!-- /.col droite -->

</div>

<script>
/* ===== Helpers ===== */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) _cmd = {configuration: {}};
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td><span class="cmdAttr" data-l1key="id"></span></td>';
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom}}"></td>';
  tr += '<td><span class="type"></span><input class="cmdAttr" data-l1key="type" value="' + (init(_cmd.type) || 'action') + '" style="display:none;"></td>';
  tr += '<td><select class="cmdAttr form-control input-sm" data-l1key="subType"><option value="other">other</option></select></td>';
  tr += '<td></td>';
  tr += '<td>';
  tr += '<a class="btn btn-success btn-xs cmdAction" data-action="save"><i class="fa fa-floppy-o"></i></a> ';
  tr += '<a class="btn btn-danger btn-xs cmdAction" data-action="remove"><i class="fa fa-trash"></i></a>';
  tr += '</td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
  jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

/* ===== UI : navigation ===== */
$('#bt_addEqLogic').on('click', function () {
  jeedom.eqLogic.save({
    type: '<?php echo $pluginId; ?>',
    eqLogics: [ { name: '{{Nouveau Deebot}}', isEnable: 1, isVisible: 1 } ],
    error: function (e) { $('#div_alert').showAlert({message: e.message, level: 'danger'}); },
    success: function () { location.reload(); }
  });
});

$('.li_eqLogic').on('click', function () {
  var eqId = $(this).data('eqlogic_id');
  jeedom.eqLogic.print({
    type: '<?php echo $pluginId; ?>',
    id: eqId,
    status: 1,
    error: function (e) { $('#div_alert').showAlert({message: e.message, level: 'danger'}); },
    success: function (data) {
      $('.eqLogic').show().setValues(data, '.eqLogic');
      if (isset(data.category)) {
        for (const i in data.category) {
          $('.eqLogic .eqLogicAttr[data-l1key=category][data-l2key=' + i + ']').prop('checked', data.category[i] == 1);
        }
      }
      $('#table_cmd tbody').empty();
      for (const i in data.cmd) {
        addCmdToTable(data.cmd[i]);
      }
      $('a[href="#eqtab"]').tab('show');
    }
  });
});

$('#bt_showPluginTab').on('click', function(){
  $('a[href="#plugintab"]').tab('show');
});

/* ===== Plugin config : save via API ===== */
$('#bt_save_mqtt').on('click', function () {
  const data = {
    mqtt_host: $('#cfg_mqtt_host').val(),
    mqtt_port: $('#cfg_mqtt_port').val(),
    mqtt_user: $('#cfg_mqtt_user').val(),
    mqtt_pass: $('#cfg_mqtt_pass').val()
  };
  jeedom.config.save({
    plugin: '<?php echo $pluginId; ?>',
    configuration: data,
    error: function (e) { $('#div_alert').showAlert({message: e.message, level: 'danger'}); },
    success: function () { $('#div_alert').showAlert({message: '{{Configuration MQTT enregistrée}}', level: 'success'}); }
  });
});

/* ===== Découverte MQTT ===== */
$('#bt_deebot_discover').on('click', function () {
  $('#deebot_discover_spinner').show();
  $('#tb_deebot_discovered').hide();
  $('#tb_deebot_discovered tbody').empty();

  $.ajax({
    type: 'POST',
    url: 'plugins/deebotcloud/core/ajax/deebotcloud.ajax.php',
    data: { action: 'discover' },
    dataType: 'json',
    error: function (req, status, err) {
      $('#deebot_discover_spinner').hide();
      $('#div_alert').showAlert({message: err, level: 'danger'});
    },
    success: function (data) {
      $('#deebot_discover_spinner').hide();
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      const list = data.result || [];
      if (list.length === 0) {
        $('#div_alert').showAlert({message: '{{Aucun robot détecté. Vérifie que le bridge publie un message retained sur}} deebot/+/state', level: 'warning'});
        return;
      }
      for (const dev of list) {
        const did = dev.did || '';
        const name = dev.name || '';
        const batt = (dev.battery !== undefined && dev.battery !== null) ? dev.battery : '';
        const st   = (dev.status !== undefined && dev.status !== null) ? JSON.stringify(dev.status) : '';
        const $tr = $('<tr/>');
        $tr.append('<td><code>'+did+'</code></td>');
        $tr.append('<td>'+$('<div/>').text(name).html()+'</td>');
        $tr.append('<td>'+batt+'</td>');
        $tr.append('<td style="max-width:360px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">'+$('<div/>').text(st).html()+'</td>');
        const $btn = $('<a class="btn btn-success btn-xs"><i class="fa fa-plus"></i> {{Créer l\'équipement}}</a>');
        $btn.on('click', function () {
          $.ajax({
            type: 'POST',
            url: 'plugins/deebotcloud/core/ajax/deebotcloud.ajax.php',
            data: { action: 'createEq', did: did, name: (name || ('Deebot '+did.slice(-4))) },
            dataType: 'json',
            error: function (r,s,e) {
              $('#div_alert').showAlert({message: e, level: 'danger'});
            },
            success: function (resp) {
              if (resp.state != 'ok') {
                $('#div_alert').showAlert({message: resp.result, level: 'danger'});
                return;
              }
              $('#div_alert').showAlert({message: '{{Équipement créé}}', level: 'success'});
            }
          });
        });
        const $td = $('<td/>').append($btn);
        $tr.append($td);
        $('#tb_deebot_discovered tbody').append($tr);
      }
      $('#tb_deebot_discovered').show();
      $('a[href="#plugintab"]').tab('show');
    }
  });
});

/* ===== Affichage par défaut ===== */
$(function(){
  // Affiche la vue équipement vide si rien n'est sélectionné
  $('.eqLogic').show();
  $('a[href="#eqtab"]').tab('show');
});
</script>
