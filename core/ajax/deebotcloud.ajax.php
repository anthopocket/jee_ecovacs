<?php
try {
  require_once __DIR__ . '/../../../../core/php/core.inc.php';
  include_file('core', 'deebotcloud', 'class', 'deebotcloud');
  if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
  }
  $action = init('action');
  if ($action == 'testPublish') {
    $eqId = init('eqId');
    $cmd  = init('cmd');
    $eq = deebotcloud::byId($eqId);
    if (!is_object($eq)) throw new Exception(__('Équipement introuvable', __FILE__));
    ajax::success($eq->publishAction($cmd));
  }
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
