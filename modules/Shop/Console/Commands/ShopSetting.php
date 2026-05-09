<?php

namespace Modules\Shop\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class ShopSetting extends Command
{
    protected $signature = 'shop:setting';

    protected $description = 'Seed the shop settings';

    public function handle(): void
    {
        $settings = get_settings(['general', 'seo', 'shop_slider', 'shop_cta', 'shop_footer', 'page_menus', 'notification', 'social_links', 'newsletter_input', 'brands_article']);
        if (! ($settings['general'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'general'], ['tec_value' => json_encode([
                'name'                           => 'SMA Shop',
                'store_id'                       => 1,
                'phone'                          => '010-1234-5678',
                'email'                          => 'contact@sma.tec.sh',
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
        }

        if (! ($settings['seo'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'seo'], ['tec_value' => json_encode([
                'title'                => trim(session('subdomain', 'Shop') . ' Home'),
                'description'          => 'This is the shop homepage description.',
                'products_title'       => trim(session('subdomain', 'Our') . ' Products'),
                'products_description' => 'Explore our wide range of products available for purchase.',
            ])]);
        }

        if (! ($settings['shop_slider'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'shop_slider'], ['tec_value' => json_encode([
                [
                    'image'       => '/img/dummy.avif',
                    'bg_image'    => '',
                    'heading'     => 'Slider Heading 1' . (session('subdomain') ? ' (' . session('subdomain') . ')' : ''),
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                    'button_text' => 'Shop Now',
                    'button_link' => url('/products'),
                ],
                [
                    'image'       => '/img/dummy.avif',
                    'bg_image'    => '',
                    'heading'     => 'Slider Heading 2' . (session('subdomain') ? ' (' . session('subdomain') . ')' : ''),
                    'description' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                    'button_text' => 'Discover More',
                    'button_link' => url('/products'),
                ],
            ])]);
        }

        if (! ($settings['shop_cta'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'shop_cta'], ['tec_value' => json_encode([
                'bg_image'    => '/img/cta-bg.avif',
                'heading'     => 'CTA Heading' . (session('subdomain') ? ' (' . session('subdomain') . ')' : ''),
                'description' => 'lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'button_text' => 'Action',
                'button_link' => url('/products'),
            ])]);
        }

        if (! ($settings['shop_footer'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'shop_footer'], ['tec_value' => json_encode([
                'sections' => [
                    ['title' => 'Section 1', 'menus' => [
                        ['label' => 'Section 1 Link 1', 'link' => url('/products')],
                        ['label' => 'Section 1 Link 2', 'link' => url('/products')],
                        ['label' => 'Section 1 Link 3', 'link' => url('/products')],
                        ['label' => 'Section 1 Link 4', 'link' => url('/products')],
                        ['label' => 'Section 1 Link 5', 'link' => url('/products')],
                    ]],
                    ['title' => 'Section 2', 'menus' => [
                        ['label' => 'Section 2 Link 1', 'link' => url('/products')],
                        ['label' => 'Section 2 Link 2', 'link' => url('/products')],
                        ['label' => 'Section 2 Link 3', 'link' => url('/products')],
                        ['label' => 'Section 2 Link 4', 'link' => url('/products')],
                        ['label' => 'Section 2 Link 5', 'link' => url('/products')],
                        ['label' => 'Section 2 Link 6', 'link' => url('/products')],
                    ]],
                    ['title' => 'Section 3', 'menus' => [
                        ['label' => 'Section 3 Link 1', 'link' => url('/products')],
                        ['label' => 'Section 3 Link 2', 'link' => url('/products')],
                        ['label' => 'Section 3 Link 3', 'link' => url('/products')],
                    ]],
                    ['title' => 'Connect', 'menus' => [
                        ['label' => 'Contact Us', 'link' => url('/products')],
                        ['label' => 'Facebook', 'link' => url('/products')],
                        ['label' => 'Instagram', 'link' => url('/products')],
                        ['label' => 'LinkedIn', 'link' => url('/products')],
                    ]],
                ],
            ])]);
        }

        if (! ($settings['page_menus'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'page_menus'], ['tec_value' => json_encode([
                ['label' => 'Blog', 'link' => url('page/contact')],
                ['label' => 'FAQ', 'link' => url('page/contact')],
                ['label' => 'About Us', 'link' => url('page/contact')],
                ['label' => 'Contact Us', 'link' => url('page/contact')],
                ['label' => 'Privacy Policy', 'link' => url('page/contact')],
                ['label' => 'Terms of Service', 'link' => url('page/contact')],
            ])]);
        }

        if (! ($settings['notification'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'notification'], ['tec_value' => json_encode([
                'message'     => 'Beta release! Shop module is still in pre-release testing!',
                'button_text' => 'Shop Now',
                'button_link' => url('/products'),
            ])]);
        }

        if (! ($settings['social_links'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'social_links'], ['tec_value' => json_encode([
                'facebook'  => '',
                'instagram' => '',
                'twitter'   => '',
                'linkedin'  => '',
                'youtube'   => '',
                'pinterest' => '',
            ])]);
        }
        if (! ($settings['newsletter_input'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'newsletter_input'], ['tec_value' => '1']);
        }
        if (! ($settings['brands_article'] ?? null)) {
            Setting::updateOrCreate(['tec_key' => 'brands_article'], ['tec_value' => '0']);
        }
    }
}
