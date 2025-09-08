<?php
/* desktop/php/deebotcloud.php */
if (!isConnect()) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('deebotcloud');
$eqLogics = eqLogic::byType($plugin->getId());
sendVarToJS('eqType', $plugin->getId());
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

  <!-- ====== Colonne droite : fiche équipement ====== -->
  <div class="col-xs-12 col-sm-8 col-md-9 eqLogic" style="display: none;" data-page="deebotcloud">

    <div class="input-group pull-right" style="margin-bottom:10px;">
      <span class="input-group-btn">
        <a class="btn btn-default btn-sm eqLogicAction" data-action="configure">
          <i class="fa fa-wrench"></i> {{Configuration du plugin}}
        </a>
        <a class="btn btn-warning btn-sm eqLogicAction" data-action="remove">
          <i class="fa fa-trash"></i> {{Supprimer}}
        </a>
        <a class="btn btn-success btn-sm eqLogicAction" data-action="save">
          <i class="fa fa-check"></i> {{Sauvegarder}}
        </a>
      </span>
    </div>

    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation" class="active"><a href="#eqtab" role="tab" data-toggle="tab"><i class="fa fa-cube"></i> {{Équipement}}</a></li>
      <li role="presentation"><a href="#configtab" role="tab" data-toggle="tab"><i class="fa fa-plug"></i> {{Configuration}}</a></li>
      <li role="presentation"><a href="#commandtab" role="tab" data-toggle="tab"><i class="fa fa-list"></i> {{Commandes}}</a></li>
    </ul>

    <div class="tab-content" style="padding-top:20px;">

      <!-- ====== Onglet Équipement (général) ====== -->
      <div role="tabpanel" class="tab-pane active" id="eqtab">
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

        </form>
      </div>

      <!-- ====== Onglet Configuration (connexions) ====== -->
      <div role="tabpanel" class="tab-pane" id="configtab">
        <form class="form-horizontal">

          <fieldset>
            <legend>{{Robot Ecovacs}}</legend>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{DID du robot}}</label>
              <div class="col-sm-4">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="did"
                       placeholder="{{ex: 12345678901234567890}}" />
              </div>
              <div class="col-sm-5 help-block">
                {{Identifiant unique du robot (visible dans les logs du bridge / app Ecovacs). Obligatoire.}}
              </div>
            </div>
          </fieldset>

          <fieldset>
            <legend>{{Broker MQTT (Jeedom)}}</legend>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Hôte}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mqtt_host" placeholder="127.0.0.1" />
              </div>

              <label class="col-sm-2 control-label">{{Port}}</label>
              <div class="col-sm-2">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mqtt_port" placeholder="1883" />
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Utilisateur}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mqtt_user" />
              </div>

              <label class="col-sm-2 control-label">{{Mot de passe}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" type="password" data-l1key="configuration" data-l2key="mqtt_pass" />
              </div>
            </div>

            <div class="form-group">
              <div class="col-sm-9 col-sm-offset-3 help-block">
                {{Le plugin publie les commandes sur }}<code>deebot/&lt;DID&gt;/set</code>{{ et peut recevoir l’état sur }}<code>deebot/&lt;DID&gt;/state</code>{{ via jMQTT ou un webhook.}}
              </div>
            </div>
          </fieldset>

        </form>
      </div>

      <!-- ====== Onglet Commandes ====== -->
      <div role="tabpanel" class="tab-pane" id="commandtab">
        <a class="btn btn-default btn-sm pull-right" id="bt_addDeebotCmd" style="margin-bottom:10px;">
          <i class="fa fa-plus"></i> {{Ajouter une commande (optionnel)}}
        </a>
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
          <tbody>
            <?php
            /** @var eqLogic $eqLogic */
            $eqLogic = eqLogic::byId(init('id'));
            if (is_object($eqLogic)) {
              foreach ($eqLogic->getCmd() as $cmd) {
                echo $cmd->toHtml();
              }
            }
            ?>
          </tbody>
        </table>
      </div>

    </div><!-- /.tab-content -->

    <script>
      // Bouton Ajouter équipement
      $('#bt_addEqLogic').off('click').on('click', function () {
        jeedom.eqLogic.save({
          type: '<?php echo $plugin->getId(); ?>',
          eqLogics: [ { name: '{{Nouveau Deebot}}', isEnable: 1, isVisible: 1 } ],
          error: function (error) { $('#div_alert').showAlert({message: error.message, level: 'danger'}); },
          success: function () { location.reload(); }
        });
      });

      // Ajouter une ligne de commande vide (facultatif, pour du custom)
      $('#bt_addDeebotCmd').on('click', function () {
        addCmdToTable({type: 'action', subType: 'other', name: 'Custom'});
      });

      // Affichage/chargement de l’équipement sélectionné
      $('.li_eqLogic').off('click').on('click', function () {
        var eqId = $(this).data('eqlogic_id');
        jeedom.eqLogic.print({
          type: '<?php echo $plugin->getId(); ?>',
          id: eqId,
          status: 1,
          error: function (error) { $('#div_alert').showAlert({message: error.message, level: 'danger'}); },
          success: function (data) {
            $('.eqLogic').show().setValues(data, '.eqLogic');
            if (isset(data.category)) {
              for (const i in data.category) {
                $('.eqLogic .eqLogicAttr[data-l1key=category][data-l2key=' + i + ']').prop('checked', data.category[i] == 1);
              }
            }
            // recharge le tableau des commandes
            $('#table_cmd tbody').empty();
            for (const i in data.cmd) {
              addCmdToTable(data.cmd[i]);
            }
          }
        });
      });

      // Helpers jeedom
      function addCmdToTable(_cmd) {
        if (!isset(_cmd)) _cmd = {configuration: {}};
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td><span class="cmdAttr" data-l1key="id"></span></td>';
        tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom}}"></td>';
        tr += '<td><span class="type"></span><input class="cmdAttr" data-l1key="type" value="' + (init(_cmd.type) || 'action') + '" style="display:none;"></td>';
        tr += '<td><select class="cmdAttr form-control input-sm" data-l1key="subType"><option value="other">other</option><option value="other">other</option></select></td>';
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
    </script>

  </div><!-- /.eqLogic -->

</div>
