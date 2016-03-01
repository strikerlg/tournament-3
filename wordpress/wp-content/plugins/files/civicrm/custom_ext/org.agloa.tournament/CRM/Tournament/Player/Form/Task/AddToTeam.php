<?php
/**
 *
 * @package Tournament
 * $Id$
 *
 */

/**
 * This class provides the functionality to group players into teams.
 */
class CRM_Tournament_Player_Form_Task_AddToTeam extends CRM_Contact_Form_Task {

  /**
   * The context that we are working on
   *
   * @var string
   */
  protected $_context;

  /**
   * The groupId retrieved from the GET vars
   *
   * @var int
   */
  protected $_id;

  /**
   * The name of the team
   *
   * @var string
   */
  protected $_title;

  /**
   * The team object
   *
   * @var object
   */
  protected $_team;

  /**
   * Store the team values
   *
   * @var array
   */
  protected $_teamValues;
  
  /**
   * Store the available players
   *
   * @var array
   */
  protected $_players;

  /**
   * Build all the data structures needed to build the form.
   *
   * @return void
   */
  public function preProcess() {  	
  	// Retrieve groups that current user may access.
  	$contact_id = user_id_get();
  	$aclGroups = get_aclGroups($contact_id);
  	$registrationGroups = get_registrationGroups($aclGroups);
  	$groups = "(";
  	foreach ($registrationGroups as $group){
  		$groups .= "{$group["id"]},";
  	}
  	$groups = rtrim($groups, ",");
  	$groups .= ")";
  	$groupWhere = " group_id IN {$groups}";
  	
    // initialize the task and row fields
    parent::preProcess();
    
    // Retrieve team games
    $query = "
    SELECT entity_id AS ID, equations_21 AS E, on_sets_22 AS O, linguishtik_23 AS L, propaganda_24 AS P, presidents_25 AS M, world_events_26 AS A, wff_n_proof_27 AS W
    FROM civicrm_value_team_data_7 AS team_games WHERE `entity_id` = %1
    ";
    
    $this->_id = $this->get('amtgID');
    $params = array(1 => array($this->_id, 'Integer'));
    
    $teamGames = CRM_Core_DAO::executeQuery($query, $params);
    $teamGames->fetch();
    
    $gamesWhere = "";
    $gamesWhere = $this->gamesWhere($gamesWhere, $teamGames->E, "equations_11");
    $gamesWhere = $this->gamesWhere($gamesWhere, $teamGames->O, "on_sets_12");
    $gamesWhere = $this->gamesWhere($gamesWhere, $teamGames->L, "linguishtik_13");
    $gamesWhere = $this->gamesWhere($gamesWhere, $teamGames->P, "propaganda_14");
    $gamesWhere = $this->gamesWhere($gamesWhere, $teamGames->M, "presidents_15");
    $gamesWhere = $this->gamesWhere($gamesWhere, $teamGames->A, "world_events_16");
    $gamesWhere = $this->gamesWhere($gamesWhere, $teamGames->W, "wff_n_proof_17");
    
    // Select players matching team criteria
    $query = "
    SELECT player.contact_id AS PlayerID, person.sort_name AS Name
    , group_id AS DistrictID, District.title AS DistrictName
    , equations_11 AS E, on_sets_12 AS O, linguishtik_13 AS L, propaganda_14 AS P, presidents_15 AS M, world_events_16 AS A, wff_n_proof_17 AS W
    FROM civicrm_value_games_registration_5 AS player_games
    JOIN civicrm_participant AS player ON player.id = player_games.entity_id
    JOIN civicrm_contact AS person ON player.contact_id = person.id
    JOIN civicrm_group_contact AS DistrictPlayer ON DistrictPlayer.contact_id = person.id
    JOIN civicrm_group AS District ON DistrictPlayer.group_id = District.ID
    WHERE player.event_id = %1 AND player.status_id = %2 AND player.role_id LIKE %3 AND is_test = 0
    AND status LIKE 'Added' AND is_active = 1 AND District.group_type LIKE %4
    ";    
    if (strlen($gamesWhere) > 0) $query .= " AND {$gamesWhere}";
    if (strlen($groupWhere) > 0) $query .= " AND {$groupWhere}";
    
    $eventID = 1;
    $activeStatus = 1;
    $playerRole = '1';
    $groupType = '%4%5%';
    
    //TODO Subract/Union players not already on a team
    // Event Participants w/ role = player > contacts in groups playing that game
    //     JOIN civicrm_group AS Teams ON DistrictPlayer.group_id = District.ID
    // Teams.group_type LIKE %4
    
    $params = array(
    		1 => array($eventID, 'Integer'),
    		2 => array($activeStatus, 'Integer'),
    		3 => array($playerRole, 'String'),
    		4 => array($groupType, 'String'),
    );
    
    $playerRecords = CRM_Core_DAO::executeQuery($query, $params);
    while ($playerRecords->fetch()) {
    	$displayText = "{$playerRecords->Name} ({$playerRecords->DistrictName})";
    	
    	$gameText = "";
    	if (strlen($playerRecords->E )> 0) $gameText .= 'E';
    	if (strlen($playerRecords->O )> 0) $gameText .= 'O';
    	if (strlen($playerRecords->L )> 0) $gameText .= 'L';
    	if (strlen($playerRecords->P )> 0) $gameText .= 'P';
    	if (strlen($playerRecords->M )> 0) $gameText .= 'M';
    	if (strlen($playerRecords->A )> 0) $gameText .= 'A';
    	if (strlen($playerRecords->W )> 0) $gameText .= 'W';
    	if (strlen($gameText)> 0) $displayText .= " ({$gameText})";
    	 
    	$this->_players[$playerRecords->PlayerID] = $displayText;
    } 
    
    $this->_teamValues = array();
    $params = array('id' => $this->_id);
    $this->_team = CRM_Contact_BAO_Group::retrieve($params, $this->_teamValues);
    $this->_context = $this->get('context');
    
    //var_dump($this->_players);
    $session = CRM_Core_Session::singleton();
    
    $urlParams = 'reset=1&force=1';
    $session->replaceUserContext(CRM_Utils_System::url('civicrm/tournament/team/search', $urlParams));
  }
  
  private function gamesWhere($gamesWhere, $gameField, $fieldName){
  	if (strlen($gameField) > 0) {
  		if (strlen($gamesWhere)  > 0) $gamesWhere .= " AND ";
  		$gamesWhere .= "{$fieldName} LIKE '$gameField'";
  	}
  	return $gamesWhere;
  }

  /**
   * Build the form object.
   *
   *
   * @return void
   */
  public function buildQuickForm() {  	
  	asort($this->_players);
  	
  	$multiSelect = &$this->addElement('advmultiselect', 'availablePlayers',
  			ts('Selected Players') . ' ', $this->_players,
  			array(
  					'size' => 5,
  					'style' => 'width:auto;min-width:150pt',
  					'class' => 'advmultiselect',
  			)
  	);
  	
    // add select for groups
    $group = CRM_Core_PseudoConstant::nestedGroup();

    $this->_title = $group[$this->_id];
    $this->assign("teamName", $this->_title);

    CRM_Utils_System::setTitle(ts('Add Players to %1', array(1 => $this->_title)));
    $this->addDefaultButtons(ts("Add Selected Players to {$this->_title}"));
  }

  /**
   * Set the default form values.
   *
   *
   * @return array
   *   the default array reference
   */
  public function setDefaultValues() {
    $defaults = array();
    return $defaults;
  }

  /**
   * Add local and global form rules.
   *
   *
   * @return void
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Tournament_Player_Form_Task_AddToTeam', 'formRule'));
  }

  /**
   * Global validation rules for the form.
   *
   * @param array $params
   *
   * @return array
   *   list of errors to be posted back to the form
   */
  public static function formRule($params) {
    $errors = array();
  	//if (count($aclGroups) < 1) return; //TODO Error message
  	// 5 pplayer limit
    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Process the form after the input has been submitted and validated.
   *
   *
   * @return void
   */
  public function postProcess() {
    $params = $this->controller->exportValues();
    $groupOption = CRM_Utils_Array::value('group_option', $params, NULL);
    if ($groupOption) {
      $groupParams = array();
      $groupParams['title'] = $params['title'];
      $groupParams['description'] = $params['description'];
      $groupParams['visibility'] = "User and User Admin Only";
      if (array_key_exists('group_type', $params) && is_array($params['group_type'])) {
        $groupParams['group_type'] = CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR,
            array_keys($params['group_type'])
          ) . CRM_Core_DAO::VALUE_SEPARATOR;
      }
      else {
        $groupParams['group_type'] = '';
      }
      $groupParams['is_active'] = 1;

      $createdGroup = CRM_Contact_BAO_Group::create($groupParams);
      $groupID = $createdGroup->id;
      $groupName = $groupParams['title'];
    }
    else {
      $groupID = $params['group_id'];
      if (!isset($groupID)) $groupID = $this->_id;
      $group = CRM_Core_PseudoConstant::group();
      $groupName = $group[$groupID];
    }
// TODO load contactIDs from availablePlayers
    $this->_contactIds = $params['availablePlayers'];
    list($total, $added, $notAdded) = CRM_Contact_BAO_GroupContact::addContactsToGroup($this->_contactIds, $groupID);

    $status = array(
      ts('%count player added to team', array(
          'count' => $added,
          'plural' => '%count players added to team',
      )),
    );
    if ($notAdded) {
      $status[] = ts('%count player was already on team', array(
          'count' => $notAdded,
          'plural' => '%count players were already on team',
        ));
    }
    $status = '<ul><li>' . implode('</li><li>', $status) . '</li></ul>';
    CRM_Core_Session::setStatus($status, ts('Added Player to %1', array(
          1 => $groupName,
          'count' => $added,
          'plural' => 'Added Players to %1',
        )), 'success', array('expires' => 0));

    if ($this->_context === 'amtg') {
      CRM_Core_Session::singleton()
        ->pushUserContext(CRM_Utils_System::url('civicrm/tournament/team/search', "reset=1&force=1"));
    }
  }

}
