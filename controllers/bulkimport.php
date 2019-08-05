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
          $key == 'name' ||
          $key == 'description' ||
          $key == 'id3_title' || $key == 'id3_album' ||
          $key == 'id3_artist' || $key == 'id3_comments' ||
          $key == 'owner_id') {
        continue;
      }

      $settings[$key] = $value;
    }

    $data = array(
      'name'        => $this->data['name'],
      'description' => $this->data['description'],
      'id'          => $this->data['id'],
      'directories' => array(
        'dir_source'  => $this->data['dir_source'],
        'dir_failed'  => $this->data['dir_failed'],
        'dir_target'  => $this->data['dir_target']
      ),
      'settings'    => $settings,
      'isnew'       => $this->data['isnew'],
      'id3'         => array(
        'artist'      => $this->data['id3_artist'],
        'album'       => $this->data['id3_album'],
        'title'       => $this->data['id3_title'],
        'comments'    => $this->data['id3_comments']
      ),
      'owner_id'    => $this->data['owner_id']
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
