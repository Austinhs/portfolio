<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5469');

if(!method_exists('CustomFieldObject', 'upgradeCustomFields')) {
	throw new Exception("This migration cannot be completed in this version");
}

CustomFieldObject::upgradeCustomFields(['SISStudent', 'SISUser']);
