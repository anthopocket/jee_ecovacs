<?php
if (!isConnect()) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$eqLogics = eqLogic::byType('deebotcloud');
?>
<div class="row">
<?php foreach ($eqLogics as $eqLogic) { ?>
  <div class="col-sm-6 col-lg-4">
    <div class="eqLogic-widget" data-eqLogic_id="<?php echo $eqLogic->getId() ?>">
      <legend><?php echo $eqLogic->getHumanName(true,true) ?></legend>
      <div class="form-group">
        <label class="control-label">{{DID}}</label>
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="did" value="<?php echo $eqLogic->getConfiguration('did') ?>"/>
      </div>
      <div class="form-group">
        <label class="control-label">{{MQTT Hôte}}</label>
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mqtt_host" value="<?php echo $eqLogic->getConfiguration('mqtt_host','127.0.0.1') ?>"/>
      </div>
      <div class="form-group">
        <label class="control-label">{{MQTT Port}}</label>
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mqtt_port" value="<?php echo $eqLogic->getConfiguration('mqtt_port','1883') ?>"/>
      </div>
      <div class="cmds">
        <?php foreach ($eqLogic->getCmd('action') as $cmd) { ?>
          <a class="btn btn-primary execute" data-cmd_id="<?php echo $cmd->getId() ?>"><?php echo $cmd->getName() ?></a>
        <?php } ?>
      </div>
      <div class="infos">
        <?php foreach ($eqLogic->getCmd('info') as $cmd) { ?>
          <div>
            <span><?php echo $cmd->getName() ?> :</span>
            <span class="cmd" data-cmd_id="<?php echo $cmd->getId() ?>"><?php echo $cmd->execCmd() ?></span>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
<?php } ?>
</div>
