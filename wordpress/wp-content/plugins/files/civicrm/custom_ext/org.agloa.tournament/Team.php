<?php

class Team extends CRM_Contact_BAO_Group {
  /**
   * static instance to hold the table name
   *
   * @var string
   */
  static $_customDataTableName = 'civicrm_value_team_data_7'; //@todo
  
  /**
   * static instance to hold the E field
   *
   * @var string
   */
  static $_E = 'equations_21'; //@todo
  
  /**
   * static instance to hold the O field
   *
   * @var string
   */
  static $_O = 'on_sets_22'; //@todo
  
  /**
   * static instance to hold the L field
   *
   * @var string
   */
  static $_L = 'linguishtik_23'; //@todo
  
  /**
   * static instance to hold the P field
   *
   * @var string
   */
  static $_P = 'propaganda_24'; //@todo
  
  /**
   * static instance to hold the M field
   *
   * @var string
   */
  static $_M = 'presidents_25'; //@todo
  
  /**
   * static instance to hold the A field
   *
   * @var string
   */
  static $_A = 'world_events_26'; //@todo
  
  /**
   * static instance to hold the W field
   *
   * @var string
   */
  static $_W = 'wff_n_proof_27'; //@todo
  /**
   * competitions the team is registered for
   *
   * @var array
   */
	public $competitions;	
  
  /**
   * players on the team
   *
   * @var array
   */
	public $players;	
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Make sure that the user has permission to access this team.
	 *
	 * @param int $id
	 *   The id of the object.
	 * @param bool $excludeHidden
	 *   Should hidden teams be excluded.
	 *   Logically this is the wrong place to filter hidden groups out as that is
	 *   not a permission issue. However, as other functions may rely on that defaulting to
	 *   FALSE for now & only the api call is calling with true.
	 *
	 * @return array
	 *   The permission that the user has (or NULL)
	 */
	public static function checkPermission($id, $excludeHidden = FALSE) {
		$allGroups = CRM_Core_PseudoConstant::allGroup(NULL, $excludeHidden);
	
// 		$permissions = NULL;
// 		if (CRM_Core_Permission::check('edit all contacts') ||
// 				CRM_ACL_API::groupPermission(CRM_ACL_API::EDIT, $id, NULL,
// 						'civicrm_saved_search', $allGroups
// 				)
// 		) {
// 			$permissions[] = CRM_Core_Permission::EDIT;
// 		}
		
		$permissions[] = CRM_Core_Permission::EDIT;
	
		// 		if (CRM_Core_Permission::check('view all contacts') ||
		// 				CRM_ACL_API::groupPermission(CRM_ACL_API::VIEW, $id, NULL,
		// 						'civicrm_saved_search', $allGroups
		// 				)
		// 		) {
		// 			$permissions[] = CRM_Core_Permission::VIEW;
		// 		}
		$permissions[] = CRM_Core_Permission::VIEW;
		
		//if (!empty($permissions) && CRM_Core_Permission::check('delete contacts')) {
		if (!empty($permissions) && CRM_Core_Permission::check('edit groups')) {
			// Note: using !empty() in if condition, restricts the scope of delete
			// permission to groups/contacts that are editable/viewable.
			// We can remove this !empty condition once we have ACL support for delete functionality.
			$permissions[] = CRM_Core_Permission::DELETE;
		}
	
		return $permissions;
	}
	
	
	/**
	 * Generate permissioned where clause for team search.
	 * @param array $params
	 * @param bool $sortBy
	 * @param bool $excludeHidden
	 *
	 * @return string
	 */
	public static function whereClause(&$params, $sortBy = TRUE, $excludeHidden = TRUE) {
		$values = array();
		$title = CRM_Utils_Array::value('title', $params);
		if ($title) {
			$clauses[] = "groups.title LIKE %1";
			if (strpos($title, '%') !== FALSE) {
				$params[1] = array($title, 'String', FALSE);
			}
			else {
				$params[1] = array($title, 'String', TRUE);
			}
		}
	
		$groupType = CRM_Utils_Array::value('group_type', $params);
		if ($groupType) {
			$types = explode(',', $groupType);
			if (!empty($types)) {
				$clauses[] = 'groups.group_type LIKE %2';
				$typeString = CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $types) . CRM_Core_DAO::VALUE_SEPARATOR;
				$params[2] = array($typeString, 'String', TRUE);
			}
		}
	
		$visibility = CRM_Utils_Array::value('visibility', $params);
		if ($visibility) {
			$clauses[] = 'groups.visibility = %3';
			$params[3] = array($visibility, 'String');
		}
	
		$groupStatus = CRM_Utils_Array::value('status', $params);
		if ($groupStatus) {
			switch ($groupStatus) {
				case 1:
					$clauses[] = 'groups.is_active = 1';
					$params[4] = array($groupStatus, 'Integer');
					break;
	
				case 2:
					$clauses[] = 'groups.is_active = 0';
					$params[4] = array($groupStatus, 'Integer');
					break;
	
				case 3:
					$clauses[] = '(groups.is_active = 0 OR groups.is_active = 1 )';
					break;
			}
		}
	
		$parentsOnly = CRM_Utils_Array::value('parentsOnly', $params);
		if ($parentsOnly) {
			$clauses[] = 'groups.parents IS NULL';
		}
	
		// only show child groups of a specific parent group
		$parent_id = CRM_Utils_Array::value('parent_id', $params);
		if ($parent_id) {
			$clauses[] = 'groups.id IN (SELECT child_group_id FROM civicrm_group_nesting WHERE parent_group_id = %5)';
			$params[5] = array($parent_id, 'Integer');
		}
	
		if ($createdBy = CRM_Utils_Array::value('created_by', $params)) {
			$clauses[] = "createdBy.sort_name LIKE %6";
			if (strpos($createdBy, '%') !== FALSE) {
				$params[6] = array($createdBy, 'String', FALSE);
			}
			else {
				$params[6] = array($createdBy, 'String', TRUE);
			}
		}
	
		if (empty($clauses)) {
			$clauses[] = 'groups.is_active = 1';
		}
	
		if ($excludeHidden) {
			$clauses[] = 'groups.is_hidden = 0';
		}
	
		$clauses[] = self::getPermissionClause();
	
		return implode(' AND ', $clauses);
	}
	
	/**
	 * Get permission relevant clauses.
	 *
	 * @param bool $force
	 *
	 * @return array
	 */
	public static function getPermissionClause($force = FALSE) {
		static $clause = 1;
		static $retrieved = FALSE;
// 		if ((!$retrieved || $force) && !CRM_Core_Permission::check('view all contacts') && !CRM_Core_Permission::check('edit all contacts')) {
// 			//get the allowed groups for the current user
// 			$groups = CRM_ACL_API::group(CRM_ACL_API::VIEW);
// 			if (!empty($groups)) {
// 				$groupList = implode(', ', array_values($groups));
// 				$clause = "groups.id IN ( $groupList ) ";
// 			}
// 			else {
// 				$clause = '1 = 0';
// 			}
// 		}
		$retrieved = TRUE;
		return $clause;
	}
	
	
	/**
	 * create a team description from competitions & players
	 *
	 * @param integer $teamID
	 *
	 * @return description
	 */
	public static function teamString($teamID){	
		$team = self::getTeam($teamID, $defaults);
		return $team->toString();
	}
	
	public function toString(){
		$description = "";
		foreach($this->competitions as $competition => $div)
		{
			if (strlen($div)>0) $description .= "{$competition}({$div}) ";
		}
		$description .= "<br/>";

		foreach($this->players as $id => $value){
			$params = array('id' => $id);
			$player = CRM_Contact_BAO_Contact::retrieve($params, $defaults);
			$description .= "{$player->display_name}, ";
		}
		$description = rtrim($description, ", ");
		return $description;
	}
	/**
	 * lookup teams by competition
	 *
	 * @param string $fieldName
	 *   competition name, e.g. 'E', 'O', etc.
	 *
	 * @param string $competition
	 *   competition value, e.g. 'EL', 'MID', etc.   
	 *   
	 * @return team
   *   teams where fieldname = competition
	 */
	public static function retrieveByCompetition($fieldName, $competition, $teamID = null){	
		$query = "SELECT entity_id FROM " . self::$_customDataTableName 
		. " WHERE " . $fieldName . " LIKE %1";
		
		if (isset($teamID)){
			$query .= " AND entity_id <> %2";
		}
		
		$params = array(1 => array($competition, 'String')
				, 2 => array($teamID, 'Integer'));
		$records = CRM_Core_DAO::executeQuery($query, $params);
		while ($records->fetch()) 
		{
			$teamID = $records->entity_id;
			$teams[$teamID] = self::getTeam($teamID, $defaults);
		}
		
		return $teams;
	}
	
	/**
	 * static function to find players on other teams for any game
	*/
	public static function OtherTeams($teamID, $playerID){
		$tournamentName = "2016";// @todo Tournament::currentTournamentName();
		$params = array('id' => $playerID);
		$player = CRM_Contact_BAO_Contact::retrieve($params, $defaults);
		$playerName = $player->sort_name;
		
		$team = Team::getTeam($teamID, $values);
		$team->otherTeamGamePlayerMessages('E', self::$_E, $playerID, $playerName, $tournamentName, $messages);
		$team->otherTeamGamePlayerMessages('O', self::$_O, $playerID, $playerName, $tournamentName, $messages);
		$team->otherTeamGamePlayerMessages('L', self::$_L, $playerID, $playerName, $tournamentName, $messages);
		$team->otherTeamGamePlayerMessages('P', self::$_P, $playerID, $playerName, $tournamentName, $messages);
		$team->otherTeamGamePlayerMessages('M', self::$_M, $playerID, $playerName, $tournamentName, $messages);
		$team->otherTeamGamePlayerMessages('A', self::$_A, $playerID, $playerName, $tournamentName, $messages);
		$team->otherTeamGamePlayerMessages('W', self::$_W, $playerID, $playerName, $tournamentName, $messages);
		return $messages;
	}
	
	private function otherTeamGamePlayerMessages($game, $field, $playerID, $playerName, $tournamentName, &$messages)
	{
		if (isset($this->competitions[$game]) && strlen($this->competitions[$game]) > 0)
		{
			$otherTeams = self::retrieveByCompetition($field, $this->competitions[$game], $this->id);
			// Is this player on any of those teams?
			foreach ($otherTeams as $otherTeam) {
				if ($otherTeam->contains($playerID))
				{
					$teamID = $otherTeam->id;
					$messages[] .= "$playerName is already on another {$game} team ({$otherTeam->title}) for tournament: {$tournamentName}";
				}
			}
		}
	}
	
	/**
	 * Does the team contain the player?
	 */
	public function contains($playerID) {
		return array_key_exists($playerID , $this->players );
	}
	
	/**
	 * Retrieve team object based on id.
	 *
	 * It also stores all the retrieved values in the default array.
	 *
	 * @param int $teamID
	 * @param array $defaults
	 *   (reference ) an assoc array to hold the flattened values.
	 *
	 * @return team
	 */
	public static function getTeam($teamID, &$defaults)
	{
		$params = array('id' => $teamID);
		
		$team = new Team();
		$team->copyValues($params);
		if ($team->find(TRUE)) {
			CRM_Core_DAO::storeValues($team, $defaults);
		}
		
		// lookup competitions from custom data table
		// Retrieve team competitions
		$query = "SELECT entity_id AS ID, "
		.  self::$_E . " AS E, "
		.  self::$_O . " AS O, "
		.  self::$_L . " AS L, "
		.  self::$_P . " AS P, "
		.  self::$_M . " AS M, "
		.  self::$_A . " AS A, "
		.  self::$_W . " AS W "
		. "FROM " . self::$_customDataTableName . " AS team_games WHERE `entity_id` = %1
		";
		
		$params = array(1 => array($teamID, 'Integer'));
		$records = CRM_Core_DAO::executeQuery($query, $params);
	
		while ($records->fetch()) 
		{
			$team->competitions['E'] = $records->E;
			$team->competitions['O'] = $records->O;
			$team->competitions['L'] = $records->L;
			$team->competitions['P'] = $records->P;
			$team->competitions['M'] = $records->M;
			$team->competitions['A'] = $records->A;
			$team->competitions['W'] = $records->W;
		}
		
		$team->players = self::getMember($teamID);
		
		return $team;
	}
}