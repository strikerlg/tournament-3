<?php
/*
 */

/**
 *
 * @package Tournament
 * $Id$
 *
 */
class CRM_Tournament_Team_Controller extends CRM_Core_Controller {

  /**
   * Class constructor.
   *
   * @param null $title
   * @param bool|int $action
   * @param bool $modal
   */
  public function __construct($title = NULL, $action = CRM_Core_Action::NONE, $modal = TRUE) {
    parent::__construct($title, $modal);

    $this->_stateMachine = new CRM_Tournament_Team_StateMachine($this, $action);

    // create and instantiate the pages
    $this->addPages($this->_stateMachine, $action);

    // hack for now, set Search to Basic mode
    //$this->_pages['Basic']->setAction(CRM_Core_Action::BASIC);

    // add all the actions
    $config = CRM_Core_Config::singleton();

    // to handle file type custom data
    $uploadDir = $config->uploadDir;

    $uploadNames = $this->get('uploadNames');
    if (!empty($uploadNames)) {
      $uploadNames = array_merge($uploadNames,
        CRM_Core_BAO_File::uploadNames()
      );
    }
    else {
      $uploadNames = CRM_Core_BAO_File::uploadNames();
    }

    // add all the actions
    $this->addActions($uploadDir, $uploadNames);
  }

  /**
   * @return mixed
   */
  public function run() {
    return parent::run();
  }

  /**
   * @return mixed
   */
  public function selectorName() {
    return $this->get('selectorName');
  }

}
