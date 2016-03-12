<?php

/**
 * Search for players on teams
 */
class CRM_Tournament_Form_Search_Player_Teams extends CRM_Contact_Form_Search_Custom_Base 
implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(ts('Search for Players on Teams'));

    $form->add('text', 'contact_id', ts('Player ID'), TRUE);
    $form->add('text', 'group_id', ts('Team ID'), TRUE);

    //$team = array('' => ts('- any team -')) + CRM_Core_PseudoConstant::stateProvince();
    //$form->addElement('select', 'group_id', ts('Team'), $team);

    // Optionally define default search values
    $form->setDefaults(array('contact_id' => '','group_id' => NULL));

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('contact_id', 'group_id'));
  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }
  
  public function count() {
  	return CRM_Core_DAO::singleValueQuery($this->sql('count(*) as total'));
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      ts('Player Id') => 'PlayerID',
      ts('Player Name') => 'PlayerName',
      ts('Team ID') => 'TeamID',
      ts('Team Name') => 'TeamName',
      ts('E') => 'E',
      ts('O') => 'O',
      ts('L') => 'L',
      ts('P') => 'P',
      ts('M') => 'M',
      ts('A') => 'A',
      ts('W') => 'W',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    $sql = $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
    return $sql;
  }
  
  /**
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $returnSQL
   *
   * @return string
   */
  public function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = FALSE) {
  	$sql = $this->sql(
  			'player.id as contact_id',
  			$offset,
  			$rowcount,
  			$sort
  	);
  	$this->validateUserSQL($sql);
  
  	if ($returnSQL) {
  		return $sql;
  	}
  
  	return CRM_Core_DAO::composeQuery($sql, CRM_Core_DAO::$_nullArray);
  }

  public static function includeContactIDs(&$sql, &$formValues) {
  	$contactIDs = array();
  	foreach ($formValues as $id => $value) {
  		if ($value &&
  				substr($id, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX
  		) {
  			$contactIDs[] = substr($id, CRM_Core_Form::CB_PREFIX_LEN);
  		}
  	}
  
  	if (!empty($contactIDs)) {
  		$contactIDs = implode(', ', $contactIDs);
  		$sql .= " AND player.id IN ( $contactIDs )";
  	}
  }
  
  /**
   * @param $sql
   * @param bool $onlyWhere
   *
   * @throws Exception
   */
  public function validateUserSQL(&$sql, $onlyWhere = FALSE) {
  	$includeStrings = array('civicrm_contact');
  	$excludeStrings = array('insert', 'delete', 'update');
  
  	if (!$onlyWhere) {
  		$includeStrings += array('select', 'from', 'where', 'civicrm_contact');
  	}
  
  	foreach ($includeStrings as $string) {
  		if (stripos($sql, $string) === FALSE) {
  			CRM_Core_Error::fatal(ts('Could not find \'%1\' string in SQL clause.',
  					array(1 => $string)
  			));
  		}
  	}
  
  	foreach ($excludeStrings as $string) {
  		if (preg_match('/(\s' . $string . ')|(' . $string . '\s)/i', $sql)) {
  			CRM_Core_Error::fatal(ts('Found illegal \'%1\' string in SQL clause.',
  					array(1 => $string)
  			));
  		}
  	}
  }
  
  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
  	$selectSQL =  "
  		player.id as PlayerID, player.sort_name AS PlayerName, team.id AS TeamID, team.title AS TeamName
			, equations_21 AS E, on_sets_22 AS O, linguishtik_23 AS L, propaganda_24 AS P, presidents_25 AS M, world_events_26 AS A, wff_n_proof_27 AS W 	
  	";
  	return $selectSQL;
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    $fromSQL = "
	    FROM civicrm_value_team_data_7 AS team_games
			JOIN civicrm_group AS team ON team.id = team_games.entity_id
			JOIN civicrm_group_contact AS team_player ON team_player.group_id = team.id
			JOIN civicrm_contact AS player ON player.id = team_player.contact_id
    ";
    return $fromSQL;
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $params = array();
    $where = "1";//contact_a.contact_type = 'Individual'";

//     $count  = 1;
//     $clause = array();
//     $name = CRM_Utils_Array::value('contact_id', $this->_formValues);
//     if ($name != NULL) {
//       if (strpos($name, '%') === FALSE) {
//         $name = "%{$name}%";
//       }
//       $params[$count] = array($name, 'String');
//       $clause[] = "contact_a.contact_id LIKE %{$count}";
//       $count++;
//     }

//     $group = CRM_Utils_Array::value('team_id', $this->_formValues);
//     if (!$group && $this->_groupID) {
//       $group = $this->_groupID;
//     }

//     if ($group) {
//       $params[$count] = array($group, 'Integer');
//       $clause[] = "group.id = %{$count}";
//     }

//     if (!empty($clause)) {
//       $where .= ' AND ' . implode(' AND ', $clause);
//     }

//     $whereClause = $this->whereClause($where, $params);
    $whereClause = "team.group_type LIKE '%3%' AND team.is_active = 1 AND team.is_hidden = 0";
    return $whereClause;
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Tournament/Player/Form/Search/Teams.tpl';
    //return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
    $row['sort_name'] .= ' ( altered )';
  }
}
