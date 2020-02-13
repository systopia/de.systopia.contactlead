<?php
/*-------------------------------------------------------+
| SYSTOPIA Contact Leads Extension                       |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


use CRM_Contactlead_ExtensionUtil as E;

/**
 * Class to inject a contact lead form into CiviCRM's contact form
 */
class CRM_Contactlead_Config {
  /**
   * Get a simple ContactLead setting
   *
   * @param $key     string setting name
   * @param $default mixed default value
   * @return mixed value
   */
  public static function getSetting($key, $default = null) {
    static $settings = null;
    if ($settings === null) {
      $settings = Civi::settings()->get('contactleads_settings');
      if ($settings === null) {
        $settings = [];
      }
    }
    return CRM_Utils_Array::value($key, $settings, $default);
  }

  /**
   * Get the list of valid categories for a lead
   *
   * @return array catgories (id -> label)
   */
  public static function getCategories() {
    static $categories = null;
    if ($categories === null) {
      $categories = [];
      $query = civicrm_api3('OptionValue', 'get', [
          'option.limit'    => 0,
          'option_group_id' => 'contact_lead_category',
          'is_active'       => 1,
          'return'          => 'value,label'
      ]);
      foreach ($query['values'] as $value) {
        $categories[$value['value']] = $value['label'];
      }
    }
    return $categories;
  }

  /**
   * Get the default category ID
   *
   * @return string category ID
   */
  public static function getDefaultCategory() {
    static $default_category_id = null;
    if ($default_category_id === null) {
      $default_category_id = '';
      $query = civicrm_api3('OptionValue', 'get', [
          'option.limit'    => 1,
          'option_group_id' => 'contact_lead_category',
          'is_active'       => 1,
          'return'          => 'value,label',
          'option.sort'     => 'is_default desc, weight asc'
      ]);
      if ($query['count']) {
        $value = reset($query['values']);
        $default_category_id = $value['value'];
      }
    }
    return $default_category_id;
  }
}
