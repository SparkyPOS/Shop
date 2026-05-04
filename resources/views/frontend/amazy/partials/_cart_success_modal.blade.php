<div
    class="modal fade theme_modal2 cart-success-modal"
    id="cart_add_modal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="cart_success_modal_title"
    aria-hidden="true"
>
    <div class="modal-dialog cart-success-dialog modal-dialog-centered" role="document">
        <div class="modal-content cart-success-content">
            <div class="modal-body cart-success-body">

                <div class="cart-success-wrapper">

                    {{-- Close Button --}}
                    <button
                        type="button"
                        class="cart-success-close"
                        data-bs-dismiss="modal"
                        aria-label="{{ __('common.close') }}"
                    >
                        <i class="ti-close"></i>
                    </button>

                    {{-- Success Header --}}
                    <div class="cart-success-header">
                        <div class="cart-success-icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 30 30" fill="none">
                                <g transform="translate(7.118 3.77)">
                                    <path
                                        d="M143.592,64.66a1.131,1.131,0,0,0-1.6,0L128.426,78.189l-4.895-5.316a1.131,1.131,0,0,0-1.664,1.532l5.692,6.182a1.13,1.13,0,0,0,.808.365h.024a1.132,1.132,0,0,0,.8-.33l14.4-14.363A1.131,1.131,0,0,0,143.592,64.66Z"
                                        transform="translate(-121.568 -64.327)"
                                        fill="currentColor"
                                    />
                                </g>
                                <path
                                    d="M28.869,13.869A1.131,1.131,0,0,0,27.739,15,12.739,12.739,0,1,1,15,2.261,1.131,1.131,0,1,0,15,0,15,15,0,1,0,30,15,1.131,1.131,0,0,0,28.869,13.869Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </div>

                        <h4 id="cart_success_modal_title" class="cart-success-title">
                            {{ __('defaultTheme.item_added_to_your_cart') }}
                        </h4>

                        <p class="cart-success-subtitle">
                            {{ __('Your item has been added to your cart.') }}
                        </p>
                    </div>

                    {{-- Product Summary --}}
                    <div class="cart-success-product-card">
                        <a id="cart_suceess_url" class="cart-success-product-link">
                            <div class="cart-success-thumb">
                                <img
                                    id="cart_suceess_thumbnail"
                                    src="{{ url('/') }}/public/frontend/amazy/img/cart_added_thumb.png"
                                    alt=""
                                    title=""
                                >
                            </div>

                            <div class="cart-success-product-info">
                                <h5 id="cart_suceess_name" class="cart-success-product-name"></h5>

                                <div class="cart-success-vendor">
                                    <span class="cart-success-vendor-label">Vendor:</span>
                                    <span id="cart_success_vendor" class="cart-success-vendor-value"></span>
                                </div>

                                <div id="cart_suceess_price" class="cart-success-price"></div>
                            </div>
                        </a>
                    </div>

                    {{-- Primary CTA --}}
                    <div class="cart-success-actions">
                        <a href="{{ url('/cart') }}" class="cart-success-view-cart">
                            {{ __('common.view_cart') }}
                        </a>

                        @if(!app('general_setting')->seller_wise_payment && !isModuleActive('MultiVendor'))
                            <a href="{{ url('/checkout') }}" class="cart-success-checkout">
                                {{ __('common.process_to_checkout') }}
                            </a>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .cart-success-modal {
        --cart-success-dark: #081225;
        --cart-success-text: #101828;
        --cart-success-muted: #667085;
        --cart-success-border: #e6ebf1;
        --cart-success-soft: #f8fafc;
        --cart-success-green: #22a35a;
        --cart-success-blue: #0d6efd;
    }

    .cart-success-dialog {
        max-width: 560px;
        margin-left: auto;
        margin-right: auto;
    }

    .cart-success-content {
        border: 0;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.22);
        overflow: hidden;
    }

    .cart-success-body {
        padding: 0;
    }

    .cart-success-wrapper {
        position: relative;
        padding: 42px 38px 34px;
        background:
            radial-gradient(circle at 50% 0%, rgba(34, 163, 90, 0.08), transparent 35%),
            #ffffff;
    }

    .cart-success-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: #344054;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        transition: background-color 0.18s ease, color 0.18s ease;
        z-index: 2;
    }

    .cart-success-close:hover {
        background: #f2f4f7;
        color: #101828;
    }

    .cart-success-close i {
        font-size: 15px;
        line-height: 1;
    }

    .cart-success-header {
        text-align: center;
        max-width: 390px;
        margin: 0 auto 28px;
        padding: 0 22px;
    }

    .cart-success-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        border-radius: 50%;
        color: var(--cart-success-green);
        background: #eefaf3;
        border: 1px solid rgba(34, 163, 90, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-success-title {
        margin: 0;
        color: var(--cart-success-text);
        font-size: 30px;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.035em;
    }

    .cart-success-subtitle {
        margin: 10px 0 0;
        color: var(--cart-success-muted);
        font-size: 15px;
        font-weight: 400;
        line-height: 1.45;
    }

    .cart-success-product-card {
        border: 1px solid var(--cart-success-border);
        border-radius: 18px;
        background: #ffffff;
        overflow: hidden;
        margin-bottom: 26px;
        box-shadow: 0 10px 32px rgba(15, 23, 42, 0.04);
    }

    .cart-success-product-link {
        display: grid;
        grid-template-columns: 190px minmax(0, 1fr);
        align-items: center;
        gap: 28px;
        padding: 18px;
        color: inherit;
        text-decoration: none;
    }

    .cart-success-product-link:hover {
        color: inherit;
        text-decoration: none;
    }

    .cart-success-thumb {
        width: 190px;
        height: 230px;
        border-radius: 14px;
        background: var(--cart-success-soft);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-success-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .cart-success-product-info {
        min-width: 0;
        padding-right: 8px;
    }

    .cart-success-product-name {
        margin: 0 0 14px;
        color: var(--cart-success-text);
        font-size: 24px;
        font-weight: 800;
        line-height: 1.22;
        letter-spacing: -0.025em;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cart-success-vendor {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 14px;
        min-width: 0;
        font-size: 17px;
        line-height: 1.3;
    }

    .cart-success-vendor-label {
        color: var(--cart-success-muted);
        font-weight: 500;
    }

    .cart-success-vendor-value {
        color: var(--cart-success-blue);
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cart-success-price {
        color: var(--cart-success-text);
        font-size: 26px;
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: -0.035em;
    }

    .cart-success-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cart-success-view-cart,
    .cart-success-checkout {
        width: 100%;
        min-height: 58px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        text-transform: uppercase;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, color 0.18s ease;
    }

    .cart-success-view-cart {
        background: var(--cart-success-dark);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(8, 18, 37, 0.22);
    }

    .cart-success-view-cart:hover {
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(8, 18, 37, 0.26);
    }

    .cart-success-checkout {
        background: #ffffff;
        color: var(--cart-success-dark);
        border: 1px solid var(--cart-success-dark);
    }

    .cart-success-checkout:hover {
        background: #f8fafc;
        color: var(--cart-success-dark);
    }

    @media (max-width: 575.98px) {
        .cart-success-dialog {
            max-width: calc(100% - 28px);
        }

        .cart-success-wrapper {
            padding: 34px 18px 22px;
        }

        .cart-success-close {
            top: 14px;
            right: 14px;
            width: 34px;
            height: 34px;
        }

        .cart-success-header {
            margin-bottom: 22px;
            padding: 0 30px;
        }

        .cart-success-icon {
            width: 42px;
            height: 42px;
            margin-bottom: 12px;
        }

        .cart-success-icon svg {
            width: 24px;
            height: 24px;
        }

        .cart-success-title {
            font-size: 24px;
        }

        .cart-success-subtitle {
            font-size: 14px;
            margin-top: 8px;
        }

        .cart-success-product-card {
            margin-bottom: 20px;
            border-radius: 16px;
        }

        .cart-success-product-link {
            grid-template-columns: 112px minmax(0, 1fr);
            gap: 14px;
            padding: 14px;
        }

        .cart-success-thumb {
            width: 112px;
            height: 148px;
            border-radius: 12px;
        }

        .cart-success-product-info {
            padding-right: 0;
        }

        .cart-success-product-name {
            font-size: 17px;
            margin-bottom: 9px;
        }

        .cart-success-vendor {
            font-size: 14px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .cart-success-price {
            font-size: 20px;
        }

        .cart-success-view-cart,
        .cart-success-checkout {
            min-height: 52px;
            border-radius: 10px;
            font-size: 13px;
        }
    }

    @media (max-width: 374.98px) {
        .cart-success-product-link {
            grid-template-columns: 92px minmax(0, 1fr);
            gap: 12px;
            padding: 12px;
        }

        .cart-success-thumb {
            width: 92px;
            height: 128px;
        }

        .cart-success-product-name {
            font-size: 16px;
        }

        .cart-success-vendor {
            font-size: 13px;
        }

        .cart-success-price {
            font-size: 18px;
        }
    }
</style>
@endpush