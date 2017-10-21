<?php if( ( $mjob_order->post_status == 'disputing' && is_super_admin() ) ):  ?>
<div class="decided mjob-admin-dispute-form">
    <p class="text"><?php _e("Admin's decided", 'enginethemes') ?></p>
    <form class="et-form">
        <div class="form-group">
            <label class="text-result-choose"><?php _e('Select a winner to the dispute:', 'enginethemes'); ?></label>
            <div class="radio">
                <label>
                    <input type="radio" name="winner" id="winner" value="<?php echo $mjob_order->post_author;  ?>" checked>
                    <span><?php echo $mjob_order->author_name; ?></span>
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="winner" id="winner" value="<?php echo $mjob_order->mjob_author ?>">
                    <span><?php echo $mjob_order->mjob_author_name; ?></span>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label for="post_content"><?php _e( 'Your explanation', 'enginethemes' ); ?></label>
            <textarea name="post_content" id="post_content" rows="10"></textarea>
        </div>
        <div class="form-group">
            <button class="<?php mje_button_classes( array() ); ?>"><?php _e('Submit', 'enginethemes'); ?></button>
            <input name="to_user" type="hidden" value="<?php echo $mjob_order->to_user; ?>">
            <input type="hidden" class="input-item post-service_nonce" name="_wpnonce" value="<?php echo de_create_nonce('ae-mjob_post-sync');?>" />
        </div>
    </form>
</div>
<?php endif; ?>