<?php

class BulkImportModel extends OBFModel {
  public function validate_settings ($data) {
    if (!is_writable($data['directories']['dir_source'])) {
      return [false, "Source directory isn't writable by server."];
    }
    if (!is_writable($data['directories']['dir_failed'])) {
      return [false, "Failed directory isn't writable by server."];
    }
    if (!is_writable($data['directories']['dir_target'])) {
      return [false, "Target directory isn't writable by server."];
    }

    $media_model = $this->load->model('Media');
    $settings = $data['settings'];
    $settings['local_id'] = 1;
    $settings['title'] = 'undefined';

    $valid = $media_model('validate', $settings, true);
    if (!$valid[0]) {
      return [false, $valid[2]];
    }

    return [true, "Data is valid."];
  }

  public function update_settings ($data) {
    $json = json_encode($data);

    $this->db->where('name', 'bulk_import_settings');
    $this->db->update('settings', [
      'name'  => 'bulk_import_settings',
      'value' => $json
    ]);

    return [true, 'Updated bulk import settings.'];
  }

  public function load_settings () {
    $this->db->where('name', 'bulk_import_settings');
    $result = $this->db->get_one('settings');

    if (!$result) {
      return [false, 'Failed to load settings from database.'];
    }

    return [true, 'Successfully loaded settings.', json_decode($result['value'])];
  }
}
