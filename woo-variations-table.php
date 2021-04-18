<?php
/*
Plugin Name: Woo Variations Table
Plugin URI: https://wordpress.org/plugins/woo-variations-table/
Description: Show a table of all the available variations of a variable product to make it easier for users to find the variation they are searching for.
Author: Alaa Rihan
Author URI: https://lb.linkedin.com/in/alaa-rihan-6971b686
Text Domain: woo-variations-table
Domain Path: /languages/
Version: 2.2.12
Requires at least: 4.7.0
Requires PHP: 5.6.20
WC requires at least: 3.0.0
WC tested up to: 4.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define("WOO_VARIATIONS_TABLE_VERSION", '2.2.12');


add_action('plugins_loaded', 'woo_variations_table_loaded', 1);
function woo_variations_table_loaded()
{
    // Load Plugin text domain
    load_plugin_textdomain( 'woo-variations-table', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

    // Check if WooCommerce is enabled
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'woo_variations_table_woocommerce_disabled_notice');
        return;
    }
}

// Display WC disabled notice
function woo_variations_table_woocommerce_disabled_notice()
{
    echo '<div class="error"><p><strong>' . __('Woo Variations Table', 'woo-variations-table') . '</strong> ' . sprintf(__('requires %sWooCommerce%s to be installed & activated!', 'woo-variations-table'), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>') . '</p></div>';
}

// Settings menu item
add_action('admin_menu', 'woo_variations_table_settings_menu', 99);
function woo_variations_table_settings_menu()
{
    add_submenu_page('woocommerce', __('Woo Variations Table', 'woo-variations-table'), __('Woo Variations Table', 'woo-variations-table'), 'manage_options', 'woo_variations_table', 'woo_variations_table_settings_page_callback');
}

// Register our settings
add_action('admin_init', 'woo_variations_table_register_settings');
function woo_variations_table_register_settings()
{
    register_setting('woo_variations_table_settings', 'woo_variations_table_columns');
    register_setting('woo_variations_table_settings', 'woo_variations_table_show_filters');
    register_setting('woo_variations_table_settings', 'woo_variations_table_show_spinner');
    register_setting('woo_variations_table_settings', 'woo_variations_table_place');
    register_setting('woo_variations_table_settings', 'woo_variations_table_show_available_options_btn');
    register_setting( 'woo_variations_table_settings', 'woo_variations_table_columns_order' ); 
}

function woo_variations_table_get_default_columns(){
    return array(
        'image_link' => 'on',
        'sku' => 'on',
        'variation_description' => 'on',
        'dimensions_html' => '',
        'weight_html' => '',
        'attributes' => '',
        'availability_html' => 'on',
        'price_html' => 'on',
        'quantity' => 'on',
    );
}

function woo_variations_table_get_columns_labels(){
    return array(
        'image_link' => __('Thumbnail', 'woo-variations-table'),
        'sku' => __('SKU', 'woo-variations-table'),
        'variation_description' => __('Description', 'woo-variations-table'),
        'dimensions_html' => __('Dimensions', 'woo-variations-table'),
        'weight_html' => __('Weight', 'woo-variations-table'),
        'attributes' => __('Attributes', 'woo-variations-table'),
        'availability_html' => __('Stock', 'woo-variations-table'),
        'price_html' => __('Price', 'woo-variations-table'),
        'quantity' => __('Quantity', 'woo-variations-table'),
    );
}

// Settings page callback function
function woo_variations_table_settings_page_callback()
{
    $default_columns = woo_variations_table_get_default_columns();
    $columns_labels = woo_variations_table_get_columns_labels();
    $columns = (array) get_option('woo_variations_table_columns', $default_columns);
    $showFilters = get_option('woo_variations_table_show_filters', 'on');
    $showSpinner = get_option('woo_variations_table_show_spinner', 'on');
    $place = get_option('woo_variations_table_place', 'woocommerce_after_single_product_summary_9');
    $showAvailableOptionBtn = get_option('woo_variations_table_show_available_options_btn', 'on');?>
<div class="wrap">
  <h1><?php echo __('Woo Variations Table Settings', 'woo-variations-table'); ?></h1>
  <form method="post" action="options.php">
      <?php settings_fields('woo_variations_table_settings');?>
      <?php do_settings_sections('woo_variations_table_settings');?>
      <table class="form-table">
          <tr valign="top">
            <th scope="row"><?php echo __('Where to Place Variations Table', 'woo-variations-table'); ?></th>
            <td>
                <select name='woo_variations_table_place' >
                    <option value="woocommerce_after_single_product_summary_9" <?php echo $place == 'woocommerce_after_single_product_summary_9' ? "selected" : ''; ?>>After product summary ( before description )</option>
                    <option value="woocommerce_single_product_summary_11" <?php echo $place == 'woocommerce_single_product_summary_11' ? "selected" : ''; ?>>After product price</option>
                    <option value="woocommerce_single_product_summary_41" <?php echo $place == 'woocommerce_single_product_summary_41' ? "selected" : ''; ?>>After product short description</option>
                    <option value="woocommerce_after_single_product_summary_11" <?php echo $place == 'woocommerce_after_single_product_summary_11' ? "selected" : ''; ?>>After product description</option>
                </select>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php echo __('Select Columns to Show in the Variations Table', 'woo-variations-table'); ?></th>
            <td><?php woo_variations_table_create_list_of_columns('woo-variations-table-columns', $default_columns, $columns, $columns_labels);?></td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php echo __('Show Filters', 'woo-variations-table'); ?></th>
            <td>
                <label><input type='checkbox' name='woo_variations_table_show_filters' <?php echo $showFilters ? "checked='checked'" : ''; ?> /> <?php echo __('Show filters and search box', 'woo-variations-table'); ?></label>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php echo __('Available Options Button', 'woo-variations-table'); ?></th>
            <td>
                <label><input type='checkbox' name='woo_variations_table_show_available_options_btn' <?php echo $showAvailableOptionBtn ? "checked='checked'" : ''; ?> /> <?php echo __('Show "Available Options" button to scroll to the variations table when clicked', 'woo-variations-table'); ?></label>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php echo __('Add to Cart Loader Icon', 'woo-variations-table'); ?></th>
            <td>
                <label><input type='checkbox' name='woo_variations_table_show_spinner' <?php echo $showSpinner ? "checked='checked'" : ''; ?> /> <?php echo __('Show animated loader icon when click add to cart and tick icon when variation added to cart', 'woo-variations-table'); ?></label>
            </td>
          </tr>
      </table>

      <?php submit_button();?>

  </form>
</div>
<?php
}

function woo_variations_table_create_list_of_columns($id, $columns, $values, $labels)
{
?>
    <ul style='margin-top: 5px;' class='mnt-checklist sortable' id='<?php echo $id; ?>' >
<?php
    $ordering  = get_option( 'woo_variations_table_columns_order' );
    if(!empty($ordering)){
        ksort($ordering);
        foreach ($ordering as $key => $value){
            if(!isset($columns[$value])) continue;
            if(!isset($values[$value])){
                $values[$value] = '';
            }
            woo_variations_table_setting_column($value, $values[$value], $labels[$value]);
        }
    }else{
        $ordering = array();
    }
    $ordered_columns = array_values($ordering);
    foreach ($columns as $key => $value){
        if(!in_array($key, $ordered_columns)){
            if(!isset($values[$key])){
                $values[$key] = '';
            }
            woo_variations_table_setting_column($key, $values[$key], $labels[$key]);
        }
    }
?>
    </ul>
<?php
}

function woo_variations_table_setting_column($key, $value, $label){
    $checked = " ";
    if (isset($value) && $value) {
        $checked = " checked='checked' ";
    }
    ?>
        <li class='ui-state-default'>
        <div class="vt-item-reorder-nav">
            <button type="button" class="vt-move-up" tabindex="0" aria-hidden="false"><?php esc_html_e( 'Move up', 'woo-variations-table' ); ?></button>
            <button type="button" class="vt-move-down" tabindex="0" aria-hidden="false"><?php esc_html_e( 'Move down', 'woo-variations-table' ); ?></button>
            <input type="hidden" name="woo_variations_table_columns_order[]" value="<?php echo esc_attr( $key ); ?>" />
        </div>
            <label>
                <input type='checkbox' name='woo_variations_table_columns[<?php echo $key; ?>]' <?php echo $checked; ?> /> 
                <?php echo $label; ?> 
            </label>
        </li>
<?php
}

// Remove default variable product add to cart
add_action('plugins_loaded', 'remove_variable_product_add_to_cart');
function remove_variable_product_add_to_cart()
{
    remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
}

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'variations_table_scripts');
function variations_table_scripts()
{
    if (is_product()) {
        wp_enqueue_script('woo-variations-table-scripts', plugins_url('js/woo-variations-table-scripts.js', __FILE__), array('jquery'), WOO_VARIATIONS_TABLE_VERSION, true);
        wp_enqueue_script('woo-variations-table-app', plugins_url('ui/public/build/woo-variations-table-app.js', __FILE__), array('wc-add-to-cart'), WOO_VARIATIONS_TABLE_VERSION, true);
        wp_enqueue_style('woo-variations-table-style', plugins_url('ui/public/woo-variations-table.css', __FILE__), array(), WOO_VARIATIONS_TABLE_VERSION);
        wp_enqueue_style('woo-variations-table-app-style', plugins_url('ui/public/build/woo-variations-table-app.css', __FILE__), array(), WOO_VARIATIONS_TABLE_VERSION);
    }
}

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'variations_table_admin_scripts');
function variations_table_admin_scripts() {
    wp_enqueue_script('woo-variations-table-scripts', plugins_url('js/settings.js', __FILE__), array('jquery-ui-sortable'), WOO_VARIATIONS_TABLE_VERSION, true);
    wp_enqueue_style('woo-variations-table-admin-style', plugins_url('css/admin.css', __FILE__), array(), WOO_VARIATIONS_TABLE_VERSION);
}

// Update database
add_action('upgrader_process_complete', 'variations_table_database_update');
function variations_table_database_update()
{
    $plugin_db_version = get_option('woo_variations_table_db_version', WOO_VARIATIONS_TABLE_VERSION);
    if (version_compare($plugin_db_version, '1.2', '<')) {
        $activeColumns = get_option('woo_variations_table_columns');
        if (isset($activeColumns['weight'])) {
            $activeColumns['weight_html'] = $activeColumns['weight'];
            unset($activeColumns['weight']);
            update_option('woo_variations_table_columns', $activeColumns);
        }
        update_option('woo_variations_table_show_attributes', '');
    }else if(version_compare($plugin_db_version, '2.2.0', '<')){
        $activeColumns = get_option('woo_variations_table_columns');
        if (isset($activeColumns['stock'])) {
            $activeColumns['availability_html'] = $activeColumns['stock'];
            unset($activeColumns['stock']);
        }
        if (isset($activeColumns['dimensions'])) {
            $activeColumns['dimensions_html'] = $activeColumns['dimensions'];
            unset($activeColumns['dimensions']);
        }
        update_option('woo_variations_table_columns', $activeColumns);
    }
    if ($plugin_db_version != WOO_VARIATIONS_TABLE_VERSION) {
        update_option('woo_variations_table_db_version', WOO_VARIATIONS_TABLE_VERSION);
    }
}

add_action('init', 'woo_variations_table_init');
function woo_variations_table_init()
{
    $place = get_option('woo_variations_table_place', 'woocommerce_after_single_product_summary_9');
    $priority = strrchr($place, "_");
    $place = str_replace($priority, "", $place);
    $priority = str_replace("_", "", $priority);
    add_action($place, 'woo_variations_table_print_table', $priority);

    $showAvailableOptionBtn = get_option('woo_variations_table_show_available_options_btn', 'on');
    if ($showAvailableOptionBtn == 'on') {
        add_action('woocommerce_single_product_summary', 'woo_variations_table_available_options_btn', 11);
    }
}

function woo_variations_table_available_options_btn()
{
    global $product;
    if (!$product->is_type('variable')) {
        return;
    }?>
  <div class="available-options-btn">
    <button scrollto="#variations-table" type="button" class="single_add_to_cart_button button alt"><?php echo apply_filters('woo_variations_table_available_options_btn_text', __('Available options', 'woo-variations-table')); ?></button>
  </div>
  <?php
}

// Print variations table
function woo_variations_table_print_table()
{
    global $product;
    if ($product->is_type('variable')) {
        $thumb_name = apply_filters('woo_variations_table_thumb_name', 'shop_single');
        $productImageURL = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), $thumb_name);
        if (is_array($productImageURL) && count($productImageURL)) {
            $productImageURL = $productImageURL[0];
        }
        $variations = $product->get_available_variations();

        // Image link is no longer exist in WooCommerce 3.x so do this work around
        foreach ($variations as $key => $variation) {
            if (!isset($variation['image_link']) && isset($variation['image'])) {
                $variations[$key]['image_link'] = $variation['image']['src'];
            }
            // price_html is empty if all variations have the same price in WooCommerce 3.x so do this work around
            if (empty($variation['price_html'])) {
                $variations[$key]['price_html'] = $product->get_price_html();
            }
        }

        $product_attributes = $product->get_attributes();
        $variation_attributes = $product->get_variation_attributes();
        $attrs = array();
        foreach ($variation_attributes as $key => $name) {
            $correctkey = strtolower(urlencode(wc_sanitize_taxonomy_name(stripslashes($key))));
            $options = array();
            for ($i = 0; count($name) > $i; $i++) {
                $terms = array_values($name);
                $term = get_term_by('slug', $terms[$i], $key);
                $slug = array_values($name);
                $slug = $slug[$i];
                if ($term) {
                    array_push($options, array('name' => $term->name, 'slug' => $slug));
                } else {
                    array_push($options, array('name' => $slug, 'slug' => $slug));
                }
            }
            array_push($attrs, array(
                "key" => $correctkey,
                "name" => wc_attribute_label($key),
                "visible" => $product_attributes[$correctkey]->get_visible(),
                "options" => $options,
            ));
        }
        $default_columns = woo_variations_table_get_default_columns();
        $activeColumns = get_option('woo_variations_table_columns', $default_columns);
        $showFilters = get_option('woo_variations_table_show_filters', 'on');
        $showSpinner = get_option('woo_variations_table_show_spinner', 'on');
        $columnsText = woo_variations_table_get_columns_labels();
        $columnsOrder  = get_option( 'woo_variations_table_columns_order', array() );

        $woo_variations_table_data = array(
            "variations" => $variations,
            "attributes" => $attrs,
            "showFilters" => $showFilters,
            "activeColumns" => $activeColumns,
            "columnsOrder" => $columnsOrder,
            "imageURL" => $productImageURL,
            "showSpinner" => $showSpinner,
            "textVars" => array(
                "columnsText" => $columnsText,
                "addToCartText" => __("Add to Cart", 'woo-variations-table'),
                "anyText" => __("Any", 'woo-variations-table'),
                "searchPlaceholderText" => __("Keywords", 'woo-variations-table'),
                "noResultsText" => __("No results!", 'woo-variations-table'),
            ),
        );

        wp_localize_script('woo-variations-table-app', 'wooVariationsTableData', $woo_variations_table_data);
        do_action( 'woo_variations_table_before_table' );
        ?>
        <div id='variations-table' class="variations-table">
          <h3 class="available-title"><?php echo esc_html_e('Available Options:', 'woo-variations-table'); ?></h3>
          <div id="woo-variations-table-component"></div>
        </div>
        <?php
        do_action( 'woo_variations_table_after_table' );
} else {
        wp_dequeue_script('woo-variations-table-scripts');
        wp_dequeue_script('woo-variations-table-app');
    }
}
