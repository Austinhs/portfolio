<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sql =
	"SELECT 
		i.id, 
		l.log_time 
	FROM 
		gl_fa_inventory i 
	JOIN 
		database_object_log l 
	ON 
		l.record_id = i.id AND 
		l.after LIKE '%\"FINALIZED\"%' 
	WHERE 
		i.finalized > 1";
$res = Database::get($sql);

Database::begin();

foreach ($res as $data) {
	$id   = $data["ID"];
	$date = $data["LOG_TIME"];
	
	(new FAInventory($id))
		->setFinalized(1)
		->setFinalizedDate($date)
		->persist();
}

Database::commit();
return true;
?>