<?php
require_once 'CRM/Tournament/TournamentObject.php';

class Tournament extends TournamentObject {
	/**
	 *
	 *
	 * @var $event
	 */
	public $event;
	function __construct(){
		parent::__construct();

		// get current tournament, i.e., latest active event of type 'Tournament'
		$apiParams = array(
				'event_type_id' => $this->tournament_event_type_id()
				, 'is_active' => 1
				, 'options' => array( 'sort' => 'start_date DESC')
		);

		$this->event = $this->civicrm_api3_get('Event', $apiParams, 'getsingle');

		if (isset($this->event['error'])) {
			$this->is_error = true;
			$this->error_message = $this->event['error'];
		}
	}
}
