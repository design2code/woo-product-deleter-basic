# WooCommerce Product Deleter Basic - Project Context

## Overview

This is a lightweight WordPress plugin for bulk managing and deleting WooCommerce products directly from the admin dashboard. Built to help sites with many products, plugins, and themes where the standard WordPress admin becomes sluggish.

**Core Purpose:** Provide an intuitive admin interface to view and permanently delete WooCommerce products without loading unnecessary data (categories, tags, SEO, pricing, dates).

## Project Structure

```
.
├── README.md                    # Plugin documentation and installation instructions
├── settings.class.php           # Main plugin PHP class - core functionality
├── index.php                    # Root protection file (WordPress standard)
├── d2c-woo-delete-admin.js      # Frontend JavaScript for AJAX operations
├── d2c-woo-delete-admin.css     # Frontend CSS styling
└── .git/                        # Version control directory
```

## Key Files Explained

### 1. README.md (Documentation)
**Purpose:** User-facing documentation

**Contains:**
- Plugin description and features
- System requirements (WordPress 4.0+, WooCommerce 3.0+, PHP 5.6+)
- Installation instructions
- Usage guide with navigation path (Tools > Woo Product Delete)
- Author information and contact details
- Warning about permanent deletion (no recovery)

**Author:** Nathaniel Hamann <coders@design2code.co.za>

### 2. settings.class.php (Main Core File)
**Purpose:** All plugin logic and functionality

**Class:** D2c_ProductDeleteSettingsPage

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `__construct()` | Registers WordPress hooks: admin menu, AJAX handlers, cron job |
| `add_login_page()` | Creates admin page under "Tools" menu with `manage_options` capability |
| `create_landing_page()` | Renders the main interface HTML form with filters and product list table |
| `d2c_delete_products()` | AJAX handler for bulk deletion (deletes multiple products at once) |
| `d2c_delete_single_product()` | AJAX handler for single product deletion |
| `d2c_check_count()` | AJAX handler to check/count products matching criteria |
| `delete_products_hourly()` | Cron job function (commented out by default) - auto-deletion every 5 mins |
| `create_pos_tabs()` | Tab navigation system (legacy/unused tabs: Online Importer, Image Import, XML Test) |
| `update_log()` | Logging helper for tracking operations to files |

**WordPress Hooks Registered:**
- `admin_menu` → Adds "Woo Product Delete" page under Tools menu
- `wp_ajax_d2c_delete_products` → Bulk delete AJAX endpoint
- `wp_ajax_d2c_delete_product` → Single delete AJAX endpoint
- `wp_ajax_d2c_check_count` → Product count AJAX endpoint

### 3. index.php (Root File)
**Purpose:** WordPress plugin root protection

**Function:** Empty file preventing direct access to plugin core files when accessing `/plugins/woo-product-deleter-basic/` directly in browser URLs.

### 4. d2c-woo-delete-admin.js (Frontend JavaScript)
**Purpose:** Client-side AJAX interactions using jQuery

**Functions:**

1. **Bulk Delete Button Handler (`.d2c_delete_products`)**
   - Shows confirmation dialog
   - Displays loading message in `#d2c_response` container
   - Sends AJAX POST request to `d2c_delete_products` action
   - Replaces response area with server return value

2. **Single Delete Button Handler (`.d2c_delete_single_product`)**
   - Shows confirmation dialog
   - Sends AJAX POST request to `d2c_delete_product` action with product ID
   - Replaces response area with server return value

3. **Product Count Check Button (`#check_count`)**
   - Sends AJAX POST request to `d2c_check_count` action
   - Updates response area with product count

**Key jQuery Patterns:**
- Uses WordPress ajaxurl for cross-domain requests
- Reads nonce tokens from data attributes on buttons
- Updates response display area via jQuery DOM manipulation

### 5. d2c-woo-delete-admin.css (Styling)
**Purpose:** Custom admin interface styling

**Components Styled:**
- `.d2c_settings_container` - Main page wrapper
- Form layout with labeled rows
- Select dropdowns and submit buttons
- DataTable overrides for compatibility

## Component Interaction Flow

### Page Load Sequence:

```
1. WordPress loads plugin via main file registration
2. settings.class.php::__construct() hooks WordPress actions:
   a) admin_menu → registers page at Tools > Woo Product Delete
   b) AJAX handlers registered for wp_ajax_* actions
3. Browser navigates to ?page=d2c-products-deleter
4. create_landing_page() renders HTML with:
   - Filter form (count, status, stock, category)
   - Nonce tokens on buttons
5. JavaScript loaded and DOM ready event fires
```

### Deletion Flow:

```
User Action: Click "Delete" or "Delete All"
    ↓
JavaScript confirms user with dialog box
    ↓
jQuery.post() sends AJAX request to WordPress REST API (admin-ajax.php)
    ↓
WordPress receives request, validates wp_nonce for security
    ↓
settings.class.php executes corresponding handler method:
  - d2c_delete_products() → bulk deletion
  - d2c_delete_single_product() → single deletion
    ↓
Handler queries products via get_posts() with applied filters
    ↓
foreach loop calls wp_delete_post($product->ID, true) for each product
    ↓
Success message returned in display div
    ↓
jQuery receives response and updates #d2c_response container
```

### Filter Application Flow:

User selects criteria → submit button click → get_posts() builds query arguments:

```php
$args = array(
    'numberposts' => $products_count,
    'post_type'   => array('product'),
    'post_status'  => $products_status,
);

// If category selected
if($category != 'any') {
    $args['tax_query'][] = array(
        'taxonomy' => 'product_cat',
        'field'    => 'id',
        'terms'    => $category
    );
}

// If stock status selected  
if($products_stock_status !== 'any') {
    $args['meta_query'][] = array(
        'key'   => '_stock_status',
        'value' => $products_stock_status,
        'compare' => '='
    );
}
```

## Data Flow Architecture

### Frontend → Backend:

```
[d2c-woo-delete-admin.js] 
    ↓ (jQuery.post to ajaxurl)
[WordPress admin-ajax.php]
    ↓ (route by action parameter)
[D2c_ProductDeleteSettingsPage method]
```

### Backend Response → Frontend:

```
[D2c_ProductDeleteSettingsPage::d2c_*_product()]
    ↓ (echo HTML/JSON response)
[d2c_response container]
    ↓ (jQuery.html())
[User sees success/error message]
```

## Security Mechanisms

1. **Nonce Verification:** All AJAX requests validated via `check_ajax_referer()`
   - Single delete uses nonce: `'d2c-delete-product-nounce'`
   - Bulk delete uses nonce: `'d2c-delete-products'`
   
2. **Capability Check:** Page restricted to `manage_options` capability
   
3. **Direct Access Protection:** Empty `index.php` prevents file enumeration

4. **Time Limit Management:** `set_time_limit(0)` and `ini_set('max_execution_time', 0)` for large deletions

## File Dependencies

- **PHP:** WordPress core, WooCommerce (for product post_type)
- **JavaScript:** jQuery (loaded by WordPress admin)
- **CSS:** Inline styles only (no external dependencies)

## Usage Patterns

### Single Product Deletion:
1. User clicks "Delete" link on individual product row
2. JavaScript confirms and sends AJAX request with product ID
3. WordPress deletes that specific post (permanently)

### Bulk Deletion:
1. User submits filter form to load products into table
2. User clicks "Delete All Products" button
3. JavaScript sends AJAX request with all filters as data attributes
4. WordPress fetches and deletes matching posts (up to count limit)

### Count Check Only:
1. User clicks "Check Product Count" button
2. JavaScript sends AJAX request without deleting
3. WordPress returns product count via `d2c_check_count()`

## Cron Job Feature (Disabled by Default)

```php
// Uncomment to enable hourly deletion
add_action('delete_products_from_site', 
    array($this, 'delete_products_hourly'));
```

When enabled: Runs every 5 minutes, deleting up to 40 products per tick.

## Notes for Future Development

- The plugin deletes posts directly (no trash) unless explicitly using `wp_trash_post()`
- Product images are deleted with the post via standard WordPress behavior
- Legacy tab functions exist but appear unused (Online Importer, Image Import, XML Test)
- Logging function exists but no log file path defined (`D2C_IMAGE_CHECKER` constant missing)
