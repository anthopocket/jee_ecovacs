/* global jeedom */
$('.eqLogic-widget').on('click', '.execute', function () {
  const cmd_id = $(this).data('cmd_id');
  jeedom.cmd.execute({id: cmd_id});
});
function deebotcloud_test(eqId, action) {
  $.ajax({
    type: "POST",
    url: "plugins/deebotcloud/core/ajax/deebotcloud.ajax.php",
    data: { action: "testPublish", eqId: eqId, cmd: action },
    dataType: 'json',
    error: function (request, status, error) {
      $('#div_alert').showAlert({message: error, level: 'danger'});
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      $('#div_alert').showAlert({message: 'OK: '+data.result, level: 'success'});
    }
  });
}
