<?php
/**
 * Template list all mJobs
 */
global $ae_post_factory;
$post_object = $ae_post_factory->get('mjob_post');
?>
<ul class="row mjob-list list-mjobs">
<?php $post_data = array();
    if(have_posts()) {
        while (have_posts()) {
            the_post();
            global $post;
            $convert = $post_object->convert( $post );
            $post_data[] = $convert;
            echo '<li class="col-lg-4 col-md-4 col-sm-6 col-xs-6 col-mobile-12 item_js_handle">';
            mje_get_template( 'template/mjob-item.php', array( 'current' => $convert ) );
            echo '</li>';
        }
    } else {
        ?>
        <div class="not-found"><?php _e('There are no mJobs found!', 'enginethemes'); ?></div>
        <?php
    }
?>

</ul>

<?php
echo '<script type="data/json" class="mJob_postdata" >'.json_encode( $post_data ).'</script>';
?>
