<?php
/*-------------------------------------------------------+
| Sculpture network CiviSEPA customizations              |
| Copyright (C) 2016 SYSTOPIA                            |
| Author: J. Schuppe (schuppe@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

require_once 'sepacustom.civix.php';
use CRM_Sepacustom_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function sepacustom_civicrm_config(&$config) {
  _sepacustom_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function sepacustom_civicrm_install() {
  _sepacustom_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function sepacustom_civicrm_enable() {
  _sepacustom_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function sepacustom_civicrm_navigationMenu(&$menu) {
  _sepacustom_civix_insert_navigation_menu($menu, NULL, array(
    'label' => E::ts('The Page'),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _sepacustom_civix_navigationMenu($menu);
} // */

/**
 * Implements hook_civicrm_create_mandate().
 *
 * @link https://github.com/Project60/org.project60.sepa#customisation
 */
function sepacustom_civicrm_create_mandate(&$mandate_parameters) {
  // Customize mandate reference when there is no custom reference given.
  if (empty($mandate_parameters['reference'])) {
    // When there is a value in field "Mitgliedsnummer" (custom_3), use that,
    // else use the contact ID. In either case, append integer suffixes when the
    // reference is already used.
    $member_no = civicrm_api3('Contact', 'getsingle', array(
      'id' => $mandate_parameters['contact_id'],
      'return' => 'custom_3',
    ));
    if (!empty($member_no['custom_3'])) {
      $reference = $member_no['custom_3'];
    }
    else {
      $reference = $mandate_parameters['contact_id'];
    }

    $n = 0;
    $limit = 25;
    do {
      if ($n > 0) {
        $reference_candidate = sprintf($reference . '-%d', $n);
      }
      else {
        $reference_candidate = $reference;
      }
      // Check if a mandate with this reference exists.
      $mandate = civicrm_api('SepaMandate', 'getsingle', array(
        'version' => 3,
        'reference' => $reference_candidate,
      ));
      if (!empty($mandate['is_error'])) {
        // does not exist! take it!
        $mandate_parameters['reference'] = $reference_candidate;
        return;
      }
      $n++;
    }
    while($n < $limit);
  }
}

/**
 * This hook is called by the batching algorithm:
 *  whenever a new installment has been created for a given RCUR mandate
 *  this hook is called so you can modify the resulting contribution,
 *  e.g. connect it to a membership, or copy custom fields
 *
 * We use it here to fill the invoice-payfor-field if a membership is paid
 * for somebody else.
 *
 * @param array  $mandate_id             the CiviSEPA mandate entity ID
 * @param array  $contribution_recur_id  the recurring contribution connected to the mandate
 * @param array  $contribution_id        the newly created contribution
 *
 * @access public
 */
function sepacustom_civicrm_installment_created($mandate_id, $contribution_recur_id, $contribution_id) {

  // get the associated membership
  $paid_by_logic = CRM_Membership_PaidByLogic::getSingleton();
  $membership_ids = $paid_by_logic->getMembershipIDs($contribution_recur_id);

  // do we have a membership at all?
  if (count($membership_ids) == 0) {
    return;
  }
  // in any case there shouldn't be more than one membership!
  elseif (count($membership_ids) > 1) {
    throw new Exception("More than one membership associated with this recurring-contribution: [$contribution_recur_id]");
  }
  else {
    $membership_id = $membership_ids[0];
  }

  // get contribution-contact-id
  $contribution_contact_id = civicrm_api3('Contribution', 'getvalue', [
      'id' => $contribution_id,
      'return'     => 'contact_id']);

  // get membership-contact-id
  $membership_contact_id = civicrm_api3('Membership', 'getvalue', [
      'id' => $membership_id,
      'return'     => 'contact_id']);

  // if the membership- and the contribution-contact differ, set the payfor-field
  if ($contribution_contact_id != $membership_contact_id) {
    $custom_field = CRM_Scripts_CustomData::getCustomField('contribution_invoice', 'invoice_payfor');
    $custom_field_key = 'custom_' . $custom_field['id'];
    civicrm_api3('Contribution', 'create', [
      'id'   => $contribution_id,
      $custom_field_key => $membership_contact_id]);
  }

}
