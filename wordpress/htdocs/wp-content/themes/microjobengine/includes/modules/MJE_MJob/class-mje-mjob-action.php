<?php
class MJE_MJob_Action extends MJE_Post_Action
{
    public static $instance;
    /**
     * get_instance method
     *
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * the constructor of this class
     *
     */
    public  function __construct($post_type = 'mjob_post'){
        parent::__construct($post_type);
        $this->add_ajax('ae-fetch-mjob_post', 'fetch_post');
        $this->add_ajax('ae-mjob_post-sync', 'sync');
        $this->add_filter('ae_convert_mjob_post', 'convert');
        $this->add_filter('ae_request_thumbnail_size', 'filterThumbnailSize');
        $this->add_ajax('mjob-get-mjob-infor', 'getMjobPost');
        $this->add_ajax('mjob-get-skill-list', 'getMjobTags');
        $this->add_ajax('mjob-get-breadcrumb-list', 'getMjobCats');
        $this->ruler = array(
            'post_title'=>'required',
            'post_content'=>'required',
            'time_delivery'=>'required',
            'et_budget'=>'required',
            //'et_carousels'=>'required'
        );
        $this->disable_plan = ae_get_option('disable_plan', false);
        $this->mail = MJE_Mailing::get_instance();
        $this->add_filter('ae_convert_user', 'mjob_convert_user');
    }
    /**
     * sync Post function
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function sync(){
        $request = $_POST;

        // Validate price
        // Use custom price if it's enable
        if('1' == ae_get_option('custom_price_mode')) {
            // Validate mjob price
            $min_price = absint(ae_get_option('mjob_min_price', 5));
            $max_price = absint(ae_get_option('mjob_max_price', 15));

            if(isset($request['ID']) && $request['et_budget'] != get_post_meta($request['ID'], 'et_budget', true)) {
                if(empty($request['et_budget'])) {
                    wp_send_json(array(
                        'success' => false,
                        'msg' => __('mJob price is required', 'enginethemes')
                    ));
                }

                $mjob_price = absint($request['et_budget']);
                if($mjob_price < $min_price || $mjob_price > $max_price) {
                    wp_send_json(array(
                        'success' => false,
                        'msg' => sprintf(__('mJob price must be between %s and %s', 'enginethemes'), mje_format_price($min_price), mje_format_price($max_price))
                    ));
                }
            }
        } elseif(!isset($request['et_budget'])) {
            $request['et_budget'] = absint(ae_get_option('mjob_price', 5));
        }

        if (!isset($request['rating_score'])) {
            $request['rating_score'] = 0;
        }
        $response = $this->validatePost($request);
        if (!$response['success']) {
            wp_send_json($response);
            exit;
        }
        $request = $response['data'];

        if( !isset($request['featured_image']) ){
            if( isset($request['et_carousels'] ) && !empty($request['et_carousels']) ){
                $request['featured_image'] = $request['et_carousels']['0'];
            }
            else{
                $response = array(
                    'success'=> false,
                    'msg'=> __('You have to upload at least one photo!', 'enginethemes')
                );
                wp_send_json($response);
                exit;
            }
        }
        else{
            if( isset($request['et_carousels'] ) && !empty($request['et_carousels']) ){
                if( !in_array($request['featured_image'], $request['et_carousels'])){
                    $request['featured_image'] = $request['et_carousels']['0'];
                }
            }
            else{
                $response = array(
                    'success'=> false,
                    'msg'=> __('You have to upload at least one photo!', 'enginethemes')
                );
                wp_send_json($response);
                exit;
            }
        }

        $request['et_total_sales'] = 0;
        $request['view_count'] = 0;

        $response = $this->sync_post($request);

        if (isset($response['data']) && !empty($response['data'])) {
            $result = $response['data'];
            if($result->opening_message) {
                $response['data']->opening_message = wpautop($result->opening_message);
                $response['data']->num_opening_message =  str_word_count($result->opening_message);
            }
            // Email notification to admin
            if($request['method'] == 'create') {
                unset($result->skill);
                if(($result->post_status == 'pending' || $result->post_status == 'publish') || $result->post_status == 'draft') {
                    $this->mail->notify_new_mjob_post($result->ID);
                }
            }
            // Email notification to author when post has changed
            if($request['method'] == 'update') {
                if(isset($request['reject_message'])) {
                    $rejectMsg = $request['reject_message'];
                } else {
                    $rejectMsg = '';
                }
                $this->mail->change_post_status($result->post_status, $request['post_status'], $result, $rejectMsg);

                /**
                 * Fire actions when mjob is updated
                 */
                if( $request['pause'] ) {
                    do_action( 'mje_paused_mjob', $result );
                }
                if( $request['archive'] ) {
                    do_action( 'mje_archived_mjob', $result );
                }
                if( $request['publish'] ) {
                    do_action( 'mje_approved_mjob', $result );
                }
                if( $request['post_status'] == 'reject' ) {
                    do_action( 'mje_rejected_mjob', $result );
                }
                if( $request['edit'] ) {
                    do_action( 'mje_edited_mjob', $result );
                }
                if ( $request['delete'] ) {
                    do_action( 'mje_deleted_mjob', $result );
                }
            }

            /**
             * check payment package and check free or use package to send redirect link
             */
            if (isset($request['et_payment_package'])) {

                // check seller use package or not
                $check = AE_Package::package_or_free($request['et_payment_package'], $result);

                // check use package or free to return url
                if ($check['success']) {
                    $result->redirect_url = $check['url'];
                }

                $result->response = $check;

                // check seller have reached limit free plan
                $check = AE_Package::limit_free_plan($request['et_payment_package']);
                if ($check['success']) {

                    // false user have reached maximum free plan
                    $response['success'] = false;
                    $response['msg'] = $check['msg'];
                    $response['data'] = $result;
                    // send response to client
                    wp_send_json($response);
                }
            }

            // check payment package


            /**
             * check disable plan and submit place to view details
             */
            if ($this->disable_plan && ( $request['method'] == 'create' || $request['renew'] == '1' ) ) {
                $result->redirect_url = $result->permalink;
                // disable plan, free to post place
                $response = array(
                    'success' => true,
                    'data' => $result,
                    'msg' => __("Successful submission.", 'enginethemes')
                );

                // send response
                wp_send_json($response);
            }
        }

        wp_send_json($response);
    }
    /**
     * convert post
     *
     * @param object $result
     * @return object $result after convert
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function convert($result){
        global $user_ID, $ae_post_factory;

        // Check if is search page
        $result->is_search = false;
        if(isset($_REQUEST['query']['is_search']) && $_REQUEST['query']['is_search'] == true) {
            $result->is_search = true;
        }

        $result->is_author = false;
        if( $result->post_author == $user_ID ){
            $result->is_author = true;
        }
        $result->is_admin = false;
        if( is_super_admin() ){
            $result->is_admin = true;
        }

        $result->page_template = '';
        if ( ! empty( $_REQUEST['query']['page_template'] ) ) {
            $result->page_template = $_REQUEST['query']['page_template'];
        }

        $result->author_name = get_the_author_meta('display_name', $result->post_author);
        $result->author_avatar = mje_avatar($result->post_author, 35);

        if( $result->post_status == 'publish' ){
            $result->mjob_status = __('Approved', 'enginethemes');
            $result->status_action = 'unapprove';
        } else if( $result->post_status == 'pending'){
            $result->mjob_status = __('Unapprove', 'enginethemes');
            $result->status_action = 'approve';
        }

        $result->edit_link = $result->permalink .'?action=edit';
        if( $result->post_status == 'pending' ){
            $result->edit_link = $result->permalink .'&action=edit';
        }

        $result->edit_link_html = self::showEditLink( $result );

        /**
         * Fire filter for mjob item class
         *
         * return string
         * @since 1.3.1
         * @author Tat Thien
         */
        $result->mjob_class = join( ' ', apply_filters( 'mje_mjob_item_class', array( 'mjob-item' ), $result ) );

        /* mjob status */
        switch($result->post_status){
            case 'publish':
                $result->status_text = __('Active', 'enginethemes');
                $result->status_class = 'active-color';
                break;
            case 'pending':
                $result->status_text = __('Pending', 'enginethemes');
                $result->status_class = 'pending-color';
                break;
            case 'archive':
                $result->status_text = __('Archived', 'enginethemes');
                $result->status_class = 'archive-color';
                break;
            case 'reject':
                $result->status_text = __('Unapprove', 'enginethemes');
                $result->status_class = 'reject-color';
                break;
            case 'pause':
                $result->status_text = __('Pause', 'enginethemes');
                $result->status_class = 'pause-color';
                break;
            case 'draft':
                $result->status_text = __('Draft', 'enginethemes');
                $result->status_class = 'draft-color';
                break;
            default:
                $result->status_text = __('Active', 'enginethemes');
                $result->status_class = 'active-color';
                break;

        }
        $result->mjob_status = '';
        $result->et_budget_text = mje_shorten_price($result->et_budget);
        $m_orig	= get_post_field( 'post_modified', $result->ID, 'raw' );
        $m_stamp= strtotime( $m_orig );
        $date_format = get_option('date_format');
        $result->modified_date = date_i18n( $date_format, $m_stamp );

        /* carousels */
        $children = get_children(array(
            'numberposts' => 15,
            'order' => 'ASC',
            'post_parent' => $result->ID,
            'post_type' => 'attachment'
        ));

        $result->et_carousels = array();
        $result->et_carousel_urls = array();
        foreach ($children as $key => $value) {
            $slider_img = wp_get_attachment_image_src($value->ID, 'mjob_detail_slider');
            $value->slider_img_url = $slider_img[0];

            $result->et_carousels[] = $key;
            $result->et_carousel_urls[] = $value;
        }

        /**
         * set post thumbnail in one of carousel if the post thumbnail does not exists
         */
        if (has_post_thumbnail($result->ID)) {
            $thumbnail_id = get_post_thumbnail_id($result->ID);
            if (!in_array($thumbnail_id, $result->et_carousels)) $result->et_carousels[] = $thumbnail_id;

            $mjob_slider = wp_get_attachment_image_src($thumbnail_id, "mjob_detail_slider");
            $result->mjob_slider_thumbnail = $mjob_slider[0];
        }
        /*
         * extras
         */
        $children = get_posts(array(
            'post_type'=>'mjob_extra',
            'showposts'=> 20,
            'post_parent'=>$result->ID
        ));
        $extra_obj = $ae_post_factory->get('mjob_extra');
        $result->mjob_extras = array();
        foreach ($children as $key => $value) {
            $value = $extra_obj->convert($value);
            $result->mjob_extras[] = $value;
        }

        // Get total review
        $result->mjob_total_reviews = mje_shorten_number(mje_get_total_reviews($result->ID));
        if( isset($result->tax_input['skill']) ){
            $result->skill = $result->tax_input['skill'];
        }

        $result->time_delivery = (int)$result->time_delivery;
        return $result;
    }

    /**
     * Add size when request thumbnail
     * @param array $thumbnail_size
     * @return  array $thumbnail_size
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author Tat Thien
     */
    public function filterThumbnailSize($thumbnail_size) {
        $thumbnail_size = wp_parse_args($thumbnail_size, array('mjob_detail_slider', 'medium_post_thumbnail'));
        return $thumbnail_size;
    }

    /**
     * validate data
     *
     * @param array $data
     * @return array $result
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function validatePost($data){
        global $user_ID;
        $result = array(
            'success'=> true,
            'msg'=> __('Success!', 'enginethemes'),
            'data'=> $data
        );

        /**
         * check payment package is valid or not
         * set up featured if this package is featured
         */
        if (isset($data['et_payment_package']) && !empty($data['et_payment_package'])) {

            /**
             * check package plan exist or not
             */
            global $ae_post_factory;
            $package = $ae_post_factory->get('pack');
            $plan = $package->get($data['et_payment_package']);
            if (!$plan){
              $result = array(
                  'success'=>false,
                  'msg'=> __("You have selected an invalid plan. Please choose another one!", 'enginethemes'),
                  'data'=> $data
              );
            }

            /**
             * if user can not edit others posts the et_featured will no be unset and check,
             * this situation should happen when user edit/add post in backend.
             * Force to set featured post
             */
            if (!isset($data['et_featured']) || !$data['et_featured']) {
                $data['et_featured'] = 0;
                if (isset($plan->et_featured) && $plan->et_featured) {
                    $data['et_featured'] = 1;
                }
            }
            $result['data'] = $data;
        }
        return $result;
    }
    /**
     * Override filter_query_args for action fetch_post.
     *
     */
    public function filter_query_args($query_args)
    {
        global $user_ID;
        $query = $_REQUEST['query'];
        // list featured profile
        if (isset($query['meta_key'])) {
            $query_args['meta_key'] = $query['meta_key'];
            if (isset($query['meta_value'])) {
                $query_args['meta_value'] = $query['meta_value'];
            }
        }

        //filter project by project category and skill
        if (isset($query['mjob_category']) && !empty($query['mjob_category'])) {
            if(is_numeric($query['mjob_category'])) {
                $tax_field = 'term_id';
            } else {
                $tax_field = 'slug';
            }

            // Filter by skill and mjob category
            if(isset($query['skill']) && !empty($query['skill'])) {
                $skill = $query['skill'];
                $query_args['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'skill',
                        'field' => 'term_id',
                        'terms' => $skill
                    ),
                    array(
                        'taxonomy' => 'mjob_category',
                        'field' => $tax_field,
                        'terms' => array($query['mjob_category'])
                    )
                );
            } else { // Filter by mjob category only
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'mjob_category',
                        'field' => $tax_field,
                        'terms' => array($query['mjob_category'])
                    )
                );
            }
        } else if(isset($query['skill']) && !empty($query['skill'])) {
            // Filter by skill only
            $skill = $query['skill'];
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'skill',
                    'field' => 'term_id',
                    'terms' => $skill
                ),
            );
        }

        // project posted from query date
        if (isset($query['date'])) {
            $date = $query['date'];
            $day = date('d', strtotime($date));
            $mon = date('m', strtotime($date));
            $year = date('Y', strtotime($date));
            $query_args['date_query'][] = array(
                'year' => $year,
                'month' => $mon,
                'day' => $day,
                'inclusive' => true
            );
        }
        /**
         * add query when archive project type
         */

        if (current_user_can('manage_options') && isset($query['is_archive_mjob_post']) && $query['is_archive_mjob_post'] == TRUE) {
            $query_args['post_status'] = array(
                'pending',
                'publish'
            );
        }
        // query arg for filter page default
        if (isset($query['orderby'])) {
            $orderby = $query['orderby'];
            switch ($orderby) {
                case 'et_featured':
                    $query_args['meta_key'] = $orderby;
                    $query_args['orderby'] = 'meta_value_num date';
                    $query_args['meta_query'] = array(
                        'relation' => 'OR',
                        array(
                            //check to see if et_featured has been filled out
                            'key' => $orderby,
                            'compare' => 'IN',
                            'value' => array(
                                0,
                                1
                            )
                        ) ,
                        array(
                            //if no et_featured has been added show these posts too
                            'key' => $orderby,
                            'value' => 0,
                            'compare' => 'NOT EXISTS'
                        )
                    );
                    break;

                case 'et_budget':
                    $query_args['meta_key'] = 'et_budget';
                    $query_args['orderby'] = 'meta_value_num date';
                    $query_args['order'] = $query['order'];
                    break;

                case 'rating_score':
                    $query_args['meta_key'] = $orderby;
                    $query_args['orderby'] = 'meta_value_num date';
                    break;
                case 'date':
                    $query_args['orderby'] = 'date';
                    $query_args['order'] = $query['order'];
                    break;
                case 'view_count':
                    $query_args['meta_key'] = $orderby;
                    $query_args['orderby'] = 'meta_value_num date';
                    break;
                case 'et_total_sales':
                    $query_args['meta_key'] = $orderby;
                    $query_args['orderby'] = 'meta_value_num date';
                    break;
                default:
                    add_filter('posts_orderby', array(
                        'ET_Microjobengine',
                        'order_by_post_pending'
                    ) , 2, 12);
                    break;
            }
        }

        /*
         * set post status when query in page profile or author.php
        */
        $query_args['post_status'] = array(
            'unpause',
            'publish'
        );

        if (isset($query['is_author']) && $query['is_author']) {
            if (!isset($query['post_status'])) {
                $query_args['post_status'] = array(
                    'close',
                    'complete',
                    'publish'
                );
            }
            $query_args['post_status'] = $query['post_status'];
        }
        if ((isset($query['post_status']) && $query['post_status'] == 'publish') || current_user_can('manage_options')){
            $query_args['post_status'] = $query['post_status'];
        }
        if (isset($query['post_status']) && isset($query['author']) && $query['post_status'] && $user_ID == $query['author']) {
            $query_args['post_status'] = $query['post_status'];
        }

        // Post status is active will be convert to publish and unpause
        if(isset($query['post_status']) && $query['post_status'] == 'active') {
            $query_args['post_status'] = array(
                'publish',
                'unpause'
            );
        }

        /**
         * Add filter for query args
         *
         * @param array $query_args
         */
        return apply_filters( 'mje_mjob_filter_query_args', $query_args );
    }
    /**
     * convert user
     *
     * @param object $user
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function mjob_convert_user($user){
        $user->mjobAjaxNonce = de_create_nonce('ae-mjob_post-sync');
        return $user;
    }
    /**
     * get mjob post
     *
     * @param integer $mjob_id
     * @return object $mjob_post / false
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function get_mjob($mjob_id=''){
        if( empty($mjob_id) ){
            return false;
        }
        global $ae_post_factory;
        $mjob_obj = $ae_post_factory->get('mjob_post');
        $post = get_post($mjob_id);
        if( $post ){
            return $mjob_obj->convert($post);
        }
        return false;

    }
    /**
     * get mjob
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function getMjobPost(){
        global $user_ID;
        $request = $_REQUEST;
        $response = array(
            'success'=> false,
            'msg'=> __('failed', 'enginethemes')
        );
        if( isset($request['ID']) && !empty($request['ID']) ){
            global $ae_post_factory;
            $mjob_obj = $ae_post_factory->get('mjob_post');
            $post = get_post($request['ID']);
            if( $post && ($post->post_author == $user_ID || is_super_admin())){
                $mjob = $mjob_obj->convert($post);
                $response = array(
                    'success'=> true,
                    'msg'=> __('Success!', 'enginethemes'),
                    'data'=>$mjob );
            }
        }
        wp_send_json($response);
    }
    /**
     * get mjob tags
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function getMjobTags(){
        $request = $_REQUEST;
        $result = '';
        if(isset($request['ID']) ){
            $post = get_post($request['ID']);
            if( $post ) {
                $result .= get_the_taxonomy_list('skill', $post);
            }
        }
        wp_send_json($result);
    }

    /**
     * get breadcum tags
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function getMjobCats(){
        $request = $_REQUEST;
        $result = '';
        $breadcrumb = '';
        if(isset($request['term_id']) ){
            $cat = get_term_by('ID', $request['term_id'], 'mjob_category');
            $breadcrumb = '<span class="mjob-breadcrumb"><a class="parent" href="'. get_term_link($cat) .'">'. $cat->name .'</a></span>';
            $parent = $cat->parent;
            if( $parent != 0 ){
                $parent = get_term_by('ID', $parent, 'mjob_category');
                $breadcrumb = '<span class="mjob-breadcrumb"><a class="parent" href="'. get_term_link($parent) .'">'. $parent->name .'</a> <i class="fa fa-angle-right"></i> <span><a class="child" href="'. get_term_link($cat) .'">'. $cat->name .'</a></span></span>';
            }
        }
        wp_send_json($breadcrumb);
    }

    /**
     * Show edit link
     *
     * @param object $post
     * @return string
     * @since MicrojobEngine 1.1.4
     * @author Tat Thien
     */
    public static function showEditLink( $post ) {
        global $user_ID;
        if( $post->post_author == $user_ID && ae_get_option( 'edit_mjob') ) :
            $edit_link = $post->permalink . '?action=edit';
            return '<li><a href="' . $edit_link . '" target="_blank"  data-toggle="tooltip" data-placement="top" title="' . __( 'Edit', 'enginethemes' ) . '" class=""><i class="fa fa-pencil"></i></a></li>';
        endif;
    }

    /**
     * Check if user can edit a mjob
     *
     * @param object $post
     * @return boolean $is_edit
     * @since MicrojobEngine 1.1.4
     * @author Tat Thien
     */
    public static function checkEdit( $post ) {
        global $user_ID;
        $is_edit = false;
        if( is_super_admin() ) {
            $is_edit = true;
        } else if( $user_ID == $post->post_author ) {
            if( ae_get_option( 'edit_mjob' ) ) {
                $is_edit = true;
            } elseif ( !in_array( $post->post_status, array('publish','pause','active','archive','unpause') ) ) {
                $is_edit = true;
            }
        }

        return $is_edit;
    }
}
new MJE_MJob_Action();