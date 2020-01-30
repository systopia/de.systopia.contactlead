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
 * Settings form
 */
class CRM_Contactlead_Form_Settings extends CRM_Core_Form {

  /**
   * Create the settings form
   */
  public function buildQuickForm() {
    // add form elements
    $this->add(
      'checkbox',
      'contact_form_inject',
      E::ts("Enter Lead in Contact Form?")
    );
    $this->add(
        'checkbox',
        'contact_form_inject_mandatory',
        E::ts("Lead Mandatory?")
    );
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    // set default values
    $settings = Civi::settings()->get('contactleads_settings');
    if (!empty($settings) && is_array($settings)) {
      $this->setDefaults($settings);
    }

    parent::buildQuickForm();
  }

  /**
   * Store the settings
   */
  public function postProcess() {
    $values = $this->exportValues();

    $settings = [];
    $valid_settings = ['contact_form_inject', 'contact_form_inject_mandatory'];
    foreach ($valid_settings as $setting_name) {
      $settings[$setting_name] = CRM_Utils_Array::value($setting_name, $values, 0);
    }
    Civi::settings()->set('contactleads_settings', $settings);

    CRM_Core_Session::setStatus(E::ts('Settings updated'), E::ts("Success"), 'info');
    parent::postProcess();
  }

}
