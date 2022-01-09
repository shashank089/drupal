<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\MenuStorage;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements custom form to add blog.
 */

class MenuCloneForm extends FormBase {

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Constructs a MenuForm object.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link manager.
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager) {
    $this->menuLinkManager = $menu_link_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.menu.link')
    );
  }

  /**
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'menu_clone_form';
  }

  /**
    * {@inheritdoc}
    */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /* $my_menu = \Drupal::entityTypeManager()->getStorage('menu_link_content')->loadByProperties(['menu_name' => 'demo-menu']);
    foreach ($my_menu as $key => $menu_item) {
      $parent_id = $menu_item->getParentId();
      $url_object = $menu_item->getUrlObject();
      echo "<pre>";
      $route_name = $url_object->getRouteName();
          $route_parameters = $url_object->getRouteParameters();
          $options = $url_object->getOptions();
          echo $url = Url::fromRoute($route_name, $route_parameters, $options)->toString();
      echo "</pre>";
    } */

    $user_menus = \Drupal::entityQuery('menu')->condition('status', 1);
    $menus = $user_menus->execute();

    $form['menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a menu to clone'),
      '#options' => $menus,
      '#required' => TRUE
    ];

    $form['clone_menu_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Clone menu name'),
      '#states' => array(
        'invisible' => array(
          ':input[name="menu"]' => ['value' => ''],
        ),
      ),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Menu name'),
      '#maxlength' => MenuStorage::MAX_ID_LENGTH,
      '#description' => $this->t('A unique name to construct the URL for the menu. It must only contain lowercase letters, numbers and hyphens.'),
      '#machine_name' => [
        'exists' => [$this, 'menuNameExists'],
        'source' => ['clone_menu_name'],
        'replace_pattern' => '[^a-z0-9-]+',
        'replace' => '-',
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clone'),
      '#button_type' => 'primary',
    ];
    
    return $form;
  }

  /**
  * {@inheritdoc}
  */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $id = $form_state->getValue('id');
    $clone_menu_name = $form_state->getValue('clone_menu_name');
    $tree = \Drupal::menuTree()->load($id, new \Drupal\Core\Menu\MenuTreeParameters());
    
    if (count($tree) > 0) {
      $form_state->setErrorByName('clone_menu_name', $this->t('The menu name already exists. Please use different name.'));
    }
  }

  /**
  * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $current_user = \Drupal::currentUser()->id();

    $id = $form_state->getValue('id');
    $menu = $form_state->getValue('menu');
    $clone_menu_name = $form_state->getValue('clone_menu_name');

    // $tree = \Drupal::menuTree()->load($menu, new \Drupal\Core\Menu\MenuTreeParameters());

    //Create new menu
    $clone_menu = \Drupal::entityTypeManager()
      ->getStorage('menu')
      ->create([
        'id' => $id,
        'label' => $clone_menu_name,
        'description' => '',
      ])
      ->save();

    //Add items to menu
    $my_menu = \Drupal::entityTypeManager()->getStorage('menu_link_content')->loadByProperties(['menu_name' => $menu]);
    foreach ($my_menu as $key => $menu_item) {
      $parent_id = $menu_item->getParentId();
      $url_object = $menu_item->getUrlObject();
      if ($url_object) {
        $url = NULL;
        $route_name = NULL;
        $route_parameters = [];
        if (!$url_object->isRouted()) {
          $url = $url_object->getUri();
        }
        else {
          $route_name = $url_object->getRouteName();
          $route_parameters = $url_object->getRouteParameters();
          $options = $url_object->getOptions();
          $url = Url::fromRoute($route_name, $route_parameters, $options)->toString();
        }
      }
      
      $menu_link = MenuLinkContent::create([
        'title' => $menu_item->getTitle(),
        'link' => ['uri' => $url],
        'menu_name' => $id,
        'expanded' => TRUE,
        'parent' => !empty($parent_id) ? $parent_id : '',
      ]);
      $menu_link->save();
    }
    
    \Drupal::messenger()->addMessage($this->t('Menu cloned successfully'), 'status', TRUE);
    // $form_state->setRedirect('entity.feed.collection');
  }

  /**
   * Returns whether a menu name already exists.
   *
   * @param string $value
   *   The name of the menu.
   *
   * @return bool
   *   Returns TRUE if the menu already exists, FALSE otherwise.
   */
  public function menuNameExists($value) {
    // Check first to see if a menu with this ID exists.
    if (\Drupal::entityTypeManager()->getStorage('menu')->getQuery()->condition('id', $value)->range(0, 1)->count()->execute()) {
      return TRUE;
    }

    // Check for a link assigned to this menu.
    return $this->menuLinkManager->menuNameInUse($value);
  }
}




