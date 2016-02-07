<?php

require_once 'tournament.civix.php';
require_once 'util.php';


/**
 * Implementation of hook_civicrm_post
 */
function tournament_post($op, $objectName, $objectId, &$objectRef) {
	switch ($objectName) {
		case 'Contribution':
			$file = '/tmp/contributions.log';
			$message = strtr("Performed \"@op\" at @time on contribution #@id\n", array(
					'@op' => $op,
					'@time' => date('Y-m-d H:i:s'),
					'@id' => $objectId,
			));
			file_put_contents($file, $message, FILE_APPEND);
			break;
		default:
			// nothing to do
	}
}

	/**
	 * Implements hook_civicrm_config().
	 *
	 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
	 */
function tournament_civicrm_config(&$config) {
	_tournament_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function tournament_civicrm_xmlMenu(&$files) {
	_tournament_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function tournament_civicrm_install() {
	_tournament_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function tournament_civicrm_uninstall() {
	_tournament_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function tournament_civicrm_enable() {
	_tournament_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function tournament_civicrm_disable() {
	_tournament_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function tournament_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
	return _tournament_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function tournament_civicrm_managed(&$entities) {
	_tournament_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function tournament_civicrm_caseTypes(&$caseTypes) {
	_tournament_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function tournament_civicrm_angularModules(&$angularModules) {
	_tournament_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function tournament_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
	_tournament_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function tournament_civicrm_navigationMenu(&$menu) {
	$path = NULL;
	// 'civicrm'; //NULL

	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Tournament', array('domain' => 'org.agloa.tournament')),
			'name' => 'tournament',
			'url' => '',
			'permission' => 'add contacts',
			'operator' => 'OR',
			'separator' => 0,
	));

	$path = 'tournament';
	$url = 'civicrm/' . $path;

	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Dashboard', array('domain' => 'org.agloa.tournament')),
			'name' => 'tournament_dashboard',
			'url' => $url,
			'permission' => 'add contacts',
	));
	
		$record = named_profile_get("Billing Organization Profile");
		$id = $record["id"];
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Billing Organizations', array('domain' => 'org.agloa.tournament')),
			'name' => 'BillingOrganizations',
			'url' => "civicrm/profile?gid={$id}",
			'permission' => 'add contacts',
	));
	
	$record = named_report_get("Preliminary Estimates Summary");
	$id = $record["id"];
	_tournament_civix_insert_navigation_menu($menu, "{$path}/BillingOrganizations", array(
			'label' => ts('Preliminary Estimates', array('domain' => 'org.agloa.tournament')),
			'name' => 'PreliminaryEstimates',
			'url' => "civicrm/report/instance/{$id}?reset=1",
	));
	
	$record = named_group_get("Billing Contacts");
	$id = $record["id"];
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Billing Individuals', array('domain' => 'org.agloa.tournament')),
			'name' => 'BillingContacts',
			'url' => "civicrm/group/search?context=smog&gid={$id}&reset=1&force=1",
			'permission' => 'add contacts',
	));

	$name = "Profiles";
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Your players, coaches, etc.', array('domain' => 'org.agloa.tournament')),
			'name' => $name,
			'permission' => 'add contacts',
			'separator' => 1,
	));
	
	$path .= "/$name";

	// add a menu item for each of the sesstion billing contact's profiles
	$registrationProfiles  = $_SESSION['registrationProfiles'];

	foreach ($registrationProfiles as $profile) {
		$id = $profile["id"];
		$title = $profile["title"];
		_tournament_civix_insert_navigation_menu($menu, $path, array(
				'label' => ts($title, array('domain' => 'org.agloa.tournament')),
				'name' => "Profiles_{$id}",
		));
		_tournament_civix_insert_navigation_menu($menu, "{$path}/Profiles_{$id}", array(
				'label' => ts("List", array('domain' => 'org.agloa.tournament')),
				'name' => "Profiles_{$id}_list",
				'url' => "civicrm/profile?gid={$id}&reset=1&force=1",
		));
		_tournament_civix_insert_navigation_menu($menu, "{$path}/Profiles_{$id}", array(
				'label' => ts("Add new", array('domain' => 'org.agloa.tournament')),
				'name' => "Profiles_{$id}_add",
				'url' => "civicrm/profile/create?gid={$id}&reset=1",
		));
	}

	_tournament_civix_navigationMenu($menu);
}