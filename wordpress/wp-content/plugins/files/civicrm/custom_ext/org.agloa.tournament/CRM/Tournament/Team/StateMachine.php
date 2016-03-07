<?php

/**
 *
 * @package Tournament
 * $Id$
 *
 */
class CRM_Tournament_Team_StateMachine extends CRM_Core_StateMachine {

  /**
   * Class constructor.
   *
   * @param object $controller
   * @param \const|int $action
   */
  public function __construct($controller, $action = CRM_Core_Action::NONE) {
    parent::__construct($controller, $action);

    $this->_pages = array(
      'CRM_Tournament_Team_Form_Edit' => NULL,
      //'CRM_Tournament_Player_Form_Search_Basic' => NULL,
      'CRM_Tournament_Player_Form_Task_AddToTeam' => NULL,
      //'CRM_Tournament_Player_Form_Task_Result' => NULL,
    );

    $this->addSequentialPages($this->_pages, $action);
  }

  /**
   * Return the form name of the task. This is
   *
   * @return string
   */
  public function getTaskFormName() {
    return CRM_Utils_String::getClassName('CRM_Tournament_Player_Form_Task_AddToGroup');
  }

}
