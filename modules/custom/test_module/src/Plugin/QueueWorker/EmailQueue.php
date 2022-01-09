<?php
/**
 * @file
 * Contains \Drupal\mymodule\Plugin\QueueWorker\EmailQueue.
 */
namespace Drupal\Learning\Plugin\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
/**
 * Processes Tasks for Learning.
 *
 * @QueueWorker(
 *   id = "email_queue",
 *   title = @Translation("Learning task worker: email queue"),
 *   cron = {"time" = 60}
 * )
 */
class EmailQueue extends QueueWorkerBase {
  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    /* $mailManager = \Drupal::service('plugin.manager.mail');
    $params = $data;
    $mailManager->mail('learning', 'email_queue', $data['email'], 'en', $params , $send = TRUE); */
  }
}