<?php

require_once 'CRM/Core/Page.php';
require_once 'api/class.api.php';
require_once 'util.php';

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
		
		// TODO: sort by sortname, e.g., array sort
		return $members;
	}
	
	function get_registrationProfiles($contact_id){
		$aclGroups = $this->get_aclGroups($contact_id);
		$registrationGroups = $this->get_registrationGroups($aclGroups);
	
		foreach ($registrationGroups as $group){
			$group_id = $group["id"];
			$apiParams = array("add_to_group_id" => $group_id
					, "limit_listings_group_id" => $group_id
					, "group_type" => "Individual"
					, "is_active" => 1
					, 'options' => array( 'sort' => 'created_date DESC', 'limit' => 1)
			);
				
			$entity_name = "uf_group";
			$result = $this->civicrm_api3_get($entity_name, $apiParams, 'getsingle');
				
			$profiles[$result["id"]] = $result;
		}
	
		return $profiles;
	}
	
	function get_aclGroups($contact_id, $is_active = 1, $params){
		$contact_groups = $this->get_contact_groups($contact_id);
		
		foreach ($contact_groups as $contact_group) {
			$groupID = $contact_group["group_id"];		
			$apiParams = $this->params_merge($params,
					 array("id" => $groupID, "group_type" => $this->access_control_group_type_id()
					 		, "is_active" => $is_active, "is_hidden" => "0"));
			
			$entity_name = "Group";
			$result = $this->civicrm_api3_get($entity_name, $apiParams);
			
			if (is_array($result)) foreach ($result as $group) $groups[$group["id"]] = $group;
			else $groups[$result["id"]] = $result;
		}
		
		return $groups;
	}	
	
	function get_registrationGroups($aclGroups){ // test 93
		$roles = $this->get_aclRoles($aclGroups);
		foreach ($roles as $role){
			$entity_id = $role["acl_role_id"]; // should be 39
			$apiParams = array("entity_id" => $entity_id
					, "deny" => 0, "object_table" => "civicrm_saved_search"
					, "entity_table" => "civicrm_acl_role", "is_active" => 1);
				
			$entity_name = "Acl";
			$result = $this->civicrm_api3_get($entity_name, $apiParams); // object_id should be 56
				
			$key = "id";
			if (is_array($result)) foreach ($result as $record) $records[$record[$key]] = $record;
			else $records[$result[$key]] = $result;
		}

		$acls = $records;
		unset($records);
		unset($result);
		
		$key = "id";
		foreach($acls as $acl) {
			$result = $this->civicrm_api3_get('Group', array($key => $acl["object_id"]));
		
			if (is_array($result)) foreach ($result as $record) $records[$record[$key]] = $record;
			else $records[$result[$key]] = $result;
		}
		
		return $records;
	}
	
	function get_aclRoles($aclGroups){
		foreach ($aclGroups as $group){
			$entity_id = $group["id"];
			$apiParams = array("entity_id" => $entity_id
					, "entity_table" => "civicrm_group", "is_active" => 1);
			
			$entity_name = "Acl_Role";
			$result = $this->civicrm_api3_get($entity_name, $apiParams);
			
			if (is_array($result)) foreach ($result as $record) $records[$record["id"]] = $record;
			else $records[$result["id"]] = $result;
		}

		return $records;
	}

	function access_control_group_type_id() {
		return $this->option_value("group_type", "Access Control");
	}
	
	function get_contact_groups($contact_id, $status = "Added"){
		$apiParams = array("contact_id" => $contact_id, "status" => $status);
		return $this->civicrm_api3_get('Group_Contact', $apiParams);
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