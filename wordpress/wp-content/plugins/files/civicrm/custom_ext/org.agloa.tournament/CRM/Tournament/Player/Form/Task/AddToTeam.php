<?php
/**
 *
 * @package Tournament
 * $Id$
 *
 */

require_once 'Team.php';
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
   * The name of the team
   *
   * @var string
   */
  protected $_teamGameText;
  
  /**
   * Store the available players
   *
   * @var array
   */
  protected $_eligiblePlayers;

  /**
   * Build all the data structures needed to build the form.
   *
   * @return void
   */
  public function preProcess() {  	  	
    // initialize the task and row fields
  	parent::preProcess();
  	$session = CRM_Core_Session::singleton();

  	// Step 1: if we can't get team ID, there's no point continuing.  
    $this->_id = $this->get('amtgID');
    if (!isset($this->_id)) $this->_id = CRM_Utils_Request::retrieve('amtgID', 'Positive');
    if (!isset($this->_id)) $this->_id = $session->get('amtgID');
    $session->set('amtgID', $this->_id);
    
    //@todo Exit more gracefully if id still isn't set
    if (!isset($this->_id)) return;  
    
    $this->_teamValues = array();
    $params = array('id' => $this->_id);
    $this->_team = CRM_Contact_BAO_Group::retrieve($params, $this->_teamValues);
    
    $this->_context = $this->get('context');
    
    $this->findTeamPlayers();
    $this->findEligiblePlayers();    
    
    $session = CRM_Core_Session::singleton();
    //$urlParams = 'reset=1&force=1';
    $session->replaceUserContext(CRM_Utils_System::url('civicrm/tournament/team'));//search', $urlParams));
  }
  
  private function findTeamPlayers(){
  	// find contacts in this group 	
  	if (isset($this->_id)){
  		$groupContacts = CRM_Contact_BAO_Group::getGroupContacts($this->_id);
  		if (count($groupContacts) > 0) {
  			$this->_contactIds = array();
  			foreach ($groupContacts as $groupContact) $this->_contactIds[] = $groupContact['contact_id'];
  		}
  	}
  }
  
  private function findEligiblePlayers(){	
  	// Retrieve registration groups available to current user
  	$contact_id = user_id_get();
  	$aclGroups = get_aclGroups($contact_id);
  	$registrationGroups = get_registrationGroups($aclGroups);
  	
  	// Start with all registered players
//   	$event_type_id = 7;
//   	$tournamentID = 1; //@todo
//   	$eventParams = array('id' => $tournamentID, 'event_type_id' => $event_type_id);
//   	$event = CRM_Event_BAO_Event::retrieve($eventParams, $eventDetails[$eventId]);
  	
//   	$params = array('event_id' => $event->id);
//   	CRM_Event_BAO_Participant::getValues($params, $defaults, $ids);	
  	
//   	foreach ($defaults as $participant)
//   	{
//   		$contact_id = $participant['contact_id'];
//   		$contacts[$contact_id] = $contact_id;
//   	}
  	
  	$groups = "(";
  	foreach ($registrationGroups as $group){
  		$groups .= "{$group["id"]},";
  	}
  	$groups = rtrim($groups, ",");
  	$groups .= ")";
  	$groupWhere = " group_id IN {$groups}";
  	
  	// Retrieve team games
  	$query = "
  	SELECT entity_id AS ID, equations_21 AS E, on_sets_22 AS O, linguishtik_23 AS L, propaganda_24 AS P, presidents_25 AS M, world_events_26 AS A, wff_n_proof_27 AS W
  	FROM civicrm_value_team_data_7 AS team_games WHERE `entity_id` = %1
  	";

  	$params = array(1 => array($this->_id, 'Integer'));
  	$teamGames = CRM_Core_DAO::executeQuery($query, $params);
  	$teamGames->fetch();

  	//@todo don't hard code game codes
  	$this->_teamGameText = "";
  	if (strlen($teamGames->E )> 0) $this->_teamGameText .= 'E';
  	if (strlen($teamGames->O )> 0) $this->_teamGameText .= 'O';
  	if (strlen($teamGames->L )> 0) $this->_teamGameText .= 'L';
  	if (strlen($teamGames->P )> 0) $this->_teamGameText .= 'P';
  	if (strlen($teamGames->M )> 0) $this->_teamGameText .= 'M';
  	if (strlen($teamGames->A )> 0) $this->_teamGameText .= 'A';
  	if (strlen($teamGames->W )> 0) $this->_teamGameText .= 'W';
  	$this->assign("teamGameText", $this->_teamGameText);

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
  	$groupType = '%4%5%'; //@todo

  	//TODO Subract/Union players already on a team
  	// Event Participants w/ role = player > contacts in groups playing that game
  	//     JOIN civicrm_group AS Teams ON DistrictPlayer.group_id = District.ID
  	// Teams.group_type LIKE %4
  	/*
  	 * SELECT `id`, `name`, `title`, `created_id` FROM `civicrm_group` WHERE `is_active` = 1 AND `group_type` LIKE '%3%' AND `is_hidden` = 0
  	*/

  	$params = array(
  			1 => array($eventID, 'Integer'),
  			2 => array($activeStatus, 'Integer'),
  			3 => array($playerRole, 'String'),
  			4 => array($groupType, 'String'),
  	);

  	$playerRecords = CRM_Core_DAO::executeQuery($query, $params);
  	while ($playerRecords->fetch()) {
  		$displayText = "{$playerRecords->Name} ({$playerRecords->DistrictName})";
  		 
  		//@todo don't hard code game codes
  		$gameText = "";
  		if (strlen($playerRecords->E )> 0) $gameText .= 'E';
  		if (strlen($playerRecords->O )> 0) $gameText .= 'O';
  		if (strlen($playerRecords->L )> 0) $gameText .= 'L';
  		if (strlen($playerRecords->P )> 0) $gameText .= 'P';
  		if (strlen($playerRecords->M )> 0) $gameText .= 'M';
  		if (strlen($playerRecords->A )> 0) $gameText .= 'A';
  		if (strlen($playerRecords->W )> 0) $gameText .= 'W';
  		if (strlen($gameText)> 0) $displayText .= " ({$gameText})";

  		$this->_eligiblePlayers[$playerRecords->PlayerID] = $displayText;
  	}
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
  	asort($this->_eligiblePlayers);
  	
  	$include = &$this->addElement('advmultiselect', 'availablePlayers',
  			ts('Eligible Players') . ' ', $this->_eligiblePlayers,
  			array(
  					'size' => 5,
  					'style' => 'width:auto;min-width:150pt',
  					'class' => 'advmultiselect',
  			)
  	);  	
  	
  	$include->setLabel(array('Team Players', 'Available', 'Already on Team'));
  	$include->setButtonAttributes('add', array('value' => ts('Add player >>')));
  	$include->setButtonAttributes('remove', array('value' => ts('<< Remove player')));
  	
  	// Select players already on ths team.
  	$include->setSelected($this->_contactIds);
  	
    // add select for groups
    $group = CRM_Core_PseudoConstant::nestedGroup();

    $this->_title = $group[$this->_id];
    $this->assign("teamName", $this->_title);

    CRM_Utils_System::setTitle(ts('Players for %1', array(1 => $this->_title)));
    $this->addDefaultButtons(ts("Commit players on the right to {$this->_title}"));
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
		define("MIN_PLAYERS", 1); //@todo disable commit until >=
		define("MAX_PLAYERS", 5); //@todo disable left until <
		define("CONTROL_NAME", 'availablePlayers');
		
  	$playerIDs = $params[CONTROL_NAME];
  	$playerCount = count($playerIDs);
    
    if ($playerCount < MIN_PLAYERS ||  $playerCount > MAX_PLAYERS) {
	    self::addError($errors[CONTROL_NAME], 
    		"Please add between " . MIN_PLAYERS . " and " . MAX_PLAYERS . " players.");
    }    
     
    $session = CRM_Core_Session::singleton();
    $teamID = $session->get('amtgID');
    foreach($playerIDs as $playerID) self::checkTeamPlayer($teamID, $playerID, $dupePlayerErrors);
    if (isset($dupePlayerErrors)) 
    {
    	self::addError($errors[CONTROL_NAME], $dupePlayerErrors);
    }
    
    return empty($errors) ? TRUE : $errors;
  }
  
  private function checkTeamPlayer($teamID, $playerID, &$errors){	
  	$otherTeams = Team::OtherTeams($teamID, $playerID);
  	if (isset($otherTeams))
  		self::addError($errors, $otherTeams);
  }
  
  private function addError(&$errors, $errorMessage){
  	if (empty($errors)) $errors = "";
  	if (is_array($errorMessage)) foreach ($errorMessage as $message) $errors .= "<p>{$message}</p>";
  	else $errors .= "<p>{$errorMessage}</p>";
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
    
    if (isset($groupID)) {
    	$selectedIDs = $params['availablePlayers'];
    	// First, remove any contacts before edit
    	CRM_Contact_BAO_GroupContact::removeContactsFromGroup($this->_contactIds, $groupID);
    	list($total, $added, $notAdded) = CRM_Contact_BAO_GroupContact::addContactsToGroup($selectedIDs, $groupID);

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

    }
    if ($this->_context === 'amtg') {
      CRM_Core_Session::singleton()
        ->pushUserContext(CRM_Utils_System::url('civicrm/tournament/team'));//search', "reset=1&force=1"));
    }
  }

}
/* Registered Players
 * SELECT player_contact.id as PlayerID,player_contact.sort_name as PlayerName,players.register_date
FROM civicrm_participant players
JOIN civicrm_contact player_contact ON player_contact.id = players.contact_id
WHERE players.event_id = 1 AND players.status_id = 1 AND players.role_id LIKE '1' AND players.is_test = 0
/
 */