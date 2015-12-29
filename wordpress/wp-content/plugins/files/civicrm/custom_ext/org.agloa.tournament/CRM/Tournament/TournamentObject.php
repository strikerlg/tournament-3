<?php

require_once 'CRM/Core/Page.php';
require_once 'api/class.api.php';

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