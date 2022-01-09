<?php

namespace Drupal\zscaler_module\Commands;

use Drush\Commands\DrushCommands;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

/**
 * A drush command file.
 *
 * @package Drupal\zscaler_module\Commands
 */
class BlogCustomCommands extends DrushCommands {

  /**
   * Drush command that displays the given text.
   *
   * 
   *   Argument with message to be displayed.
   * @command blog_mail_notify:notify
   * @aliases bm-notify
   */
  public function notify() {
    
    $conn = \Drupal::database();
    $query = $conn->select('blog_table');
    $query->fields('blog_table');
    $query->condition('notification_status', 0);
    $query->range(0, 5);
    $results = $query->execute();
    $rows = $results->fetchAll();

    foreach ($rows as $key => $value) {
      $node = Node::load($value->nid);
      $uid = $node->getOwnerId();
      
      $mail = \Drupal::service('plugin.manager.mail');
      $module = 'zscaler_module';
      $key = 'create_blog';
      $to = User::load($uid)->getEmail();
      $params['message'] = 'New Blog created.';
      $params['node_title'] = Node::load($value->nid)->get('title')->value;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;

      $result = $mail->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if($result['result']){
        $time = \Drupal::time()->getCurrentTime();
        $conn = \Drupal::database();
        $query = $conn->update('blog_table');
        $query->fields(['updated' => $time, 'notification_status' => 1]);
        $query->condition('nid', $value->nid);
        $query->execute();
      }
    }

    if (count($rows) >= 1) {
      $text = 'Mails sent to Authors.';
    } else {
      $text = 'There were no new Blogs to notify.';
    }

    $this->output()->writeln($text);
  }
  
}