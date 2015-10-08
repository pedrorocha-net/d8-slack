<?php

/**
 * @file
 * Contains Drupal\slack\Form\SettingsForm.
 * Configures administrative settings for slack.
 */

namespace Drupal\slack\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\slack\Form
 *
 * @ingroup slack
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'slack_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['slack.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('slack.settings');
    
    $form['slack_webhook_url'] = array(
    '#type' => 'textfield',
    '#title' => $this->t('Webhook URL'),
    '#description' => $this->t('Enter your Webhook URL from an Incoming WebHooks integration. It looks like https://hooks.slack.com/services/XXXXXXXXX/YYYYYYYYY/ZZZZZZZZZZZZZZZZZZZZZZZZ'),
    '#default_value' => $config->get('slack_webhook_url'),
    '#required' => TRUE,
    );
    $form['slack_channel'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default channel'),
      '#description' => $this->t('Enter your channel name with # symbol, for example #general (or @username for a private message or a private group name).'),
      '#default_value' => $config->get('slack_channel'),
    );
    $form['slack_username'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default username'),
      '#description' => $this->t('What would you like to name your Slack bot?'),
      '#default_value' => $config->get('slack_username'),
    );
    $form['slack_icon_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Type of image'),
      '#options' => array (
        'emoji' => $this->t('Emoji'),
        'image' => $this->t('Image'),
        'none' => $this->t('None (Use default integration settings)')),
      '#default_value' => $config->get('slack_icon_type'),
    );
    $form['slack_icon_emoji'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Emoji code'),
      '#default_value' => $config->get('slack_icon_emoji'),
      '#description' => $this->t('What emoji would you use for your SlackBot?'),
      '#states' => array(
        'visible' => array(
          ':input[name="slack_icon_type"]' => array(
            'value' => 'emoji',
          ),
        ),
      ),
    );
    $form['slack_icon_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Image URL '),
      '#default_value' => $config->get('slack_icon_url'),
      '#description' => $this->t('What icon would you use for your SlackBot?'),
      '#states' => array(
        'visible' => array(
          ':input[name="slack_icon_type"]' => array(
            'value' => 'image',
          ),
        ),
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('slack.settings');
    $config
      ->set('slack_webhook_url', $form_state->getValue('slack_webhook_url'))
      ->set('slack_channel', $form_state->getValue('slack_channel'))
      ->set('slack_username', $form_state->getValue('slack_username'))
      ->set('slack_icon_type', $form_state->getValue('slack_icon_type'))
      ->set('slack_icon_emoji', $form_state->getValue('slack_icon_emoji'))
      ->set('slack_icon_url', $form_state->getValue('slack_icon_url'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}