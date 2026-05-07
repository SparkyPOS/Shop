<style>
    #cart_add_modal.cart-added-pro-modal {
        --cart-added-dark: #071225;
        --cart-added-text: #111827;
        --cart-added-muted: #6b7280;
        --cart-added-border: #e5e7eb;
        --cart-added-soft: #f8fafc;
        --cart-added-green: #22a463;
        --cart-added-green-soft: #eaf8ef;
        --cart-added-blue: #0d6efd;
    }

    #cart_add_modal.cart-added-pro-modal .modal-dialog {
        max-width: 460px !important;
        width: calc(100% - 28px) !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    #cart_add_modal.cart-added-pro-modal .modal-content {
        border: 0 !important;
        border-radius: 18px !important;
        background: #ffffff !important;
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28) !important;
        overflow: hidden !important;
    }

    #cart_add_modal.cart-added-pro-modal .modal-body {
        padding: 0 !important;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-shell {
        position: relative;
        background: #ffffff;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 24px 24px 20px;
        border-bottom: 1px solid #eef2f6;
        background: linear-gradient(180deg, #fbfffd 0%, #ffffff 100%);
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-status {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        min-width: 0;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-check {
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #ecfdf3;
        color: var(--cart-added-green);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-check svg {
        width: 24px;
        height: 24px;
        display: block;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-heading {
        min-width: 0;
        padding-top: 1px;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-title {
        margin: 0;
        color: var(--cart-added-text);
        font-size: 20px;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-message {
        margin: 6px 0 0;
        color: var(--cart-added-muted);
        font-size: 13.5px;
        line-height: 1.45;
        font-weight: 400;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-close {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border: 0 !important;
        border-radius: 50%;
        background: transparent !important;
        color: #374151;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        line-height: 1;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-close:hover {
        background: #f3f4f6 !important;
        color: #111827;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-close i {
        font-size: 14px;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-body {
        padding: 22px 24px 24px;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-product-card {
        border: 1px solid var(--cart-added-border);
        border-radius: 16px;
        background: #ffffff;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-product-link {
        display: grid !important;
        grid-template-columns: 118px minmax(0, 1fr);
        gap: 18px;
        align-items: center;
        padding: 14px;
        color: inherit !important;
        text-decoration: none !important;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-product-link:hover {
        color: inherit !important;
        text-decoration: none !important;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-thumb {
        width: 118px;
        height: 150px;
        border-radius: 12px;
        background: var(--cart-added-soft);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-thumb img {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        display: block !important;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-info {
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-name {
        margin: 0 0 8px;
        color: var(--cart-added-text);
        font-size: 17px;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.01em;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-meta {
        margin: 0;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-meta-row {
        margin: 0 0 4px;
        color: var(--cart-added-muted);
        font-size: 12.5px;
        font-weight: 500;
        line-height: 1.35;
        display: flex;
        align-items: flex-start;
        gap: 4px;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-meta-row span {
        flex: 0 0 auto;
        color: var(--cart-added-muted);
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-meta-row strong {
        color: var(--cart-added-text);
        font-weight: 800;
        min-width: 0;
        word-break: break-word;
    }

    #cart_add_modal.cart-added-pro-modal #cart_success_vendor {
        color: var(--cart-added-blue);
    }

    #cart_add_modal.cart-added-pro-modal #cart_success_variant {
        color: var(--cart-added-text);
        font-weight: 800;
        line-height: 1.35;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #eef2f6;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-qty-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 32px;
        padding: 6px 11px;
        border-radius: 10px;
        background: var(--cart-added-green-soft);
        color: var(--cart-added-green);
        font-size: 13px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-qty-badge svg {
        width: 15px;
        height: 15px;
        display: block;
        flex: 0 0 auto;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-price {
        margin: 0;
        color: var(--cart-added-text);
        font-size: 18px;
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -0.02em;
        text-align: right;
        white-space: nowrap;
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-view-cart {
        width: 100%;
        min-height: 52px;
        border-radius: 12px;
        background: var(--cart-added-dark);
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        text-transform: uppercase;
        text-decoration: none !important;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.045em;
        box-shadow: 0 10px 22px rgba(7, 18, 37, 0.22);
    }

    #cart_add_modal.cart-added-pro-modal .cart-added-view-cart:hover {
        background: #111827;
        color: #ffffff !important;
        text-decoration: none !important;
    }

    @media (max-width: 575.98px) {
        #cart_add_modal.cart-added-pro-modal .modal-dialog {
            max-width: calc(100% - 22px) !important;
            width: calc(100% - 22px) !important;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-top {
            padding: 20px 18px 17px;
            gap: 12px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-status {
            gap: 11px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-check {
            width: 34px;
            height: 34px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-title {
            font-size: 18px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-message {
            font-size: 12.5px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-body {
            padding: 18px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-product-link {
            grid-template-columns: 104px minmax(0, 1fr);
            gap: 14px;
            padding: 12px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-thumb {
            width: 104px;
            height: 138px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-name {
            font-size: 15.5px;
            margin-bottom: 7px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-meta-row {
            font-size: 12px;
            margin-bottom: 3px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-summary {
            margin-top: 10px;
            padding-top: 10px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-qty-badge {
            min-height: 30px;
            padding: 6px 10px;
            font-size: 12.5px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-price {
            font-size: 16.5px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-view-cart {
            min-height: 50px;
            border-radius: 10px;
        }
    }

    @media (max-width: 374.98px) {
        #cart_add_modal.cart-added-pro-modal .cart-added-body {
            padding: 16px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-product-link {
            grid-template-columns: 88px minmax(0, 1fr);
            gap: 12px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-thumb {
            width: 88px;
            height: 122px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-summary {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }

        #cart_add_modal.cart-added-pro-modal .cart-added-price {
            text-align: left;
        }
    }
</style>

<div
    class="modal fade theme_modal2 cart-added-pro-modal"
    id="cart_add_modal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="cart_success_modal_title"
    aria-hidden="true"
>
    <div class="modal-dialog max_width_430 modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">

                <div class="cart-added-shell">

                    <div class="cart-added-top">
                        <div class="cart-added-status">
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

                            <div class="cart-added-heading">
                                <h4 id="cart_success_modal_title" class="cart-added-title">
                                    {{ __('defaultTheme.item_added_to_your_cart') }}
                                </h4>

                                <p class="cart-added-message">
                                    {{ __('Your item has been added to your cart.') }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="cart-added-close"
                            data-bs-dismiss="modal"
                            data-dismiss="modal"
                            aria-label="{{ __('common.close') }}"
                        >
                            <i class="ti-close"></i>
                        </button>
                    </div>

                    <div class="cart-added-body">
                        <div class="cart-added-product-card">
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

                                    <div class="cart-added-meta">
                                        <p class="cart-added-meta-row" id="cart_success_vendor_wrap">
                                            <span>Vendor:</span>
                                            <strong id="cart_success_vendor"></strong>
                                        </p>

                                        <p class="cart-added-meta-row" id="cart_success_store_wrap">
                                            <span>Store:</span>
                                            <strong id="cart_success_store"></strong>
                                        </p>

                                        <p class="cart-added-meta-row" id="cart_success_variant_wrap">
                                            <strong id="cart_success_variant"></strong>
                                        </p>
                                    </div>

                                    <div class="cart-added-summary">
                                        <span class="cart-added-qty-badge" id="cart_success_qty_wrap">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path
                                                    d="M7 8V7a5 5 0 0 1 10 0v1h1.25A2.75 2.75 0 0 1 21 10.75v7.5A2.75 2.75 0 0 1 18.25 21H5.75A2.75 2.75 0 0 1 3 18.25v-7.5A2.75 2.75 0 0 1 5.75 8H7Zm2 0h6V7a3 3 0 0 0-6 0v1Zm-3.25 2A.75.75 0 0 0 5 10.75v7.5c0 .414.336.75.75.75h12.5a.75.75 0 0 0 .75-.75v-7.5a.75.75 0 0 0-.75-.75H5.75Z"
                                                    fill="currentColor"
                                                />
                                            </svg>
                                            <span>Qty</span>
                                            <strong id="cart_success_qty"></strong>
                                        </span>

                                        <h5 id="cart_suceess_price" class="cart-added-price"></h5>
                                    </div>
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
</div>