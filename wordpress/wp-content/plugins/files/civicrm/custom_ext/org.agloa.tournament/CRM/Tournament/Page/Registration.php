<?php

require_once 'CRM/Core/Page.php';
require_once 'api/class.api.php';
class TournamentObject{	
	/**
	 *
	 *
	 * @var bool
	 */
	protected $is_error;
	/**
	 *
	 *
	 * @var string
	 */
	protected $error_message;
	
	function __construct(){
		$this->is_error = 0;
	}	
	
// 	private static $tournament_event_type_id;
// 	public static function tournament_event_type_id() {
// 		if (!isset($tournament_event_type_id)) {			
// 			$tournament_event_type_id = getOptionValue('event_type', 'Tournament');
// 		}
// 		return $tournament_event_type_id;
// 	}	
	
	private static $billing_contact_relationship_type_id;
	public static function billing_contact_relationship_type_id() {
		if (!isset($billing_contact_relationship_type_id))
			$billing_contact_relationship_type_id = TournamentObject::relationship_type_id('Billing Contact for');
		return $billing_contact_relationship_type_id;
	}
	
	public static function relationship_type_id($name){
		$apiParams = array('name_a_b' => $name);
		$records = TournamentObject::civicrm_api3_get('Relationship Type', $apiParams);
		foreach ($records as $record) return $record['id']; 
	}
	
	private static $member_relationship_type_id;
	public static function member_relationship_type_id($name) {
		if (!isset($member_relationship_type_id))
			$member_relationship_type_id = TournamentObject::relationship_type_id('Participates for');
		return $member_relationship_type_id;
	}
	
	public static function get_contact($contact_id){
		$apiParams = array('id' => $contact_id);//var_dump($apiParams); die('48');
		$api = new civicrm_api3();
		if ($api->Contact->get($apiParams)) {
			return $api->values[0];
		} else {
			return $api->error_message();
		}
	}
	
	function get_active_relationships($cid = null,  $apiParams) {
		return TournamentObject::get_relationships($cid, 1,  $apiParams);
	}
		
	function get_active_b_relationships($cid = null, $apiParams) {
		return TournamentObject::get_relationships($cid, 1, $apiParams, 'contact_id_b');
	}
		
	function get_relationships($cid = null, $is_active = 0, $apiParams, $field = 'contact_id_a') {
		if (isset($cid)){
		  $apiParams = TournamentObject::params_merge($apiParams, array($field => $cid));
		}
		
		$apiParams = TournamentObject::params_merge($apiParams, array('is_active' => $is_active));
		return TournamentObject::civicrm_api3_get('Relationship', $apiParams);
	}
	
	function params_merge($apiParams, $params){
		if (isset($apiParams)) return array_merge($apiParams, $params);
		else return $params;
	}
	
	function civicrm_api3_get($tableName, $apiParams) {	
		try{
			$result = civicrm_api3($tableName, 'get', $apiParams);
			return $result[values];
		}
		catch (CiviCRM_API3_Exception $e) {
			// handle error here
			$errorMessage = $e->getMessage();
			$errorCode = $e->getErrorCode();
			$errorData = $e->getExtraParams();
			return array('error' => $errorMessage, 'error_code' => $errorCode, 'error_data' => $errorData);
		}
	}
	
	public static function get_members($organization){
		$apiParams = array('relationship_type_id' => TournamentObject::member_relationship_type_id());
		
		$relations = TournamentObject::get_active_b_relationships($organization->id, $apiParams);
		$members = array();
		foreach ($relations as $relation){
			$member = TournamentObject::get_contact($relation['contact_id_a']);
			$members[$member->id] = $member;
		}
		return $members;
	}
	
	public static function get_billing_organization($contact){
		$apiParams = array('relationship_type_id' => TournamentObject::billing_contact_relationship_type_id()
				, 'options' => array( 'sort' => 'start_date DESC')
		);
		
		$relations = TournamentObject::get_active_relationships($contact->id, $apiParams);
		foreach ($relations as $relation)
			$org = TournamentObject::get_contact($relation['contact_id_b']);
		return $org;
	}
	
	public static function getOptionValue($optionGroupName, $optionValueName){
		$option_group_id = getActiveNamedTableValue('option_group', $optionGroupName, 'id');
		$params = array('option_group_id' => $option_group_id);
		return getActiveNamedTableValue('option_value', $optionValueName, 'value', $params);
	}
	
// 	public static function getActiveNamedTableValue($table, $name, $valueKey = 'value', $params = null) {
// 		$api = new civicrm_api3();
			
// 		$apiParams = array( 'name' => $name, 'is_active' => 1);
			
// 		if (isset($params)) $apiParams = array_merge($apiParams, $params);
	
// 		if ($api->$table->get($apiParams)) {
// 			$result = $api->values[0];
// 		} else {
// 			return $api->error_message();
// 		}
	
// 		if (is_array($result)) foreach($result['values'] as $value) $result_value = $value[$valueKey];
// 		else $result_value = $result->$valueKey;
// 		return $result_value;
// 	}
}

class Tournament extends TournamentObject {
	/**
	 *
	 *
	 * @var $event
	 */
	public $event;
	function __construct(){	
		parent::__construct();	
		$api = new civicrm_api3();
		
		// get current tournament, i.e., latest active event of type 'Tournament'
		$apiParams = array(
				'event_type_id' => $tournament_event_type_id
				, 'is_active' => 1
				, 'options' => array( 'sort' => 'start_date DESC')
		);
		
		$table = 'Event';
		if ($api->$table->get($apiParams)) {
			$this->event = $api->values[0];
		} else {
			$this->is_error = 1;
			$this->error_message = $api->error_message();
		}
	}
}

class BillingContact extends TournamentObject{	
	/**
	 *
	 *
	 * @var $tournament
	 */
	public $tournament;
	/**
	 *
	 *
	 * @var contact
	 */
	public $contact;
	/**
	 *
	 *
	 * @var organization
	 */
	public $organization;	
	/**
	 *
	 *
	 * @var members
	 */
	public $members;
	
	function __construct($contact_id) {
		parent::__construct();
		
		$this->tournament = new Tournament();
		$this->contact = TournamentObject::get_contact($contact_id);
		$this->organization = TournamentObject::get_billing_organization($this->contact);
		$this->members = TournamentObject::get_members($this->organization);
	}
}

class CRM_Tournament_Page_Registration extends CRM_Core_Page {
  public function run() {
    // billing contact is current user, unless admin is using cid argument
    if (isset($_REQUEST["cid"])) {
    	$billing_contact_id = $_REQUEST["cid"];
    } else {
    	// Get logged in user
    	$session = CRM_Core_Session::singleton();
    	$billing_contact_id = $session->get('userID');
    }
    
    $billing_contact = new BillingContact($billing_contact_id); //var_dump($billing_contact); //die('128');
    
    $this->assign('billing_contact', $billing_contact);

    //$this->registration_group_contact_get($contact_id); 

    // get assigned registration groups : search for contact in groups of type Registration group
    // Find all of the groups contacts

    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    //CRM_Utils_System::setTitle(ts('Registration'));
    parent::run();
  }

  function registration_group_contact_get($contact_id){
    $option_group_id = $this->getActiveNamedTableValue('option_group', 'group_type', 'id');
    $params = array('option_group_id' => $option_group_id);

    // Expected value = 4
    $registration_group_type = $this->getActiveNamedTableValue('option_value', 'Registration Group', 'value', $params); //var_dump($registration_group_type); die('72');

    $contact_groups = $this->group_contact_get($contact_id); //var_dump($contact_groups);
    $registrationGroups = array();
    foreach($contact_groups['values'] as $contact_group){
      $params = array('id' => $contact_group["group_id"], 'is_active' => 1, 'group_type' => $registration_group_type);
      $registrationGroups = array_merge($registrationGroups, civicrm_api3('group', 'get', $params)); 
    }
    
    $registrationGroupNames = array();
    foreach($registrationGroups as $group) foreach($group as $groupElement) $registrationGroupNames = array_merge($registrationGroupNames, $groupElement['description']);

    $this->assign('registration_groups', $registrationGroups);
    $this->assign('registration_group_names', implode(", " , $registrationGroupNames));

    return;// $this->group_contact_get($contact_id);
  }

  function group_contact_get($contact_id){ 
    
    $params = array(
      'contact_id' => $contact_id,
    );

    try{
      $result = civicrm_api3('group_contact', 'get', $params);
    }
    catch (CiviCRM_API3_Exception $e) {
      // handle error here
      $errorMessage = $e->getMessage();
      $errorCode = $e->getErrorCode();
      $errorData = $e->getExtraParams();
      return array('error' => $errorMessage, 'error_code' => $errorCode, 'error_data' => $errorData);
    }

    return $result;
  }
}
