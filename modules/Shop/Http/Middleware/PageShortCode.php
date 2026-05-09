<?php

namespace Modules\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PageShortCode
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (! method_exists($response, 'content')) {
            return $response;
        }

        $content = $response->content();
        $address = str($content)->before('] -->')->after('<!-- [map:');
        $contact = view('shop::components.shortcode.contact-form')->render();
        $map = view('shop::components.shortcode.map', compact('address'))->render();

        $content = str_replace('<!-- [map:' . $address . '] -->', $map, $content);
        $content = str_replace('<!-- [contact-form] -->', $contact, $content);
        $response->setContent($content);

        return $response;
    }
}
