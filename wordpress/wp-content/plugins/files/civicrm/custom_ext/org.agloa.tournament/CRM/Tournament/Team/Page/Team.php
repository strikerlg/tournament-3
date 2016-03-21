<?php

/**
 *
 * @package Tournament
 * $Id$
 *
 */
require_once 'Team.php';
class CRM_Tournament_Team_Page_Team extends CRM_Core_Page_Basic {
  protected $_sortByCharacter;

  /**
   * @return string
   */
  public function getBAOName() {
    return 'CRM_Contact_BAO_Group';
  }

  /**
   * Define action links.
   *
   *   self::$_links array of action links
   */
  public function &links() {
  }

  /**
   * Return class name of edit form.
   *
   * @return string
   */
  public function editForm() {
    return 'CRM_Tournament_Team_Form_Edit';
  }

  /**
   * Return name of edit form.
   *
   * @return string
   */
  public function editName() {
    return ts('Edit Team');
  }

  /**
   * Return name of delete form.
   *
   * @return string
   */
  public function deleteName() {
    return 'Delete Team';
  }

  /**
   * Return user context uri to return to.
   *
   * @param null $mode
   *
   * @return string
   */
  public function userContext($mode = NULL) {
    return 'civicrm/tournament/team';
  }

  /**
   * Return user context uri params.
   *
   * @param null $mode
   *
   * @return string
   */
  public function userContextParams($mode = NULL) {
    return 'reset=1&action=browse';
  }

  /**
   * Make sure that the user has permission to access this group.
   *
   * @param int $id
   *   The id of the object.
   * @param int $title
   *   Name or title of the object.
   *
   * @return string
   *   the permission that the user has (or null)
   */
  public function checkPermission($id, $title) {
    return Team::checkPermission($id, $title);
  }

  /**
   * We need to do slightly different things for groups vs saved search groups, hence we
   * reimplement browse from Page_Basic
   *
   * @param int $action
   *
   * @return void
   */
  public function browse($action = NULL) {
    $groupPermission = CRM_Core_Permission::check('edit groups') ? CRM_Core_Permission::EDIT : CRM_Core_Permission::VIEW;
    $this->assign('groupPermission', $groupPermission);

//    $showOrgInfo = FALSE;

//     $reservedPermission = CRM_Core_Permission::check('administer reserved groups') ? CRM_Core_Permission::EDIT : CRM_Core_Permission::VIEW;
//     $this->assign('reservedPermission', $reservedPermission);

//     if (CRM_Core_Permission::check('administer Multiple Organizations') &&
//       CRM_Core_Permission::isMultisiteEnabled()
//     ) {
//       $showOrgInfo = TRUE;
//     }
//     $this->assign('showOrgInfo', $showOrgInfo);

    // Refresh cache
    CRM_Contact_BAO_GroupContactCache::fillIfEmpty();

    $this->search();
  }

  public function search() {
    if ($this->_action & (CRM_Core_Action::ADD |
        CRM_Core_Action::UPDATE |
        CRM_Core_Action::DELETE
      )
    ) {
      return;
    }

    $form = new CRM_Core_Controller_Simple('CRM_Tournament_Team_Form_Search', ts('Search Teams'), CRM_Core_Action::ADD);
    $form->setEmbedded(TRUE);
    $form->setParent($this);
    $form->process();
    $form->run();
  }

}
