<?php
/**
 * Search for group which matches title
 *
 *
 * @param $title string
 * @return array
 */
function named_group_get($title){
	return named_record_get("Group", $title);
}
/**
 * Search for profile which matches title
 *
 *
 * @param $title string
 * @return array
 */
function named_profile_get($title){
	return named_record_get("uf_group", $title);
}
/**
 * Search for report which matches title
 *
 *
 * @param $title string
 * @return array
 */
function named_report_get($title){
	return named_record_get("Report Instance", $title);
}

/**
 * Search table for record which matches title
 *
 *
 * @param $tableName string
 * @param $title string
 * @return array
 */
function named_record_get($tableName, $title){
	$action = "get";
	$apiParams = array("title" => $title);
	$result = civicrm_api3($tableName, $action, $apiParams);
	return $result;
}