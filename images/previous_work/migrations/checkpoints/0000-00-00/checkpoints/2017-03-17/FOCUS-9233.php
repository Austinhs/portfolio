<?php

$referral_fields = Database::get("SELECT ID, SORT_ORDER FROM discipline_referrals_fields WHERE SORT_ORDER IS NOT NULL");

// first sort the results using the existing sort_order value
usort($referral_fields, function($a, $b) {
	// '-1' indicates 'last', so represent it with a reasonably large positive integer during sorting
	$a = $a['SORT_ORDER']==='-1' ? 1000 : $a['SORT_ORDER'];
	$b = $b['SORT_ORDER']==='-1' ? 1000 : $b['SORT_ORDER'];

	return $a - $b;
});

$max = 0;
$seen = array();
foreach($referral_fields as &$field)
{
	if($field['SORT_ORDER'] === '-1')
	{
		//move to the end
		$field['SORT_ORDER'] = $max + 1;
	}

	while(in_array($field['SORT_ORDER'], $seen))
	{
		//duplicate value, increment until unique
		$field['SORT_ORDER']++;
	}

	array_push($seen, $field['SORT_ORDER']);
	$max = max($seen);

	//update database with new sort order
	Database::query("UPDATE discipline_referrals_fields SET SORT_ORDER = ".$field['SORT_ORDER']." WHERE ID = ".$field['ID']);
}
