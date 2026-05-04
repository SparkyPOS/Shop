<style>
    #cart_add_modal.cart-added-clean-modal .modal-dialog {
        max-width: 430px !important;
        width: calc(100% - 30px);
        margin-left: auto;
        margin-right: auto;
    }

    #cart_add_modal.cart-added-clean-modal .modal-content {
        border: 0 !important;
        border-radius: 0 !important;
        background: #ffffff !important;
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.22) !important;
        overflow: visible !important;
    }

    #cart_add_modal.cart-added-clean-modal .modal-body {
        padding: 0 !important;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-panel {
        position: relative;
        padding: 34px 34px 36px;
        background: #ffffff;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 34px;
        height: 34px;
        border: 0 !important;
        background: transparent !important;
        color: #111827;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        z-index: 3;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-close i {
        font-size: 15px;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-header {
        text-align: center;
        padding: 0 28px;
        margin-bottom: 26px;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-check {
        width: 34px;
        height: 34px;
        margin: 0 auto 10px;
        color: #4cb473;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-title {
        margin: 0;
        color: #101828;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-message {
        margin: 8px 0 0;
        color: #667085;
        font-size: 14px;
        line-height: 1.45;
        font-weight: 400;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-product {
        margin-bottom: 28px;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-product-link {
        display: grid !important;
        grid-template-columns: 128px minmax(0, 1fr);
        gap: 22px;
        align-items: center;
        color: inherit !important;
        text-decoration: none !important;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-product-link:hover {
        color: inherit !important;
        text-decoration: none !important;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-thumb {
        width: 128px;
        height: 168px;
        background: #ffffff;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-thumb img {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        display: block !important;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-info {
        min-width: 0;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-name {
        margin: 0 0 10px;
        color: #101828;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-vendor {
        margin: 0 0 8px;
        color: #667085;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.35;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-vendor strong {
        color: #0d6efd;
        font-weight: 700;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-price {
        margin: 0;
        color: #101828;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.2;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-view-cart {
        width: 100%;
        min-height: 54px;
        background: #081225;
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        text-transform: uppercase;
        text-decoration: none !important;
        border: 0;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    #cart_add_modal.cart-added-clean-modal .cart-added-view-cart:hover {
        color: #ffffff !important;
        background: #101828;
        text-decoration: none !important;
    }

    @media (max-width: 575.98px) {
        #cart_add_modal.cart-added-clean-modal .modal-dialog {
            width: calc(100% - 24px);
            max-width: calc(100% - 24px) !important;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-panel {
            padding: 32px 20px 24px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-close {
            top: 12px;
            right: 12px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-header {
            padding: 0 30px;
            margin-bottom: 22px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-title {
            font-size: 20px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-message {
            font-size: 13px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-product-link {
            grid-template-columns: 112px minmax(0, 1fr);
            gap: 16px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-thumb {
            width: 112px;
            height: 150px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-name {
            font-size: 16px;
            margin-bottom: 8px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-vendor {
            font-size: 13px;
            margin-bottom: 7px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-price {
            font-size: 15px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-view-cart {
            min-height: 50px;
        }
    }

    @media (max-width: 374.98px) {
        #cart_add_modal.cart-added-clean-modal .cart-added-product-link {
            grid-template-columns: 94px minmax(0, 1fr);
            gap: 13px;
        }

        #cart_add_modal.cart-added-clean-modal .cart-added-thumb {
            width: 94px;
            height: 132px;
        }
    }
</style>

<div
    class="modal fade theme_modal2 cart-added-clean-modal"
    id="cart_add_modal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="cart_success_modal_title"
    aria-hidden="true"
>
    <div class="modal-dialog max_width_430 modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">

                <div class="cart-added-panel">

                    <button
                        type="button"
                        class="cart-added-close"
                        data-bs-dismiss="modal"
                        data-dismiss="modal"
                        aria-label="{{ __('common.close') }}"
                    >
                        <i class="ti-close"></i>
                    </button>

                    <div class="cart-added-header">
                        <div class="cart-added-check" aria-hidden="true">
                            <svg width="30" height="30" viewBox="0 0 30 30">
                                <g transform="translate(7.118 3.77)">
                                    <path
                                        d="M143.592,64.66a1.131,1.131,0,0,0-1.6,0L128.426,78.189l-4.895-5.316a1.131,1.131,0,0,0-1.664,1.532l5.692,6.182a1.13,1.13,0,0,0,.808.365h.024a1.132,1.132,0,0,0,.8-.33l14.4-14.363A1.131,1.131,0,0,0,143.592,64.66Z"
                                        transform="translate(-121.568 -64.327)"
                                        fill="currentColor"
                                    />
                                </g>
                                <g>
                                    <path
                                        d="M28.869,13.869A1.131,1.131,0,0,0,27.739,15,12.739,12.739,0,1,1,15,2.261,1.131,1.131,0,1,0,15,0,15,15,0,1,0,30,15,1.131,1.131,0,0,0,28.869,13.869Z"
                                        fill="currentColor"
                                    />
                                </g>
                            </svg>
                        </div>

                        <h4 id="cart_success_modal_title" class="cart-added-title">
                            {{ __('defaultTheme.item_added_to_your_cart') }}
                        </h4>

                        <p class="cart-added-message">
                            {{ __('Your item has been added to your cart.') }}
                        </p>
                    </div>

                    <div class="cart-added-product">
                        <a id="cart_suceess_url" class="cart-added-product-link" href="javascript:void(0)">
                            <div class="cart-added-thumb">
                                <img
                                    id="cart_suceess_thumbnail"
                                    src="{{ url('/') }}/public/frontend/amazy/img/cart_added_thumb.png"
                                    alt=""
                                    title=""
                                >
                            </div>

                            <div class="cart-added-info">
                                <h5 id="cart_suceess_name" class="cart-added-name"></h5>

                                <p class="cart-added-vendor">
                                    Vendor:
                                    <strong id="cart_success_vendor"></strong>
                                </p>

                                <h5 id="cart_suceess_price" class="cart-added-price"></h5>
                            </div>
                        </a>
                    </div>

                    <a href="{{ url('/cart') }}" class="cart-added-view-cart">
                        {{ __('common.view_cart') }}
                    </a>

                </div>

            </div>
        </div>
    </div>
</div>