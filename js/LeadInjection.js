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

(function ($) {
    let contactLeadWrapper = $('#contactLeadWrapper');
    let accordionWrappers = $('.crm-accordion-wrapper').not(contactLeadWrapper);

    // make sure there's something there...
    let firstAccordion = accordionWrappers[0];
    if (!firstAccordion)
    {
        return;
    }

    // collapse other accordions, if subject is not filled yet
    if (cj("#contactlead_contact").val()) {
        contactLeadWrapper.addClass('collapsed');
    } else {
        accordionWrappers.addClass('collapsed');
    }

    // insert ours at the top
    contactLeadWrapper.insertBefore(firstAccordion);

    // open main contact when fields are filled
    cj("#contactlead_contact").change(function() {
        if (cj("#contactlead_contact").val()) {
            cj('.crm-accordion-wrapper')
                .not(contactLeadWrapper)
                .first()
                .removeClass('collapsed');
        }
    });
})(cj);