<?php
/* plugins/deebotcloud/core/ajax/deebotcloud.ajax.php */
try {
  require_once __DIR__ . '/../../../../core/php/core.inc.php';
  include_file('core', 'deebotcloud', 'class', 'deebotcloud');

  if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
  }

  $pluginId = 'deebotcloud';
  $action = init('action');

  // ----------- DÉCOUVERTE VIA MQTT RETAINED -----------
  if ($action == 'discover') {
    $host = config::byKey('mqtt_host', $pluginId, '127.0.0.1');
    $port = config::byKey('mqtt_port', $pluginId, '1883');
    $user = config::byKey('mqtt_user', $pluginId, '');
    $pass = config::byKey('mqtt_pass', $pluginId, '');

    // On lit jusqu'à 20 messages retained sur le topic discovery
    $cmd = 'mosquitto_sub -v -h ' . escapeshellarg($host)
         . ' -p ' . escapeshellarg($port)
         . ($user !== '' ? ' -u ' . escapeshellarg($user) : '')
         . ($pass !== '' ? ' -P ' . escapeshellarg($pass) : '')
         . ' -t ' . escapeshellarg('deebot/+/state')
         . ' -C 20 -W 2';

    $output = shell_exec($cmd . ' 2>&1');
    if ($output === null) {
      ajax::error(__('Échec d’exécution mosquitto_sub', __FILE__));
    }

    $lines = array_filter(array_map('trim', explode("\n", $output)));
    $seen = [];
    $devices = [];

    foreach ($lines as $line) {
      // format attendu : "deebot/<did>/state {json}"
      $spacePos = strpos($line, ' ');
      if ($spacePos === false) continue;
      $topic = substr($line, 0, $spacePos);
      $payload = substr($line, $spacePos + 1);

      $parts = explode('/', $topic);
      if (count($parts) < 3) continue;
      if ($parts[0] !== 'deebot' || $parts[2] !== 'state') continue;

      $did = $parts[1];
      if (isset($seen[$did])) continue;

      $data = json_decode($payload, true);
      if (!is_array($data)) $data = [];

      $devices[] = [
        'did'     => $did,
        'name'    => isset($data['name']) ? $data['name'] : '',
        'battery' => isset($data['battery']) ? $data['battery'] : null,
        'status'  => isset($data['status']) ? $data['status'] : null,
        'raw'     => $data,
      ];
      $seen[$did] = true;
    }

    ajax::success($devices);
  }

  // ----------- CRÉER UN ÉQUIPEMENT À PARTIR D’UN DID -----------
  if ($action == 'createEq') {
    $did  = trim(init('did'));
    $name = trim(init('name', ''));
    if ($did == '') {
      throw new Exception(__('DID manquant', __FILE__));
    }
    if ($name == '') {
      $name = 'Deebot ' . substr($did, -4);
    }

    $mqtt_host = config::byKey('mqtt_host', $pluginId, '127.0.0.1');
    $mqtt_port = config::byKey('mqtt_port', $pluginId, '1883');
    $mqtt_user = config::byKey('mqtt_user', $pluginId, '');
    $mqtt_pass = config::byKey('mqtt_pass', $pluginId, '');

    // Éviter les doublons (un eq par DID)
    foreach (eqLogic::byType('deebotcloud') as $eq) {
      if ($eq->getConfiguration('did') == $did) {
        ajax::error(__('Un équipement existe déjà pour ce DID', __FILE__));
      }
    }

    $eq = new deebotcloud();
    $eq->setEqType_name('deebotcloud');
    $eq->setName($name);
    $eq->setIsEnable(1);
    $eq->setIsVisible(1);
    $eq->setConfiguration('did', $did);
    $eq->setConfiguration('mqtt_host', $mqtt_host);
    $eq->setConfiguration('mqtt_port', $mqtt_port);
    if ($mqtt_user !== '') $eq->setConfiguration('mqtt_user', $mqtt_user);
    if ($mqtt_pass !== '') $eq->setConfiguration('mqtt_pass', $mqtt_pass);
    $eq->save();

    ajax::success(['eqId' => $eq->getId(), 'name' => $eq->getName()]);
  }

  // ----------- TEST PUBLISH EXISTANT -----------
  if ($action == 'testPublish') {
    $eqId = init('eqId');
    $cmd  = init('cmd');
    $eq = deebotcloud::byId($eqId);
    if (!is_object($eq)) throw new Exception(__('Équipement introuvable', __FILE__));
    ajax::success($eq->publishAction($cmd));
  }

  // ----------- PUSH STATE (webhook depuis le bridge) -----------
  if ($action == 'pushState') {
    $did = init('did');
    $battery = init('battery', null);
    $status  = init('status', null);
    $eqList = eqLogic::byType('deebotcloud');
    foreach ($eqList as $eq) {
      if ($eq->getConfiguration('did') == $did) {
        $eq->updateInfo($battery, $status);
        ajax::success('ok');
      }
    }
    throw new Exception(__('DID non associé', __FILE__));
  }

  throw new Exception(__('Action non supportée', __FILE__));

} catch (Exception $e) {
  ajax::error($e->getMessage());
}
