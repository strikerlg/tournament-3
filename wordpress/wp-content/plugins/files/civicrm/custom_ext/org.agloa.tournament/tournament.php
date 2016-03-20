<?php
/* TODO
 * Invoices - cost sheet
 */
require_once 'tournament.civix.php';
require_once 'util.php';
//require_once 'Team.php';

// class Tournament extends CRM_Event_BAO_Event {
// 	public static function currentTournament()
// 	{
// 		$params = array('event_type_id' => 7, 'is_active' => 1);
// 		$tournament = self::retrieve($params, $tournamentArray);
// 		return $tournament;
// 	}
	
// 	public static function currentTournamentID()
// 	{
// 		$tournament = self::currentTournament();
// 		return $tournament->id;
// 	}
	
// 	public static function currentTournamentName()
// 	{
// 		$tournament = self::currentTournament();
// 		return $tournament->title;
// 	}
// }

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function tournament_civicrm_navigationMenu(&$menu) {
	$domain = array('domain' => domain());
	$path = path();
	$name = $path;
	// This inserts a menu at the end the bar. TODO: How to insert at beginning?
	_tournament_civix_insert_navigation_menu($menu, NULL, array(
			'label' => ts('Tournament', $domain), 'name' => $name, 'url' => ''));

	$path = $name;
	$name = 'tournament_dashboard';
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Dashboard', $domain),
			'name' => $name,
			'url' => 'civicrm',
			'permission' => 'access Contact Dashboard',
	));

	$name = 'individual_profile';

	$billing_contact = billing_contact_get();
	$billing_contact_id = $billing_contact["id"];

	$record = named_profile_get("Billing Individual Profile");
	$gid = $record["id"];
	$delim = '?';
	
	$HREF = contact_profile_HREF_data($billing_contact, $record, $delim);
	$label = $HREF['title'];
	$url = $HREF['relativeURL'];

	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts($label, $domain),
			'name' => $name,
			'url' => $url,
			'permission' => 'edit my contact',
	));

 	$name = 'organization_profile';
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts("Organizations", $domain),
			'name' => $name,
			'url' => '',
	));
	
	$path .= "/{$name}";
	
	$profile = named_profile_get("Billing Organization Profile");
	$billing_organizations = billing_organizations_get($billing_contact_id);
	if (count($billing_organizations) > 0) foreach ($billing_organizations as $org){		
		$HREF = contact_profile_HREF_data($org, $profile, $delim);
		$label = $HREF['title'];
		$url = $HREF['relativeURL'];

		_tournament_civix_insert_navigation_menu($menu, $path, array(
				'label' => ts($label, $domain),
				'name' => "{$name}_{$organization_name}",
				'url' => $url,
				'permission' => 'access contact reference fields',
				));
	}
	
	$path = path();
	$name = 'Profiles';
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Your players, coaches, etc.', $domain),
			'name' => $name,
			'permission' => 'profile edit',
			'separator' => 1,
	));

	$path .= "/{$name}";

	// add a menu item for each of the session billing contact's profiles
	$registrationProfiles = get_registrationProfiles($billing_contact_id);
	if (count($registrationProfiles) > 0)
		foreach ($registrationProfiles as $profile) {
		$id = $profile["id"];
		$title = $profile["title"];
		_tournament_civix_insert_navigation_menu($menu, $path, array(
				'label' => ts("$title", array('domain' => $domain)),
				'name' => "{$name}_{$id}",
				'permission' => 'profile edit',
				));
		_tournament_civix_insert_navigation_menu($menu, "$path/{$name}_{$id}", array(
				'label' => ts("Find Contacts", array('domain' => $domain)),
				'name' => "{$name}_{$id}_list",
				'url' => "civicrm/profile?gid={$id}&reset=1&force=1",
				'permission' => 'profile edit',
				));
		_tournament_civix_insert_navigation_menu($menu, "{$path}/{$name}_{$id}", array(
				'label' => ts("New Contact", array('domain' => $domain)),
				'name' => "{$name}_{$id}_add",
				'url' => "civicrm/profile/create?gid={$id}&reset=1",
				'permission' => 'profile create',
				));
	}
	
	$path = path();
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Register Contacts for Tournament', $domain),
			'name' => 'registration',
			'permission' => 'edit event participants',
			'separator' => 1,
	));
	_tournament_civix_insert_navigation_menu($menu, "{$path}/registration", array(
			'label' => ts('Register a contact', $domain),
			'name' => 'registerParticipant',
			'url' => registrationRelativeURL("?"),
			'permission' => 'edit event participants',
	));
	_tournament_civix_insert_navigation_menu($menu, "{$path}/registration", array(
			'label' => ts('List/edit contacts already registered', $domain),
			'name' => 'participantList',
			'url' => registrationReportRelativeURL("?"),
			'permission' => 'edit event participants',
	));
	
	$path = path();
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Team Builder', array('domain' => $domain)),
			'name' => "teamBuilder",
			'permission' => 'edit groups',
			'separator' => 1,
			));
	
		_tournament_civix_insert_navigation_menu($menu, "{$path}/teamBuilder", array(
				'label' => ts('New Team', array('domain' => $domain)),
				'name' => 'NewTeam',
				'url' => "civicrm/tournament/team/add?reset=1",
				'permission' => 'edit groups',
				));
	
		_tournament_civix_insert_navigation_menu($menu, "{$path}/teamBuilder", array(
				'label' => ts('List/edit existing teams', array('domain' => $domain)),
				'name' => 'teamList',
				'url' => "civicrm/tournament/team/search",//?reset=1",
				'permission' => 'edit groups',
				));
	
	$path = path();
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Advanced Operations', $domain),
			'name' => 'bulkOperations',
			'permission' => 'edit event participants',
			'url' => bulkOperationsRelativeURL("?"),
			'separator' => 1,
	));

	$path = null;
	$name = "TournamentAdmin";
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Tournament Admins', array('domain' => $domain)),
			'name' => $name,
			'permission' => 'edit all contacts',
			));
	
	$path = $name;
	$record = named_profile_get("Billing Organization Profile");
	$id = $record["id"];
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Billing Organizations', array('domain' => $domain)),
			'name' => 'BillingOrganizations',
			'url' => "civicrm/profile?gid={$id}",
			'permission' => 'view all contacts',
			));

	$record = named_report_get("Preliminary Estimates Summary");
	$id = $record["id"];
	_tournament_civix_insert_navigation_menu($menu, "{$path}/BillingOrganizations", array(
			'label' => ts('Preliminary Estimates', array('domain' => $domain)),
			'name' => 'PreliminaryEstimates',
			'url' => "civicrm/report/instance/{$id}?reset=1",
			'permission' => 'view all contacts',
			));

	$record = named_group_get("Billing Contacts");
	$id = $record["id"];
	_tournament_civix_insert_navigation_menu($menu, $path, array(
			'label' => ts('Billing Individuals', array('domain' => $domain)),
			'name' => 'BillingContacts',
			'url' => "civicrm/group/search?context=smog&gid={$id}&reset=1&force=1",
			'permission' => 'view all contacts',
			));

	_tournament_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_dashboard().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_dashboard
 */
function tournament_civicrm_dashboard( $contactID, &$contentPlacement ) {	
	//TODO restrict this dashboard by access rights
//       if (!CRM_Core_Permission::check('administer reserved groups')) {
//         CRM_Core_Error::statusBounce(ts("You do not have sufficient permission to change settings for this reserved group."));
//       }
	// Insert custom content above activities
	$contentPlacement = CRM_Utils_Hook::DASHBOARD_ABOVE;
	
	$cid = CRM_Utils_Request::retrieve('bid', 'Positive', $this);
	if (!isset($cid)) $cid = $contactID;
	$session = CRM_Core_Session::singleton();
	$session->set('billing_contact_id', $cid);
	
	$contact = contact_get($cid);
	$profile = named_profile_get("Billing Individual Profile");
	$currentUserHREF = profileEditHREF($profile, $contact);
	$currentUserHTML = "Welcome, {$currentUserHREF}. The links on this page (and the Tournament navigation menu above) will guide you through the steps of tournament registration."
	. "<p>If you are new to this system, the first step is to double-check your contact information. You probably only need to do this once, ever.<p>";
	//The links on this 'dash";
	
	$billingOrgsHTML = "The next step is to double-check your organization's contact information. You probably only need to do this once, ever. <p>This is also where you submit your Preliminary Estimates. You do need to do this once per tournament, in January.</p>You are a contact for these organizations:<ol>";
	$billingOrgs = billing_organizations_get($cid);	
	$profile = named_profile_get("Billing Organization Profile");
	foreach($billingOrgs as $contact) {		
		$billingOrgsHTML .= "<li>" . profileEditHREF($profile, $contact) . "</li>";
	}
	$billingOrgsHTML .= "</ol>";
	
	$registrationProfilesHTML = "The next step is to enter contacts for your group(s). You probably only need to do this once per contact, ever."
	. "<p>This only enters them into the database. it doesn't mean they are committed to attending a tournament.</p>"
	. "You have access to contacts in these groups:<ol>";
	$registrationProfiles = get_registrationProfiles($cid);	
	foreach($registrationProfiles as $profile) {
		$registrationProfilesHTML .= "<li>" . $profile['title'] . "<ul>";
		$registrationProfilesHTML .= "<li>" . profileListHREF($profile) . "</li>";
		$registrationProfilesHTML .= "<li>" . profileCreateHREF($profile) . "</li>";
		$registrationProfilesHTML .= "</li></ul>";
	}
	$registrationProfilesHTML .= "</ol>";

	$registerParticipantHTML = 
	"Once you have entered all the contacts for your group(s), you can register them to attend the tournament. Be sure to indicate which competitions they will enter. That's important for the team registration step. <ul>"
	. "<li><a href = \"" . baseURL() . registrationRelativeURL() 
	."\">Use this link to register a contact for the tournament</a>.</li>"
	. "<li><a href = \"" . baseURL() . registrationReportRelativeURL() 
	."\">Use this link to list/edit contacts already registered for the tournament</a>.</li>"	
	."</ul>";

	$buildTeamsHTML = 
	"Once you have registered all the players for your district/league, you can combine them into teams.<ul>"
	. "<li><a href = \"" . baseURL() . newTeamRelativeURL() 
	."\">Use this link to start a start a new team</a>.</li>"
	. "<li><a href = \"" . baseURL() . listTeamsRelativeURL() 
	."\">Use this link to list/edit your teams</a>.</li>"	
	."</ul>";
	
	return array( '1. Your Contact Data' => $currentUserHTML,
			'2. Billing Organizations (e.g., School Districts)' => $billingOrgsHTML,
			'3. Contacts (Players, coaches, etc.)' => $registrationProfilesHTML,
			'4. Register Contacts for Tournament' => $registerParticipantHTML,
			'5. Combine Registered Players into Teams' => $buildTeamsHTML,
			// @todo cost sheet/invoice
	);
}

function newTeamRelativeURL($delim = "&"){
	return "civicrm/tournament/team/add{$delim}reset=1";
}

function listTeamsRelativeURL($delim = "&"){
	return "civicrm/tournament/team/search";
}

function registrationRelativeURL($delim = "&"){
	$eid = 1; //TODO
	return "civicrm/participant/add{$delim}reset=1&action=add&context=standalone&eid={$eid}";
}

function registrationReportRelativeURL($delim = "&"){
	$id = 25; //TODO
	return "civicrm/report/instance/{$id}{$delim}reset=1";
}

function teamGroupType(){ return 3; } //TODO

function bulkOperationsRelativeURL($delim = "&"){
	return "civicrm/contact/search{$delim}reset=1&force=1";
}

/**
 * billing contact is current user, unless admin is using bid argument
 *
 *
 * @return long
 */
function billing_contact_get(){
	$billing_contact_id = CRM_Utils_Request::retrieve('bid', 'Positive');
	$session = CRM_Core_Session::singleton();
	if (!isset($billing_contact_id)) $billing_contact_id = $session->get('billing_contact_id');
	if (!isset($billing_contact_id)) $billing_contact_id = $session->get('userID');
	$session->set('billing_contact_id', $billing_contact_id);
	return contact_get($billing_contact_id);
}

/**
 * find ID of current user, unless admin is using uid argument
 *
 *
 * @return long
 */
function user_id_get(){
	$uid = CRM_Utils_Request::retrieve('uid', 'Positive');
	$session = CRM_Core_Session::singleton();
	if (!isset($uid)) $uid = $session->get('userID');
	return $uid;
}

/**
 * Get orgs for which $contactID is billing contact
 *
 *
 * @param $name string
 * comment here
 * @return array
 */
function billing_organizations_get($contactID){
	$apiParams = array('relationship_type_id' => relationship_type_id('Billing Contact for'));
	$relations = get_active_relationships($contactID, $apiParams);
	//$orgs = array();
	foreach ($relations as $relation) {
		$orgID = $relation['contact_id_b'];
		$orgs[$orgID] = contact_get($orgID);
	}
	return $orgs;
}

/**
 * Get registration profiles for $contactID registration groups
 *
 * @param $contact_id long
 * @return array
 */
function get_registrationProfiles($contact_id){
	$aclGroups = get_aclGroups($contact_id);
	$registrationGroups = get_registrationGroups($aclGroups);

	if(count($registrationGroups) > 0) foreach ($registrationGroups as $group){
		$group_id = $group["id"];
		$apiParams = array("add_to_group_id" => $group_id
				, "limit_listings_group_id" => $group_id
				, "group_type" => "Individual"
				, "is_active" => 1
				, 'options' => array( 'sort' => 'created_date DESC', 'limit' => 1)
		);

		$entity_name = "uf_group";
		$result = civicrm_api3_get($entity_name, $apiParams, 'getsingle');

		$profiles[$result["id"]] = $result;
	}

	return $profiles;
}

function get_registrationGroups($aclGroups){
	$roles = get_aclRoles($aclGroups);
	if (count($roles)>0) foreach ($roles as $role){
		$entity_id = $role["acl_role_id"]; 
		$apiParams = array("entity_id" => $entity_id
				, "deny" => 0, "object_table" => "civicrm_saved_search"
				, "entity_table" => "civicrm_acl_role", "is_active" => 1);

		$entity_name = "Acl";
		$result = civicrm_api3_get($entity_name, $apiParams); // object_id should be 56

		$key = "id";
		if (is_array($result)) foreach ($result as $record) $records[$record[$key]] = $record;
		else $records[$result[$key]] = $result;
	}

	$acls = $records;
	unset($records);
	unset($result);

	$key = "id";
	if (count($acls) > 0) foreach($acls as $acl) {
		$result = civicrm_api3_get('Group', array($key => $acl["object_id"]));

		if (is_array($result)) foreach ($result as $record) $records[$record[$key]] = $record;
		else $records[$result[$key]] = $result;
	}

	return $records;
}

function domain(){
	return 'org.agloa.tournament';
}
function path(){
	return 'tournament';
}

/**
 * Implementation of hook_civicrm_preProcess
 */
function tournament_civicrm_preProcess($formName, &$form){
	switch ($formName) {
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