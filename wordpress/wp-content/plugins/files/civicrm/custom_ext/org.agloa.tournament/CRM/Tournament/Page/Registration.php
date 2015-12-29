<?php

require_once 'CRM/Tournament/TournamentObject.php';

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

class CRM_Tournament_Page_Registration extends CRM_Core_Page {
	public $billing_contact;
	
  public function run() {
//  	$smarty->debugging_ctrl = ($_SERVER['SERVER_NAME'] == 'localhost') ? 'URL' : 'NONE';
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
    
    $baseURL = "admin.php?page=CiviCRM&q=";
    $indProfile = $this->getProfileID('Billing Individual Profile');
    if (isset($indProfile))
    	$this->assign('indEditLink', $baseURL.
    		"civicrm/profile/edit&reset=1&id={$this->billing_contact->contact['id']}&gid={$indProfile}");
    else
    	$this->assign('indEditLink', $baseURL.
    		"civicrm/contact/view&reset=1&cid={$this->billing_contact->contact['id']}");
    
    $orgProfile = $this->getProfileID('Billing Organization Profile');
    if (isset($orgProfile))
    	$this->assign('orgEditLink', $baseURL.
    		"civicrm/profile/edit&reset=1&id={$this->billing_contact->organization['id']}&gid={$orgProfile}");
    else
    	$this->assign('orgEditLink', $baseURL.
    		"civicrm/contact/view&reset=1&cid={$this->billing_contact->organization['id']}");
    
    // TODO: how to get search results (members) to use this profile?
    $memberProfile = $this->getProfileID('Member Profile');
    if (isset($memberProfile)){
    	$this->assign('pMemberAdd',"civicrm/profile/create");
    	$this->assign('qMemberAdd', "gid={$memberProfile}&reset=1");
    	$this->assign('memberEditLink', $baseURL.
    		"civicrm/profile/edit&gid={$memberProfile}&reset=1");
    }
    else{
    	$this->assign('pMemberAdd',"civicrm/contact/add");
    	$this->assign('qMemberAdd', "ct=Individual&reset=1");
    	$this->assign('memberEditLink', $baseURL.
    		"civicrm/contact/edit");
    }
    
		$this->columnHeaders = array();
		$this->columnHeaders['sort_name'] = array(
					'name' =>	'Member'
				, 'field_name' => 'sort_name'
				, 'sort' =>	'sort_name'
				, 'direction' => 1
				);
		
		$this->assign('columnHeaders', $this->columnHeaders);
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    //CRM_Utils_System::setTitle(ts('Registration'));
    parent::run();
  }
  
  function getProfileID($title) {  	
  	return $this->billing_contact->table_name_to_id('uf_group', $title, 'title', array('is_active' => 1));
  }
}
