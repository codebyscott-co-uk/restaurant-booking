<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\Billing\FeatureGate;
use App\Services\Reports\AnalyticsReport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportsController extends Controller
{
    public function index(Request $request, AnalyticsReport $analytics, FeatureGate $features): View
    {
        $venue = $this->currentVenue($request);
        $report = $analytics->build($venue, $request);

        return view('admin.reports.index', [
            'venue' => $venue,
            'report' => $report,
            'canUseAdvancedReports' => $features->canUse($venue, 'advanced_reporting'),
            'requiredPlan' => $features->requiredPlanFor('advanced_reporting'),
        ]);
    }

    public function export(Request $request, string $report, AnalyticsReport $analytics): StreamedResponse|Response
    {
        $venue = $this->currentVenue($request);
        abort_unless(in_array($report, ['bookings', 'covers', 'services', 'customers', 'operations'], true), 404);

        $data = $analytics->build($venue, $request);
        $filename = 'resora-'.$report.'-'.$data['range']['start']->format('Y-m-d').'-'.$data['range']['end']->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($report, $data): void {
            $handle = fopen('php://output', 'w');

            match ($report) {
                'bookings' => $this->writeBookingsCsv($handle, $data['bookings']),
                'covers' => $this->writeCoversCsv($handle, $data['bookingsByDay']),
                'services' => $this->writeServicesCsv($handle, $data['servicePerformance']),
                'customers' => $this->writeCustomersCsv($handle, $data['repeatCustomers']),
                'operations' => $this->writeOperationsCsv($handle, $data),
            };

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function writeBookingsCsv($handle, $bookings): void
    {
        fputcsv($handle, ['Reference', 'Date', 'Time', 'Guest', 'Email', 'Service', 'Party size', 'Status', 'Source']);

        $bookings->each(function (Booking $booking) use ($handle): void {
            fputcsv($handle, [
                $booking->booking_reference,
                $booking->starts_at->format('Y-m-d'),
                $booking->starts_at->format('H:i'),
                $booking->customer?->full_name,
                $booking->customer?->email,
                $booking->service?->name,
                $booking->party_size,
                $booking->status,
                $booking->source,
            ]);
        });
    }

    private function writeCoversCsv($handle, $days): void
    {
        fputcsv($handle, ['Day', 'Bookings', 'Covers']);

        $days->each(fn (array $day) => fputcsv($handle, [$day['label'], $day['bookings'], $day['covers']]));
    }

    private function writeServicesCsv($handle, $services): void
    {
        fputcsv($handle, ['Service', 'Bookings', 'Covers', 'Average party size', 'Cancelled', 'No-shows']);

        $services->each(fn (array $service) => fputcsv($handle, [
            $service['name'],
            $service['bookings'],
            $service['covers'],
            $service['average_party'],
            $service['cancelled'],
            $service['no_show'],
        ]));
    }

    private function writeCustomersCsv($handle, $customers): void
    {
        fputcsv($handle, ['Customer', 'Email', 'Total bookings']);

        $customers->each(fn (array $customer) => fputcsv($handle, [
            $customer['name'],
            $customer['email'],
            $customer['bookings'],
        ]));
    }

    private function writeOperationsCsv($handle, array $data): void
    {
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total bookings', $data['metrics']['total_bookings']]);
        fputcsv($handle, ['Covers', $data['metrics']['covers']]);
        fputcsv($handle, ['Average party size', $data['metrics']['average_party_size']]);
        fputcsv($handle, ['Cancellation rate', $data['metrics']['cancellation_rate'].'%']);
        fputcsv($handle, ['No-show rate', $data['metrics']['no_show_rate'].'%']);
        fputcsv($handle, ['Table utilisation', $data['metrics']['table_utilisation'].'%']);
        fputcsv($handle, ['Repeat visit rate', $data['metrics']['repeat_visit_rate'].'%']);
        fputcsv($handle, ['Forecast next 7 days', $data['metrics']['forecast_covers'] ?? 'Not enough data']);
    }
}
