<?php

if (php_sapi_name() !== 'cli') exit();

require('../../../components.php');

$db     = OBFDB::get_instance();
$models = OBFModels::get_instance();

$bulk_settings = $models->BulkImport('load_overview');

if (!$bulk_settings[0]) {
  exit($bulk_settings[1]);
}

if (!is_writable(OB_ASSETS .'/uploads')) {
  exit("Uploads directory isn't writable.");
}

foreach ($bulk_settings[2] as $setting) {
  if (!is_writable($setting['dir_source']) ||
      !is_writable($setting['dir_failed']) ||
      !is_writable($setting['dir_target'])) {
    echo "[" . $setting['name'] .  "] One or more bulk import directories not writable.\n";
    continue;
  }

  foreach (new FilesystemIterator($setting['dir_source']) as $file) {
    if ($file->isDir()) {
      continue;
    }

    $src    = $file->getPathname();
    $fn     = $file->getFilename();
    $info   = $models->media('media_info', ['filename' => $src]);
    $expiry = time() + 86400;
    $key    = bin2hex(openssl_random_pseudo_bytes(16));

    if (file_exists($setting['dir_source'] . "/.locks/" . $fn . ".lock")) {
      echo "File is locked. Skipping for now.\n";
      continue;
    }

    $file_id = $db->insert('uploads', array(
      'key'      => $key,
      'expiry'   => $expiry,
      'type'     => $info['type'],
      'format'   => $info['format'],
      'duration' => $info['duration']
    ));

    if (!$file_id) {
      rename($src, $setting['dir_failed'] . "/" . $fn);
      cleanup_uploads($file_id);
      continue;
    }

    $tgt = OB_ASSETS . '/uploads/' . $file_id;
    if (!copy($src, $tgt)) {
      rename($src, $setting['dir_failed'] . "/" . $fn);
      cleanup_uploads($file_id);
      continue;
    }

    $item = array(
      'file_id'   => $file_id,
      'file_info' => $info,
      'title'     => pathinfo($src, PATHINFO_FILENAME),
      'local_id'  => 1,
      'owner_id'  => $setting['owner_id']
    );
    foreach (json_decode($setting['settings']) as $field => $value) {
      $item[$field] = $value;
    }

    // replace with ID3 fields depending on setting.
    $id3 = $models->media('getid3', ['filename' => $src]);
    foreach (json_decode($setting['id3']) as $field => $use_id3) {
      if ($use_id3 && isset($id3[$field])) {
        $item[$field] = $id3[$field][0];
      }
    }

    $valid = $models->media('validate', ['item' => $item]);
    if (!$valid[0]) {
      echo "Validation error: " . $valid[2] . "\n";
      rename($src, $setting['dir_failed'] . "/" . $fn);
      cleanup_uploads($file_id);
      continue;
    }

    $media_id = $models->media('save', ['item' => $item]);
    $title    = pathinfo($src, PATHINFO_FILENAME);
    $ext      = pathinfo($src, PATHINFO_EXTENSION);
    if (!$media_id) {
      if (file_exists($setting['dir_failed'] . "/" . $fn)) {
        $i = 1;
        while (file_exists($setting['dir_failed'] . "/" . $title . "." . $i . "." . $ext)) {
          $i++;
        }
        rename($src, $setting['dir_failed'] . "/" . $title . "." . $i . "." . $ext);
      } else {
        rename($src, $setting['dir_failed'] . "/" . $fn);
      }
      cleanup_uploads($file_id);
      continue;
    }

    if (file_exists($setting['dir_target'] . "/" . $fn)) {
      $i = 1;
      while (file_exists($setting['dir_target'] . "/" . $title . "." . $i . "." . $ext)) {
        $i++;
      }
      rename($src, $setting['dir_target'] . "/" . $title . "." . $i . "." . $ext);
    } else {
      rename($src, $setting['dir_target'] . "/" . $fn);
    }
    echo "[" . $setting['name'] . "] Processed file: " . $fn . "\n";
    cleanup_uploads($file_id);
  }
}

function cleanup_uploads ($file_id) {
  $db = OBFDB::get_instance();
  $db->where('id', $file_id);
  $db->delete('uploads');

  @unlink(OB_ASSETS . '/uploads/' . $file_id);
}
