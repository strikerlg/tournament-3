<?php
require_once 'CRM/Tournament/Tournament.php';

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

	//private $billing_contact_relationship_type_id;

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
	
	function addLatest(){
		// Hack: find newest Individual contact and associate with billing_contact_district
		// Make sure creation date isn't too far off
		// contact_type = idividual
		// civicrm_log
		/*
		 * SELECT  `entity_id`
		FROM  `civicrm_log`
		WHERE  `entity_table` LIKE  'civicrm_contact'
		AND  `modified_id` =$billing_contact_id
		ORDER BY  `civicrm_log`.`modified_date` DESC
		LIMIT 0 , 1
		*/
		
		// 	$apiParams = array(
		// 			'entity_table' => 'civicrm_contact'
		// 			,'modified_id' => $billing_contact_id
		// 			,'options' => array('sort' => 'modified_date DESC')
		// 	);
		// 	$newMember = $billing_contact->civicrm_api3_get('Log', array('id' => $contact_id), 'getsingle');
		
		/*
		 * SELECT `id` FROM `civicrm_contact`
		* WHERE `contact_type` LIKE 'Individual' AND `is_deleted` = 0
		* ORDER BY `created_date` DESC
		*/
		
		$apiParams = array(
				'contact_type' => 'Individual'
				,'is_deleted' => 0
				,'options' => array('sort' => 'created_date DESC')
		);
			$newMember = $this->civicrm_api3_get('Contact', $apiParams, 'getsingle');
		$newMemberId = $newMember['id'];
		var_dump($newMemberId);var_dump($billing_contact->organization['id']);//var_dump($newMember);
		$tableName = 'Relationship';
		$action = 'create';		
		
		$apiParams = array(
				'contact_id_a' => $newMember['id'],
				'contact_id_b' => $this->organization['id'],
				'relationship_type_id' => $this->relationship_type_id('Member of'),
		);
		$result = civicrm_api3($tableName, $action, $apiParams);
		return $result;
	}
}
