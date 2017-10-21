<?php
global $user_ID;

$total = ( float ) $product->et_budget;

/* Get extra services */
$extras_ids = array();
if ( isset( $_GET['extras_ids'] ) ) {
    $extras_ids = $_GET['extras_ids'];
}
if ( !empty($extras_ids ) ) {
    foreach ( $extras_ids as $key => $value ) {
        $extra = mje_extra_action()->get_extra_of_mjob( $value, $product->ID );
        if ($extra) {
            $total += ( float ) $extra->et_budget;
        } else {
            unset($extras_ids[$key]);
        }
    }
}

$total_text = mje_format_price( $total );

// Generate order args
$order_args = array();

$default_order_args = array(
    'mjob_name' => $product->post_title,
    'post_title' => sprintf( __('Order for %s ', 'enginethemes' ), $product->post_title ),
    'post_content' => sprintf( __('Order for %s ', 'enginethemes' ), $product->post_title ),
    'post_parent' => $product->ID,
    'et_budget' => $product->et_budget,
    'total' => $total,
    'extra_ids' => $extras_ids,
    'post_type' => 'mjob_order',
    'method' => 'create',
    '_wpnonce' => de_create_nonce('ae-mjob_post-sync'),
);

// Opening message
if( ! empty( $product->opening_message ) ) {
    $default_order_args['opening_message'] = $product->opening_message;
}

// Merge order args with default order args
$order_args = wp_parse_args( $order_args, $default_order_args );

if( $user_ID != $product->post_author ) :
?>
    <div class="title-top-pages">
        <p class="block-title"><?php _e('Checkout details', 'enginethemes') ?></p>
    </div>

    <div class="row order-information">
        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 items-chosen">
            <div class="block-items">
                <p class="title-sub"><?php _e('Microjob name', 'enginethemes'); ?></p>
                <div class="mjob-list">
                    <?php mje_get_template( 'template/mjob-item.php', array( 'current' => $product ) ); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 order mjob-order-info">
            <div class="row inner">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="title-sub"><?php _e('Summary description', 'enginethemes'); ?></div>
                    <?php echo $product->post_content; ?>
                    <div class="mjob-order-info row">
                        <div class="title-sub col-lg-2 col-md-2 col-sm-2 col-xs-2"><?php _e('Price', 'enginethemes') ;?></div>
                        <p class="price col-lg-10 col-md-10 col-sm-10 col-xs-10 float-right"><?php echo $product->et_budget_text; ?></p>
                    </div>
                </div>
            </div>
            <div class="add-extra">
                <span class="title-sub"><?php _e('Extra', 'enginethemes'); ?></span>
                <div class="extra-container">
                    <?php mje_get_template( 'template/checkout/list-extras.php', array( 'post' => $product ) ); ?>
                </div>
            </div>
            <div class="float-right action-order">
                <p>
                    <span class="total-text"><?php _e('Total', 'enginethemes' ); ?></span>
                    <span class="total-price mjob-price"><?php echo $total_text; ?></span>
                </p>
                <button class="<?php mje_button_classes( array( 'btn-checkout', 'mjob-btn-checkout', 'waves-effect', 'waves-light' ) ); ?>"><?php _e('Checkout now', 'enginethemes'); ?></button>
            </div>
        </div>
    </div>

    <?php
    //echo '<script type="text/template" id="mjob_single_data" >' . json_encode($product) . '</script>';
    echo '<script type="text/template" id="mje-checkout-info">' . json_encode( $order_args ) . '</script>';
    echo '<script type="text/template" id="mje-extra-ids">' . json_encode( $extras_ids ) . '</script>';
    ?>
<?php else: ?>
    <div class="error-block">
        <p><?php _e('You cannot make an order for your own mJob', 'enginethemes'); ?></p>
        <p><?php printf(__('Please browsing other <a href="%s">mJobs</a> to find the correct one.', 'enginethemes'), get_post_type_archive_link('mjob_post')); ?></p>
    </div>
<?php endif; ?>
