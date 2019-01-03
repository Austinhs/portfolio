<?php
$keys       = [
	"gl::view_all_recons"     => "gl::ia_view_all_recons",
	"gl::allow_upload_recons" => "gl::ia_allow_upload_recons",
	"gl::allow_manual_recons" => "gl::ia_allow_manual_recons",
	"gl::allow_edit_recons"   => "gl::ia_allow_edit_recons",
	"gl::allow_delete_recons" => "gl::ia_allow_delete_recons"
];
$keysString = implode("','", array_keys($keys));
$results    = Database::get(
  "SELECT
    profile_id,
    \"key\"
  FROM
    " . Permission::$table . "
  WHERE
    \"key\" IN ('{$keysString}')"
);

Database::begin();

foreach ($results as $result) {
  $profileId = $result["PROFILE_ID"];
  $key       = $result["KEY"];
  $newKey    = $keys[$key];

  $exists    = Permission::getOne([
  	"\"key\" = '{$newKey}'",
  	"profile_id = {$profileId}"
  ]);

  if ($exists) {
  	continue;
  }

  (new Permission())
    ->setProfileId($profileId)
    ->setKey($newKey)
    ->persist();
}

Database::commit();
?>