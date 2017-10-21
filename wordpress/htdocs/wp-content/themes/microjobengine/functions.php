<?php
define("ET_UPDATE_PATH", "http://update.enginethemes.com/?do=product-update");
define('ET_VERSION', '1.3.4');
if (!defined('ET_URL')) define('ET_URL', 'http://www.enginethemes.com/');
if (!defined('ET_CONTENT_DIR')) define('ET_CONTENT_DIR', WP_CONTENT_DIR . '/et-content/');
define('TEMPLATEURL', get_template_directory_uri() );
$theme_name = 'microjobengine';
define('THEME_NAME', $theme_name);

define('MOBILE_PATH', TEMPLATEPATH . '/mobile/');

//Start pagination page
define('PAGINATION_START', 1);
/**
 * Turn on/off theme debug by writing issues into log file
 * path for log file: /wp-content/et-content/theme.log
 */
define('ET_DEBUG', false);

if (!defined('THEME_CONTENT_DIR ')) define('THEME_CONTENT_DIR', WP_CONTENT_DIR . '/et-content' . '/' . $theme_name);
if (!defined('THEME_CONTENT_URL')) define('THEME_CONTENT_URL', content_url() . '/et-content' . '/' . $theme_name);

// theme language path
if (!defined('THEME_LANGUAGE_PATH')) define('THEME_LANGUAGE_PATH', THEME_CONTENT_DIR . '/lang/');

if (!defined('ET_LANGUAGE_PATH')) define('ET_LANGUAGE_PATH', THEME_CONTENT_DIR . '/lang');

if (!defined('ET_CSS_PATH')) define('ET_CSS_PATH', THEME_CONTENT_DIR . '/css');

if (!defined('USE_SOCIAL')) define('USE_SOCIAL', 1);

// define posttype
if(!defined('MJOB')) {
    define('MJOB', 'mjob_post');
}

require_once dirname(__FILE__) . '/includes/index.php';
new AE_Taxonomy_Meta('mjob_category');
if (!class_exists('AE_Base')) return;

add_filter('show_text_button_process_payment','show_text_button_process_payment_callback',10,2);
function show_text_button_process_payment_callback($content,$ad){
    ob_start();
    ?>
    <a href="<?php echo get_the_permalink($ad->ID) ?>" class="<?php mje_button_classes( array( ) ); ?>">
        <?php _e('Visit your mJob Order', 'enginethemes'); ?><i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
    <?php
    return ob_get_clean();

}