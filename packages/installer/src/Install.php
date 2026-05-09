<?php

namespace Tecdiary\Installer;

use App\Models\Role;
use App\Models\User;
use App\Models\Setting;
use App\Tec\Helpers\Env;
use Illuminate\Http\Request;
use App\Models\Sma\Product\Unit;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\Setting\Store;
use Modules\Shop\Models\ShopPage;
use Nnjeim\World\Models\Currency;
use Illuminate\Support\Facades\DB;
use App\Models\Sma\People\Customer;
use App\Models\Sma\People\Supplier;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Product\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Modules\Shop\Models\ShopCurrency;
use App\Models\Sma\Accounting\Account;
use Illuminate\Support\Facades\Storage;
use App\Models\Sma\Accounting\AccountType;
use Modules\Shop\Models\ShopShippingMethod;

class Install
{
    public static function createDefaultData()
    {
        set_time_limit(300);
        try {
            cache()->flush();
            $at = AccountType::create(['name' => 'Cash']);
            Account::create(['title' => 'Default Account', 'account_type_id' => $at->id, 'opening_balance' => 0, 'active' => 1]);
            Store::create(['name' => 'Default Store', 'account_id' => 1, 'phone' => '0123456789', 'email' => 'store@example.com', 'state_id' => '2495', 'country_id' => 132, 'active' => 1]);

            Brand::create(['name' => 'General', 'active' => 1]);
            Category::create(['name' => 'General', 'active' => 1]);
            Unit::create(['name' => 'Piece', 'code' => 'pc']);
            Product::create([
                'type'        => 'Standard',
                'name'        => 'Test Product 1',
                'code'        => 'TP01',
                'cost'        => '59',
                'price'       => '100',
                'symbology'   => 'CODE128',
                'category_id' => 1,
                'brand_id'    => 1,
                'unit_id'     => 1,
                'active'      => 1,
            ]);

            Customer::create(['name' => 'Walk-in Customer', 'company' => 'Walk-in Customer', 'phone' => '0123456789', 'email' => 'customer@example.com', 'user_id' => 1]);
            Supplier::create(['name' => 'Test Supplier', 'company' => 'Test Supplier', 'phone' => '0123456789', 'email' => 'supplier@example.com', 'user_id' => 1]);

            Setting::updateOrCreate(['tec_key' => 'overselling'], ['tec_value' => '1']);
            Setting::updateOrCreate(['tec_key' => 'default_account'], ['tec_value' => '1']);
            Setting::updateOrCreate(['tec_key' => 'default_category'], ['tec_value' => '1']);
            Setting::updateOrCreate(['tec_key' => 'default_customer'], ['tec_value' => '1']);
            Setting::updateOrCreate(['tec_key' => 'default_currency'], ['tec_value' => '236']);

            if (get_module('shop')) {
                Setting::updateOrCreate(['tec_key' => 'general'], ['tec_value' => json_encode([
                    'name'                           => 'SMA Shop',
                    'store_id'                       => mt_rand(1, 3),
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
                    'captcha'                        => 'local',
                ])]);

                Setting::updateOrCreate(['tec_key' => 'seo'], ['tec_value' => json_encode([
                    'title'                => 'Shop Home',
                    'description'          => 'This is the shop homepage description.',
                    'products_title'       => 'Our Products',
                    'products_description' => 'Explore our wide range of products available for purchase.',
                ])]);

                Setting::updateOrCreate(['tec_key' => 'shop_slider'], ['tec_value' => json_encode([
                    [
                        'image'       => '/img/dummy.avif',
                        'bg_image'    => '',
                        'heading'     => 'Slider Heading 1',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                        'button_text' => 'Shop Now',
                        'button_link' => url('/products'),
                    ],
                    [
                        'image'       => '/img/dummy.avif',
                        'bg_image'    => '',
                        'heading'     => 'Slider Heading 2',
                        'description' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                        'button_text' => 'Discover More',
                        'button_link' => url('/products'),
                    ],
                ])]);
                Setting::updateOrCreate(['tec_key' => 'shop_cta'], ['tec_value' => json_encode([
                    'bg_image'    => '/img/cta-bg.avif',
                    'heading'     => 'CTA Heading',
                    'description' => 'lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                    'button_text' => 'Action',
                    'button_link' => url('/products'),
                ])]);
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
                Setting::updateOrCreate(['tec_key' => 'page_menus'], ['tec_value' => json_encode([
                    ['label' => 'Blog', 'link' => url('page/contact')],
                    ['label' => 'FAQ', 'link' => url('page/contact')],
                    ['label' => 'About Us', 'link' => url('page/contact')],
                    ['label' => 'Contact Us', 'link' => url('page/contact')],
                    ['label' => 'Privacy Policy', 'link' => url('page/contact')],
                    ['label' => 'Terms of Service', 'link' => url('page/contact')],
                ])]);
                Setting::updateOrCreate(['tec_key' => 'notification'], ['tec_value' => json_encode([
                    'message'     => 'Beta release! Shop module is still in pre-release testing!',
                    'button_text' => 'Shop Now',
                    'button_link' => url('/products'),
                ])]);
                Setting::updateOrCreate(['tec_key' => 'social_links'], ['tec_value' => json_encode([
                    'facebook'  => '',
                    'instagram' => '',
                    'twitter'   => '',
                    'linkedin'  => '',
                    'youtube'   => '',
                    'pinterest' => '',
                ])]);
                Setting::updateOrCreate(['tec_key' => 'newsletter_input'], ['tec_value' => '1']);
                Setting::updateOrCreate(['tec_key' => 'brands_article'], ['tec_value' => '0']);
                Setting::updateOrCreate(['tec_key' => 'payment'], ['tec_value' => json_encode([
                    'default_currency' => 'USD',
                    'gateway'          => 'Stripe',
                    'services'         => [
                        'paypal' => ['enabled' => '1', 'client_id' => '', 'secret' => ''],
                        'stripe' => ['enabled' => '1', 'key' => '', 'secret' => ''],
                    ],
                ])]);

                if ($usd = Currency::where('code', 'USD')->first()) {
                    ShopCurrency::updateOrCreate(['currency_id' => $usd->id], ['exchange_rate' => 1.00]);
                }
                if ($eur = Currency::where('code', 'EUR')->first()) {
                    ShopCurrency::updateOrCreate(
                        ['currency_id' => $eur->id],
                        ['exchange_rate' => 0.9, 'show_at_end' => false, 'auto_update' => true]
                    );
                }

                ShopPage::updateOrCreate(
                    ['slug' => 'about-us'],
                    [
                        'title'       => 'About Us',
                        'description' => 'This is about us page description.',
                        'body'        => 'This is test about us page :)',
                    ]);

                ShopPage::updateOrCreate(
                    ['slug' => 'contact'],
                    [
                        'title'       => 'Contact Us',
                        'description' => 'Get in touch with us for any inquiries or support.',
                        'body'        => '<!-- [map:Av. Gustave Eiffel, 75007 Paris, France] -->

<!-- [contact-form] -->',
                    ]
                );

                ShopShippingMethod::updateOrCreate(
                    ['provider_name' => 'DHL eCommerce'],
                    ['rate' => 8, 'provider_name' => 'DHL eCommerce']
                );

                cache()->flush();
            }

            return ['success' => true, 'message' => 'Data created, please finalize the installation.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function createDemoData()
    {
        set_time_limit(300);
        try {
            $demoData = Storage::disk('local')->get('demo.sql');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $data = self::dbTransaction($demoData);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return $data;
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function createEnv()
    {
        File::copy(base_path('.env.example'), base_path('.env'));
        Env::update(['APP_URL' => url('/')], false);
    }

    public static function createTables(Request $request, $data, $installation_id = null)
    {
        $result = self::isDbValid($data);
        if (! $result || $result['success'] == false) {
            return $result;
        }

        set_time_limit(300);
        $data['license']['id'] = '23045302';
        $data['license']['version'] = '4.0';
        $data['license']['type'] = 'install';

        $result = ['success' => false, 'message' => ''];
        $url = 'https://api.tecdiary.net/v1/dbtables';
        $response = Http::withoutVerifying()->acceptJson()->post($url, $data['license']);
        if ($response->ok()) {
            $sql = $response->json();
            if (empty($sql['database'])) {
                $result = ['success' => false, 'message' => $sql['database'] ?? 'No database received from install server, please check with developer.'];
            } else {
                $result = self::dbTransaction($sql['database']);
            }
            Storage::disk('local')->put('modules.json', json_encode([
                'sma'  => $data['license']['code'],
                'pos'  => ['key' => $data['license']['code'], 'enabled' => true],
                'shop' => ['key' => $data['license']['code'], 'enabled' => true],
            ], JSON_PRETTY_PRINT));
        } else {
            $result = ['success' => false, 'message' => $response->json()];
        }

        return $result;
    }

    public static function createUser($user)
    {
        $user['active'] = 1;
        $user['employee'] = 1;
        $user['phone'] = '0123456789';
        $user['password'] = Hash::make($user['password']);
        $user['email_verified_at'] = now();
        $user = User::create($user);
        $super = Role::create(['name' => 'Super Admin']);
        $user->assignRole($super);
        auth()->login($user);

        Role::create(['name' => 'Customer']);
        Role::create(['name' => 'Supplier']);

        // Add default settings
        Setting::updateOrCreate(['tec_key' => 'name'], ['tec_value' => 'SMA Business Manager']);
        Setting::updateOrCreate(['tec_key' => 'short_name'], ['tec_value' => 'SMA']);
        Setting::updateOrCreate(['tec_key' => 'icon'], ['tec_value' => url('/img/sma-icon.svg')]);
        Setting::updateOrCreate(['tec_key' => 'icon_dark'], ['tec_value' => url('/img/sma-icon-light.svg')]);
        Setting::updateOrCreate(['tec_key' => 'logo'], ['tec_value' => url('/img/sma.svg')]);
        Setting::updateOrCreate(['tec_key' => 'logo_dark'], ['tec_value' => url('/img/sma-light.svg')]);
        Setting::updateOrCreate(['tec_key' => 'timezone_id'], ['tec_value' => '229']);
        Setting::updateOrCreate(['tec_key' => 'company'], ['tec_value' => 'Tec.sh']);
        Setting::updateOrCreate(['tec_key' => 'email'], ['tec_value' => 'noreply@tec.sh']);
        Setting::updateOrCreate(['tec_key' => 'phone'], ['tec_value' => '909-795-1234']);
        Setting::updateOrCreate(['tec_key' => 'address'], ['tec_value' => '795 Gordon Street, La']);
        Setting::updateOrCreate(['tec_key' => 'state_id'], ['tec_value' => '2495']);
        Setting::updateOrCreate(['tec_key' => 'country_id'], ['tec_value' => '132']);
        Setting::updateOrCreate(['tec_key' => 'theme'], ['tec_value' => 'dark']);
        Setting::updateOrCreate(['tec_key' => 'hide_id'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'rows_per_page'], ['tec_value' => '15']);
        Setting::updateOrCreate(['tec_key' => 'language'], ['tec_value' => 'en']);
        Setting::updateOrCreate(['tec_key' => 'date_number_locale'], ['tec_value' => 'en-US']);
        Setting::updateOrCreate(['tec_key' => 'stock'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'fraction'], ['tec_value' => '2']);
        Setting::updateOrCreate(['tec_key' => 'inventory_accounting'], ['tec_value' => 'FIFO']);
        Setting::updateOrCreate(['tec_key' => 'quantity_fraction'], ['tec_value' => '0']);
        Setting::updateOrCreate(['tec_key' => 'inclusive_tax_formula'], ['tec_value' => 'inclusive']);
        Setting::updateOrCreate(['tec_key' => 'max_discount'], ['tec_value' => '20']);
        Setting::updateOrCreate(['tec_key' => 'quick_cash'], ['tec_value' => '10|50|100|500|1000']);
        Setting::updateOrCreate(['tec_key' => 'confirmation'], ['tec_value' => 'modal']);
        Setting::updateOrCreate(['tec_key' => 'show_tax'], ['tec_value' => '0']);
        Setting::updateOrCreate(['tec_key' => 'show_image'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'show_tax_summary'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'show_discount'], ['tec_value' => '0']);
        Setting::updateOrCreate(['tec_key' => 'show_zero_taxes'], ['tec_value' => '0']);
        Setting::updateOrCreate(['tec_key' => 'dimension_unit'], ['tec_value' => 'cm']);
        Setting::updateOrCreate(['tec_key' => 'weight_unit'], ['tec_value' => 'kg']);
        Setting::updateOrCreate(['tec_key' => 'restaurant'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'reference'], ['tec_value' => 'monthly']);
        Setting::updateOrCreate(['tec_key' => 'label_width'], ['tec_value' => '300']);
        Setting::updateOrCreate(['tec_key' => 'label_height'], ['tec_value' => '150']);
        Setting::updateOrCreate(['tec_key' => 'auto_open_order'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'support_links'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'sidebar_dropdown'], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'date_format'], ['tec_value' => 'php']);
        Setting::updateOrCreate(['tec_key' => 'pos_design'], ['tec_value' => 'Simple']);
        Setting::updateOrCreate(['tec_key' => 'mail'], ['tec_value' => json_encode(['default' => 'log'])]);
        Setting::updateOrCreate(['tec_key' => 'payment'], ['tec_value' => json_encode(['default_currency' => 'USD', 'gateway' => 'Stripe', 'services' => ['stripe' => ['enabled' => false], 'paypal' => ['enabled' => false]]])]);
    }

    public static function finalize()
    {
        Env::update([
            'APP_INSTALLED'  => 'true',
            'APP_DEBUG'      => 'false',
            'APP_URL'        => url('/'),
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE'    => 'database',
        ], false);

        return true;
    }

    public static function isDbValid($data)
    {
        if (! File::exists(base_path('.env'))) {
            self::createEnv();
        }

        Env::update([
            'DB_HOST'     => $data['database']['host'],
            'DB_PORT'     => $data['database']['port'],
            'DB_DATABASE' => $data['database']['name'],
            'DB_USERNAME' => $data['database']['user'],
            'DB_PASSWORD' => $data['database']['password'] ?? '',
            'DB_SOCKET'   => $data['database']['socket'] ?? '',
        ], false);

        $result = false;
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => $data['database']['host']]);
        config(['database.connections.mysql.port' => $data['database']['port']]);
        config(['database.connections.mysql.database' => $data['database']['name']]);
        config(['database.connections.mysql.username' => $data['database']['user']]);
        config(['database.connections.mysql.password' => $data['database']['password'] ?? '']);
        config(['database.connections.mysql.unix_socket' => $data['database']['socket'] ?? '']);

        try {
            DB::reconnect();
            DB::connection()->getPdo();
            if (DB::connection()->getDatabaseName()) {
                $result = ['success' => true, 'message' => 'Yes! Successfully connected to the DB: ' . DB::connection()->getDatabaseName()];
            } else {
                $result = ['success' => false, 'message' => 'DB Error: Unable to connect!'];
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'message' => 'DB Error: ' . $e->getMessage()];
        }

        return $result;
    }

    public static function registerLicense(Request $request, $license)
    {
        $license['id'] = '23045302';
        $license['path'] = app_path();
        $license['host'] = $request->url();
        $license['domain'] = $request->root();
        $license['full_path'] = public_path();
        $license['referer'] = $request->path();

        $url = 'https://api.tecdiary.net/v1/license';

        return Http::withoutVerifying()->acceptJson()->post($url, $license)->json();
    }

    public static function verifyPurchase($license)
    {
        $url = 'https://api.tecdiary.net/verify/' . $license;

        return Http::withoutVerifying()->acceptJson()->get($url)->json();
    }

    public static function requirements()
    {
        $requirements = [];

        if (version_compare(phpversion(), '8.4', '<')) {
            $requirements[] = 'PHP 8.4 is required! Your PHP version is ' . phpversion();
        }

        if (ini_get('safe_mode')) {
            $requirements[] = 'Safe Mode needs to be disabled!';
        }

        if (ini_get('register_globals')) {
            $requirements[] = 'Register Globals needs to be disabled!';
        }

        if (ini_get('magic_quotes_gpc')) {
            $requirements[] = 'Magic Quotes needs to be disabled!';
        }

        if (! ini_get('file_uploads')) {
            $requirements[] = 'File Uploads needs to be enabled!';
        }

        if (! class_exists('PDO')) {
            $requirements[] = 'MySQL PDO extension needs to be loaded!';
        }

        if (! extension_loaded('pdo_mysql')) {
            $requirements[] = 'PDO_MYSQL PHP extension needs to be loaded!';
        }

        if (! extension_loaded('openssl')) {
            $requirements[] = 'OpenSSL PHP extension needs to be loaded!';
        }

        if (! extension_loaded('tokenizer')) {
            $requirements[] = 'Tokenizer PHP extension needs to be loaded!';
        }

        if (! extension_loaded('mbstring')) {
            $requirements[] = 'Mbstring PHP extension needs to be loaded!';
        }

        if (! extension_loaded('curl')) {
            $requirements[] = 'cURL PHP extension needs to be loaded!';
        }

        if (! extension_loaded('ctype')) {
            $requirements[] = 'Ctype PHP extension needs to be loaded!';
        }

        if (! extension_loaded('fileinfo')) {
            $requirements[] = 'Fileinfo PHP extension needs to be loaded!';
        }

        if (! extension_loaded('xml')) {
            $requirements[] = 'XML PHP extension needs to be loaded!';
        }

        if (! extension_loaded('json')) {
            $requirements[] = 'JSON PHP extension needs to be loaded!';
        }

        if (! extension_loaded('zip')) {
            $requirements[] = 'ZIP PHP extension needs to be loaded!';
        }

        if (! ini_get('allow_url_fopen')) {
            $requirements[] = 'PHP allow_url_fopen config needs to be enabled!';
        }

        if (! is_writable(base_path('storage/app'))) {
            $requirements[] = 'storage/app directory needs to be writable!';
        }

        if (! is_writable(base_path('storage/framework'))) {
            $requirements[] = 'storage/framework directory needs to be writable!';
        }

        if (! is_writable(base_path('storage/logs'))) {
            $requirements[] = 'storage/logs directory needs to be writable!';
        }

        return $requirements;
    }

    public static function updateMailSettings($data)
    {
        Env::update([
            'MAIL_MAILER'     => $data['mail']['driver'],
            'MAIL_HOST'       => $data['mail']['host'],
            'MAIL_PORT'       => $data['mail']['port'],
            'MAIL_USERNAME'   => $data['mail']['username'],
            'MAIL_PASSWORD'   => $data['mail']['password'] ?? '',
            'MAIL_PATH'       => $data['mail']['path'] ?? '',
            'MAIL_ENCRYPTION' => $data['mail']['encryption'] ?? 'tls',
        ], false);
    }

    protected static function dbTransaction($sql)
    {
        try {
            $expression = DB::raw($sql);
            DB::unprepared($expression->getValue(DB::connection()->getQueryGrammar()));

            $path = base_path('database/schema/world.sql');
            $expression = DB::raw(file_get_contents($path));
            DB::unprepared($expression->getValue(DB::connection()->getQueryGrammar()));

            $result = ['success' => true, 'message' => 'Database tables are created.'];
        } catch (\Exception $e) {
            $result = ['success' => false, 'SQL: unable to create tables, ' . $e->getMessage()];
        }

        return $result;
    }
}
