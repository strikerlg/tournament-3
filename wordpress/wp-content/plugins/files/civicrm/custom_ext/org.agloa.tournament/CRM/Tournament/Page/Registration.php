<?php

require_once 'CRM/Core/Page.php';
require_once 'api/class.api.php';

class CRM_Tournament_Page_Registration extends CRM_Core_Page {
  public function run() {
    $tournament_event_type_id = $this->getOptionValue('event_type', 'Tournament');
    
    $api = new civicrm_api3();
    // get current tournament, i.e., latest active event of type 'Tournament'
    $apiParams = array(
        'event_type_id' => $tournament_event_type_id
	      , 'is_active' => 1
      , 'options' => array( 'sort' => 'start_date DESC')
    );

    $table = 'Event';
    if ($api->$table->get($apiParams)) {
      $result = $api->values[0];
    } else {
      echo $api->errorMsg();
      return;
    }  
    $this->assign('current_event', $result);
    
    // Get logged in user
    $session = CRM_Core_Session::singleton();
    $userId = $session->get('userID');
    
    $apiParams = array('id' => $userId);
    $table = 'Contact';
    if ($api->$table->get($apiParams)) {
    	$result = $api->values[0];
    } else {
    	echo $api->errorMsg();
      return;
    }
    $current_user = $result;

    // registrar is current user, unless admin is using cid argument
    if (isset($_REQUEST["cid"])) {
      $contact_id = $_REQUEST["cid"];
      $apiParams = array('id' => $contact_id);
      if ($api->$table->get($apiParams)) {
        $result = $api->values[0];
      } else {
        echo $api->errorMsg();
      }
      $registrar = $result;
    }
    else 
      $registrar = $current_user;
    
    $this->assign('registrar', $registrar);

    $this->registration_group_contact_get($contact_id); 

    // get assigned registration groups : search for contact in groups of type Registration group
    // Find all of the groups contacts

    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('Registration'));

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
  
  function getOptionValue($optionGroupName, $optionValueName){
  	$option_group_id = $this->getActiveNamedTableValue('option_group', $optionGroupName, 'id');
  	$params = array('option_group_id' => $option_group_id);  	
  	return $this->getActiveNamedTableValue('option_value', $optionValueName, 'value', $params);
  }
  
  function getActiveNamedTableValue($table, $name, $valueKey = 'value', $params = null) {  	
  	$api = new civicrm_api3();
  	
  	$apiParams = array( 'name' => $name, 'is_active' => 1);
  	
    if (isset($params)) $apiParams = array_merge($apiParams, $params); 
    
    if ($api->$table->get($apiParams)) {
    	$result = $api->values[0];
    } else {
    	return $api->errorMsg();
    }

    if (is_array($result)) foreach($result['values'] as $value) $result_value = $value[$valueKey];
    else $result_value = $result->$valueKey;
    return $result_value;
  }
}
