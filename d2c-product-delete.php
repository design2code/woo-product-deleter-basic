<?php
/*
Plugin Name: WooCommerce Product Deleter Basic
Plugin URI:  www.design2code.co.za
Description: Get all posts of type product and delete selected
Version:     1.0.0
Author:      Nathaniel Hamann
Author URI:  www.design2code.co.za
License:
License URI:
Text Domain: d2c-product-delete
Domain Path: /languages
*/

/*
 * Activation and deactivation hooks provide ways to perform actions when plugins are activated or deactivated.
 */
define('D2C_PRODUCT_DELETER', plugin_dir_path(__FILE__));

//Check is woocommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  /* CREATE A SETTINGS PAGE LINK */
  // add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pos_settings_links');
  // function d2c_product_deleter_settings_links($links)
  // {
  //   $links[] = '<a href="' . admin_url('options-general.php?page=d2c-img-checker&tab=check_images') . '">' . __('Settings') . '</a>';
  //   return $links;
  // }//end nc_settings_link()

  /* New cron schedule */
  add_filter('cron_schedules', 'add_product_deleter_cron_interval');
  function add_product_deleter_cron_interval($schedules)
  {
    $schedules['ten_minutes'] = array(
      'interval' => 600,
      'display' => esc_html__('Every 10 Minutes'),
    );
    return $schedules;
  }

  /* Activation */
  register_activation_hook(__FILE__, 'enable_d2c_product_deleter');
  function enable_d2c_product_deleter()
  {
    //notify of enable
    wp_mail('coders@design2code.co.za', 'New plugin activation - Product Delete', 'The Product delete plugin has just been activated on ' . get_bloginfo('url'));
    // schedule the product json export cron
    // if (!wp_next_scheduled('delete_products_from_site')) {
    //   wp_schedule_event(time(), 'ten_minutes', 'delete_products_from_site');
    // }
  }

  /* Deactivation */
  register_deactivation_hook(__FILE__, 'disable_d2c_product_deleter');
  function disable_d2c_product_deleter()
  {
    wp_mail('coders@design2code.co.za', 'Plugin deactivation - Product Delete', 'The Product Delete plugin has just been deactivated on ' . get_bloginfo('url'));
    //WP cron to check and update
    // wp_clear_scheduled_hook('delete_products_from_site');
  }


  /* Load custom stylesheet for backend */
  function d2c_product_deleter_scripts()
  {
    wp_enqueue_style('d2c-product-deleter-admin-css', plugin_dir_url(__FILE__) . '/d2c-woo-delete-admin.css');

    // wp_enqueue_script('d2c-img-chkr-admin-js', plugin_dir_url(__FILE__) . '/d2c-img-chkr-admin.js', array('jQuery'), '02062024', true);

    wp_register_script('d2c-product-deleter-admin-js', plugin_dir_url(__FILE__) . '/d2c-woo-delete-admin.js', array('jquery'), false, true);
    wp_enqueue_script('d2c-product-deleter-admin-js');
  }
  add_action('admin_enqueue_scripts', 'd2c_product_deleter_scripts');



  /* Setup Class For Cron */
  // require_once 'img-checker.class.php';
  require_once 'settings.class.php';

  //instantiate class
  new D2c_ProductDeleteSettingsPage();
}
