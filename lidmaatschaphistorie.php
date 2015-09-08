<?php

require_once 'lidmaatschaphistorie.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function lidmaatschaphistorie_civicrm_config(&$config) {
  _lidmaatschaphistorie_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function lidmaatschaphistorie_civicrm_xmlMenu(&$files) {
  _lidmaatschaphistorie_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function lidmaatschaphistorie_civicrm_install() {
  return _lidmaatschaphistorie_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function lidmaatschaphistorie_civicrm_uninstall() {
  return _lidmaatschaphistorie_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function lidmaatschaphistorie_civicrm_enable() {
  return _lidmaatschaphistorie_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function lidmaatschaphistorie_civicrm_disable() {
  return _lidmaatschaphistorie_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function lidmaatschaphistorie_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _lidmaatschaphistorie_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function lidmaatschaphistorie_civicrm_managed(&$entities) {
  return _lidmaatschaphistorie_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function lidmaatschaphistorie_civicrm_caseTypes(&$caseTypes) {
  _lidmaatschaphistorie_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function lidmaatschaphistorie_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _lidmaatschaphistorie_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_pre
 * 
 * If the lidmaataschap is changed create a activity lidmaatschap historie with the changed values
 */
function lidmaatschaphistorie_civicrm_pre( $op, $objectName, $id, &$params ){
  // if it is membership and is alterted
  if('Membership' == $objectName){  
    $lidmaatschap_config = CRM_Lidmaatschaphistorie_Config::singleton();
    
    lidmaatschap_historie($op, $id, $params);
  }
}

function lidmaatschap_historie($op, $id, $params){  
  $lidmaatschap_config = CRM_Lidmaatschaphistorie_Config::singleton();
    
  if('create' == $op){
    $newvalues = lidmaatschap_historie_new_values($params);
    $activity_params = lidmaatschap_historie_create($lidmaatschap_config, $newvalues);
  }
  
  if('edit' == $op){
    $newvalues = lidmaatschap_historie_new_values($params);
    $oldvalues = lidmaatschap_historie_old_values($id);    
    
    $activity_params = lidmaatschap_historie_edit($lidmaatschap_config, $newvalues, $oldvalues);
  } 
    
  if('create' == $op or 'edit' == $op){
    if($activity_params){ // if there are no activity params (nothing has changed) don not create a lidmaatschap historie activity      
      lidmaatschap_historie_activity($lidmaatschap_config, $params, $activity_params);
    }
  }
  
  if('delete' == $op){
    foreach($params as $membership_id => $membership){
      $activity_params = lidmaatschap_historie_delete();
      
      lidmaatschap_historie_activity($lidmaatschap_config, $membership, $activity_params);
    }
  }
}

function lidmaatschap_historie_create($lidmaatschap_config, $newvalues){  
  $lidmaatschap_historie_custom_fields_by_name = $lidmaatschap_config->get_lidmaatschap_historie_custom_fields_by_name();
  $lidmaataschap_custom_fields = $lidmaatschap_config->get_lidmaatschap_custom_fields();
  
  $activity_params = array();
  
  $date_time = date('Y-m-d H:i:s');
  $details = ts(sprintf('Op %s is de lidmaatschap toegevoegd.', date('d-m-Y H:i:s', strtotime($date_time))));
  
  $activity_params['subject'] = ts(sprintf('Lidmaatschap toegevoegd op %s.', date('d-m-Y H:i:s', strtotime($date_time))));
  
  // loop through the changed values and set the paramter
  foreach($newvalues as $field => $value){
    switch($field){
      case 'status_id':
        $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusVan']['id']] = '';
        $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusNaar']['id']] = $value;
        break;
      case 'source':
        if(empty($value)){ 
          $value = ts('leeg');
        }
        
        $details .= '<br/>' . ts(sprintf('Bron is %s.', $value));
        break;
      case 'is_override':
        if('1' == $value){
          $details .= '<br/>' . ts('Handmatige status is aan.');
        }else if('0' == $value){
          $details .= '<br/>' . ts('Handmatige status is uit.');
        }
        break;
      case 'membership_type_id':
        $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeVan']['id']] = '';
        $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeNaar']['id']] = $value;
        break;
      case 'join_date':
        if('1970-01-01' == $value or empty($value)){
          $details .= '<br/>' . ts('Lidsinds is leeg.');
        }else {
          $details .= '<br/>' . ts(sprintf('Lidsinds %s.', date('d-m-Y', strtotime($value))));
        }
        break;
      case 'start_date':
        if('1970-01-01' == $value or empty($value)){
          $details .= '<br/>' . ts('Begindatum is leeg.');
        }else {
          $details .= '<br/>' . ts(sprintf('Begindatum %s.', date('d-m-Y', strtotime($value))));
        }
        break;
      case 'end_date':
        if('1970-01-01' == $value or empty($value)){
          $details .= '<br/>' . ts('Einddatum is leeg.');
        }else {
          $details .= '<br/>' . ts(sprintf('Einddatum %s.', date('d-m-Y', strtotime($value))));
        }
        break;
      default:
        if('custom_' == substr($field, 0, 7)){
          $custom_id = str_replace('custom_', '', $field);

          if('Maatschappij_lid' == $lidmaataschap_custom_fields[$custom_id]['name']){
            $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijVan']['id']] = '';
            $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijNaar']['id']] = $value;
          }

          if('lidnr' == $lidmaataschap_custom_fields[$custom_id]['name']){
            $details .= '<br/>' . ts(sprintf('%s %s.', $lidmaataschap_custom_fields[$custom_id]['label'], $value));
          }

          if('Maatschappij_anders' == $lidmaataschap_custom_fields[$custom_id]['name']){
            if(empty($value)){ 
              $value = ts('leeg');
            }
            $details .= '<br/>' . ts(sprintf('%s %s.', $lidmaataschap_custom_fields[$custom_id]['label'], $value));
          }
        }
    }
  }
  
  $activity_params['details'] = '<p>' . $details . '</p>';
  
  return $activity_params;
}

function lidmaatschap_historie_edit($lidmaatschap_config, $newvalues, $oldvalues){  
  // compare the new values with the old values
  // loop through the newvalues
  $is_changed = false;
  $changed_values = array();

  foreach($newvalues as $field => $newvalue){
    // if the value is empty and the old field does not exists or is empty don nothing
    if(empty($newvalue) and (!isset($oldvalues[$field]) or empty($oldvalues[$field]))){
      continue;
    }

    // status_id is speciale, because if is_override is on it must not be checked (both the newvalues and the oldvlaues)
    if('status_id' == $field){
      // if is_override is off both the newvalues and the oldvalues donot check the status_id
      if(!$newvalues['is_override'] and (!isset($oldvalues['is_override']) or !$oldvalues['is_override'])){
        continue;
      }

      // if the override is set off
      if(!$newvalues['is_override']){
        continue;
      }
    }

    // if the new value is diffrent than the old value, something has changes
    if(!isset($oldvalues[$field]) or $newvalue != $oldvalues[$field]){
      $is_changed = true;
      if(!isset($oldvalues[$field])){
        $oldvalues[$field] = ts('geen');
      }
      $changed_values[$field] = array('new' => $newvalue, 'old' => $oldvalues[$field]);
    }
  }
    
  // if there is nothing changed don create a lidmaatschap hsitorie activity
  if(!$is_changed){
    return false;
  }  
  
  $lidmaatschap_historie_custom_fields_by_name = $lidmaatschap_config->get_lidmaatschap_historie_custom_fields_by_name();
  $lidmaataschap_custom_fields = $lidmaatschap_config->get_lidmaatschap_custom_fields();
  $lidmaatschap_custom_fields_by_name = $lidmaatschap_config->get_lidmaatschap_custom_fields_by_name();
  
  $activity_params = array();
  
  $date_time = date('Y-m-d H:i:s');
  $details = ts(sprintf('Op %s is de lidmaatschap veranderd.', date('d-m-Y H:i:s', strtotime($date_time))));
  
  $activity_params['subject'] = ts(sprintf('Lidmaatschap veranderd op %s.', date('d-m-Y H:i:s', strtotime($date_time))));
    
  // loop through the changed values and set the paramter
  foreach($changed_values as $field => $values){
    switch($field){
      case 'status_id':
        $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusVan']['id']] = $values['old'];
        $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusNaar']['id']] = $values['new'];
        break;
      case 'source':
        if(empty($values['old'])){ 
          $values['old'] = ts('leeg');
        }
        if(empty($values['new'])){ 
          $values['new'] = ts('leeg');
        }

        $details .= '<br/>' . ts(sprintf('Bron is van %s naar %s.', $values['old'], $values['new']));
        break;
      case 'is_override':
        if('1' == $values['new']){
          $details .= '<br/>' . ts('Handmatige status is aan gezet.');
        }else if('0' == $values['new']){
          $details .= '<br/>' . ts('Handmatige status is uit gezet.');
        }
        break;
      case 'membership_type_id':
        $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeVan']['id']] = $values['old'];
        $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeNaar']['id']] = $values['new'];
        break;
      case 'join_date':
        // if both are empty or 1970-01-01, do nothing
        if((empty($values['old']) or '1970-01-01' == $values['old']) and (empty($values['new']) or '1970-01-01' == $values['new'])){
          
        }else if(empty($values['new'])) {
          $details .= '<br/>' . ts(sprintf('Lidsinds is van %s naar %s.', date('d-m-Y', strtotime($values['old']))));
          
        }else if('1970-01-01' == $values['old']){
          $details .= '<br/>' . ts(sprintf('Lidsinds is van %s naar %s.', date('d-m-Y', strtotime($values['new']))));
          
        }else {
          $details .= '<br/>' . ts(sprintf('Lidsinds is van %s naar %s.', date('d-m-Y', strtotime($values['old'])), date('d-m-Y', strtotime($values['new']))));
        }
        
        break;
      case 'start_date':
        // if both are empty or 1970-01-01, do nothing
        if((empty($values['old']) or '1970-01-01' == $values['old']) and (empty($values['new']) or '1970-01-01' == $values['new'])){
          
        }else if(empty($values['new'])) {
          $details .= '<br/>' . ts(sprintf('Begindatum is van %s naar %s.', date('d-m-Y', strtotime($values['old'])), date('d-m-Y', strtotime($values['new']))));
          
        }else if('1970-01-01' == $values['old']){
          $details .= '<br/>' . ts(sprintf('Begindatum is van leeg naar %s.', date('d-m-Y', strtotime($values['new']))));
          
        }else {
          $details .= '<br/>' . ts(sprintf('Begindatum is van %s naar %s.', date('d-m-Y', strtotime($values['old'])), date('d-m-Y', strtotime($values['new']))));
        }
        
        break;
      case 'end_date':
        // if both are empty or 1970-01-01, do nothing
        if((empty($values['old']) or '1970-01-01' == $values['old']) and (empty($values['new']) or '1970-01-01' == $values['new'])){
          
        }else if(empty($values['new'])) {
          $details .= '<br/>' . ts(sprintf('Einddatum is van %s naar leeg.', date('d-m-Y', strtotime($values['old']))));
          
        }else if('1970-01-01' == $values['old'] or 'geen' == $values['old']){
          $details .= '<br/>' . ts(sprintf('Einddatum is van leeg naar %s.', date('d-m-Y', strtotime($values['new']))));
        
        }else {
          $details .= '<br/>' . ts(sprintf('Einddatum is van %s naar %s.', date('d-m-Y', strtotime($values['old'])), date('d-m-Y', strtotime($values['new']))));
        }
        break;
      default:
        if('custom_' == substr($field, 0, 7)){
          $custom_id = str_replace('custom_', '', $field);

          if('Maatschappij_lid' == $lidmaataschap_custom_fields[$custom_id]['name']){
            $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijVan']['id']] = $values['old'];
            $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijNaar']['id']] = $values['new'];
          }

          if('lidnr' == $lidmaataschap_custom_fields[$custom_id]['name']){
            $details .= '<br/>' . ts(sprintf('%s is van %s naar %s.', $lidmaataschap_custom_fields[$custom_id]['label'], $values['old'], $values['new']));
          }

          if('Maatschappij_anders' == $lidmaataschap_custom_fields[$custom_id]['name']){
            if(empty($values['old'])){ 
              $values['old'] = ts('leeg');
            }
            if(empty($values['new'])){ 
              $values['new'] = ts('leeg');
            }
            $details .= '<br/>' . ts(sprintf('%s is van %s naar %s.', $lidmaataschap_custom_fields[$custom_id]['label'], $values['old'], $values['new']));
          }
        }
    }
  }
  
  /**
   * If the fields membership_type_id, status_id and Maatschappij-lid are not changed, then it must be filled
   * with the current value not empty. A request of the client
   */
  
  // membership_type_id
  // LidmaatschapsTypeVan
  if(!isset($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeVan']['id']]) or empty($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeVan']['id']])){
    $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeVan']['id']] = $newvalues['membership_type_id'];
  }
  
  // LidmaatschapsTypeNaar
  if(!isset($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeNaar']['id']]) or empty($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeNaar']['id']])){
    $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsTypeNaar']['id']] = $newvalues['membership_type_id'];
  }
  
  // status_id
  // LidmaatschapsStatusVan
  if(!isset($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusVan']['id']]) or empty($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusVan']['id']])){
    $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusVan']['id']] = $newvalues['status_id'];
  }
  
  // LidmaatschapsStatusNaar
  if(!isset($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusNaar']['id']]) or empty($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusNaar']['id']])){
    $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['LidmaatschapsStatusNaar']['id']] = $newvalues['status_id'];
  }
    
  // Maatschappij-lid
  // MaatschappijVan
  if(!isset($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijVan']['id']]) or empty($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijVan']['id']])){
    $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijVan']['id']] = $newvalues['custom_' . $lidmaatschap_custom_fields_by_name['Maatschappij_lid']['id']];
  }
  
  // MaatschappijNaar
  if(!isset($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijNaar']['id']]) or empty($activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijNaar']['id']])){
    $activity_params['custom_' . $lidmaatschap_historie_custom_fields_by_name['MaatschappijNaar']['id']] = $newvalues['custom_' . $lidmaatschap_custom_fields_by_name['Maatschappij_lid']['id']];
  }
    
  $activity_params['details'] = '<p>' . $details . '</p>';
    
  return $activity_params;
}

function lidmaatschap_historie_delete(){
  $date_time = date('Y-m-d H:i:s');
  
  $activity_params['subject'] = ts(sprintf('Lidmaatschap verwijderd op %s.', date('d-m-Y H:i:s', strtotime($date_time))));
  $activity_params['details'] = ts(sprintf('Op %s is de lidmaatschap verwijderd.', date('d-m-Y H:i:s', strtotime($date_time))));
  
   return $activity_params;
}

function lidmaatschap_historie_old_values($id){
  /*
  * Get the old values from the database
  * Compare the old values with the new values
  * If something is changed, create a new activty lidmaatschap historie with the changes
  */
 // get the old values
 $oldvalues_params = array(
   'version' => 3,
   'sequential' => 1,
   'id' => $id,
 );
 $oldvalues = civicrm_api('Membership', 'getsingle', $oldvalues_params);
 
 return $oldvalues;
}

function lidmaatschap_historie_new_values($params){
  // list with fields that must be checked
  $check_fields = array('status_id', 'source', 'is_override', 'membership_type_id', 'join_date', 'start_date', 'end_date', 'custom');   

  // first we make the params (newvalues) equal with oldvalues from the api
  $newvalues = array();
  foreach($params as $field => $value){
    // if field must be checked
    if(in_array($field, $check_fields)){

      // if it is custom 
      if('custom' == $field){
        foreach($value as $number1 => $array){
          foreach($array as $number2 => $array2){
            $field = 'custom_' . $number1;
            $newvalue = $array2['value'];
            $newvalues[$field] = $newvalue;
          }
        }
      }elseif('join_date' == $field or 'start_date' == $field or 'end_date' == $field){
        $newvalues[$field] = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
        // if date is 1970-01-01 it is officially empty
        if('1970-01-01' == $newvalues[$field] or '--' == $newvalues[$field]){
          $newvalues[$field] = '';
        }

      }else {
        $newvalues[$field] = $value;
      }
    }
  }
  
  return $newvalues;
}

function lidmaatschap_historie_activity($lidmaatschap_config, $params, $activity_params){
  $date_time = date('Y-m-d H:i:s');
  
  $activity_params = array_merge($activity_params, array(
    'version' => 3,
    'sequential' => 1,
    'activity_type_id' => $lidmaatschap_config->get_lidmaatschap_historie_activity_id(),
    'activity_date_time' => $date_time,
    //'duration' => '0',
    'location' => ts('Apeldoorn'),
    'status_id' => '2',
    'priority_id' => '2',
    'is_test' => '0',
    'is_auto' => '1',
    'is_current_revision' => '1',
    'is_deleted' => '0',
    'target_contact_id' => $params['contact_id'],
  ));
  
  /**
   * BOSW1509035 vnv.nl - aanmelden via webform
   * check if there is a source_contact_id
   * sometimes a memebership is created trough a drupal form when
   * nobody is loggedin so there is no source_contact_id, and 
   * if the source_contact_id is empty than the creation of the activity
   * fails. The source_contact_id must always defined.
   */
  $session = CRM_Core_Session::singleton();
  $source_contact_id = $session->get('userID');
  if(!empty($source_contact_id)){
    $activity_params['source_contact_id'] = $source_contact_id;
  }else {
    $activity_params['source_contact_id'] = $params['contact_id'];
  }
  
  // get display name
  try {
    $contact_params = array(
      'version' => 3,
      'sequential' => 1,
      'id' => $params['contact_id'],
      'return' => 'display_name',
    );
    $contact_result = civicrm_api('Contact', 'getvalue', $contact_params);
    
  } catch (CiviCRM_API3_Exception $ex) {
    throw new Exception('Could not get value display name from contact, '
      . 'error from API Contact getvalue: '.$ex->getMessage());
  }     	
  
  try {
    $activity_result = civicrm_api('Activity', 'create', $activity_params);
        
    if(isset($activity_result['is_error']) and $activity_result['is_error']){
      CRM_Core_Session::setStatus( ts(sprintf('Could not create activity lidmaatschap historie, error from API Activity create: %s', $activity_result['error_message'])), ts('nl.vnv.lidmaatschap'), 'error');
    }else {
      CRM_Core_Session::setStatus( ts(sprintf('Lidmaatschap historie voor de %s is automatisch toegevoegd.', $contact_result)), ts('Lidmaatschap Historie - Klaar'), 'success');
    }

  } catch (CiviCRM_API3_Exception $ex) {
    throw new Exception('Could not create activity lidmaatschap historie, '
      . 'error from API Activity create: '.$ex->getMessage());
    CRM_Core_Session::setStatus( 'Could not try to create activity lidmaatschap historie, error from API Activity create: '.$ex->getMessage(), ts('nl.vnv.lidmaatschap'), 'error');
  }
}