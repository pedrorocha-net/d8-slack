<?php

/**
 * @file
 * Contains Drupal\slack\Form\SendTestMessageForm.
 */

namespace Drupal\slack\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\slack\SlackAPI;

/**
 * Class SendTestMessageForm.
 *
 * @package Drupal\slack\Form
 */
class SendTestMessageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'slack_send_test_message';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('slack.settings');

    $form['slack_test_channel'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Channel'),
      '#default_value' => $config->get('slack_channel'),
    );
    $form['slack_test_message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send message'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $channel = $form_state->getValue('slack_test_channel');
    $message = $form_state->getValue('slack_test_message');

    $SlackAPI = new SlackAPI();
    $result = $SlackAPI->send($message, $channel);
    if (!$result) {
      drupal_set_message($this->t("Message wasn't sent. Please, check slack module configuration."));
    }
    elseif (!isset($result->error) && $result->getStatusCode() == SLACK_CODE_OK) {
      drupal_set_message($this->t('Message was successfully sent.'));
    }
    else {
      drupal_set_message($this->t("Message wasn't sent."), 'error');
    }
  }

}