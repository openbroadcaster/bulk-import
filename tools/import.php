<?php

if (php_sapi_name() !== 'cli') exit();

require('../../../components.php');

$load = OBFLoad::get_instance();
$db   = OBFDB::get_instance();

$media_model   = $load->model('media');
$bulk_model    = $load->model('BulkImport');
$bulk_settings = $bulk_model('load_settings');

if (!$bulk_settings[0]) {
  exit($bulk_settings[1]);
}

$directories = $bulk_settings[2]->directories;
$defaults    = $bulk_settings[2]->settings;

if (!is_writable($directories->dir_source) ||
    !is_writable($directories->dir_failed) ||
    !is_writable($directories->dir_target)) {
  exit("One or more bulk import directories not writable by script.");
}

if (!is_writable(OB_ASSETS . '/uploads')) {
  exit("Uploads directory isn't writable.");
}

foreach (new FilesystemIterator($directories->dir_source) as $file) {
  if ($file->isDir()) {
    continue;
  }

  $src  = $file->getPathname();
  $fn = $file->getFilename();
  $info = $media_model('media_info', $src);
  $expiry = time() + 86400;
  $key = bin2hex(openssl_random_pseudo_bytes(16));

  $file_id = $db->insert('uploads', array(
    'key'      => $key,
    'expiry'   => $expiry,
    'type'     => $info['type'],
    'format'   => $info['format'],
    'duration' => $info['duration']
  ));

  if (!$file_id) {
    rename($src, $directories->dir_failed . "/" . $fn);
    cleanup_uploads($file_id);
    continue;
  }

  $tgt = OB_ASSETS . '/uploads/' . $file_id;
  if (!copy($src, $tgt)) {
    rename($src, $directories->dir_failed . "/" . $fn);
    cleanup_uploads($file_id);
    continue;
  }

  $item = array(
    'file_id'   => $file_id,
    'file_info' => $info,
    'title'     => pathinfo($src, PATHINFO_FILENAME),
    'local_id'  => 1
  );
  foreach ($defaults as $field => $value) {
    $item[$field] = $value;
  }

  $valid = $media_model('validate', $item);
  if (!$valid[0]) {
    echo "Validation error: " . $valid[2] . "\n";
    rename($src, $directories->dir_failed . "/" . $fn);
    cleanup_uploads($file_id);
    continue;
  }

  $media_id = $media_model('save', $item);
  if (!$media_id) {
    rename($src, $directories->dir_failed . "/" . $fn);
    cleanup_uploads($file_id);
    continue;
  }

  rename($src, $directories->dir_target . "/" . $fn);
  echo "Processed file: " . $fn . "\n";
  cleanup_uploads($file_id);

}

function cleanup_uploads ($file_id) {
  $db = OBFDB::get_instance();
  $db->where('id', $file_id);
  $db->delete('uploads');

  @unlink(OB_ASSETS . '/uploads/' . $file_id);
}
