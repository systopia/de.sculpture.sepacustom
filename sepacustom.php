<?php

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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function sepacustom_civicrm_xmlMenu(&$files) {
  _sepacustom_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function sepacustom_civicrm_postInstall() {
  _sepacustom_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function sepacustom_civicrm_uninstall() {
  _sepacustom_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function sepacustom_civicrm_enable() {
  _sepacustom_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function sepacustom_civicrm_disable() {
  _sepacustom_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function sepacustom_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sepacustom_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function sepacustom_civicrm_managed(&$entities) {
  _sepacustom_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function sepacustom_civicrm_caseTypes(&$caseTypes) {
  _sepacustom_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function sepacustom_civicrm_angularModules(&$angularModules) {
  _sepacustom_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function sepacustom_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _sepacustom_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function sepacustom_civicrm_preProcess($formName, &$form) {

} // */

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
 * This hook lets you modify the parameters of a to-be-created mandate.
 *
 * As an example, we use this pattern to generate our custom mandate reference:
 *   P60-00C00000099D20150115N1
 *                            \__ counter to allow multiple mandates per
 * contact and date
 *                   \_______\___ date
 *          \_______\____________ contact ID
 *       \_\_____________________ inteval, 00=OOFF, 04=quarterly, 02=monthly,
 * etc.
 *   \__\________________________ identifier string
 */
function sepacustom_civicrm_create_mandate(&$mandate_parameters) {

  if (isset($mandate_parameters['reference']) && !empty($mandate_parameters['reference'])) {
    return;   // user defined mandate
  }

  // load contribution
  if ($mandate_parameters['entity_table'] == 'civicrm_contribution') {
    $contribution = civicrm_api('Contribution', 'getsingle', array(
      'version' => 3,
      'id' => $mandate_parameters['entity_id'],
    ));
    $interval = '00';   // one-time
  }
  elseif ($mandate_parameters['entity_table'] == 'civicrm_contribution_recur') {
    $contribution = civicrm_api('ContributionRecur', 'getsingle', array(
      'version' => 3,
      'id' => $mandate_parameters['entity_id'],
    ));
    if ($contribution['frequency_unit'] == 'month') {
      $interval = sprintf('%02d', 12 / $contribution['frequency_interval']);
    }
    elseif ($contribution['frequency_unit'] == 'year') {
      $interval = '01';
    }
    else {
      // error:
      $interval = '99';
    }
  }
  else {
    die("unsupported mandate");
  }

  // Wenn alte Kundennummer vorhanden, wird diese gesetzt
  // Wenn nicht, CiviCRM-ID
  // Wenn bereits verwendet, wird -1, -2 etc. als Suffix angehängt
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
  do {
    if ($n > 0) {
      $reference_candidate = sprintf($reference . '-%d', $n);
    }
    else {
      $reference_candidate = $reference;
    }
    // check if it exists
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
  while(TRUE);
  // TODO: Any limit?
}
