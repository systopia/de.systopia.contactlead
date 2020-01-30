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
class CRM_Contactlead_Injection {

  /**
   * Perform actions on hook_civicrm_buildForm().
   *
   * @param string $formName
   * @param CRM_Contact_Form_Contact $form
   */
  public static function addFormElements($formName, &$form) {
    // gather information
    $required = (boolean) CRM_Contactlead_Config::getSetting('contact_form_inject_mandatory');
    $categories = CRM_Contactlead_Config::getCategories();

    // add form elements
    $form->add(
      'select',
      'contactlead_category',
      E::ts('Category'),
      $categories,
      $required
    );
    $form->add(
        'text',
        'contactlead_contact',
        E::ts('Lead Contact'),
        $required
    );
    $form->add(
        'checkbox',
        'contactlead_important',
        E::ts('Important')
    );

    $form->setDefaults([
        'contactorigin_campaign' => 0,
        'contactorigin_date'     => date('Y-m-d')
    ]);

    // inject template and script
    CRM_Core_Region::instance('page-body')->add(['template' => E::path("templates/CRM/Contactlead/Form/LeadSnippet.tpl")]);
    Civi::resources()->addScriptFile('de.systopia.contactlead', "js/LeadInjection.js");
    //Civi::resources()->addVars('contactlead', ['campaigns' => $campaigns]);
  }

  /**
   * Perform actions on hook_civicrm_postProcess().
   *
   * @param string $formName
   * @param CRM_Contact_Form_Contact $form
   */
  public static function postProcess($formName, &$form) {
    // TODO: process
    $values = $form->exportValues();
  }

  /**
   * List all campaigns that shall be shown in the dropdown menu.
   * @return array The campaign list in the form "id => title".
   */
  public static function getCategories() {

    $campaigns = civicrm_api3(
      'Campaign',
      'get',
        [
            'sequential'   => 1,
            'is_active'    => 1,
            'return'       => ["id", "title"],
            'option.limit' => 0
        ]
    );

    $campaignIdTitleMap = [
      0 => ts('No Campaign')
    ];

    foreach ($campaigns['values'] as $campaign) {
      $campaignIdTitleMap[$campaign['id']] = $campaign['title'];
    }

    return $campaignIdTitleMap;
  }
}
