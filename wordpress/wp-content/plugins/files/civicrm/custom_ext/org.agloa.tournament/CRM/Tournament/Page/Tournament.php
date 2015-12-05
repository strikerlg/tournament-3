<?php

require_once 'CRM/Core/Page.php';

class CRM_Tournament_Page_Tournament extends CRM_Core_Page {
  public function run() {
	//var_dump($this);
	//var_dump($_SESSION);
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
 //   CRM_Utils_System::setTitle(ts('Tournament'));

    	$this->assign('baseURL', 'admin.php?page=CiviCRM&q=');
	$this->assign('eventID', 1);
	parent::run(); // Otherwise, not much happens.
  }
}
