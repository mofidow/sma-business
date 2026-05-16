<?php

use Modules\Shop\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '', 'as' => 'shop.', 'middleware' => ['shopmode']], function () {
    Route::get('/', Livewire\Index::class)->name('home');
    Route::get('/signin', Livewire\Auth\SignIn::class)->name('signin');
    Route::get('/signup', Livewire\Auth\SignUp::class)->name('signup');
    Route::get('/otp-code', Livewire\Auth\TwoFactorChallenge::class)->name('otp-code');
    Route::get('/forgot-password', Livewire\Auth\ForgotPassword::class)->name('password.request');

    Route::get('products', Livewire\Products::class)->name('products');
    Route::get('products/{slug}', Livewire\Product::class)->name('product');
    Route::get('buy/{slug}', Livewire\QuickOrder::class)->name('quick-order');

    Route::get('brands', Livewire\Brands::class)->name('brands');
    Route::get('categories', Livewire\Categories::class)->name('categories');
    Route::get('brands/{slug}', Livewire\BrandProducts::class)->name('brand');
    Route::get('categories/{slug}', Livewire\CategoryProducts::class)->name('category');
    Route::get('categories/{slug}/{sub_slug}', Livewire\CategoryProducts::class)->name('subcategory');

    Route::post('/message', [Controllers\ContactController::class, 'send'])->name('message');
    Route::get('/page/{page:slug}', Livewire\Page::class)->name('page')->middleware('shortcode');

    Route::middleware('signed')->group(function () {
        Route::get('/orders/{id}/{hash}', Livewire\Page::class)->name('order.guest');
        Route::get('/payments/{id}/{hash}', Livewire\Page::class)->name('payment.guest');
    });

    Route::prefix('/cart')->group(function () {
        Route::get('/', Livewire\Components\Cart\View::class)->name('cart');
        Route::get('/checkout', Livewire\Components\Cart\Checkout::class)->name('checkout');
    });

    // Route::post('paypal/ipn', [Controllers\PayPalController::class, 'ipn'])->name('paypal.ipn');
    Route::get('paypal/{payment}', [Controllers\PayPalController::class, 'pay'])->name('paypal.pay');
    Route::get('paypal/{payment}/completed', [Controllers\PayPalController::class, 'completed'])->name('paypal.completed');

    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
        Route::get('/profile', Livewire\Auth\Profile::class)->name('profile');

        Route::middleware('verified')->group(function () {
            Route::get('/orders', Livewire\Components\Customer\Orders::class)->name('orders');
            Route::get('/payments', Livewire\Components\Customer\Payments::class)->name('payments');
            Route::get('/wishlist', Livewire\Components\Cart\Wishlist::class)->name('wishlist');
            Route::get('/billing', Livewire\Components\Customer\Billing::class)->name('billing');
            Route::get('/addresses', Livewire\Components\Customer\Addresses::class)->name('addresses');
            Route::get('/order/{id}', Livewire\Components\Customer\Order::class)->name('order');
            Route::get('/payment/{id}', Livewire\Components\Customer\Payment::class)->name('payment');
        });

        Route::prefix('shop/admin')->group(function () {
            Route::get('/settings', Livewire\Admin\Settings::class)->name('settings');
            Route::get('/custom_code', Livewire\Admin\CustomCode::class)->name('custom_code');

            Route::get('/pages', Livewire\Admin\Page\Index::class)->name('pages');
            Route::get('/pages/create', Livewire\Admin\Page\Form::class)->name('pages.create');
            Route::get('/pages/{page}/edit', Livewire\Admin\Page\Form::class)->name('pages.edit');

            Route::get('/coupons', Livewire\Admin\Coupon\Index::class)->name('coupons');
            Route::get('/coupons/create', Livewire\Admin\Coupon\Form::class)->name('coupons.create');
            Route::get('/coupons/{coupon}/edit', Livewire\Admin\Coupon\Form::class)->name('coupons.edit');

            Route::get('/currencies', Livewire\Admin\Currency\Index::class)->name('currencies');
            Route::get('/currencies/create', Livewire\Admin\Currency\Form::class)->name('currencies.create');
            Route::get('/currencies/{currency}/edit', Livewire\Admin\Currency\Form::class)->name('currencies.edit');

            Route::get('/shipping_methods', Livewire\Admin\ShippingMethod\Index::class)->name('shipping_methods');
            Route::get('/shipping_methods/create', Livewire\Admin\ShippingMethod\Form::class)->name('shipping_methods.create');
            Route::get('/shipping_methods/{shipping_method}/edit', Livewire\Admin\ShippingMethod\Form::class)->name('shipping_methods.edit');
        });
    });
});

// Route::redirect('/login', '/signin')->name('login');
Route::get('/reset-password/{token}', Livewire\Auth\ResetPassword::class)->name('password.reset');
Route::get('/email/verify', Livewire\Auth\EmailVerification::class)->name('verification.notice');

Route::middleware('signed')->group(function () {
    Route::get('/order/{id}/{hash?}', Livewire\Components\Customer\Order::class)->name('order.guest');
    Route::get('/payment/{id}/{hash?}', Livewire\Components\Customer\Payment::class)->name('payment.guest');

    Route::get('/newsletters/{id}', [Controllers\NewsletterController::class, 'confirm'])->name('newsletters.confirm');
    Route::get('/newsletter/unsubscribe', [Controllers\NewsletterController::class, 'unsubscribe'])->name('newsletters.unsubscribe');
});

Route::get('/private', Livewire\ShopMode\ModePrivate::class)->name('shop.private')->middleware('guest');
Route::get('/maintenance', Livewire\ShopMode\ModeMaintenance::class)->name('shop.maintenance')->middleware('guest');
