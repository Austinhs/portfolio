<?php
	error_reporting(0);
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
	ini_set('display_errors', 1);
	
	//Require Warehouse
	require("../Warehouse.php");

	//Include JQuery
	echo "<script src='{$GLOBALS['FocusURL']}/assets/jquery/jquery.js'></script>";
	//Include Handlebars
	echo "<script src='{$GLOBALS['FocusURL']}/assets/handlebars/handlebars.min.js'></script>";

	//Get the User
	$user    = Authenticate::getCurrent();
	//Check if SISUser
	$is_user = get_class($user) === 'SISUser';

	//Die if Not Super User
	if(!($is_user && $user->isSuperUser())) {
		//Exit if not Admin
		die('You do not have permission to use this page');
	}

	//QPD Preseeded Data for FLShots Staging Environment
	$preseeded = [
		//A QBP query containing the following QPD segment will return “too many results”
		'QPD|Z34^Request Immunization History^HL70471|Query38a|AB589^^^^MR|MYXX^R^^^^^L||20120101|F||^^^^^000^0000000|Y|3',
		//A QBP query containing the following QPD segment will return multiple matches:
		'QPD|Z34^Request Immunization History^HL70471|Query38a|477157789^^^^SS|MYXX^RIANNA^^^^^L||20120101|F||^^^^^000^0000000|Y|3',
		//A QBP query containing the following QPD segment will return an exact match (and, thus, include Immunization Status and Vaccinations for the client):
		'QPD|Z34^Request Immunization History^HL70471|Query38a|ABC125^^^^MR~155116789^^^^SS|MYXX^JODY^A^^^^L||20120101|F|3453 AHEPB AD^^TALLAHASSEE^FL^32356^USA^H|^^^^^000^0000000|Y|3',
		//A QBP query containing the following QPD segment will return “no match found”:
		'QPD|Z34^Request Immunization History^HL70471|Query38a|AB589^^^^MR|MYRR^X^^^^^L||20120101|F||^^^^^000^0000000|Y|3',
	];

	//Test Scenarios for FLShots Testing Environment
	$scenarios = [
        'Too Many Results',
        'Too Many Matches',
        'Match Found',
        'Matches were not found!',
	];

	//Prepare the Context
	for($i = 0; $i < count($scenarios); $i++) {
		$context['utility'][$i]['test']  = $scenarios[$i];
		$context['utility'][$i]['query'] = $preseeded[$i];
	}

	$context['builder']['patient_identifier'] = [
		//Social Security Number
		'SS',
		//State Immunization Identifier
		'SR',
		//Medical Record Number
		'MR',
	];

	$context['builder']['gender'] = [
		//Male
		'Male',
		//Female
		'Female',
	];
	
	//Focus Module Standard Path
	$paths[] = FocusModule::STANDARD;
	//FLShots Path
	$paths[] = realpath('../modules/CustomFields/SISStudent/flshots');

	//Entry Point for JavaScript
	echo '<div class="flshot_utilities"></div>';

	//Render the Focus Module
	FocusModule::render($paths, $context);

