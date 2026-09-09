<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\StorefrontLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class StripStorefrontLocalePrefix
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('cpanel', 'cpanel/*', 'livewire/*', 'webhooks/*', 'up')) {
            return $next($request);
        }

        $segment = $request->segment(1);
        $available = StorefrontLocale::available();
        $default = StorefrontLocale::default();

        if (! is_string($segment) || ! in_array($segment, $available, true)) {
            return $next($request);
        }

        if ($segment === $default) {
            $segments = $request->segments();
            array_shift($segments);
            $target = '/'.implode('/', $segments);
            $query = $request->getQueryString();

            if ($target === '/') {
                $target = '/';
            }

            return redirect($query ? $target.'?'.$query : $target, 301);
        }

        $this->rewriteWithoutLocalePrefix($request, $segment);

        return $next($request);
    }

    private function rewriteWithoutLocalePrefix(Request $request, string $locale): void
    {
        $path = $request->getPathInfo();
        $prefix = '/'.$locale;
        $stripped = $path === $prefix || $path === $prefix.'/'
            ? '/'
            : substr($path, strlen($prefix));

        if (! is_string($stripped) || $stripped === '') {
            $stripped = '/';
        }

        $query = $request->getQueryString();
        $requestUri = $stripped.($query ? '?'.$query : '');
        $server = $request->server->all();
        unset($server['PATH_INFO']);
        $server['REQUEST_URI'] = $request->getBaseUrl().$requestUri;

        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            [...$request->attributes->all(), 'storefront_locale' => $locale],
            $request->cookies->all(),
            $request->files->all(),
            $server,
            $request->getContent(),
        );

        app()->instance('request', $request);
    }
}
