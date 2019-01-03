<?php 
$baseKey = "menu::ap_invoices"; 
$newKey  = "ap::allow_non_po_invoices_district"; 
$results = Database::get( 
  "SELECT 
    DISTINCT profile_id 
  FROM 
    permission 
  WHERE 
    \"key\" = '{$baseKey}' AND 
    profile_id NOT IN 
      ( 
        SELECT 
          DISTINCT profile_id 
        FROM 
          permission 
        WHERE 
          \"key\" = '{$newKey}' 
      )" 
); 
 
Database::begin(); 
$permissions = [];
 
foreach ($results as $result) { 
  $profileId = $result["PROFILE_ID"]; 
 
  $permission = new Permission();
  $permission
    ->setProfileId($profileId) 
    ->setKey($newKey);

  $permissions[] = $permission;
} 

if ($permissions) {
  DatabaseObject::insert($permissions);
}
 
Database::commit(); 
?>