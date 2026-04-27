<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class D2c_ProductDeleteSettingsPage
{

    // public $logfile = POS_PLUGIN_PATH.'pos_settings_log.txt';
    // public $image_logfile = POS_PLUGIN_PATH.'pos_settings_images_log.txt';

    //define endpoints

    public function __construct()
    {
        set_time_limit(0);
        //Add new menu item to the admin
        add_action('admin_menu', array($this, 'add_login_page'));
        // Ajax function for deleting products
        add_action('wp_ajax_d2c_delete_products', array($this, 'd2c_delete_products'));
        add_action('wp_ajax_d2c_delete_product', array($this, 'd2c_delete_single_product'));
        add_action('wp_ajax_d2c_check_count', array($this, 'd2c_check_count'));

        //Hourly Cron
        // add_action('delete_products_from_site', array($this, 'delete_products_hourly'));

    }

    //Add options page
    public function add_login_page()
    {
        // This page will be under main menu
        // add_options_page( // This adds it to the setting page
        add_management_page(
            'Woo Product Delete',
            'Woo Product Delete',
            'manage_options',
            'd2c-products-checker',
            array($this, 'create_landing_page')
        );
    }

    /*
     * create landing page
     *
     * Add the content for the page in the menu item  
     *
     * @Author Nathaniel Hamann <coders@design2code.co.za>
     * @return void
     */
    public function create_landing_page()
    {
        ?>

        <div class="d2c_settings_container card">
            <div id="d2c_response"></div>
            
            <div class="wrap">
                <h1><?php echo __('Check Products', ''); ?></h1>
                <small>Please note, this plugin permanently deletes posts and does not send them to trash..</small>
                <?php if (!isset($_POST['check_products'])): ?>
                    <form method="post"
                        action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $_GET['page'] . '&action=d2c-products-checker'; ?>"
                        id="d2c_pos_import_pos_form">
                        <?php wp_nonce_field('d2c_get_products', 'd2c_get_products'); ?>
                        <h2>Check Products</h2>
                        <div class="d2c_settings_row">
                            <label>Products to check:</label>
                            <select name="product_count" id="">
                                <option value="1">1</option>
                                <option value="10">10</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="-1">All</option>
                            </select>
                        </div>
                        <div class="d2c_settings_row">
                            <label>Product Status:</label>
                            <select name="product_status" id="product_status">
                                <option value="any">All</option>
                                <option value="publish">Published</option>
                                <option value="draft">Draft</option>
                                <option value="trash">Trashed</option>
                            </select>
                        </div>
                        <div class="d2c_settings_row">
                            <label>Product Category:</label>
                            <select name="product_category" id="product_category">
                                <option value="any">All</option>
                                <?php
                                $categories = get_terms( array(
                                    'taxonomy'   => 'product_cat',
                                    'hide_empty' => false, // Set to true to hide empty categories
                                ) );
                            
                                if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                                    foreach ( $categories as $category ) {
                                        echo '<option value="' . esc_attr( $category->term_id ) . '">' . esc_html( $category->name ) . '</option>';
                                    }
                                }
                                
                                ?>
                            </select>
                        </div>
                        <div class="d2c_settings_row">
                            <input type="submit" name="check_products" id="submit_check_products"
                                class="button button-primary button-large" value="View / Delete Products" />
                                <input type="button" name="check_count" id="check_count" class="button button-primary button-large" value="Check Product Count" />
                        </div>
                    </form>
                    <?php

                else:

                    global $post;
                    $site_url = get_bloginfo('url');
                    $count = $_POST['product_count'];
                    $status = $_POST['product_status'];
                    $category = $_POST['product_category'];

                    //Fetch products, limited to selected amount
                    if($category != 'any'){
                        $args = array(
                            'numberposts' => $count,
                            'post_type' => array('product'),
                            'post_status' => $status,
                            'tax_query'      => array(
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field'    => 'id', // Use 'id' if using category ID instead
                                    'terms'    => $category,
                                ),
                            ),
                        );
                    }else{
                        $args = array(
                            'numberposts' => $count,
                            'post_type' => array('product'),
                            'post_status' => $status
                        );
                    }


                    $postslist = get_posts($args);

                    if ($postslist) {

                        echo '<h2>Total used products found: ' . count($postslist) . '</h2>';
                        $ajax_single_nonce = wp_create_nonce('d2c-delete-product-nounce');

                        foreach ($postslist as $product) {
                            echo $product->post_title . ' <a href="#" class="d2c_delete_single_product delete_single_product" data-d2c_delete_product_nonce="'.$ajax_single_nonce.'" data-product_id="'.$product->ID.'" >Delete</a><br />';
                        }

                        //link to delete
                        //Set Your Nonce
                        $ajax_nonce = wp_create_nonce('d2c-delete-products');

                        if($category != 'any'){
                            echo '<button data-cat="'.$category.'" data-status="' . $status . '" data-count="' . $count . '"  data-d2c_delete_products_nonce="' . $ajax_nonce . '" name="d2c_delete_products"  class="button button-primary button-large d2c_delete_products" >Delete All Products</button>';
                        }else{
                            echo '<button data-cat="any" data-status="' . $status . '" data-count="' . $count . '"  data-d2c_delete_products_nonce="' . $ajax_nonce . '" name="d2c_delete_products"  class="button button-primary button-large d2c_delete_products" >Delete Products</button>';
                        }

                    }
                    echo '&nbsp;<button name="d2c_refresh" class="button button-primary button-large" onClick="location.reload()" >Go Back</button>';

                    // Reset All Post Data
                    wp_reset_postdata();

                endif;
                ?>

            </div>
        </div>
        <!-- </div> -->
        <?php
    }

    /*
     * D2c delete products
     *
     * The AJAX endpoint to delete all discovered products  
     *
     * @Author Nathaniel Hamann <coders@design2code.co.za>
     * @return void
     */
    public function d2c_delete_products()
    {

        set_time_limit(0);
        ini_set('max_execution_time', 0);
        
        $products_count = $_POST['count'];
        $products_status = $_POST['status'];
        $category = $_POST['category'];

        $display = '<div class="updated">';

        check_ajax_referer('d2c-delete-products', 'wp_nonce');

        // Arguments for get_posts

        if($category != 'any'){
            $args = array(
                'numberposts' => $products_count,
                'post_type' => array('product'),
                'post_status' => $products_status,
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'id', // Use 'id' if using category ID instead
                        'terms'    => $category,
                    ),
                ),
            );
        }else{
            $args = array(
                'numberposts' => $products_count,
                'post_type' => array('product'),
                'post_status' => $products_status
            );
        }

        // Fetch posts based on arguments
        $products = get_posts($args);

        foreach ($products as $product) {
            // add second parameter to true to immediately permanently delete post
            wp_delete_post($product->ID, true);
            //To send to trach first, select the below option
            // wp_trash_post( $product->ID )
        }

        //now we delete the images
        $display .= '<p>Products succesfully deleted';
        $display .= '&nbsp;<button name="d2c_refresh" class="button button-primary button-large" onClick="location.reload()" >Go Back</button></p>';
        $display .= '</div>';

        // If display var has not changed, dont display it.
        if ($display != '<div class="updated"></div>') {
            echo $display;
        } else {
            echo '<div class="error"><p>Sorry, Something went wrong..</p></div>';
        }

        wp_die();
    }//end function

    /*
     * D2c delete single product
     *
     * The AJAX endpoint to delete a single product  
     *
     * @Author Nathaniel Hamann <coders@design2code.co.za>
     * @return void
     */
    public function d2c_delete_single_product()
    {
        
        $pid = $_POST['pid'];

        $display = '<div class="updated">';

        check_ajax_referer('d2c-delete-product-nounce', 'wp_nonce');

        // add second parameter to true to immediately permanently delete post
        wp_delete_post($pid, true);
        //To send to trach first, select the below option
        // wp_trash_post( $product->ID )

        //now we delete the images
        $display .= '<p>Product succesfully deleted.</p>';
        // $display .= '&nbsp;<button name="d2c_refresh" class="button button-primary button-large" onClick="location.reload()" >Go Back</button></p>';
        $display .= '</div>';

        // If display var has not changed, dont display it.
        if ($display != '<div class="updated"></div>') {
            echo $display;
        } else {
            echo '<div class="error"><p>Sorry, Something went wrong..</p></div>';
        }

        wp_die();
    }//end function

    /*
     * D2c check count
     *
     * The AJAX endpoint to count products  
     *
     * @Author Nathaniel Hamann <coders@design2code.co.za>
     * @return void
     */
    public function d2c_check_count()
    {
        $products_status = $_POST['status'];
        $category = $_POST['category'];

        $display = '<div class="updated">';

        // Arguments for get_posts

        if($category != 'any'){
            $args = array(
                'numberposts' => -1,
                'post_type' => array('product'),
                'post_status' => $products_status,
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'id', // Use 'id' if using category ID instead
                        'terms'    => $category,
                    ),
                ),
            );
        }else{
            $args = array(
                'numberposts' => -1,
                'post_type' => array('product'),
                'post_status' => $products_status
            );
        }

        // Fetch posts based on arguments
        $products = get_posts($args);


        //now we delete the images
        $display .= '<p>'.count($products).' items were found.</p>';
        $display .= '</div>';

        // If display var has not changed, dont display it.
        if ($display != '<div class="updated"></div>') {
            echo $display;
        } else {
            echo '<div class="error"><p>Sorry, Something went wrong..</p></div>';
        }

        wp_die();
    }//end function

    /*
     * delete products hourly
     *
     * The cron to delete products, limited to 20 per 5m  
     *
     * @Author Nathaniel Hamann <coders@design2code.co.za>
     * @return void
     */
    public function delete_products_hourly()
    {
        // Arguments for get_posts
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'numberposts' => 40, // To fetch all matching posts
        );

        // Fetch posts based on arguments
        $products = get_posts($args);

        foreach ($products as $product) {
            // add second parameter to true to immediately permanently delete post
            wp_trash_post($product->ID);
        }
    }//end function




    //Decide which tab and contents to show
    public function create_pos_tabs()
    {
        //If none is set, set it to index
        if ((!isset($_GET['tab'])) || ($_GET['tab'] == '')) {
            $page = 'index';
        } else {
            $page = $_GET['tab'];
        }

        //Set tab titles
        $tabs = array(
            'index' => 'Online Importer',
            'image_import' => 'Image Import',
            'xml' => 'XML Test'
        );
        ?>
        <div id="icon-themes" class="icon32"><br></div>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ($tabs as $tab => $name) {
                $class = ($tab == $page) ? ' nav-tab-active' : '';
                echo '<a class="nav-tab' . $class . '" href="?page=d2c-pos-importer&tab=' . $tab . '" >' . $name . '</a>';
            }
            ?>
        </h2>

        <?php
        switch ($page) {
            case 'index':
                $this->pos_fetch_page('index');
                break;
            case 'image_import':
                $this->pos_fetch_page('image_import');
                break;
            case 'xml':
                $this->pos_fetch_page('xml');
                break;
        }
    }



    /*
     * Update log
     *
     * Update log file
     *
     * @Author Nathaniel Hamann <coders@design2code.co.za>
     * @param string $file Name of the file to record message to
     * @param string $message String message to record
     * @return void
     */
    public function update_log($file, $message)
    {
        //create file
        $logfile = fopen(D2C_IMAGE_CHECKER . $file, "a+");
        $message = $message . ' ' . date('h:i:s A') . "\n";
        fwrite($logfile, $message);
        fclose($logfile);
    }

}//end class
