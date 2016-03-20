<?php
/**
 *
 * @package Tournament
 *
 */
require_once 'Team.php';

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
			$groups = self::getTeamListSelector($params);

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
			
			// restrict to teams logged in user may access
			$contact = billing_contact_get(); //@todo remove for admin
			$params['created_by'] = $contact['sort_name'];

			// get team list
			$groups = self::getTeamListSelector($params);

			// if no groups found with parent-child hierarchy and logged in user say can view child groups only (an ACL case),
			// go ahead with flat hierarchy
			if (empty($groups)) {
				$groupsAccessible = CRM_Core_PseudoConstant::group();
				$parentsOnly = CRM_Utils_Array::value('parentsOnly', $params);
				if (!empty($groupsAccessible) && $parentsOnly) {
					// recompute group list with flat hierarchy
					$params['parentsOnly'] = 0;
					$groups = self::getTeamListSelector($params);
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


	/**
	 * wrapper for ajax group selector.
	 *
	 * @param array $params
	 *   Associated array for params record id.
	 *
	 * @return array
	 *   associated array of group list
	 *   -rp = rowcount
	 *   -page= offset
	 * @todo there seems little reason for the small number of functions that call this to pass in
	 * params that then need to be translated in this function since they are coding them when calling
	 */
	static public function getTeamListSelector(&$params) {
		// format the params
		$params['offset'] = ($params['page'] - 1) * $params['rp'];
		$params['rowCount'] = $params['rp'];
		$params['sort'] = CRM_Utils_Array::value('sortBy', $params);

		// get groups
		$groups = self::getGroupList($params);

		//skip total if we are making call to show only children
		if (empty($params['parent_id'])) {
			// add total
			$params['total'] = CRM_Contact_BAO_Group::getGroupCount($params);

			// get all the groups
			$allGroups = CRM_Core_PseudoConstant::allGroup();
		}

		// format params and add links
		$groupList = array();
		if (!empty($groups)) {
			foreach ($groups as $id => $value) {
				$groupList[$id]['group_id'] = $value['id'];
				$groupList[$id]['count'] = $value['count'];
				$groupList[$id]['group_name'] = $value['title'];

				// append parent names if in search mode
				if (empty($params['parent_id']) && !empty($value['parents'])) {
					$groupIds = explode(',', $value['parents']);
					$title = array();
					foreach ($groupIds as $gId) {
						$title[] = $allGroups[$gId];
					}
					$groupList[$id]['group_name'] .= '<div class="crm-row-parent-name"><em>' . ts('Child of') . '</em>: ' . implode(', ', $title) . '</div>';
					$value['class'] = array_diff($value['class'], array('crm-row-parent'));
				}
				$value['class'][] = 'crm-entity';
				$groupList[$id]['class'] = $value['id'] . ',' . implode(' ', $value['class']);

				$groupList[$id]['group_description'] = Team::teamString($id);
				//CRM_Utils_Array::value('description', $value);
				if (!empty($value['group_type'])) {
					$groupList[$id]['group_type'] = $value['group_type'];
				}
				else {
					$groupList[$id]['group_type'] = '';
				}
				$groupList[$id]['visibility'] = $value['visibility'];
				$groupList[$id]['links'] = $value['action'];
				$groupList[$id]['org_info'] = CRM_Utils_Array::value('org_info', $value);
				$groupList[$id]['created_by'] = CRM_Utils_Array::value('created_by', $value);

				$groupList[$id]['is_parent'] = $value['is_parent'];
			}
			return $groupList;
		}
	}

	/**
	 * This function to get list of groups.
	 *
	 * @param array $params
	 *   Associated array for params.
	 *
	 * @return array
	 */
	public static function getGroupList(&$params) {
		$config = CRM_Core_Config::singleton();

		$whereClause = Team::whereClause($params, FALSE);

		//$this->pagerAToZ( $whereClause, $params );

		if (!empty($params['rowCount']) &&
				$params['rowCount'] > 0
		) {
			$limit = " LIMIT {$params['offset']}, {$params['rowCount']} ";
		}

		$orderBy = ' ORDER BY groups.title asc';
		if (!empty($params['sort'])) {
			$orderBy = ' ORDER BY ' . CRM_Utils_Type::escape($params['sort'], 'String');

			if (strpos($params['sort'], 'count') === 0) {
				$orderBy = $limit = '';
			}
		}

		$select = $from = $where = "";
		$groupOrg = FALSE;
		if (CRM_Core_Permission::check('administer Multiple Organizations') &&
				CRM_Core_Permission::isMultisiteEnabled()
		) {
			$select = ", contact.display_name as org_name, contact.id as org_id";
			$from = " LEFT JOIN civicrm_group_organization gOrg
			ON gOrg.group_id = groups.id
			LEFT JOIN civicrm_contact contact
			ON contact.id = gOrg.organization_id ";

			//get the Organization ID
			$orgID = CRM_Utils_Request::retrieve('oid', 'Positive', CRM_Core_DAO::$_nullObject);
			if ($orgID) {
				$where = " AND gOrg.organization_id = {$orgID}";
			}

			$groupOrg = TRUE;
		}

		$query = "
		SELECT groups.*, createdBy.sort_name as created_by {$select}
		FROM  civicrm_group groups
		LEFT JOIN civicrm_contact createdBy
		ON createdBy.id = groups.created_id
		{$from}
		WHERE $whereClause {$where}
		GROUP BY groups.id
		{$orderBy}
		{$limit}";

		$object = CRM_Core_DAO::executeQuery($query, $params, TRUE, 'CRM_Contact_DAO_Group');

		//FIXME CRM-4418, now we are handling delete separately
		//if we introduce 'delete for group' make sure to handle here.
		$groupPermissions = array(CRM_Core_Permission::VIEW);
		if (CRM_Core_Permission::check('edit groups')) {
			$groupPermissions[] = CRM_Core_Permission::EDIT;
			$groupPermissions[] = CRM_Core_Permission::DELETE;
		}

		$reservedPermission = CRM_Core_Permission::check('administer reserved groups');

		$links = self::actionLinks();

		$allTypes = CRM_Core_OptionGroup::values('group_type');
		$values = $groupsToCount = array();

		$visibility = CRM_Core_SelectValues::ufVisibility();

		while ($object->fetch()) {
			$permission = Team::checkPermission($object->id, $object->title);
			//@todo CRM-12209 introduced an ACL check in the whereClause function
			// it may be that this checking is now obsolete - or that what remains
			// should be removed to the whereClause (which is also accessed by getCount)

			if ($permission) {
				$newLinks = $links;
				$values[$object->id] = array(
						'class' => array(),
						'count' => '0',
				);
				CRM_Core_DAO::storeValues($object, $values[$object->id]);
				// Wrap with crm-editable. Not an ideal solution.
				if (in_array(CRM_Core_Permission::EDIT, $groupPermissions)) {
					$values[$object->id]['title'] = '<span class="crm-editable crmf-title">' . $values[$object->id]['title'] . '</span>';
				}

				if ($object->saved_search_id) {
					$values[$object->id]['title'] .= ' (' . ts('Smart Group') . ')';
					// check if custom search, if so fix view link
					$customSearchID = CRM_Core_DAO::getFieldValue(
							'CRM_Contact_DAO_SavedSearch',
							$object->saved_search_id,
							'search_custom_id'
					);

					if ($customSearchID) {
						$newLinks[CRM_Core_Action::VIEW]['url'] = 'civicrm/contact/search/custom';
						$newLinks[CRM_Core_Action::VIEW]['qs'] = "reset=1&force=1&ssID={$object->saved_search_id}";
					}
				}

				$action = array_sum(array_keys($newLinks));

				if (array_key_exists('is_reserved', $object)) {
					//if group is reserved and I don't have reserved permission, suppress delete/edit
					if ($object->is_reserved && !$reservedPermission) {
						$action -= CRM_Core_Action::DELETE;
						$action -= CRM_Core_Action::UPDATE;
						$action -= CRM_Core_Action::DISABLE;
					}
				}

				if (array_key_exists('is_active', $object)) {
					if ($object->is_active) {
						$action -= CRM_Core_Action::ENABLE;
					}
					else {
						$values[$object->id]['class'][] = 'disabled';
						$action -= CRM_Core_Action::VIEW;
						$action -= CRM_Core_Action::DISABLE;
					}
				}

				$action = $action & CRM_Core_Action::mask($groupPermissions);

				$values[$object->id]['visibility'] = $visibility[$values[$object->id]['visibility']];

				$groupsToCount[$object->saved_search_id ? 'civicrm_group_contact_cache' : 'civicrm_group_contact'][] = $object->id;

				if (isset($values[$object->id]['group_type'])) {
					$groupTypes = explode(CRM_Core_DAO::VALUE_SEPARATOR,
							substr($values[$object->id]['group_type'], 1, -1)
					);
					$types = array();
					foreach ($groupTypes as $type) {
						$types[] = CRM_Utils_Array::value($type, $allTypes);
					}
					$values[$object->id]['group_type'] = implode(', ', $types);
				}
				$values[$object->id]['action'] = CRM_Core_Action::formLink($newLinks,
						$action,
						array(
								'id' => $object->id,
								'ssid' => $object->saved_search_id,
						),
						ts('more'),
						FALSE,
						'group.selector.row',
						'Group',
						$object->id
				);

				// If group has children, add class for link to view children
				$values[$object->id]['is_parent'] = FALSE;
				if (array_key_exists('children', $values[$object->id])) {
					$values[$object->id]['class'][] = "crm-group-parent";
					$values[$object->id]['is_parent'] = TRUE;
				}

				// If group is a child, add child class
				if (array_key_exists('parents', $values[$object->id])) {
					$values[$object->id]['class'][] = "crm-group-child";
				}

				if ($groupOrg) {
					if ($object->org_id) {
						$contactUrl = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$object->org_id}");
						$values[$object->id]['org_info'] = "<a href='{$contactUrl}'>{$object->org_name}</a>";
					}
					else {
						$values[$object->id]['org_info'] = ''; // Empty cell
					}
				}
				else {
					$values[$object->id]['org_info'] = NULL; // Collapsed column if all cells are NULL
				}
				if ($object->created_id) {
					$contactUrl = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$object->created_id}");
					$values[$object->id]['created_by'] = "<a href='{$contactUrl}'>{$object->created_by}</a>";
				}
			}
		}

		// Get group counts - executes one query for regular groups and another for smart groups
		foreach ($groupsToCount as $table => $groups) {
			$where = "g.group_id IN (" . implode(',', $groups) . ")";
			if ($table == 'civicrm_group_contact') {
				$where .= " AND g.status = 'Added'";
			}
			// Exclude deleted contacts
			$where .= " and c.id = g.contact_id AND c.is_deleted = 0";
			$dao = CRM_Core_DAO::executeQuery("SELECT g.group_id, COUNT(g.id) as `count` FROM $table g, civicrm_contact c WHERE $where GROUP BY g.group_id");
			while ($dao->fetch()) {
				$values[$dao->group_id]['count'] = $dao->count;
			}
		}

		// CRM-16905 - Sort by count cannot be done with sql
		if (!empty($params['sort']) && strpos($params['sort'], 'count') === 0) {
			usort($values, function($a, $b) {
				return $a['count'] - $b['count'];
			});
			if (strpos($params['sort'], 'desc')) {
				$values = array_reverse($values, TRUE);
			}
			return array_slice($values, $params['offset'], $params['rowCount']);
		}

		return $values;
	}


	/**
	 * Define action links.
	 *
	 * @return array
	 *   array of action links
	 */
	static function actionLinks() {
		$links = array(
				CRM_Core_Action::VIEW => array(
						'name' => ts('Players'),
						'url' => 'civicrm/tournament/team/editPlayers',
						'qs' => 'reset=1&force=1&context=amtg&amtgID=%%id%%',
						'title' => ts('Team Players'),
				),
				CRM_Core_Action::UPDATE => array(
						'name' => ts('Games'),
						'url' => 'civicrm/group',
						'qs' => 'reset=1&action=update&id=%%id%%',
						'title' => ts('Edit Team'),
				),
				CRM_Core_Action::DISABLE => array(
						'name' => ts('Disable'),
						'ref' => 'crm-enable-disable',
						'title' => ts('Disable Team'),
				),
				CRM_Core_Action::ENABLE => array(
						'name' => ts('Enable'),
						'ref' => 'crm-enable-disable',
						'title' => ts('Enable Team'),
				),
				CRM_Core_Action::DELETE => array(
						'name' => ts('Delete'),
						'url' => 'civicrm/group',
						'qs' => 'reset=1&action=delete&id=%%id%%',
						'title' => ts('Delete Team'),
				),
		);

		return $links;
	}
}