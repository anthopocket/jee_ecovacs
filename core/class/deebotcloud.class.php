<?php
require_once __DIR__ . '/../../../../core/php/core.inc.php';

class deebotcloud extends eqLogic {
  public function preSave() {
    if ($this->getConfiguration('did', '') == '') {
      throw new Exception(__('Le DID du robot est obligatoire', __FILE__));
    }
    if ($this->getConfiguration('mqtt_host', '') == '') {
      $this->setConfiguration('mqtt_host', '127.0.0.1');
    }
    if ($this->getConfiguration('mqtt_port', '') == '') {
      $this->setConfiguration('mqtt_port', '1883');
    }
  }
  public function postSave() {
    $this->createCmdIfMissing('start', 'Start', 'action');
    $this->createCmdIfMissing('pause', 'Pause', 'action');
    $this->createCmdIfMissing('stop',  'Stop',  'action');
    $this->createCmdIfMissing('dock',  'Dock',  'action');
    $this->createCmdIfMissing('battery', 'Battery', 'info', 'numeric', '%');
    $this->createCmdIfMissing('status',  'Status',  'info', 'string');
  }
  private function createCmdIfMissing($logicalId, $name, $type, $subType = null, $unite = null) {
    $cmd = $this->getCmd(null, $logicalId);
    if (!is_object($cmd)) {
      $cmd = new deebotcloudCmd();
      $cmd->setName($name);
      $cmd->setEqLogic_id($this->getId());
      $cmd->setLogicalId($logicalId);
      $cmd->setType($type);
      if ($subType) $cmd->setSubType($subType);
      if ($unite) $cmd->setUnite($unite);
      $cmd->save();
    }
  }
  public function publishAction($action) {
    $did = $this->getConfiguration('did');
    $host = $this->getConfiguration('mqtt_host', '127.0.0.1');
    $port = $this->getConfiguration('mqtt_port', '1883');
    $user = $this->getConfiguration('mqtt_user', '');
    $pass = $this->getConfiguration('mqtt_pass', '');
    $payload = json_encode(['action' => $action], JSON_UNESCAPED_SLASHES);
    $topic   = 'deebot/' . $did . '/set';
    $cmd = 'mosquitto_pub -h ' . escapeshellarg($host) .
           ' -p ' . escapeshellarg($port) .
           ($user !== '' ? ' -u ' . escapeshellarg($user) : '') .
           ($pass !== '' ? ' -P ' . escapeshellarg($pass) : '') .
           ' -t ' . escapeshellarg($topic) .
           ' -m ' . escapeshellarg($payload);
    $result = shell_exec($cmd . ' 2>&1');
    log::add('deebotcloud','debug','Publish cmd: '.$cmd.' | result: '.$result);
    return $result;
  }
  public function updateInfo($battery = null, $status = null) {
    if ($battery !== null) {
      $this->checkAndUpdateCmd('battery', (int)$battery);
    }
    if ($status !== null) {
      $this->checkAndUpdateCmd('status', (string)$status);
    }
  }
}

class deebotcloudCmd extends cmd {
  public function execute($_options = array()) {
    $eq = $this->getEqLogic();
    $logicalId = $this->getLogicalId();
    if ($this->getType() == 'action') {
      switch ($logicalId) {
        case 'start': return $eq->publishAction('start');
        case 'pause': return $eq->publishAction('pause');
        case 'stop':  return $eq->publishAction('stop');
        case 'dock':  return $eq->publishAction('dock');
        default: throw new Exception(__('Action inconnue', __FILE__));
      }
    }
    return null;
  }
}
