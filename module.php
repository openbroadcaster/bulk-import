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