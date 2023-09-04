<?php

/**
 * @file
 * Contains \Drupal\cosign\Form\CosignAdmin.
 */

namespace Drupal\cosign\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class CosignAdmin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cosign_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cosign.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);

    //clear all routing caches to update any changed settings.
    \Drupal::service("router.builder")->rebuild();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cosign.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {

    $form['cosign_branded'] = [
      '#type' => 'textfield',
      '#title' => t('Brand Cosign'),
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_branded'),
      '#size' => 80,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#description' => t("Enter what you want Cosign to be called if your organization brands it differently."),
    ];

    $form['cosign_logout_path'] = [
      '#type' => 'textfield',
      '#title' => t('Logout Path'),
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_logout_path'),
      '#size' => 80,
      '#maxlength' => 200,
      '#description' => t("The address (including http(s)) of the machine and script path for logging out. Cosign has two options for logout. The default of the Cosign module is to use the local server logout script. This script immediately kills the local service cookie and redirects the user to central Cosign weblogin to logout.<br/><br/>Alternatively, you can use the Cosign services' weblogin logout script (e.g. https://weblogin.yoursite.edu/cgi-bin/logout).  Note that using a central logout script, the user's service cookie will not be immediately destroyed (it could take up to a minute), so this extension attempts to overwrite the active cookie to jibberish to effect a logout immediately."),
    ];

    $form['cosign_logout_to'] = [
      '#type' => 'textfield',
      '#title' => t('Logout to'),
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_logout_to'),
      '#size' => 80,
      '#maxlength' => 200,
      '#description' => t("The address to redirect users to after they have logged out. Cosign requires you add a trailing slash for a home address (i.e. https://example.com/), but *not* for a page address (i.e. https://example.com/page)"),
    ];

    $YesNo = [
      1 => 'Yes',
      0 => 'No',
    ];

    $form['cosign_allow_anons_on_https'] = [
      '#type' => 'select',
      '#title' => t('Allow anonymous users to browse over https?'),
      '#description' => t('Recommended "Yes" for most installations.  If "Yes", users are not logged in automatically to drupal even if they have Cosign cookies and visit Drupal via HTTPS.  If "No", users are forcibly logged in to Drupal via Cosign after visiting any HTTPS address.  NOTE: if your Cosign installation does not force user logins at HTTPS and you set this to No, but your site *does* allow anonymous (HTTP) browsing, users will lose the ability to see this content when using HTTPS.'),
      '#options' => $YesNo,
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_allow_anons_on_https'),
    ];

    $form['cosign_allow_cosign_anons'] = [
      '#type' => 'select',
      '#title' => t('Allow logged in cosign users to browse anonymously?'),
      '#description' => t('If "Yes", logged in cosign users can browse the site anonymously by logging out of (or not logging into) Drupal.  If "No", users with valid Cosign cookies will be logged in automatically to Drupal.'),
      '#options' => $YesNo,
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_allow_cosign_anons'),
    ];

    $form['cosign_login_path'] = [
      '#type' => 'textfield',
      '#title' => t('Login Path'),
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_login_path'),
      '#size' => 80,
      '#maxlength' => 200,
      '#description' => t("The address (including https://) of the machine and script path for logging in to Cosign.  The address should be everything before the query (e.g. 'https://weblogin.yoursite.edu/').  This ONLY has an effect if 'Allow anonymous browsing' is set to False (and only if your Cosign service does NOT force Cosign logins on all https addresses)."),
    ];

    //TODO this is not implemented, this always happens currently. not sure what the use case for keeping drupal users logged in
    $form['cosign_autologout'] = [
      '#type' => 'select',
      '#title' => t('Logout users from Drupal when their Cosign session expires?'),
      '#description' => t('<strong>Not implemented: always "Yes" in this version.  Retained for schema compatibility.</strong>  If set to "No", when users log out of Cosign they will NOT also be automatically be logged out of Drupal.  This could lead to confusion has security implications if users think they\'re logged out of Drupal, but are not.'),
      '#options' => $YesNo,
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_autologout'),
    ];

    $form['cosign_autocreate'] = [
      '#type' => 'select',
      '#title' => 'Auto-create Users?',
      '#options' => $YesNo,
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_autocreate'),
      '#description' => t('Creates a matching Drupal user when a valid Cosign user attempts to log in.  <strong>Untested in this version.</strong>'),
    ];

    $form['cosignautocreate_email_domain'] = [
      '#type' => 'textfield',
      '#title' => t('Email Domain for auto-generated users'),
      '#default_value' => \Drupal::config('cosign.settings')->get('cosignautocreate_email_domain'),
      '#size' => 80,
      '#maxlength' => 200,
      '#description' => t("If 'Auto-create Users' set to 'Yes', this supplies a default email domain for the drupal user account."),
    ];
    //TODO currently, they get autocreated depending on friend status or anonymous user is returned. not sure we have a use case for this in the UM library at least...
    $form['cosign_invalid_login'] = [
      '#type' => 'textfield',
      '#title' => t('Redirect for Cosign users without a Drupal account'),
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_invalid_login'),
      '#size' => 80,
      '#maxlength' => 200,
      '#description' => t("An address or path where valid Cosign users without a Drupal account (i.e. anons) will be sent.  Applies if 'Auto-create Users' and/or 'Allow friend accounts' is set to 'No'.  Please ensure any referenced internal path exists and is accessible."),
    ];
    //TODO not implemented. see above
    $form['cosign_invalid_login_message'] = [
      '#type' => 'textarea',
      '#title' => t('Message for Cosign users without a Drupal account'),
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_invalid_login_message'),
      '#description' => t("This message is displayed to users who have a Cosign account but no Drupal account.  Applies if 'Auto-create Users' and/or 'Allow friend accounts' is set to 'No'."),
    ];

    $form['cosign_allow_friend_accounts'] = [
      '#type' => 'select',
      '#title' => 'Allow friend accounts?',
      '#options' => $YesNo,
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_allow_friend_accounts'),
      '#description' => t("Whether or not to permit Cosign Friends (users external to the organisation/domain) access to Drupal.  Requires local Cosign support for this feature."),
    ];

    $form['cosign_friend_account_message'] = [
      '#type' => 'textarea',
      '#title' => t('Message displayed to users after they authenticate to Cosign with a friend account'),
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_friend_account_message'),
      '#description' => t("This message is displayed to Cosign Friend users who have no Drupal account.  Applies if 'Auto-create Users' and/or 'Allow friend accounts' is set to 'No'."),
    ];

    $form['cosign_ban_password_resets'] = [
      '#type' => 'select',
      '#title' => t('Ban users access to the /user/password and /user/reset core functions'),
      '#description' => t('If Yes, users cannot change their drupal passwords.  You should also customise the User form to remove password and other irrelevant fields.'),
      '#options' => $YesNo,
      '#default_value' => \Drupal::config('cosign.settings')->get('cosign_ban_password_resets'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('cosign_allow_anons_on_https') == 0 && $form_state->getValue('cosign_allow_cosign_anons') == 1) {
      $form_state->setErrorByName(
        'cosign_allow_anons_on_https',
        $this->t("Cosign users cannot browse anonymously if Anonymous users can't. Set Allow Anonymous Users to browse over HTTPS to Yes. OR")
      );
      $form_state->setErrorByName(
        'cosign_allow_cosign_anons',
        $this->t("Cosign users cannot browse anonymously if Anonymous users can't. Set Allow Cosign Users to browse anonymously to No.")
      );
    }
  }

}
