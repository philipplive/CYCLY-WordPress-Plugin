<?php
/**
 * CYCLY Connector
 *
 * @package           	CYCLY
 * @author            	CYCLY (Optimanet Schweiz AG)
 * @copyright         	2021 Optimanet Schweiz AG
 * @contact				https://cycly.ch/kontakt/
 *
 * Plugin Name:       	CYCLY Connector
 * Plugin URI:        	https://cycly.ch
 * Description:       	Ermöglicht das Einbinden von Daten aus CYCLY direkt auf Ihre Website.
 * Version:           	0.9.11.1
 * Requires at least: 	5.0
 * Requires PHP:     	7.4
 * Author:           	CYCLY (Optimanet Schweiz AG)
 * Author URI:      	https://optimanet.ch
 * License:          	Apache License (V2)
 * GitHubId:			301326603
 */

// HfCore
if (!class_exists('HfCore\System'))
	require_once('core/System.php');

// Projekt Cycly
require_once('objects/Item.php');
require_once('objects/Branch.php');
require_once('objects/OpeningHours.php');
require_once('objects/OpeningHoursHour.php');
require_once('objects/OpeningHoursIrregular.php');
require_once('CyclyApi.php');
require_once('CyclySystem.php');

// Frontend / Backend
if (is_admin()) {
	require_once('CyclyBackend.php');
	new CyclyBackend();
}
else {
	require_once('objects/Image.php');
	require_once('objects/BlogEntry.php');
	require_once('objects/VehicleCategory.php');
	require_once('objects/VehicleType.php');
	require_once('objects/VehicleParameter.php');
	require_once('objects/Vehicle.php');
	require_once('objects/Employee.php');
	require_once('CyclyFrontendBlog.php');
	require_once('CyclyFrontendVehicles.php');
	require_once('CyclyFrontendEmployees.php');
	require_once('CyclyFrontend.php');
	new CyclyFrontend();
}

