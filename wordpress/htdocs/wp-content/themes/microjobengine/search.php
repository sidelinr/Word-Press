<?php
global $wp_query, $ae_post_factory, $post;
$post_object = $ae_post_factory->get('mjob_post');
get_header();
?>
	<div id="content">
		<?php get_template_part('template/content', 'page');?>
		<div class="block-page mjob-container-control search-result">
			<div class="container">
				<h2 class="block-title">
					<p class="block-title-text" data-prefix="<?php _e('in', 'enginethemes'); ?>">
						<?php
						$term_id = (isset($_GET['mjob_category']) && !empty($_GET['mjob_category'])) ? $_GET['mjob_category'] : '';
						$term = get_term($term_id);						
						// Get search result
						$search_result = $wp_query->found_posts;

						if($search_result == 1) {
							printf(__('<span class="search-result-count">%s</span> <span class="search-text-result">MJOB AVAILABLE', 'enginethemes'), $search_result);
						} else {
							printf(__('<span class="search-result-count">%s</span> <span class="search-text-result">MJOBS AVAILABLE', 'enginethemes'), $search_result);
						}

						?>
					</p>
					<div class="visible-lg visible-md">
						<?php get_template_part('template/sort', 'template'); ?>
					</div>
					<div class="show-filter-wrap hidden-lg hidden-md">
						<a class="filter-open-btn" href=""><?php _e('FILTER MJOB', 'enginethemes'); ?> <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
					</div>
				</h2>
				<div class="row search-content">
					<div class="col-lg-3 col-md-3 col-sm-12 col-sx-12">
						<div class="mje-col-left-wrap">
							<div class="mje-col-left">
								<div class="header-filter-wrap hidden-lg hidden-md">
									<a class="filter-close-btn" href=""><i class="fa fa-chevron-left" aria-hidden="true"></i><?php _e('BACK', 'enginethemes'); ?></a>
								</div>
								 <div class="hidden-lg hidden-md">
								 	<a class="clear-filter-btn" href= "<?php echo get_site_url() . '/?s'?> "><?php _e('CLEAR ALL FILTER', 'enginethemes'); ?></a>
								 </div>
								<div class="hidden-lg hidden-md">
									<?php get_template_part('template/sort', 'template'); ?>
								</div>
								<div class="menu-left">
									<p class="title-menu"><?php _e('Categories', 'enginethemes'); ?></p>
									<?php
										mje_show_filter_categories( 'mjob_category', array('parent' => 0), $term_id);
									?>
								</div>
								<div class="filter-tags">
									<p  class="title-menu"><?php _e('Tags', 'enginethemes'); ?></p>
									<?php
										$skill = array();
					                    if( !empty($_GET['skill_ids'])) {
					                        	$skill = explode(',', $_GET['skill_ids'] );	
					                    }
										//mje_show_filter_tags(array('skill'), array('hide_empty' => false));
										echo '<div class="tags et-form">';
										ae_tax_dropdown( 'skill' , array(
					                            'attr' => 'multiple data-placeholder="'.__("Filter by tag", 'enginethemes').'"',
					                            'class' => 'multi-tax-item is-chosen',
					                            'hide_empty' => false,
					                            'hierarchical' => true ,
					                            'selected' => $skill,
					                            'show_option_all' => false,			                           
					                    ));
					                    echo "</div>";
									?>
								</div>
								<div class="advanced-filters et-form">
									<p  class="title-menu"><?php _e('FILTER', 'enginethemes'); ?></p>

									<?php do_action( 'mje_template_search_advance_before' ); ?>

									<div class="tags  filter-language advanced-filters-item">
										<p class="filter-title"><?php _e('Language', 'enginethemes'); ?></p>			        
					                    <div class="choose-language">
					                        <?php
					                        $language_ids = array();
					                        if( !empty($_GET['language_ids'])) {
					                        	$language_ids = explode(',', $_GET['language_ids'] );	
					                        }				                        					                                            
					                        ae_tax_dropdown( 'language' , array(
					                            'attr' => 'multiple data-placeholder="'.__("Choose the language(s)", 'enginethemes').'"',
					                            'class' => 'multi-tax-item is-chosen',
					                            'hide_empty' => false,
					                            'hierarchical' => true ,
					                            'id' => 'language' ,
					                            'selected' => $language_ids,
					                            'show_option_all' => false,			                           
					                        ));
					                        ?>
					                    </div>
									</div>
									<?php 
									 $min = isset($_GET['price_min']) ? $_GET['price_min'] : '';
									 $max = isset($_GET['price_max']) ? $_GET['price_max'] : '';
									?>
									<div class="filter-price-mjob advanced-filters-item">
										<p class="filter-title"><?php _e('Price', 'enginethemes'); ?></p>
			                            <input  class="filter-budget-min" type="text" name="min_budget" value="<?php echo $min ?>" >
			                            <span>-</span>
			                            <input class="filter-budget-max" type="text" name="max_budget" value="<?php echo $max ?>" >
										<button type="button" class="btn filter-price"><?php _e('GO', 'enginethemes'); ?></button>
									</div>
									<?php do_action( 'mje_template_search_advance_after' ); ?>
								</div><!-- end .advanced-filters -->
							</div>
							
						</div>
					</div>
					<div class="col-lg-9 col-md-9 col-sm-12 col-sx-12">
						<div class="block-items no-margin mjob-list-container">
							<?php
							get_template_part('template/list', 'mjobs-search');							
							$filter = apply_filters( 'mje_mjob_param_filter_query', $_GET );
                            $wp_query->query = array_merge( $wp_query->query, $filter);

							echo '<div class="paginations-wrapper">';
							ae_pagination($wp_query, get_query_var('paged'));
							echo '</div>';
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
get_footer();
?>