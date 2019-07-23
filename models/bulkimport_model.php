<?php

class BulkImportModel extends OBFModel {
  public function validate_settings ($data) {
    if (empty($data['name'])) {
      return [false, "Name cannot be an empty string."];
    }

    if (!is_writable($data['directories']['dir_source'])) {
      return [false, "Source directory isn't writable by server."];
    }
    if (!is_writable($data['directories']['dir_failed'])) {
      return [false, "Failed directory isn't writable by server."];
    }
    if (!is_writable($data['directories']['dir_target'])) {
      return [false, "Target directory isn't writable by server."];
    }

    if ($data['isnew'] != 'true') {
      if (empty($data['id'])) return [false, "No ID given for updating directory settings."];

      $this->db->where('id', $data['id']);
      $result = $this->db->get_one('module_bulk_import');
      if (!$result) {
        return [false, "Couldn't find directory settings with given ID."];
      }
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
    $json   = json_encode($data['settings']);
    $fields = [
      'name'       => htmlspecialchars($data['name']),
      'dir_source' => $data['directories']['dir_source'],
      'dir_failed' => $data['directories']['dir_failed'],
      'dir_target' => $data['directories']['dir_target'],
      'settings'   => $json
    ];

    if ($data['isnew'] != 'true') {
      $this->db->where('id', $data['id']);
      $this->db->update('module_bulk_import', $fields);

      return [true, 'Updated bulk import directory settings.'];
    } else {
      $this->db->insert('module_bulk_import', $fields);
      return [true, 'Added bulk import directory settings.'];
    }
  }

  public function load_settings ($data) {
    $this->db->where('id', $data['id']);
    $result = $this->db->get_one('module_bulk_import');

    if (!$result) {
      return [false, 'Failed to load settings from database.'];
    }

    return [true, 'Successfully loaded settings.', $result];
  }

  public function load_overview () {
    return [true, 'Successfully loaded directory settings.', $this->db->get('module_bulk_import')];
  }

  public function delete_settings ($data) {
    $this->db->where('id', $data['id']);
    $result = $this->db->delete('module_bulk_import');

    if (!$result) {
      return [false, 'Failed to remove bulk directory settings.'];
    } else {
      return [true, 'Successfully removed bulk directory settings.'];
    }
  }
}
