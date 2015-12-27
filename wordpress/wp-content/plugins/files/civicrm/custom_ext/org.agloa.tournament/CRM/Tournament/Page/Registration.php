<?php

require_once 'CRM/Core/Page.php';
require_once 'api/class.api.php';


class Tournament extends TournamentObject {
	/**
	 *
	 *
	 * @var $event
	 */
	public $event;
	function __construct(){	
		parent::__construct();	

		// get current tournament, i.e., latest active event of type 'Tournament'
		$apiParams = array(
				'event_type_id' => $this->tournament_event_type_id()
				, 'is_active' => 1
				, 'options' => array( 'sort' => 'start_date DESC')
		);
		
		$this->event = $this->civicrm_api3_get('Event', $apiParams, 'getsingle');
		
		if (isset($this->event['error'])) {
			$this->is_error = true;
			$this->error_message = $this->event['error'];
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
	
	private $billing_contact_relationship_type_id;
	
	function __construct($contact_id) {
		parent::__construct();
		
		$this->tournament = new Tournament();
		if ($this->tournament->is_error) {
			$this->is_error = true;
			$this->error_message = $this->tournament->error_message;
			return;
		}
		
		$this->contact = $this->get_contact($contact_id);
		$this->organization = $this->get_billing_organization();
		$this->members = $this->get_members($this->organization);
	}
	
	function get_billing_organization(){
		$apiParams = array('relationship_type_id' => $this->relationship_type_id('Billing Contact for')
				, 'options' => array( 'sort' => 'start_date DESC', 'limit' => 1)
		);
	
		$relations = $this->get_active_relationships($this->contact['id'], $apiParams);
		foreach ($relations as $relation) return $this->get_contact($relation['contact_id_b']);
	}
}
class TournamentObject{
	/**
	 *
	 *
	 * @var bool
	 */
	public $is_error;
	/**
	 *
	 *
	 * @var string
	 */
	public $error_message;

	function __construct(){
		$this->is_error = 0;
	}

	function tournament_event_type_id(){
		return $this->option_value('event_type', 'Tournament');
	}

	function get_active_relationships($cid = null,  $apiParams) {
		return $this->get_relationships($cid, 1,  $apiParams);
	}

	function get_active_b_relationships($cid = null, $apiParams) {
		return $this->get_relationships($cid, 1, $apiParams, 'contact_id_b');
	}

	function get_members($organization){
		$apiParams = array('relationship_type_id' => $this->member_relationship_type_id());

		$relations = $this->get_active_b_relationships($organization['id'], $apiParams);
		$members = array();
		foreach ($relations as $relation){
			$member = $this->get_contact($relation['contact_id_a']);
			$members[$member['id']] = $member;
		}
		return $members;
	}

	function member_relationship_type_id() {
		return $this->relationship_type_id('Member of');
	}

	function get_relationships($cid = null, $is_active = 0, $apiParams, $field = 'contact_id_a') {
		if (isset($cid)){
			$apiParams = $this->params_merge($apiParams, array($field => $cid));
		}

		$apiParams = $this->params_merge($apiParams, array('is_active' => $is_active));
		return $this->civicrm_api3_get('Relationship', $apiParams);
	}

	function option_value($groupName, $name){
		$groupID = $this->option_group_id($groupName);
		$apiParams = array('option_group_id' => $groupID, 'name' => $name);
		$table = 'Option Value';
		$record = $this->civicrm_api3_get($table, $apiParams, 'getsingle');
		return $record['value'];
	}

	function option_group_id($name){
		$apiParams = array('name' => $name);
		return $this->table_name_to_id('Option Group', $name);
	}

	function relationship_type_id($name){
		$record = $this->civicrm_api3_get('Relationship Type', array('name_a_b' => $name), 'getsingle');
		return $record['id'];
	}

	function table_name_to_id($table, $name, $field = 'name', $params = null){
		$apiParams = $this->params_merge($params, array($field => $name));
		return $this->table_record_id($table, $apiParams);
	}

	function table_record_id($table, $apiParams){
		$record = $this->civicrm_api3_get($table, $apiParams, 'getsingle');
		return $record['id'];
	}

	function get_contact($contact_id){
		return $this->civicrm_api3_get('Contact', array('id' => $contact_id), 'getsingle');
	}

	function civicrm_api3_get($tableName, $apiParams, $action = 'get') {
		try{
			if ($action == 'getsingle')
				$apiParams['options'] = $this->params_merge($apiParams['options'], array('limit' => 1));
				
			$result = civicrm_api3($tableName, $action, $apiParams);
			if ($action == 'getsingle') return $result;
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

	function params_merge($oldParams, $newParams){
		if (isset($oldParams)) return array_merge($oldParams, $newParams);
		else return $newParams;
	}
}

class CRM_Tournament_Page_Registration extends CRM_Core_Page {
	public $billing_contact;
	
  public function run() {
    // billing contact is current user, unless admin is using cid argument
    if (isset($_REQUEST["cid"])) {
    	$billing_contact_id = $_REQUEST["cid"];
    } else {
    	// Get logged in user
    	$session = CRM_Core_Session::singleton();
    	$billing_contact_id = $session->get('userID');
    }
    
    $this->billing_contact = new BillingContact($billing_contact_id); //var_dump($billing_contact); //die('128');
    if ($this->billing_contact->is_error) var_dump($this->billing_contact);
     
    $this->assign('billing_contact', $this->billing_contact);
    
    // uf_group is_active=1 name = Billing Organization Profile
    $indProfile = $this->getProfileID('Billing Individual Profile');
    if (isset($indProfile))
    	$this->assign('indEditLink', 
    		"admin.php?page=CiviCRM&q=civicrm/profile/edit&reset=1&id={$this->billing_contact->contact['id']}&gid={$indProfile}");
    else
    	$this->assign('indEditLink', 
    		"admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$this->billing_contact->contact['id']}");
    
    $orgProfile = $this->getProfileID('Billing Organization Profile');
    if (isset($orgProfile))
    	$this->assign('orgEditLink', 
    		"admin.php?page=CiviCRM&q=civicrm/profile/edit&reset=1&id={$this->billing_contact->organization['id']}&gid={$orgProfile}");
    else
    	$this->assign('orgEditLink',
    		"admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$this->billing_contact->organization['id']}");
    
    $memberProfile = $this->getProfileID('MemberProfile');
    if (isset($memberProfile)){
    	$this->assign('pMemberAdd',"civicrm/profile/create");
    	$this->assign('qMemberAdd', "gid={$memberProfile}&reset=1");
    }
    else{
    	$this->assign('pMemberAdd',"civicrm/contact/add");
    	$this->assign('qMemberAdd', "ct=Individual&reset=1");
    }
    

    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    //CRM_Utils_System::setTitle(ts('Registration'));
    parent::run();
  }
  
  function getProfileID($title) {  	
  	return $this->billing_contact->table_name_to_id('uf_group', $title, 'title', array('is_active' => 1));
  }
}
