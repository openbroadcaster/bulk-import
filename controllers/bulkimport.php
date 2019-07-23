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
          $key == 'dir_target' ||
          $key == 'isnew' ||
          $key == 'id' ||
          $key == 'name' ) {
        continue;
      }

      $settings[$key] = $value;
    }

    $data = array(
      'name'        => $this->data['name'],
      'id'          => $this->data['id'],
      'directories' => array(
        'dir_source'  => $this->data['dir_source'],
        'dir_failed'  => $this->data['dir_failed'],
        'dir_target'  => $this->data['dir_target']
      ),
      'settings'    => $settings,
      'isnew'       => $this->data['isnew']
    );

    $result = $this->model('validate_settings', $data);
    if (!$result[0]) {
      return $result;
    }

    return $this->model('update_settings', $data);
  }

  public function load_settings () {
    $data['id'] = $this->data['id'];

    return $this->model('load_settings', $data);
  }

  public function load_overview () {
    return $this->model('load_overview');
  }

  public function delete_settings () {
    $data['id'] = $this->data['id'];

    return $this->model('delete_settings', $data);
  }

}
