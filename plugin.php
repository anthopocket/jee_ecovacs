<?php
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
$plugin = plugin::byId('deebotcloud');
sendVarToJS('eqType', 'deebotcloud');
include_file('core', 'deebotcloud', 'class', 'deebotcloud');
include_file('desktop', 'deebotcloud', 'php', 'deebotcloud');
