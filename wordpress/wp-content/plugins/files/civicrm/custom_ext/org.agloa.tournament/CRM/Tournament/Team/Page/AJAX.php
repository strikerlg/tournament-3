<?php

/**
 *
 * @package Tournament
 *
 */

/**
 * This class contains the functions that are called using AJAX (jQuery)
 */
class CRM_Tournament_Team_Page_AJAX {
  /**
   * Get list of teams.
   *
   * @return array
   */
  public static function getTeamList() {
    $params = $_REQUEST;

    if (isset($params['parent_id'])) {
      // requesting child groups for a given parent
      $params['page'] = 1;
      $params['rp'] = 0;
      $groups = CRM_Contact_BAO_Group::getGroupListSelector($params);

      CRM_Utils_JSON::output($groups);
    }
    else {
      $sortMapper = array(
        0 => 'groups.title',
        1 => 'count',
        2 => 'createdBy.sort_name',
        3 => '',
        4 => 'groups.group_type',
        5 => 'groups.visibility',
      );

      $sEcho = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
      $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
      $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
      $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
      $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

      if ($sort && $sortOrder) {
        $params['sortBy'] = $sort . ' ' . $sortOrder;
      }

      $params['page'] = ($offset / $rowCount) + 1;
      $params['rp'] = $rowCount;
      $params['group_type'] = teamGroupType();
      $contact = billing_contact_get();
      $params['created_by'] = $contact['sort_name']; 

      // get team list
      $groups = CRM_Contact_BAO_Group::getGroupListSelector($params);
      // restrict to teams logged in user may access

      // if no groups found with parent-child hierarchy and logged in user say can view child groups only (an ACL case),
      // go ahead with flat hierarchy, CRM-12225
      if (empty($groups)) {
        $groupsAccessible = CRM_Core_PseudoConstant::group();
        $parentsOnly = CRM_Utils_Array::value('parentsOnly', $params);
        if (!empty($groupsAccessible) && $parentsOnly) {
          // recompute group list with flat hierarchy
          $params['parentsOnly'] = 0;
          $groups = CRM_Contact_BAO_Group::getGroupListSelector($params);
        }
      }

      $iFilteredTotal = $iTotal = count($groups);//$params['total'];
      $selectorElements = array(
        'group_name',
        'count',
        'created_by',
      'group_description',
        'group_type',
        'visibility',
        'org_info',
        'links',
        'class',
      );

      if (empty($params['showOrgInfo'])) {
        unset($selectorElements[6]);
      }
      //add setting so this can be tested by unit test
      //@todo - ideally the portion of this that retrieves the groups should be extracted into a function separate
      // from the one which deals with web inputs & outputs so we have a properly testable & re-usable function
      if (!empty($params['is_unit_test'])) {
        return array($groups, $iFilteredTotal);
      }
      header('Content-Type: application/json');
      echo CRM_Utils_JSON::encodeDataTableSelector($groups, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
      CRM_Utils_System::civiExit();
    }
  }

}
