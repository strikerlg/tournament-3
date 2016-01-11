<?php

require_once 'CRM/Tournament/BillingContact.php';
require_once 'CRM/Core/Page.php';

class CRM_Tournament_Page_Registration_NewMemberProcess extends CRM_Core_Page {
  public function run() {
	//var_dump($this);
	//var_dump($_SESSION);
	//var_dump($_REQUEST);
	//$CiviCRM = $_SESSION['CiviCRM'];
	//var_dump($CiviCRM);//['view.id']);
	//$billing_contact = $_SESSION['billing_contact'];
	//var_dump($billing_contact->tournament);
	//var_dump($billing_contact->contact);
		$billing_contact_id = $_SESSION['billing_contact_id'];
		$billing_contact = new BillingContact($billing_contact_id); //var_dump($billing_contact); //die('128');
		
		$newRelation = $billing_contact->addLatest(); //var_dump($newRelation);
	    //parent::run();
		return CRM_Utils_System::redirect("admin.php?page=CiviCRM&q=civicrm/tournament");
  }
}