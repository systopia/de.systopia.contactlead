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
   * Test if the contact lead fields should be injected
   * @return bool should it be injected
   */
  public static function shouldInject() {
    // if there is a CID, this is an edit!
    $cid = CRM_Utils_Request::retrieve('cid', 'Integer');
    if ($cid) {
      return false;
    }

    // check if the injection is turned on
    return (bool) CRM_Contactlead_Config::getSetting('contact_form_inject');
  }

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
    $form->addEntityRef(
        'contactlead_contact',
        E::ts('Lead Contact'),
        [
            'contact_type'      => 'Individual',
            'check_permissions' => 0,
        ]
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
  }

  /**
   * Perform actions on hook_civicrm_postProcess().
   *
   * @param string $formName
   * @param CRM_Contact_Form_Contact $form
   */
  public static function postProcess($formName, &$form) {
    $values = $form->exportValues();

    if (!empty($values['contactlead_contact'])) {
      $new_lead_data = [
          'contact_leads.contact_lead_category'  => $values['contactlead_category'],
          'contact_leads.contact_lead_id'        => $values['contactlead_contact'],
          'contact_leads.contact_lead_from'      => date('YmdHis'),
          'contact_leads.contact_lead_enabled'   => 1,
          'contact_leads.contact_lead_important' => CRM_Utils_Array::value('contactlead_important', $values, 0),
      ];
      CRM_Contactlead_CustomData::resolveCustomFields($new_lead_data);

      // build record and append ':-1' to all keys to indicate new record
      $new_lead_record = ['entity_id' => $form->_contactId];
      foreach ($new_lead_data as $key => $value) {
        $new_lead_record["{$key}:-1"] = $value;
      }
      civicrm_api3('CustomValue', 'create', $new_lead_record);
    }
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
