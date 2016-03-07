<?php

/**
 *
 * @package Tournament
 * $Id$
 *
 */
class CRM_Tournament_Team_Form_Search extends CRM_Core_Form {

  public function preProcess() {
    parent::preProcess();

    CRM_Core_Resources::singleton()->addPermissions('edit groups');
  }

  /**
   * @return array
   */
  public function setDefaultValues() {
    $defaults = array();
    $defaults['group_status[1]'] = 1;
    return $defaults;
  }

  public function buildQuickForm() {
    $this->add('text', 'title', ts('Find'),
      CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Group', 'title')
    );

    $this->add('text', 'created_by', ts('Created By'),
      CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Group', 'title')
    );

    $this->addButtons(array(
      array(
        'type' => 'refresh',
        'name' => ts('Search'),
        'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
    $this->assign('suppressForm', TRUE);
  }

  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    $parent = $this->controller->getParent();
    $parent->set('group_type', teamGroupType());
    if (!empty($params)) {
      $fields = array('title', 'created_by');
      foreach ($fields as $field) {
        if (isset($params[$field]) &&
          !CRM_Utils_System::isNull($params[$field])
        ) {
          $parent->set($field, $params[$field]);
        }
        else {
          $parent->set($field, NULL);
        }
      }
    }
  }

}
