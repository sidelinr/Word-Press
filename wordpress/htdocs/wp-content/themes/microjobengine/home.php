<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme and one
 * of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query,
 * e.g., it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage MicrojobEngine
 * @since MicrojobEngine 1.0
 *
 * Template Name: Home Page
 */
get_header();
// Get heading title and sub title
$heading_title = get_theme_mod('home_heading_title') ? get_theme_mod('home_heading_title') : __('Get your stuffs done from $5', 'enginethemes');
$sub_title = get_theme_mod('home_sub_title') ? get_theme_mod('home_sub_title') : __('Browse through millions of micro jobs. Choose one you trust. Pay as you go.', 'enginethemes');

$img_url = ae_get_option('search_background');
$img_theme_mod = get_theme_mod('search_background');
if(!empty($img_url)) {
    $img_url = $img_url['full']['0'];
} elseif(false === $img_theme_mod) {
    $img_url = get_template_directory_uri() . '/assets/img/bg-slider.jpg';
} else {
    $img_url = "";
}

?>
    <div class="slider">
        <div class="search-form">
            <h1 class="wow fadeInDown"><?php echo $heading_title; ?></h1>
            <h4 class="wow fadeInDown"><?php echo $sub_title; ?></h4>
            <form action="<?php echo get_site_url(); ?>" class="form-search">
                <div class="outer-form">
                    <span class="text"><?php _e('I am looking for', 'enginethemes'); ?></span>
                    <input type="text" name="s" class="text-search-home" placeholder="<?php _e('a logo design', 'enginethemes'); ?>">
                    <button class="btn-search hvr-buzz-out waves-effect waves-light">
                        <div class="search-title">
                            <span class="text-search"><?php _e('SEARCH NOW', 'enginethemes') ;?></span>
                        </div>
                    </button>
                </div>
            </form>
        </div>
        <div class="background-image">
            <div class="backgound-under"></div>
            <img src="<?php echo $img_url; ?>" alt="" class="wow fadeIn">
        </div>
        <div class="statistic-job-number">
            <p class="link-last-job"><?php echo sprintf(__('There are %s microjobs more', 'enginethemes'), mje_get_mjob_count()); ?> <div class="bounce"><i class="fa fa-angle-down"></i></div></p>
        </div>
    </div>
    <div id="content">
        <div class="block-hot-items">
            <div class="container inner-hot-items wow fadeInUpBig">
                <?php
                $cat_title = get_theme_mod('mje_other_title_category') ? get_theme_mod('mje_other_title_category') : __('FIND WHAT YOU NEED', 'enginethemes');
                ?>
                <p class="block-title"><?php echo $cat_title;  ?></p>
                <?php
                $terms = get_terms( 'mjob_category', 'orderby=count&hide_empty=0' ); ?>
                <ul class="row">
                    <?php
                    if( !empty($terms) ):
                        $i = 0;
                        $img_url = get_template_directory_uri().'/assets/img/icon-1.png';
                        foreach( $terms as $key=>$term){
                            $link = get_term_link($term->term_id, 'mjob_category');
                            $featured = get_term_meta($term->term_id, 'featured-tax', true);
                            $img = get_term_meta($term->term_id, 'mjob_category_image', true);
                            if( !empty($img) ){
                                $img_url = esc_url( wp_get_attachment_image_url( $img, 'full' ) );
                            }
                            if( $featured && $i < 8 ):
                                $i++;
                    ?>
                    <li class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <a href="<?php echo $link; ?>">
                            <div class="hvr-float-shadow">
                                <div class="avatar">
                                    <img src="<?php echo $img_url;?>" alt="">
                                    <div class="line"><span class="line-distance"></span></div>
                                </div>
                                <h2 class="name-items">
                                    <?php echo $term->name ?>
                                </h2>
                            </div>
                        </a>
                    </li>
                    <?php
                        endif;
                    }
                    endif; ?>
                </ul>
            </div>
        </div>
        <div class="block-items">
            <div class="container">
                <?php
                $mjob_title = get_theme_mod('mje_other_title_service') ? get_theme_mod('mje_other_title_service') : __('LATEST MICROJOBS', 'enginethemes');
                ?>
                <p class="block-title float-center"><?php echo $mjob_title ;?></p>
                <?php
                $args = array(
                    'post_type'=> 'mjob_post',
                    'post_status'=> array(
                        'publish',
                        'unpause'
                        ),
                    'showposts'=> 8,
                    'orderby'=>'date',
                    'order'=> 'DESC'
                    );
                $home_query = new WP_Query( $args );
                global $ae_post_factory;
                $post_object = $ae_post_factory->get( 'mjob_post' );
                ?>
                <?php if( $home_query->have_posts() ) : ?>
                    <ul class="row mjob-list">
                    <?php while( $home_query->have_posts() ) : ?>
                        <?php $home_query->the_post(); ?>
                        <li class="col-lg-3 col-md-3 col-sm-6 col-xs-6 col-mobile-12">
                            <?php
                            global $post;
                            $convert = $post_object->convert( $post );
                            mje_get_template( 'template/mjob-item.php', array( 'current' => $convert ) );
                            ?>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
        <?php get_template_part('template/about', 'block'); ?>
    </div>
<?php
get_footer();
?>