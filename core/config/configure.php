<?php
/* plugins/deebotcloud/core/config/configure.php */
if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$pluginId = 'deebotcloud';

$mqtt_host = config::byKey('mqtt_host', $pluginId, '127.0.0.1');
$mqtt_port = config::byKey('mqtt_port', $pluginId, '1883');
$mqtt_user = config::byKey('mqtt_user', $pluginId, '');
$mqtt_pass = config::byKey('mqtt_pass', $pluginId, '');
?>

<form class="form-horizontal">

  <fieldset>
    <legend>{{Connexion MQTT (broker utilisé par Jeedom)}}</legend>

    <div class="form-group">
      <label class="col-sm-3 control-label">{{Hôte}}</label>
      <div class="col-sm-3">
        <input class="configKey form-control" data-l1key="mqtt_host" value="<?php echo $mqtt_host; ?>" placeholder="127.0.0.1"/>
      </div>

      <label class="col-sm-2 control-label">{{Port}}</label>
      <div class="col-sm-2">
        <input class="configKey form-control" data-l1key="mqtt_port" value="<?php echo $mqtt_port; ?>" placeholder="1883"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">{{Utilisateur}}</label>
      <div class="col-sm-3">
        <input class="configKey form-control" data-l1key="mqtt_user" value="<?php echo $mqtt_user; ?>"/>
      </div>

      <label class="col-sm-2 control-label">{{Mot de passe}}</label>
      <div class="col-sm-3">
        <input class="configKey form-control" type="password" data-l1key="mqtt_pass" value="<?php echo $mqtt_pass; ?>"/>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-9 col-sm-offset-3 help-block">
        {{Le bridge Ecovacs publie l’état sur}} <code>deebot/&lt;DID&gt;/state</code> {{(retained) et écoute les commandes sur}} <code>deebot/&lt;DID&gt;/set</code>.
      </div>
    </div>
  </fieldset>

  <fieldset>
    <legend>{{Découverte automatique des robots (via MQTT retained)}}</legend>
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

<script>
  $('#bt_deebot_discover').off('click').on('click', function () {
    $('#deebot_discover_spinner').show();
    $('#tb_deebot_discovered').hide();
    $('#tb_deebot_discovered tbody').empty();

    $.ajax({
      type: 'POST',
      url: 'plugins/deebotcloud/core/ajax/deebotcloud.ajax.php',
      data: {
        action: 'discover'
      },
      dataType: 'json',
      error: function (request, status, error) {
        $('#deebot_discover_spinner').hide();
        $('#div_alert').showAlert({message: error, level: 'danger'});
      },
      success: function (data) {
        $('#deebot_discover_spinner').hide();
        if (data.state != 'ok') {
          $('#div_alert').showAlert({message: data.result, level: 'danger'});
          return;
        }
        const list = data.result || [];
        if (list.length === 0) {
          $('#div_alert').showAlert({message: '{{Aucun robot détecté. Assure-toi que le bridge publie un message retained sur}} deebot/+/state', level: 'warning'});
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
      }
    });
  });
</script>
