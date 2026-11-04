<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The member app used to live under /new_frontend. This sends every old address to its new /user address for good
 * (301 for reads, 308 for writes so the method and body survive), keeping the query string.
 */
final class LegacyUrlRedirectController extends Controller
{
    public function __invoke(Request $request, ?string $path = null): RedirectResponse
    {
        $path = trim((string) $path, '/');
        $parts = explode('/', $path, 2);

        // the new wallet routes moved to /user/wallet-hub because /user/wallet* already belongs to the original wallet
        if ($parts[0] === 'wallet') {
            $path = 'wallet-hub'.(isset($parts[1]) ? '/'.$parts[1] : '');
        }

        $target = '/user'.($path !== '' ? '/'.$path : '');
        if (($query = $request->getQueryString()) !== null && $query !== '') {
            $target .= '?'.$query;
        }

        return redirect($target, $request->isMethodSafe() ? 301 : 308);
    }
}
