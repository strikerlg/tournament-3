<?php
// This file declares a managed database record of type "CustomSearch".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Tournament_Form_Search_Player_Teams',
    'entity' => 'CustomSearch',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Player_Teams',
      'description' => 'Player_Teams (org.agloa.tournament)',
      'class_name' => 'CRM_Tournament_Form_Search_Player_Teams',
    ),
  ),
);