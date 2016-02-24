<?php
/**
 * get acl groups a contact is member of
 *
 * @param $contact_id long
 * @param $status string
 * @return array
 */
function get_aclGroups($contact_id, $is_active = 1, $params = null){
	$contact_groups = get_contact_groups($contact_id);

	foreach ($contact_groups as $contact_group) {
		$groupID = $contact_group["group_id"];
		$apiParams = params_merge($params,
				array("id" => $groupID, "group_type" => access_control_group_type_id()
						, "is_active" => $is_active, "is_hidden" => "0"));
			
		$entity_name = "Group";
		$result = civicrm_api3_get($entity_name, $apiParams);
			
		if (is_array($result)) foreach ($result as $group) $groups[$group["id"]] = $group;
		else $groups[$result["id"]] = $result;
	}

	return $groups;
}

/**
 * get groups a contact is member of
 * 
 * @param $contact_id long
 * @param $status string
 * @return array
 */
function get_contact_groups($contact_id, $status = "Added"){
	$apiParams = array("contact_id" => $contact_id, "status" => $status);
	return civicrm_api3_get('Group_Contact', $apiParams);
}

/**
 * get acl roles groups
 *
 * @param $aclGroups array
 * @return array
 */
function get_aclRoles($aclGroups){
	if (count($aclGroups) > 0) foreach ($aclGroups as $group){
		$entity_id = $group["id"];
		$apiParams = array("entity_id" => $entity_id
				, "entity_table" => "civicrm_group", "is_active" => 1);
			
		$entity_name = "Acl_Role";
		$result = civicrm_api3_get($entity_name, $apiParams);
			
		if (is_array($result)) foreach ($result as $record) $records[$record["id"]] = $record;
		else $records[$result["id"]] = $result;
	}

	return $records;
}

/**
 * get civicrm base URL
 *
 * @return string
 */
function baseURL(){
	$config = CRM_Core_Config::singleton();
	$userFrameworkBaseURL = rtrim($config->userFrameworkBaseURL, "/");
	$civiURL =  $config->civiRelativeURL;
	return "{$userFrameworkBaseURL}{$civiURL}";
}

/**
 * get contact matching id
 *
 * @param $id long
 * @return array
 */
function contact_get($id){
	$result = civicrm_api3('contact', 'get', array("id" => $id));
	return $result['values'][$id];
}

/**
 * get HREF to profile listing
 *
 * @param $profile array
 * @param $contact array
 * @return string
 */
function profileEditHREF($profile, $contact){
	$HREF = contact_profile_HREF_data($contact, $profile);
	//$cid = $contact['id'];
	$text = $HREF['text'];//$contact['display_name'];
	$title = $HREF['title'];//"Use this link to edit $text";
	$token = $HREF['relativeURL'];//"civicrm/profile/edit&reset=1&=1&id={$cid}&gid=";
	return tokenIDHREF($token, $title, $text);
}

/**
 * get HREF to profile listing
 *
 * @param $profile array
 * @return string
 */
function profileListHREF($profile){
	$text = 'List';
	$title = "Use this link to edit contacts in {$profile['title']}";
	$token = "civicrm/profile&reset=1&force=1&gid={$profile['id']}";
	return tokenIDHREF($token, $title, $text);
}

/**
 * get HREF to create with profile
 *
 * @param $profile array
 * @return string
 */
function profileCreateHREF($profile){
	$text = "Add New";
	$title = "Use this link to add new contacts to {$profile['title']}";
	$token = "civicrm/profile/create&reset=1&gid={$profile['id']}";
	return tokenIDHREF($token, $title, $text);
}

/**
 * build HREF from base URL, token, record id, link title, and record name
 *
 * @param $token string
 * @param $title string
 * @param $id long
 * @param $name string
 * @return string
 */
function tokenIDHREF($token, $title, $name){
	$baseURL = baseURL();
	return "<a title=\"$title\" href=\"{$baseURL}{$token}\">$name</a>";
}

/**
 * build HREF from contact & profile
 *
 * @param $contact array
 * @param $profile array
 * @return array
 */
function contact_profile_HREF_data($contact, $profile, $delim='&'){
	$cid = $contact['id'];
	$gid = $profile['id'];
	$text = $contact['display_name'];
	$HREF['text'] = $text;
	$HREF['title'] = "Use this link to edit {$text}";
	$HREF['relativeURL'] = "civicrm/profile/edit{$delim}reset=1&id={$cid}&gid={$gid}";
	return $HREF;
}

/**
 * get id for Access Control group type
 *
 * @return long
 */
function access_control_group_type_id() {
	return option_value("group_type", "Access Control");
}

/**
 * get id for for a named option value
 *
 * @param $groupName string
 * @param $name string
 * @return long
 */
function option_value($groupName, $name){
	$groupID = option_group_id($groupName);
	$apiParams = array('option_group_id' => $groupID, 'name' => $name);
	$table = 'Option Value';
	$record = civicrm_api3_get($table, $apiParams, 'getsingle');
	return $record['value'];
}

/**
 * get id for for a named option group
 *
 * @param $name string
 * @return long
 */
function option_group_id($name){
	$apiParams = array('name' => $name);
	return table_name_to_id('Option Group', $name);
}

/**
 * get id for for a named table field
 *
 * @param $table string
 * @param $name string
 * @param $field string
 * @param $params array
 * @return long
 */
 function table_name_to_id($table, $name, $field = 'name', $params = null){
	$apiParams = params_merge($params, array($field => $name));
	return table_record_id($table, $apiParams);
}

/**
 * get id for a record in a table
 *
 * @param $table string
 * @param $apiParams array
 * @return long
 */
function table_record_id($table, $apiParams){
	$record = civicrm_api3_get($table, $apiParams, 'getsingle');
	return $record['id'];
}

/**
 * Search for group which matches title
 *
 *
 * @param $title string
 * @return array
 */
function named_group_get($title){
	return named_record_get("Group", $title);
}
/**
 * Search for profile which matches title
 *
 *
 * @param $title string
 * @return array
 */
function named_profile_get($title){
	return named_record_get("uf_group", $title);
}
/**
 * Search for report which matches title
 *
 *
 * @param $title string
 * @return array
 */
function named_report_get($title){
	return named_record_get("Report Instance", $title);
}

/**
 * Search table for record which matches title
 *
 *
 * @param $tableName string
 * @param $title string
 * @return array
 */
function named_record_get($tableName, $title){
	$action = "get";
	$apiParams = array("title" => $title);
	$result = civicrm_api3($tableName, $action, $apiParams);
	return $result;
}

/**
 * Lookup relationship type id from name
 *
 *
 * @param $name string
 * @return long
 */
function relationship_type_id($name){
	$record = civicrm_api3_get('Relationship Type', array('name_a_b' => $name), 'getsingle');
	return $record['id'];
}

/**
 * 'Get' wrapper around civicrm_api3
 *
 *
 * @param $tableName string
 * @param $apiParams array
 * @return array
 */
function civicrm_api3_get($tableName, $apiParams, $action = 'get') {
	try{
		if ($action == 'getsingle')
			$apiParams['options'] = params_merge($apiParams['options'], array('limit' => 1));

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

/**
 * Get active relationships for a contact id
 *
 *
 * @param $cid long
 * @param $apiParams array
 * @param $field string
 * @return array
 */
function get_active_relationships($cid = null,  $apiParams, $field = 'contact_id_a') {
	return get_relationships($cid, 1,  $apiParams, $field);
}

/**
 * Get relationships for a contact id
 *
 *
 * @param $cid long
 * @param $is_active short
 * @param $apiParams array
 * @param $field string
 * @return array
 */
function get_relationships($cid = null, $is_active = 0, $apiParams, $field = 'contact_id_a') {
	if (isset($cid)){
		$apiParams = params_merge($apiParams, array($field => $cid));
	}

	$apiParams = params_merge($apiParams, array('is_active' => $is_active));
	return civicrm_api3_get('Relationship', $apiParams);
}

/**
 * Merge parameter sets
 *
 *
 * @param $oldParams array
 * @param $newParams array
 * @return array
 */
 function params_merge($oldParams, $newParams){
	if (isset($oldParams)) return array_merge($oldParams, $newParams);
	else return $newParams;
}