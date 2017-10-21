<?php
$custom_order = $product;
$product = mje_mjob_action()->get_mjob( $custom_order->mjob_id );
$product->type = 'custom_order';

// Get offer information
$offer = array();
$offer['id'] = ( int ) get_post_meta( $custom_order->ID, 'custom_offer_id', true );
$offer_post = get_post( $offer['id'] );
$offer['budget'] = get_post_meta( $offer['id'], 'custom_offer_budget', true );
$offer['time_delivery'] = get_post_meta( $offer['id'], 'custom_offer_etd', true );
$offer['content'] = $offer_post->post_content;
$offer['attach_file'] = mje_get_list_attach_files( $offer['id'] );

// Get total of checkout
$total = $offer['budget'];
$total_text = mje_format_price( $total );

$extras_ids = array();

// Generate order args
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

$order_args = array(
    'post_title' => sprintf( __('Custom order for %s ', 'enginethemes' ), $product->post_title ),
    'post_content' => sprintf( __('Custom order for %s ', 'enginethemes' ), $product->post_title ),
    'custom_order_id' => $custom_order->ID,
    'custom_offer_id' => $offer['id']
);

// Opening message
if( ! empty( $product->opening_message ) ) {
    $default_order_args['opening_message'] = $product->opening_message;
}

// Merge order args with default order args
$order_args = wp_parse_args( $order_args, $default_order_args );

$custom_order_status = get_post_meta( $custom_order->ID, 'custom_order_status', true );
if( ! empty( $custom_order_status ) && in_array ( $custom_order_status, array('decline', 'reject', 'checkout') ) ):
    $arr_custom_order_status = array(
        'decline' =>  __('This custom order has been declined.','enginethemes'),
        'reject' => __('This offer has been rejected.', 'enginethemes'),
        'checkout' => __('This offer has been accepted.', 'enginethemes')
    );

    echo '<div class="error-block">';
    echo '<p>' . $arr_custom_order_status[$custom_order_status] . '</p>';
    echo '</div>';
else: ?>
    <div class="title-top-pages">
        <p class="block-title"><?php _e('Checkout details', 'enginethemes') ?></p>
    </div>

    <div class="row order-information">
        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 items-chosen">
            <div class="block-items">
                <p class="title-sub"><?php _e('Custom for mJob', 'enginethemes'); ?></p>
                <div class="mjob-list">
                    <?php mje_get_template( 'template/mjob-item.php', array( 'current' => $product ) ); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 order mjob-order-info">
            <div class="row inner">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="title-sub"><?php _e('Summary description', 'enginethemes'); ?></div>
                    <?php mje_render_toggle_content( $product->post_content ); ?>
                </div>
            </div>

            <div class="row">
                <div class="custom-offer-info">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="title-sub"><?php _e('Custom offer', 'enginethemes'); ?></div>
                        <?php echo wpautop( $offer['content'] ); ?>
                        <div class="attachment-file-name">
                            <?php echo $offer['attach_file']; ?>
                        </div>
                        <div class="time-delivery-custom">
                            <span class="title-sub"><?php _e('Time of delivery', 'enginethemes'); ?></span>
                            <span><?php echo $offer['time_delivery']; ?> <?php echo __('day(s)', 'enginethemes'); ?></span>
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mjob-order-info">
                        <div class="row">
                            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 title-sub"><?php _e('Price', 'enginethemes') ;?></div>
                            <p class="col-lg-10 col-md-10 col-sm-10 col-xs-10 price float-right "><?php echo mje_format_price( $offer['budget'] ); ?></p>
                        </div>
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
    echo '<script type="text/template" id="mje-checkout-info">' . json_encode( $order_args ) . '</script>';
    echo '<script type="text/template" id="mje-extra-ids">' . json_encode( $extras_ids ) . '</script>';
    ?>
<?php endif;?>
