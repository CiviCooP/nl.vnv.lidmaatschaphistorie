<?php
/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Jan-Derek (CiviCooP) <j.vos@bosqom.nl>
 */
class CRM_Lidmaatschaphistorie_Config {
  /*
   * singleton pattern
   */
  static private $_singleton = NULL;
  
  protected $_activity_type_id = 0;
  protected $_lidmaatschap_historie_activity_id = 0;
  
  protected $_lidmaatschap_historie_custom_group_id = 0;
  protected $_lidmaatschap_historie_custom_group = array();
  protected $_lidmaatschap_historie_custom_fields = array();
  protected $_lidmaatschap_historie_custom_fields_by_name = array();
  protected $_lidmaatschap_historie_option_values = array();
  
  protected $_lidmaatschap_custom_group_id = 0;
  protected $_lidmaatschap_custom_group = array();
  protected $_lidmaatschap_custom_fields = array();

  /**
   * Constructor
   */
  function __construct() {
    $this->set_activitytype_id();
    $this->set_lidmaatschap_historie_activity_id();
        
    $this->set_lidmaatschap_historie_custom_group_id();
    $this->set_lidmaatschap_historie_custom_group();
    $this->set_lidmaatschap_historie_custom_fields();
    $this->set_lidmaatschap_historie_option_values();
    
    $this->set_lidmaatschap_custom_group_id();
    $this->set_lidmaatschap_custom_group();
    $this->set_lidmaatschap_custom_fields();
  }
  
  /**
   * Function to return singleton object
   * 
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Lidmaatschaphistorie_Config();
    }
    return self::$_singleton;
  }
  
  /*
   * Set activity type id
   */
  protected function set_activitytype_id() {
    try {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'name' => 'activity_type',
      );
      $result = civicrm_api('OptionGroup', 'getsingle', $params);
      $this->_activity_type_id = $result['id'];
      
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find activity type id, '
        . 'error from API OptionGroup getsingle: '.$ex->getMessage());
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  /*
   * Get activity type id
   */
  public function get_activitytype_id() {
    return $this->_activity_type_id;
  }
    
  /*
   * Set lidmaatschap historie activity id
   */
  protected function set_lidmaatschap_historie_activity_id() {
    try {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'option_group_id' => $this->_activity_type_id,
        'name' => 'Lidmaatschap Historie',
      );
      $result = civicrm_api('OptionValue', 'getsingle', $params);      
      $this->_lidmaatschap_historie_activity_id = $result['value'];
      
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find lidmaatschap historie activity id, '
        . 'error from API OptionValue getsingle: '.$ex->getMessage());
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  /*
   * Get lidmaatschap historie activity id
   */
  public function get_lidmaatschap_historie_activity_id() {
    return $this->_lidmaatschap_historie_activity_id;
  }
  
  /*
   * Set lidmaatschap historie custom group id
   * The id is to get all the custom fields
   */
  protected function set_lidmaatschap_historie_custom_group_id() {
    try {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'name' => 'Lidmaatschap_Historie',
      );
      $result = civicrm_api('CustomGroup', 'getsingle', $params);     
      $this->_lidmaatschap_historie_custom_group_id = $result['id'];
      
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find lidmaatschap historie custom group id, '
        . 'error from API CustomGroup getsingle: '.$ex->getMessage());
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  /*
   * Get lidmaatschap historie custom group id
   */
  public function get_lidmaatschap_historie_custom_group_id() {
    return $this->_lidmaatschap_historie_custom_group_id;    
  }
  
  /*
   * Set lidmaatschap historie custom group
   */
  protected function set_lidmaatschap_historie_custom_group() {
    try {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $this->_lidmaatschap_historie_custom_group_id,
      );
      $result = civicrm_api('CustomGroup', 'getsingle', $params);     
      $this->_lidmaatschap_historie_custom_group = $result;
      
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find lidmaatschap historie custom group, '
        . 'error from API CustomGroup getsingle: '.$ex->getMessage());
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  /*
   * Get lidmaatschap historie custom group
   */
  public function get_lidmaatschap_historie_custom_group() {
    return $this->_lidmaatschap_historie_custom_group;    
  }
  
  /*
   * Set lidmaatschap historie custom fields
   * Get all the custom fields belongs to lidmaatschap historie
   */
  protected function set_lidmaatschap_historie_custom_fields() {
    try {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'custom_group_id' => $this->_lidmaatschap_historie_custom_group_id,
        'is_active' => '1',
      );
      $result = civicrm_api('CustomField', 'get', $params);  
            
      foreach ($result['values'] as $key => $array){
        $this->_lidmaatschap_historie_custom_fields[$array['id']] = array(
          'id' => $array['id'],
          'name' => $array['name'],
          'label' => $array['label'],
          'column_name' => $array['column_name'],
        );
        
        $this->_lidmaatschap_historie_custom_fields_by_name[$array['name']] = array(
          'id' => $array['id'],
          'name' => $array['name'],
          'label' => $array['label'],
          'column_name' => $array['column_name'],
        );
                
        if(isset($array['option_group_id']) and !empty($array['option_group_id'])){
          $this->_lidmaatschap_historie_custom_fields[$array['id']]['option_group_id'] = $array['option_group_id'];
          $this->_lidmaatschap_historie_custom_fields_by_name[$array['name']]['option_group_id'] = $array['option_group_id'];
        }
      }
      
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find lidmaatschap historie custom fields, '
        . 'error from API CustomField get: '.$ex->getMessage());
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  /*
   * Get lidmaatschap historie custom fields
   */
  public function get_lidmaatschap_historie_custom_fields() {
    return $this->_lidmaatschap_historie_custom_fields;
  }
  
  /*
   * Get lidmaatschap historie custom fields
   */
  public function get_lidmaatschap_historie_custom_fields_by_name() {
    return $this->_lidmaatschap_historie_custom_fields_by_name;
  }
  
  /*
   * Get lidmaatschap historie option values from the lidmaatschap historie custom fields
   */
  public function set_lidmaatschap_historie_option_values() {
    foreach($this->_lidmaatschap_historie_custom_fields as $fid => $custom_field){
      try {
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'option_group_id' => $custom_field['option_group_id'],
          'is_active' => '1'
        );
        $result = civicrm_api('OptionValue', 'get', $params);
        
        $this->_lidmaatschap_historie_option_values[$custom_field['option_group_id']] = array();
        
        foreach ($result['values'] as $key => $array){
          $this->_lidmaatschap_historie_option_values[$array['option_group_id']][$array['id']] = array(
            'id' => $array['id'],
            'name' => $array['name'],
            'label' => $array['label'],
            'value' => $array['value']
          );
        }

      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not find lidmaatschap historie option values, '
          . 'error from API OptionValue get: '.$ex->getMessage());
      }
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  public function get_lidmaatschap_historie_option_values() {
    return $this->_lidmaatschap_historie_option_values;
  }
  
  /*
   * Set lidmaatschap custom group id
   * The id is to get all the custom fields
   */
  protected function set_lidmaatschap_custom_group_id() {
    try {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'name' => 'Lidmaatschap__Maatschappij',
      );
      $result = civicrm_api('CustomGroup', 'getsingle', $params);     
      $this->_lidmaatschap_custom_group_id = $result['id'];
      
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find lidmaatschap historie custom group id, '
        . 'error from API CustomGroup getsingle: '.$ex->getMessage());
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  /*
   * Get lidmaatschap custom group id
   */
  public function get_lidmaatschap_custom_group_id() {
    return $this->_lidmaatschap_custom_group_id;    
  }
  
  /*
   * Set lidmaatschap custom group
   */
  protected function set_lidmaatschap_custom_group() {
    try {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $this->_lidmaatschap_custom_group_id,
      );
      $result = civicrm_api('CustomGroup', 'getsingle', $params);     
      $this->_lidmaatschap_custom_group = $result;
      
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find lidmaatschap historie custom group, '
        . 'error from API CustomGroup getsingle: '.$ex->getMessage());
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  /*
   * Get lidmaatschap custom group
   */
  public function get_lidmaatschap_custom_group() {
    return $this->_lidmaatschap_custom_group;    
  }
  
  
  /*
   * Set lidmaatschap custom fields
   * Get all the custom fields belongs to lidmaatschap
   */
  protected function set_lidmaatschap_custom_fields() {
    try {
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'custom_group_id' => $this->_lidmaatschap_custom_group_id,
        'is_active' => '1',
      );
      $result = civicrm_api('CustomField', 'get', $params);  
            
      foreach ($result['values'] as $key => $array){
        $this->_lidmaatschap_custom_fields[$array['id']] = array(
          'id' => $array['id'],
          'name' => $array['name'],
          'label' => $array['label'],
          'column_name' => $array['column_name'],
        );
        
        if(isset($array['option_group_id']) and !empty($array['option_group_id'])){
          $this->_lidmaatschap_custom_fields[$array['id']]['option_group_id'] = $array['option_group_id'];
        }
      }
      
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find lidmaatschap historie custom fields, '
        . 'error from API CustomField get: '.$ex->getMessage());
    }
    
    //CRM_Utils_System::civiExit();
  }
  
  /*
   * Get lidmaatschap historie custom fields
   */
  public function get_lidmaatschap_custom_fields() {
    return $this->_lidmaatschap_custom_fields;
  }
}