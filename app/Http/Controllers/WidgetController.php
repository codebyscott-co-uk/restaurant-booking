<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WidgetController extends Controller
{
    public function show(?Venue $venue = null): View
    {
        $venue = $venue ?: Venue::firstOrFail();

        abort_unless($venue->widget_enabled, 404);

        return view('widget.bookings', [
            'venue' => $venue,
            'apiBase' => request()->route('venue') ? url('/api/v1/'.$venue->slug) : url('/api/v1'),
        ]);
    }

    public function script(?Venue $venue = null): Response
    {
        $url = $venue ? route('tenant.widget.bookings', $venue) : route('widget.bookings');
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
  iframe.title = 'Resora OS booking widget';
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
