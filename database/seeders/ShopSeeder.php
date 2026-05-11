<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Modules\Shop\Models\ShopPage;
use Modules\Shop\Models\ShopCoupon;
use Modules\Shop\Models\ShopCurrency;
use Modules\Shop\Models\ShopShippingMethod;

/**
 * Safe wrapper around ShopSettingSeeder.
 * Skips currency/country lookups when world data is not yet installed.
 * Run php artisan world:install first for full seeding.
 */
class ShopSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(['tec_key' => 'general'], ['tec_value' => json_encode([
            'name'                           => 'Rozana Somalia',
            'store_id'                       => 1,
            'phone'                          => '+252-61-000-0000',
            'email'                          => 'info@rozanasomalia.com',
            'logo'                           => '/img/sma-icon.svg',
            'logo_dark'                      => '/img/sma-icon-light.svg',
            'products_per_page'              => '24',
            'shop_mode'                      => 'public',
            'hide_price'                     => '0',
            'disable_cart'                   => '0',
            'guest_checkout'                 => '0',
            'max_unpaid_orders'              => '1',
            'user_registration'              => '1',
            'new_account_email_confirmation' => '1',
        ])]);

        Setting::updateOrCreate(['tec_key' => 'seo'], ['tec_value' => json_encode([
            'title'                => 'Rozana Somalia — Online Shop',
            'description'          => 'Shop online at Rozana Somalia. Quality products delivered to you.',
            'products_title'       => 'Our Products',
            'products_description' => 'Browse our wide range of products.',
        ])]);

        Setting::updateOrCreate(['tec_key' => 'shop_slider'], ['tec_value' => json_encode([
            [
                'image'       => '/img/dummy.avif',
                'bg_image'    => '',
                'heading'     => 'Welcome to Rozana Somalia',
                'description' => 'Quality products, fast delivery.',
                'button_text' => 'Shop Now',
                'button_link' => url('/products'),
            ],
        ])]);

        Setting::updateOrCreate(['tec_key' => 'shop_cta'], ['tec_value' => json_encode([
            'bg_image'    => '/img/dummy.avif',
            'heading'     => 'Special Offers',
            'description' => 'Discover our latest deals and promotions.',
            'button_text' => 'View Deals',
            'button_link' => url('/products'),
        ])]);

        Setting::updateOrCreate(['tec_key' => 'shop_footer'], ['tec_value' => json_encode([
            'sections' => [
                ['title' => 'Shop', 'menus' => [
                    ['label' => 'Products', 'link' => url('/products')],
                    ['label' => 'About Us', 'link' => url('/page/about-us')],
                    ['label' => 'Contact Us', 'link' => url('/page/contact')],
                ]],
            ],
        ])]);

        Setting::updateOrCreate(['tec_key' => 'page_menus'], ['tec_value' => json_encode([
            ['label' => 'About Us',   'link' => url('/page/about-us')],
            ['label' => 'Contact Us', 'link' => url('/page/contact')],
        ])]);

        Setting::updateOrCreate(['tec_key' => 'notification'],     ['tec_value' => json_encode(['message' => '', 'button_text' => '', 'button_link' => ''])]);
        Setting::updateOrCreate(['tec_key' => 'social_links'],     ['tec_value' => json_encode(['facebook' => '', 'instagram' => '', 'twitter' => '', 'linkedin' => '', 'youtube' => '', 'pinterest' => ''])]);
        Setting::updateOrCreate(['tec_key' => 'newsletter_input'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'brands_article'],   ['tec_value' => '0']);
        Setting::updateOrCreate(['tec_key' => 'payment'],          ['tec_value' => json_encode(['default_currency' => 'USD', 'gateway' => 'Stripe', 'services' => []])]);

        // Try currencies — only works when world:install has been run
        try {
            $usd = \Nnjeim\World\Models\Currency::where('code', 'USD')->first();
            if ($usd) {
                ShopCurrency::updateOrCreate(['currency_id' => $usd->id], ['exchange_rate' => 1.00]);
            }
        } catch (\Throwable) {
            $this->command?->warn('Skipping currencies — run: php8.4 artisan world:install');
        }

        ShopPage::updateOrCreate(['slug' => 'about-us'], [
            'title'       => 'About Us',
            'description' => 'Learn about Rozana Somalia.',
            'body'        => '<h2>About Us</h2><p>Rozana Somalia is your trusted online marketplace.</p>',
        ]);

        ShopPage::updateOrCreate(['slug' => 'contact'], [
            'title'       => 'Contact Us',
            'description' => 'Get in touch with us.',
            'body'        => '<!-- [contact-form] -->',
        ]);

        ShopCoupon::updateOrCreate(['code' => 'WELCOME10'], [
            'discount'    => 10,
            'allowed'     => 1,
            'active'      => 1,
            'expiry_date' => now()->addYear(),
        ]);

        ShopShippingMethod::updateOrCreate(['provider_name' => 'Standard Delivery'], [
            'rate'          => 5,
            'provider_name' => 'Standard Delivery',
        ]);

        cache()->flush();

        $this->command?->info('Shop settings seeded successfully!');
    }
}
