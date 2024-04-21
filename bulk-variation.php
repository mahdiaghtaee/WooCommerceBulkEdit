<?php

/**
 * @version    1.0.0
 * @since      1.0.0
 * @author     Mahdi Aghtaee <mahdi.aghtaee@gmail.com>
 * @Text Domain: bulk-variation
 */


if (!defined('ABSPATH')) {
  exit;
}

class BulkVariation {

  public function __construct() {
    $this->hooks();
  }

  public function hooks() {
    add_filter('bulk_actions-edit-product', [$this, 'add_custom_bulk_action']);
    add_action('admin_action_all_variation_zero_stock', [$this, 'handle_zero_stock_bulk_action']);
  }

  function add_custom_bulk_action($bulk_actions) {
    $bulk_actions['all_variation_zero_stock'] = __('Make All Variation Zero Stock', 'bulk-variation');
    return $bulk_actions;
  }

  function handle_zero_stock_bulk_action() {

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    if ('all_variation_zero_stock' === $action) {

      $product_ids = isset($_REQUEST['post']) ? $_REQUEST['post'] : array();

      foreach ($product_ids as $product_id) {
        $this->set_variation_quantity($product_id, 0);
        $var_ids = $this->get_variation_post_ids($product_id);

        foreach ($var_ids as $key => $v_id) {
          $this->set_variation_quantity($v_id, 0);
        }
      }
    }
  }


  function set_variation_quantity($variation_id, $quantity = 0) {
    update_post_meta($variation_id, '_stock', $quantity);
    update_post_meta($variation_id, '_manage_stock', 'yes');
    update_post_meta($variation_id, '_stock_status', 'outofstock');
  }

  function get_variation_post_ids($parent_id) {
    $args = array(
      'post_parent' => $parent_id,
      'post_type'   => 'product_variation',
      'numberposts' => -1,
      'post_status' => 'any'
    );

    $variations = get_children($args);

    // Extract post IDs from the variation objects
    $variation_ids = array();
    foreach ($variations as $variation) {
      $variation_ids[] = $variation->ID;
    }

    return $variation_ids;
  }
}


add_action('admin_init', function () {
  new BulkVariation();
});
