/**
 * Created by Jack Bui on 1/21/2016.
 */
(function($, Models, Collections, Views) {
    $(document).ready(function () {
        Views.Order = Backbone.View.extend({
            el: '.mjob-order-page',
            events: {
                'click .mjob-btn-checkout': 'checkOut',
                'click .select-payment': 'selectPayment',
            },
            initialize: function (options) {
                AE.pubsub.on('ae:form:submit:success', this.afterValidateCreditCheckout, this);
                AE.pubsub.on('mje:update:checkout:total', this.updateTotal, this);
                AE.pubsub.on('mje:update:checkout:product:data', this.updateProductData, this);

                if (typeof this.extraCollection == 'undefined') {
                    // Get extra data
                    if ($('.extra_postdata').length > 0) {
                        var extra = JSON.parse($('.extra_postdata').html());
                        this.extraCollection = new Collections.Extras(extra);
                    } else {
                        this.extraCollection = new Collections.Extras();
                    }
                }

                this.blockUi = new Views.BlockUi();

                this.setupCheckout();
            },
            afterValidateCreditCheckout: function(result, resp, jqXHR, type) {
                var view = this;
                if(type == 'validate-credit-checkout') {
                    if(resp.success == true) {
                        // Process payment
                        view.checkoutModel.set('p_payment', 'credit');
                        view.productData.payment_type = 'credit';
                        view.saveOrder();
                    }
                }
            },
            checkOut: function(e){
                var view = this;

                $('html, body').animate({
                    scrollTop: $("html, body").offset().top
                }, 1000);

                $('.mjob-order-info').hide();
                $('#checkout-step2').fadeIn(500);
                $(".items-chosen").hide();
                $(".mjob-order-page").addClass("continue");

                e.preventDefault();

                // Add trigger after user click on Checkout button
                AE.pubsub.trigger('mje:after:setup:checkout', view.checkoutModel);
            },
            selectPayment: function(e){
                e.preventDefault();
                var $target = $(e.currentTarget),
                    paymentType = $target.attr('data-type'),
                    view = this;

                this.blockUi.block($target.parents('ul.list-price'));

                // Update payment gateway
                view.checkoutModel.set('p_payment', paymentType);
                view.productData.payment_type = paymentType;

                view.saveOrder();
            },
            setupCheckout: function(){
                var view = this;
                if( typeof view.checkoutModel === 'undefined' ){
                    if( $('#mje-checkout-info').length > 0 ) {
                        view.productData = JSON.parse($('#mje-checkout-info').html());
                        view.checkoutModel = new Models.Order();
                        view.checkoutModel.set('p_data', view.productData);
                        view.checkoutModel.set('p_type', view.productData.post_type);
                        view.checkoutModel.set('p_total', view.productData.total);
                        view.checkoutModel.set('p_nonce', view.$el.find('#_wpnonce').val());
                    }
                }
            },
            updateTotal: function(amount) {
                var view = this;
                var total = parseFloat( view.checkoutModel.get('p_total') );
                total += parseFloat( amount );
                view.checkoutModel.set('p_total', total);
                view.productData.total = total;
                view.$el.find('.total-price').html(AE.App.mJobPriceFormat(total));
            },
            updateProductData: function(data, key) {
                var view = this;
                var pData = view.checkoutModel.get('p_data');
                pData[key] = data;
            },
            saveOrder: function(){
                var view = this;
                view.checkoutModel.save( '', '', {
                    beforeSend: function () {
                    },
                    success: function ( result, res, jqXHR ) {
                        if (res.success && res.data.ACK) {
                            window.location.href = res.data.url;
                        } else {
                            AE.pubsub.trigger('ae:notification', {
                                msg: res.msg,
                                notice_type: 'error'
                            });
                        }
                    }
                } );
            },
        });
        new Views.Order();

        var responsiveMjobItem = function() {
          if($('body').outerWidth() < 992) {
            $('.mjob-order-page').find('.mjob-list').addClass('mjob-list--horizontal');
          } else {
            $('.mjob-order-page').find('.mjob-list').removeClass('mjob-list--horizontal');
          }
        }
        responsiveMjobItem();
        $(window).resize(function() {
            responsiveMjobItem();
        })
    });
})(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);
