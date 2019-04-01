<?php

class BulkImport extends OBFController {
  public function __construct () {
    parent::__construct();
    $this->model = $this->load->model('BulkImport');
    $this->user->require_permission('bulk_import_module');
  }
  
  public function update_settings () {
    $settings = array();
    foreach ($this->data as $key => $value) {
      if ($key == 'dir_source' || 
          $key == 'dir_failed' || 
          $key == 'dir_target') {
        continue;
      }
      
      $settings[$key] = $value;
    }
    
    $data = array(
      'directories' => array(
        'dir_source' => $this->data['dir_source'],
        'dir_failed' => $this->data['dir_failed'],
        'dir_target' => $this->data['dir_target']
      ),
      'settings' => $settings
    );
    
    $result = $this->model('validate_settings', $data);
    if (!$result[0]) { 
      return $result;
    }
    
    return $this->model('update_settings', $data);
  }
  
  public function load_settings () {
    return $this->model('load_settings');
  }
}