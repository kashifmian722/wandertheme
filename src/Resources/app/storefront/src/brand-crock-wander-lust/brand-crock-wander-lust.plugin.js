import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class BrandCrockWanderlust extends Plugin {
    /**
	 *  Plugin initialization
	 *
	 * @public
	 */
    init() {
        const that = this;
        that._client = new HttpClient(window.accessKey, window.contextToken);

        let productPopupInterval = false;
        let disableBcWlPopup = false;
        let disableBcWlPromotionPopup = false;
        let adRevealPopupInterval = false;

        $(document).ready(function () {
            $('#preloader').css('display', 'none');
            $('.bc-wl-slider').addClass('bc_slider_hide');
            $('.bc-promotion-list').each(function () {
                $('.bc-wl-slider:first', this).addClass('bc_slider_show fade-in');
            });
            that._client.get(`${window.router['wanderlust.get.wlsession']}`, (response) => {
                const res = JSON.parse(response);
                if(res.disableBcWlPopup == 1) {
                    disableBcWlPopup = true;
                }
                if(res.disableBcWlPromotionPopup == 1) {
                    disableBcWlPromotionPopup = true;
                }
                if (hasClass(document.body, 'is-act-home') && disableBcWlPromotionPopup == false && $('.product_promotion_block').length) {
					var bcPromotionPopup = $('#bcPromotionPopup').val() > 1000 ? $('#bcPromotionPopup').val() : 10000;
                    $('.product_promotion_block').each(function () {
                        $('.product_promotion_popup:first', this).addClass('popup_show');
                    });
                    if($('div.product_promotion_popup').length != 1) {
						productPopupInterval = setInterval(function () {
							let active = 1;
							const current = $('div.product_promotion_popup:visible').data('popupid');
							if (current < 4) {
								active = parseInt(current, 10) + 1;
							} else if(current == 4) {
								let showFirstChild = $('.product_promotion_popup:nth-child(1)').data('popupid');
								active = showFirstChild;
							}
							$('.product_promotion_popup[data-popupid="' + current + '"]').removeClass('fade-in');
							$('.product_promotion_popup[data-popupid="' + current + '"]').addClass('popup_none');
							$('.product_promotion_popup[data-popupid="' + active + '"]').addClass('fade-in');
							$('.product_promotion_popup[data-popupid="' + active + '"]').removeClass('popup_none');
						}, bcPromotionPopup);
					} else {
						$('.product_promotion_popup:nth-child(1)').addClass('popup_show');
					}
                }
				const isRevealEnabled = $('#isReveal').val();
                if ( hasClass(document.body, 'is-act-home') && disableBcWlPopup == false && isRevealEnabled == 1) {
					var revealTime = $('#revealtimer').val() > 1000 ? $('#revealtimer').val() : 10000;
                    adRevealPopupInterval = setTimeout(function() {
						$('#reveal_popup').modal('show');
                    },revealTime);
                }
            });
            setInterval(function () {
                let active = 1;
                const current = $('li.bc-wl-slider:visible').data('slideritem');
                if (current < 4) {
                    active = parseInt(current, 10) + 1;
                }
                $('.bc-wl-slider[data-slideritem="' + current + '"]').removeClass('fade-in');
                $('.bc-wl-slider[data-slideritem="' + current + '"]').addClass('fade-out');
                $('.bc-wl-slider[data-slideritem="' + active + '"]').addClass('fade-in');
                $('.bc-wl-slider[data-slideritem="' + active + '"]').removeClass('fade-out');
            }, 4000);
            if(hasClass(document.body, 'is-act-home')) {
                if($('.cms-section.pos-0').length && ( $('.cms-section.pos-0.bc-wl-top-banner').length ) ) {
                    $('header').addClass('bcwl_absoluteheader');
                } else {
                     $('header').addClass('bcwl_relativeheader');
                }
                $('.cms-section').each(function() {
                    if( ( $(this).find('.cms-block-text-on-image').length || $(this).find('.cms-block-image-cover').length || $(this).find('.cms-block-image-text-cover').length ) && !$(this).hasClass('pos-0')) {
                        $(this).css({ 'padding' : '40px 20px 40px 20px'});
                    }
                });
                var bcwl_banner_overlay = $('#bcwl_banner_overlay').val();
                if( bcwl_banner_overlay == 1 && ( $('.cms-section.bc-wl-promotion-banner').length || $('.cms-section.bc-wl-top-banner').length ) && ( $('.bc-wl-promotion-banner').find('.cms-block-text-on-image').length || $('.bc-wl-top-banner').find('.cms-block-text-on-image').length ) ) {
					var bcwl_overlay_brightness = $('#bcwl_banner_overlay_brightness').val();
					if($('.bc-wl-top-banner').length) {
						if($('.bc-wl-top-banner').find('img').length) {
							$('.bc-wl-top-banner img').each(function( index ) {
								$(this).css('filter', 'brightness(' + bcwl_overlay_brightness + ')');
							});
						} else {
							$('.bc-wl-top-banner .cms-block.bg-image.cms-block-text-on-image').each(function( index ) {
								$(this).addClass('overlay_banner_image');
								$(this).attr('data-overlay-brightness', bcwl_overlay_brightness);
							});
						}
					}
					if($('.bc-wl-promotion-banner').length) {
						if($('.bc-wl-promotion-banner').find('img').length) {
							$('.bc-wl-promotion-banner img').each(function( index ) {
								$(this).css('filter', 'brightness(' + bcwl_overlay_brightness + ')');
							});
						} else {
							$('.bc-wl-promotion-banner .cms-block.bg-image.cms-block-text-on-image').each(function( index ) {
								$(this).addClass('overlay_banner_image');
								$(this).attr('data-overlay-brightness', bcwl_overlay_brightness);
							});
						}
					}
				}
            }
            var maxLength = 150;
            $('.product-desc').each(function(){
                var myStr = $(this).text();
                if($.trim(myStr).length > maxLength){
                    var newStr = myStr.substring(0, maxLength);
                    var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
                    $(this).empty().html(newStr);
                    $(this).append(' <a href="javascript:void(0);" rel="noopener" title="read more" class="read-more">'+ $('#readMoreTitle').val()+'</a>');
                    $(this).append('<span class="more-info">' + removedStr + '</span>');
                }
            });
            $('.read-more').click(function(){
                $(this).siblings('.more-info').contents().unwrap();
                $(this).remove();
            });
        });
		$(document).on('click', '.btn-detail-view', function() {
			$(this).closest('.product-info').children('.bc-wl-view-more').addClass('product-overlay-show');
		});
		$(document).on('click', '.product-overlay-close', function() {
			$(this).parent().removeClass('product-overlay-show');
		});
        let menu_id = '';
        $('.main-navigation-link').hover(
            function () {
                menu_id = $(this).attr('data-flyout-menu-trigger');
                if (menu_id != undefined) {
                    $('.navigation-flyout[data-flyout-menu-id="' + menu_id + '"]').addClass('show_sub_menu');
                }
            },
            function () {
                if (menu_id != undefined) {
                    $('.navigation-flyout[data-flyout-menu-id="' + menu_id + '"]').removeClass('show_sub_menu');
                }
            }
		);
        $('.close-button').on('click', function () {
            that._client.get(`${window.router['wanderlust.set.popup']}?type=revealpopup`, (response) => {
                return response;
            });
            clearTimeout(adRevealPopupInterval);            
            $('#reveal_popup').modal('hide');
            adRevealPopupInterval = false;
        });
        $('.popup_close_btn').on('click', function () {
            that._client.get(`${window.router['wanderlust.set.promotionpopup']}?type=promotionpopup`, (response) => {
                return response;
            });
            $('.product_promotion_popup').addClass('popup_none');
            $('.product_promotion_popup').removeClass('fade-in');
            clearInterval(productPopupInterval);
            productPopupInterval = false;
        });
        window.addEventListener('scroll', function () {
            let win_scroll = '';
            if (window.scrollY != undefined) {
                win_scroll = window.scrollY;
            } else {
                win_scroll = document.documentElement.scrollTop;
            }
            const fixed_nav = $('#fixed_nav').val();
            if (win_scroll >= 400 && fixed_nav != undefined && fixed_nav == 1) {
                $('.header-main').addClass('stickynav');
            } else {
                $('.header-main').removeClass('stickynav');
            }
        });
        function hasClass(element, cls) {
            return element.classList.contains(cls);
        }
        $('.gallery-slider-thumbnails-item-inner').mouseover(function () {
            $(this).click();
        });
        $('#bc_wl_custom_slide').click(function () {
			$('.bc_wl_backdrop').addClass('show');
            $('.bc_wl_listing_side_bar').addClass('bc_modal_is_open');
            $('body').css('overflow', 'hidden');
        });
        $('#bc_wl_slide_close').click(function () {
            $('.bc_wl_listing_side_bar').removeClass('bc_modal_is_open');
            $('.bc_wl_backdrop').removeClass('show');
            $('body').css('overflow', 'auto');
        });
        $('.bc_wl_backdrop').click(function (e) {
            if (!$(e.target).closest('.bc_modal_is_left').length) {
                $('.bc_wl_listing_side_bar').removeClass('bc_modal_is_open');
                $('.bc_wl_backdrop').removeClass('show');
                $('body').css('overflow', 'auto');
            }
        });
		if($('#bc_wl_horizontal_btn').length) {
			$(document).on('click', '#bc_wl_horizontal_btn', function () {
				if ($('#bc_wl_horizontal_filter').css('visibility') === 'hidden') {
					$('#bc_wl_horizontal_filter').addClass('bc_wl_hori_filter_show');
				} else if ($('#bc_wl_horizontal_filter').css('visibility') === 'visible') {
					$('#bc_wl_horizontal_filter').removeClass('bc_wl_hori_filter_show');
				}
			});
		}
    }
}
