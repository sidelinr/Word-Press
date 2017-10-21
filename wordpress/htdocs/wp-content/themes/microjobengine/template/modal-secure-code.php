<div class="modal fade" id="checkout_secure_code" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><img
                            src="<?php echo get_template_directory_uri() ?>/assets/img/icon-close.png" alt=""></span></button>
                <h4 class="modal-title" id="myModalLabelForgot"><?php _e('Secure Code', 'enginethemes'); ?></h4>
            </div>

            <div class="modal-body">
                <form class="checkout_secure_code_form secure-code et-form">
                    <div class="form-group clearfix value-payment">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-shield"></i></div>
                            <input type="password" name="secure_code" id="secure_code" placeholder="<?php _e('Secure code', 'enginethemes'); ?>">
                        </div>
                    </div>

                    <div class="form-group ml-35">
                        <span><?php _e("Don't have secure code or forgot it? ", 'enginethemes'); ?><a href="#" class="request-secure-code"><?php _e('Request here', 'enginethemes'); ?></a></span>
                    </div>

                    <input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo de_create_nonce('withdraw_action') ?>">
                    <div class="form-group ml-35">
                        <button class="<?php mje_button_classes( array( 'submit', 'waves-effect', 'waves-light' ) ); ?>"><?php _e('Pay now', 'enginethemes'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>