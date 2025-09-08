<?php
if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
  <fieldset>
    <legend>{{Paramètres par défaut MQTT}}</legend>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Hôte}}</label>
      <div class="col-sm-3">
        <input class="configKey form-control" data-l1key="mqtt_host" placeholder="127.0.0.1"/>
      </div>
      <label class="col-sm-3 control-label">{{Port}}</label>
      <div class="col-sm-3">
        <input class="configKey form-control" data-l1key="mqtt_port" placeholder="1883"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Utilisateur}}</label>
      <div class="col-sm-3">
        <input class="configKey form-control" data-l1key="mqtt_user"/>
      </div>
      <label class="col-sm-3 control-label">{{Mot de passe}}</label>
      <div class="col-sm-3">
        <input class="configKey form-control" data-l1key="mqtt_pass" type="password"/>
      </div>
    </div>
  </fieldset>
</form>
