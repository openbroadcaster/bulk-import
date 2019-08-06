<?php

if (php_sapi_name() !== 'cli') exit();
chdir(__DIR__);

require('../../../components.php');

$load = OBFLoad::get_instance();
$db   = OBFDB::get_instance();

$bulk_model    = $load->model('BulkImport');
$bulk_settings = $bulk_model('load_overview');

if (!$bulk_settings[0]) {
  exit($bulk_settings[1]);
}

if (posix_getuid() != 0) {
  exit("Locking script needs to be run as root.\n");
}

foreach ($bulk_settings[2] as $source) {
  if (!is_writable($source['dir_source'])) {
    echo $source['dir_source'] . " is not a writable directory.\n";
    continue;
  }

  exec(__DIR__ . '/inotify_lock.sh ' . $source['dir_source'] . ' >2 &');
}
