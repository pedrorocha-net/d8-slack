<?php

/**
 * @file
 * Contains Drupal\slack\SlackAPI.
 * Slack integration module API functions.
 */

namespace Drupal\slack;

use GuzzleHttp\Psr7\Request;

/**
 * Class SlackAPI.
 */
class SlackAPI {

  /**
   * Send message to the Slack.
   *
   * @param string $message
   *   The message sent to the channel.
   * @param string $channel
   *   The channel in the Slack service to send messages.
   * @param string $username
   *   The bot name displayed in the channel.
   *
   * @return bool|object
   *   Slack response.
   */
  function send($message, $channel = '', $username = '') {
    $config = $this->prepareMessage($channel, $username);
    $result = $this->sendMessage(
      $config['webhook_url'],
      $message,
      $config['message_options']
    );
    return $result;
  }

  private function prepareMessage($channel, $username) {
    $config = \Drupal::config('slack.settings');

    $webhook_url = $config->get('slack_webhook_url');
    if (!$webhook_url) {
      throw new \Exception('No $webhook_url defined.');
    }

    $message_options = array();
    if (!empty($channel)) {
      $message_options['channel'] = $channel;
    }
    elseif (!empty($config->get('slack_channel'))) {
      $message_options['channel'] = $config->get('slack_channel');
    }
    if (!empty($username)) {
      $message_options['username'] = $username;
    }
    elseif (!empty($config->get('slack_username'))) {
      $message_options['username'] = $config->get('slack_username');
    }
    $icon_type = $config->get('slack_icon_type');
    if ($icon_type == 'emoji') {
      $message_options['icon_emoji'] = $config->get('slack_icon_emoji');
    }
    elseif ($icon_type == 'image') {
      $message_options['icon_url'] = $config->get('slack_icon_url');
    }
    return [
      'webhook_url' => $webhook_url,
      'message_options' => $message_options
    ];
  }

  /**
   * Send message to the Slack with more options.
   *
   * @param string $team_name
   *   Your team name in the Slack.
   * @param string $team_token
   *   The token from "Incoming WebHooks" integration in the Slack.
   * @param string $message
   *   The message sent to the channel.
   * @param array $message_options
   *   An associative array, it can contain:
   *     - channel: The channel in the Slack service to send messages
   *     - username: The bot name displayed in the channel
   *     - icon_emoji: The bot icon displayed in the channel
   *     - icon_url: The bot icon displayed in the channel
   *
   * @return object
   *   Can contain:
   *                          success      fail          fail
   *     - data:                ok         No hooks      Invalid channel specified
   *     - status message:      OK         Not found     Server Error
   *     - code:                200        404           500
   *     - error:               -          Not found     Server Error
   */
  function sendMessage($webhook_url, $message, $message_options = array()) {
    $headers = array(
      'Content-Type' => 'application/x-www-form-urlencoded',
    );
    $message_options['text'] = $this->processMessage($message);
    $sending_data = 'payload=' . json_encode($message_options);
    $sending_url = $webhook_url;

    $request = new Request('POST', $sending_url, $headers, $sending_data);

    try {
      $result = \Drupal::httpClient()->send($request, ['timeout' => 2]);
      return $result;
    } catch (\Exception $e) {
      drupal_set_message("Exception catched");
    }
    return FALSE;
  }

  /**
   * Replaces links with slack friendly tags. Strips all other html.
   *
   * @param string $message
   *   The message sent to the channel.
   *
   * @return string
   *   Replaces links with slack friendly tags. Strips all other html.
   */
  function processMessage($message) {
    $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    if (preg_match_all("/$regexp/siU", $message, $matches, PREG_SET_ORDER)) {
      $i = 1;
      foreach ($matches as $match) {
        $new_link = "<$match[2] | $match[3]>";
        $links['link-' . $i] = $new_link;
        $message = str_replace($match[0], 'link-' . $i, $message);
        $i++;
        $message = strip_tags($message);
        foreach ($links as $id => $link) {
          $message = str_replace($id, $link, $message);
        }
      }
    }
    return $message;
  }

}