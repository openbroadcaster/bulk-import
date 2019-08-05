<?php

class BulkImportModule extends OBFModule {

  public $name        = "Bulk Import v0.1";
  public $description = "Imports bulk media items from predetermined folders.";

  public function callbacks () {

  }

  public function install () {
    $this->db->insert('users_permissions', [
      'category'    => 'administration',
      'description' => 'import bulk media items',
      'name'        => 'bulk_import_module'
    ]);

    $this->db->query('CREATE TABLE IF NOT EXISTS `module_bulk_import` (
      `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `description` text,
      `dir_source` varchar(255) NOT NULL,
      `dir_failed` varchar(255) NOT NULL,
      `dir_target` varchar(255) NOT NULL,
      `settings` text,
      `id3` text,
      `owner_id` int(10) UNSIGNED NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

    return true;
  }

  public function uninstall () {
    $this->db->where('name', 'bulk_import_module');
    $permission = $this->db->get_one('users_permissions');

    $this->db->where('permission_id', $permission['id']);
    $this->db->delete('users_permissions_to_groups');

    $this->db->where('id', $permission['id']);
    $this->db->delete('users_permissions');

    return true;
  }
}
