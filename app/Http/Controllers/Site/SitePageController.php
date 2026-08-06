<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class SitePageController extends Controller
{
    public function home(): View
    {
        return view('site.home');
    }
}
