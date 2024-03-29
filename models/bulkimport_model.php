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

    if (!isset($data['owner_id'][0])) {
      return [false, "Owner ID required for imported media."];
    }
    $user_model = $this->load->model('Users');
    if (!$user_model('get_by_id', $data['owner_id'][0])) {
      return [false, 'Failed to find owner ID in database.'];
    }

    if ($data['isnew'] != 'true') {
      if (empty($data['id'])) return [false, "No ID given for updating directory settings."];

      $this->db->where('id', $data['id']);
      $result = $this->db->get_one('module_bulk_import');
      if (!$result) {
        return [false, "Couldn't find directory settings with given ID."];
      }
    }

    $models = OBFModels::get_instance();
    $settings = $data['settings'];
    $settings['local_id'] = 1;
    $settings['title'] = 'undefined';

    $valid = $models->media('validate', ['item' => $settings, 'skip_upload_check' => true]);
    if (!$valid[0]) {
      return [false, $valid[2]];
    }

    return [true, "Data is valid."];
  }

  public function update_settings ($data) {
    $json   = json_encode($data['settings']);
    $id3    = json_encode($data['id3']);
    $fields = [
      'name'        => htmlspecialchars($data['name']),
      'description' => htmlspecialchars($data['description']),
      'dir_source'  => $data['directories']['dir_source'],
      'dir_failed'  => $data['directories']['dir_failed'],
      'dir_target'  => $data['directories']['dir_target'],
      'settings'    => $json,
      'id3'         => $id3,
      'owner_id'    => $data['owner_id'][0]
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
