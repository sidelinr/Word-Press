<?php
get_header();
$skin_assets_path = MJE_Skin_Action::get_skin_assets_path();
?>
    <!--SECTION SLIDER-->
    <div class="block-slider">
        <div class="slideshow">
            <!--CONTENT SLIDER-->
            <?php
                $slide_images = array();
                if( get_theme_mod( 'mje_diplomat_slide_custom' ) ) {
                    // Use custom slide image
                    for ( $i = 1; $i <= 5; $i++ ) { 
                        $image = wp_get_attachment_image_src( get_theme_mod( "mje_diplomat_slide_{$i}" ), array( 1920, 548) );
                        if( $image ) {
                            $slide_images[] = $image[0];
                        }
                    }

                } else {
                    // Use default slide image
                    for( $i = 1; $i <= 5; $i++ ) {
                        $slide_images[] = $skin_assets_path . '/img/img-slider-' . $i . '.jpg';
                    }
                }
            ?>
            <div class="slider-wrapper default">
                <div id="slider">
                    <?php 
                        foreach ( $slide_images as $image ) {
                            if( ! empty( $image ) ) {
                                echo '<img src="' . $image . '" alt="slide_image" />';
                            }
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="search-form">
            <!--CONTENT FORM SEARCH-->
            <?php
            // Get heading title and sub title
            $heading_title = get_theme_mod('home_heading_title') ? get_theme_mod('home_heading_title') : __('Get your stuffs done from $5', 'enginethemes');
            $sub_title = get_theme_mod('home_sub_title') ? get_theme_mod('home_sub_title') : __('Browse through millions of micro jobs. Choose one you trust. Pay as you go.', 'enginethemes');
            ?>
            <h1><?php echo $heading_title; ?></h1>
            <h4><?php echo $sub_title; ?></h4>
            <form class="form-search">
                <div class="outer-form-search">
                    <span class="text"><?php _e( 'I am looking for', 'enginethemes' ); ?></span>
                    <input type="text" name="s" class="text-search-home" placeholder="<?php _e( 'a logo design', 'enginethemes' ); ?>">
                    <button class="btn-diplomat btn-find btn- waves-effect waves-light"><div class="search-title"><span class="text-search"><?php _e(  'Search now', 'enginethemes' ); ?></span></div></button>
                </div>
            </form>
        </div>
        <div class="statistic-job-number">
            <p class="link-last-job"><?php printf( __( 'There are %s microjobs more ', 'enginethemes' ), mje_get_mjob_count() ); ?></p><div class="bounce"><i class="fa fa-angle-down"></i></div><p></p>
        </div>
    </div>

    <!--SECTION WHAT -->
    <div class="block-intro-what">
        <div class="container">
            <?php
                $what_title = get_theme_mod( 'mje_diplomat_what_title' ) ? get_theme_mod( 'mje_diplomat_what_title' ) : 'What is <p class="color-customize name-site"><span class="bold">Microjob</span> ENGINE?</p>';
                $what_desc = get_theme_mod( 'mje_diplomat_what_desc' ) ? get_theme_mod( 'mje_diplomat_what_desc' ) : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam blandit feugiat molestie. Suspendisse ut eros diam. Maecenas mattis enim in tristique eleifend. Sed vel turpis sagittis, venenatis nunc a, interdum tortor. Duis tortor diam, vestibulum et mi ac, molestie iaculis sapien. Donec ac fermentum urna. Nullam quis erat vel dui commodo bibendum. Quisque posuere est dolor, vitae semper lorem scelerisque in. Donec semper volutpat turpis suscipit feugiat. Nam cursus vel libero sed dapibus. Cras commodo, ante id efficitur pharetra, tortor elit suscipit nunc, id faucibus turpis ex sed felis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.';
            ?>
            <h2 class="name"><?php echo $what_title; ?></h2>
            <div class="bg-customize line-text"></div>
            <p class="text-content"><?php echo $what_desc ?></p>
        </div>
    </div>

    <!--SECTION FEATURED MICROJOB-->
    <div class="block-items block-items-diplomat">
        <div class="container">
            <h6><?php echo get_theme_mod( 'mje_other_title_service' ) ? get_theme_mod( 'mje_other_title_service' ) : __( 'Latest Microjobs', 'enginethemes' ); ?></h6>
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

    <!--SECTION WHY-->
    <div class="block-intro-why">
        <div class="container">
            <h6><?php echo get_theme_mod( 'mje_diplomat_why_title' ) ? get_theme_mod( 'mje_diplomat_why_title' ) : __( 'Why work with MjE?', 'enginethemes' ); ?></h6>
            <div class="bg-customize line-text"></div>
            <ul class="row">
                <?php
                    $default_why_title = array(
                        __( 'Top Safe', 'enginethemes' ),
                        __( 'Secured Transaction', 'enginethemes' ),
                        __( 'Top Sellers', 'enginethemes' )
                    )
                ?>

                <?php
                    for( $i = 1; $i <= 3; $i++ ) {
                        $why_item["title_{$i}"] = get_theme_mod( "mje_diplomat_why_item_{$i}_title" ) ? get_theme_mod( "mje_diplomat_why_item_{$i}_title" ) : $default_why_title[$i-1];
                        
                        $why_item["desc_{$i}"] = get_theme_mod( "mje_diplomat_why_item_{$i}_desc" ) ? get_theme_mod( "mje_diplomat_why_item_{$i}_desc" ) : 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cum ducimus enim id minus mollitia numquam odit placeat provident, quasi quis quod quos?';
                        
                        $why_item_icon = wp_get_attachment_image_src( get_theme_mod( "mje_diplomat_why_item_{$i}_img" ), array( 65, 65) );
                        $why_item["icon_{$i}"] = $why_item_icon ? $why_item_icon[0] : $skin_assets_path . "/img/why-icon-{$i}.png";

                        ?>
                        <li class="why-item-<?php echo $i ?> col-lg-4 col-md-4 col-sm-4 col-xs-12">
                            <img src="<?php echo $why_item["icon_{$i}"]; ?>" alt="">
                            <p class="title"><?php echo $why_item["title_{$i}"]; ?></p>
                            <p class="content"><?php echo $why_item["desc_{$i}"]; ?></p>
                        </li>
                        <?php
                    }
                ?>
            </ul>
        </div>
    </div>

    <!--SECTION JOBS CATEGORIES-->
    <div class="block-categories">
        <div class="container">
            <h6><?php echo get_theme_mod( 'mje_other_title_category' ) ? get_theme_mod( 'mje_other_title_category' ) : __( 'Job Categories', 'enginethemes' ); ?></h6>
            <ul class="clearfix">
            <?php
            $terms = get_terms( 'mjob_category', 'orderby=count&hide_empty=0' );
            if( !empty($terms) ):
                $i = 0;
                $img_url = get_template_directory_uri().'/assets/img/icon-1.png';
                foreach( $terms as $key=>$term){
                    $link = get_term_link($term->term_id, 'mjob_category');
                    $featured = get_term_meta($term->term_id, 'featured-tax', true);
                    $img = get_term_meta($term->term_id, 'mjob_category_image', true);
                    if( !empty($img) ){
                        $img_url = esc_url( wp_get_attachment_image_url( $img, 'mje_category_thumbnail' ) );
                    }
                    if( $featured && $i < 8 ):
                        $i++;
                        ?>
                        <li class="col-lg-3 col-md-3 col-sm-4 col-xs-12 clearfix wow fadeIn">
                            <div class="inner">
                                <a href="<?php echo $link; ?>">
                                    <span class="category-overlay"></span>
                                    <img src="<?php echo $img_url;?>" alt="<?php echo $term->name ?>">
                                    <h2 class="name-categories"><?php echo $term->name ?></h2>
                                </a>
                            </div>
                        </li>
                        <?php
                    endif;
                }
            endif; ?>
            </ul>
        </div>
    </div>

    <!--SECTION HOW-->
    <div class="block-intro-how">
        <div class="container">
            <h6><?php echo get_theme_mod( 'mje_diplomat_how_title' ) ? get_theme_mod( 'mje_diplomat_how_title' ) : __( 'How works with MjE', 'enginethemes' ); ?></h6>
            <div class="bg-customize line-text"></div>
            <div class="contents">
                <ul class="steps">
                    <li class="col-lg-4 col-md-4 col-sm-4 col-xs-12 wow fadeIn"><span class="number-steps">1</span></li>
                    <li class="col-lg-4 col-md-4 col-sm-4 col-xs-12 wow fadeIn"><span class="number-steps">2</span></li>
                    <li class="col-lg-4 col-md-4 col-sm-4 col-xs-12 wow fadeIn"><span class="number-steps">3</span></li>
                </ul>
                <div class="clearfix"></div>
                <ul class="text-information">
                    <?php
                    $default_how_title = array(
                        __( 'Enter your needs', 'enginethemes' ),
                        __( 'Select your favorite seller', 'enginethemes' ),
                        __( 'Get your stuffs done', 'enginethemes' )
                    )
                    ?>

                    <?php
                    for( $i = 1; $i <= 3; $i++ ) {
                        $how_item["title_{$i}"] = get_theme_mod( "mje_diplomat_how_item_{$i}_title" ) ? get_theme_mod( "mje_diplomat_how_item_{$i}_title" ) : $default_how_title[$i-1];

                        $how_item["desc_{$i}"] = get_theme_mod( "mje_diplomat_how_item_{$i}_desc" ) ? get_theme_mod( "mje_diplomat_how_item_{$i}_desc" ) : 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cum ducimus enim id minus mollitia numquam odit placeat provident, quasi quis quod quos?';

                        $how_item_icon = wp_get_attachment_image_src( get_theme_mod( "mje_diplomat_how_item_{$i}_img" ), array( 130, 130 ) );
                        $how_item["icon_{$i}"] = $how_item_icon ? $how_item_icon[0] : $skin_assets_path . "/img/how-icon-{$i}.png";

                        ?>
                        <li class="how-item-<?php echo $i ?> col-lg-4 col-md-4 col-sm-4 col-xs-12 wow fadeIn">
                            <div class="steps-line"></div>
                            <div class="icon">
                                <div class="hex">
                                   <img src="<?php echo $how_item["icon_{$i}"]; ?>" alt="">
                                </div>
                            </div>
                            <p class="title"><?php echo $how_item["title_{$i}"]; ?></p>
                            <p class="content"><?php echo $how_item["desc_{$i}"]; ?></p>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <!--SECTION SIGN UP FOOTER -->
    <div class="block-login wow fadeIn">
        <div class="background-opacity"></div>
        <div class="container inner">
            <?php if( is_user_logged_in() ) : ?>
                <div class="logged-in">
                    <?php
                        $heading_title = get_theme_mod( 'mje_diplomat_login_footer_heading_title' ) ? get_theme_mod( 'mje_diplomat_login_footer_heading_title' ) : __( 'Congrats! You are in!', 'enginethemes' );
                        $sub_title = get_theme_mod( 'mje_diplomat_login_footer_sub_title' ) ? get_theme_mod( 'mje_diplomat_login_footer_sub_title' ) : __( 'Let\'s begin to get stuffs done in MicrojobEngine', 'enginethemes' );
                        $button_text = get_theme_mod( 'mje_diplomat_login_footer_button_text' ) ? get_theme_mod( 'mje_diplomat_login_footer_button_text' ) : __( 'GO TO DASHBOARD', 'enginethemes' );
                    ?>
                    <p class="main-title"><?php echo $heading_title; ?></p>
                    <p class="sub-title"><?php echo $sub_title; ?></p>
                    <a href="<?php echo et_get_page_link( 'dashboard' ); ?>" class="btn-diplomat btn-link-site waves-effect waves-light"><span class="text"><?php echo $button_text; ?></span><i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
                </div>
            <?php else : ?>
                <div class="non-login">
                    <?php
                    $heading_title = get_theme_mod( 'mje_diplomat_non_login_footer_heading_title' ) ? get_theme_mod( 'mje_diplomat_non_login_footer_heading_title' ) : __( 'Join Now', 'enginethemes' );
                    $sub_title = get_theme_mod( 'mje_diplomat_non_login_footer_sub_title' ) ? get_theme_mod( 'mje_diplomat_non_login_footer_sub_title' ) : __( 'Thank you for showing an interest in MicrojobEngine', 'enginethemes' );
                    $button_text = get_theme_mod( 'mje_diplomat_non_login_footer_button_text' ) ? get_theme_mod( 'mje_diplomat_non_login_footer_button_text' ) : __( 'BE A MEMBER TODAY', 'enginethemes' );
                    ?>
                    <p class="main-title"><?php echo $heading_title; ?></p>
                    <p class="sub-title"><?php echo $sub_title; ?></p>
                    <a href="<?php echo et_get_page_link( 'sign-in' ) . '#register'; ?>" class="btn-diplomat btn-link-site waves-effect waves-light"><span class="text"><?php echo $button_text; ?></span><i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
get_footer(); ?>