<?php
/* plugins/deebotcloud/core/class/deebotcloud.class.php */
require_once __DIR__ . '/../../../../core/php/core.inc.php';

class deebotcloud extends eqLogic {

  /**
   * Avant sauvegarde : contrôle des champs et valeurs par défaut
   */
  public function preSave() {
    // DID obligatoire
    if ($this->getConfiguration('did', '') == '') {
      throw new Exception(__('Le DID du robot est obligatoire', __FILE__));
    }

    // Valeurs par défaut prises depuis la configuration du plugin
    $pluginId = 'deebotcloud';

    if ($this->getConfiguration('mqtt_host', '') == '') {
      $this->setConfiguration('mqtt_host', config::byKey('mqtt_host', $pluginId, '127.0.0.1'));
    }
    if ($this->getConfiguration('mqtt_port', '') == '') {
      $this->setConfiguration('mqtt_port', config::byKey('mqtt_port', $pluginId, '1883'));
    }
    if ($this->getConfiguration('mqtt_user', '') == '') {
      $this->setConfiguration('mqtt_user', config::byKey('mqtt_user', $pluginId, ''));
    }
    if ($this->getConfiguration('mqtt_pass', '') == '') {
      $this->setConfiguration('mqtt_pass', config::byKey('mqtt_pass', ''));
    }
  }

  /**
   * Après sauvegarde : créer les commandes si absentes
   */
  public function postSave() {
    // Actions
    $this->createCmdIfMissing('start', 'Start', 'action');
    $this->createCmdIfMissing('pause', 'Pause', 'action');
    $this->createCmdIfMissing('stop',  'Stop',  'action');
    $this->createCmdIfMissing('dock',  'Dock',  'action');

    // Infos
    $this->createCmdIfMissing('battery', 'Battery', 'info', 'numeric', '%');
    $this->createCmdIfMissing('status',  'Status',  'info', 'string');

    // Petite log de synthèse
    log::add('deebotcloud', 'info', sprintf(
      'Équipement [%s] sauvegardé (DID=%s, MQTT=%s:%s)',
      $this->getHumanName(),
      $this->getConfiguration('did'),
      $this->getConfiguration('mqtt_host', '127.0.0.1'),
      $this->getConfiguration('mqtt_port', '1883')
    ));
  }

  /**
   * Création de commande si manquante
   */
  private function createCmdIfMissing($logicalId, $name, $type, $subType = null, $unite = null) {
    $cmd = $this->getCmd(null, $logicalId);
    if (!is_object($cmd)) {
      $cmd = new deebotcloudCmd();
      $cmd->setEqLogic_id($this->getId());
      $cmd->setLogicalId($logicalId);
      $cmd->setName($name);
      $cmd->setType($type);
      if ($subType !== null) $cmd->setSubType($subType);
      if ($unite !== null)   $cmd->setUnite($unite);
      $cmd->save();
      log::add('deebotcloud', 'debug', "Commande auto-créée : $logicalId ($name)");
    }
  }

  /**
   * Accès simple à la configuration MQTT de l'équipement
   */
  public function getMqttConfig() {
    return [
      'host' => $this->getConfiguration('mqtt_host', '127.0.0.1'),
      'port' => $this->getConfiguration('mqtt_port', '1883'),
      'user' => $this->getConfiguration('mqtt_user', ''),
      'pass' => $this->getConfiguration('mqtt_pass', ''),
      'did'  => $this->getConfiguration('did'),
      'topic_set' => 'deebot/' . $this->getConfiguration('did') . '/set',
      'topic_state'=> 'deebot/' . $this->getConfiguration('did') . '/state',
    ];
  }

  /**
   * Publication d'une action JSON vers le topic set
   */
  public function publishAction($action) {
    $action = trim($action);
    if ($action === '') {
      throw new Exception(__('Action vide', __FILE__));
    }
    $cfg = $this->getMqttConfig();

    // Vérifier la présence de mosquitto_pub
    $bin = trim(shell_exec('command -v mosquitto_pub 2>/dev/null'));
    if ($bin === '') {
      log::add('deebotcloud', 'error', 'mosquitto_pub introuvable. Installez le paquet mosquitto-clients.');
      throw new Exception(__('mosquitto_pub introuvable. Installez "mosquitto-clients".', __FILE__));
    }

    $payload = json_encode(['action' => $action], JSON_UNESCAPED_SLASHES);
    $cmd = escapeshellcmd($bin)
         . ' -h ' . escapeshellarg($cfg['host'])
         . ' -p ' . escapeshellarg($cfg['port'])
         . ($cfg['user'] !== '' ? ' -u ' . escapeshellarg($cfg['user']) : '')
         . ($cfg['pass'] !== '' ? ' -P ' . escapeshellarg($cfg['pass']) : '')
         . ' -t ' . escapeshellarg($cfg['topic_set'])
         . ' -m ' . escapeshellarg($payload)
         . ' -q 1';

    $result = shell_exec($cmd . ' 2>&1');
    log::add('deebotcloud', 'info', sprintf(
      'Action publiée [%s] sur %s (résultat: %s)', $action, $cfg['topic_set'], trim((string)$result)
    ));

    return $result;
  }

  /**
   * Mise à jour des infos : battery / status
   * Appelée par l’ajax pushState ou un scénario.
   */
  public function updateInfo($battery = null, $status = null) {
    if ($battery !== null && $battery !== '') {
      $this->checkAndUpdateCmd('battery', (int)$battery);
    }
    if ($status !== null && $status !== '') {
      $this->checkAndUpdateCmd('status', (string)$status);
    }
  }
}

/**
 * Commandes de l’équipement
 */
class deebotcloudCmd extends cmd {

  public function execute($_options = array()) {
    /** @var deebotcloud $eq */
    $eq = $this->getEqLogic();
    $logicalId = $this->getLogicalId();

    if ($this->getType() === 'action') {
      switch ($logicalId) {
        case 'start':
        case 'pause':
        case 'stop':
        case 'dock':
          return $eq->publishAction($logicalId);
        default:
          throw new Exception(__('Action inconnue : ', __FILE__) . $logicalId);
      }
    }

    // Les commandes info n'ont rien à exécuter
    return null;
  }
}
