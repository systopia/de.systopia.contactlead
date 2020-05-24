<?php
/*-------------------------------------------------------+
| SYSTOPIA EXTENSIBLE EXPORT EXTENSION                   |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Xportx_ExtensionUtil as E;

/**
 * Provides XPortX access to the contacts linked as leads
 */
class CRM_Xportx_Module_ContactLeads extends CRM_Xportx_Module
{

    /**
     * This module can do with any base_table
     * (as long as it has a contact_id column)
     */
    public function forEntity()
    {
        return 'Entity';
    }

    /**
     * Get this module's preferred alias.
     * Must be all lowercase chars: [a-z]+
     */
    public function getPreferredAlias()
    {
        return 'leads';
    }

    /**
     * add this module's joins clauses to the list
     * they can only refer to the main contact table
     * "contact" or other joins from within the module
     */
    public function addJoins(&$joins)
    {
        // join lead table
        $contact_id = $this->getContactIdExpression();
        $leads_alias = $this->getAlias('lead_contacts');
        // conditions:
        $condition = '';
        if (!empty($this->config['active_only'])) {
            $condition = " AND {$leads_alias}.is_enabled = 1";
            $condition .= " AND ({$leads_alias}.from_date IS NULL OR {$leads_alias}.from_date < NOW())";
            $condition .= " AND ({$leads_alias}.to_date IS NULL OR {$leads_alias}.to_date > NOW())";
        }
        // JOIN lead custom table
        $joins[]    = "LEFT JOIN civicrm_value_contact_lead {$leads_alias} ON {$leads_alias}.entity_id = {$contact_id} {$condition}";

        // JOIN linked contact data
        $leads_contact_alias = $this->getAlias('lead_contact_data');
        $joins[]    = "LEFT JOIN civicrm_contact {$leads_contact_alias} ON {$leads_contact_alias}.id = {$leads_alias}.lead_id";
    }

    /**
     * add this module's select clauses to the list
     * they can only refer to the main contact table
     * "contact" or this module's joins
     */
    public function addSelects(&$selects)
    {
        $lead_alias = $this->getAlias('lead_contacts');
        $lead_contact_alias = $this->getAlias('lead_contact_data');
        $value_prefix  = $this->getValuePrefix();

        foreach ($this->config['fields'] as $field_spec) {
            $field_name = $field_spec['key'];
            if (in_array($field_name, ['from', 'to', 'is_enabled', 'is_important'])) {
                // this is a lead metadata field
                $selects[] = "{$lead_alias}.{$field_name} AS {$value_prefix}{$field_name}";
            } else {
                // this is a lead contact field
                // TODO: process exceptions (like prefix, etc.)
                $selects[] = "{$lead_contact_alias}.{$field_name} AS {$value_prefix}{$field_name}";
            }
        }
    }
}
