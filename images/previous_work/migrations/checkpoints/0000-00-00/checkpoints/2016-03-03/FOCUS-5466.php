<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5468');

if(!method_exists('SISAddress', 'upgradeAddresses')) {
	throw new Exception("This migration cannot be completed in this version");
}

SISAddress::upgradeAddresses();
