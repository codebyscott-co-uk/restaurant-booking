<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WidgetController extends Controller
{
    public function show(): View
    {
        $venue = Venue::firstOrFail();

        abort_unless($venue->widget_enabled, 404);

        return view('widget.bookings', ['venue' => $venue]);
    }

    public function script(): Response
    {
        $url = route('widget.bookings');
        $script = <<<JS
(function () {
  var currentScript = document.currentScript;
  var target = document.querySelector('[data-restaurant-booking-widget]');
  if (!target && currentScript) {
    target = document.createElement('div');
    currentScript.parentNode.insertBefore(target, currentScript);
  }
  if (!target) return;
  var iframe = document.createElement('iframe');
  iframe.src = '{$url}';
  iframe.title = 'Restaurant booking widget';
  iframe.loading = 'lazy';
  iframe.style.width = '100%';
  iframe.style.maxWidth = target.dataset.width || '520px';
  iframe.style.height = target.dataset.height || '760px';
  iframe.style.border = '0';
  iframe.style.borderRadius = '12px';
  iframe.style.overflow = 'hidden';
  target.appendChild(iframe);
})();
JS;

        return response($script, 200, ['Content-Type' => 'application/javascript']);
    }
}
