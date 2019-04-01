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
    
    $metadata_model = $this->load->model('MediaMetadata');
    $req_fields = $metadata_model('get_fields')[2];
        
    foreach ($req_fields as $field => $req) {
      if ($req == 'required' && 
          (!isset($data['settings'][$field]) ||
          $data['settings'][$field] == '')) {
        return [false, "Not all required fields filled out.", $field];
      }
    }
    
    /* Edge case in case category is set but genre somehow is not,
       yet still required in the metadata settings. */
    if (isset($req_fields['category_id']) &&
        $req_fields['category_id'] == 'required' &&
        (!isset($data['settings']['genre_id']) ||
        $data['settings']['genre_id'] == '')) {
      return [false, "Not all required fields filled out.", 'genre_id'];
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