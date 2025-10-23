<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ReportsController
 *
 * Handles admin reporting features including trip history viewing and CSV export.
 * Authorization is enforced via route middleware (auth, can:view-reports).
 *
 * @package App\Http\Controllers
 */
class ReportsController extends Controller
{
    /**
     * Display paginated trip history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $trips = Trip::with(['user'])
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return view('admin.reports.index', compact('trips'));
    }

    /**
     * Export trip history as CSV.
     *
     * Streams CSV file with columns: trip_id, user_pseudonym, origin, destination,
     * started_at, ended_at, status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = 'trip-report-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Write CSV header
            fputcsv($file, [
                'Trip ID',
                'User Pseudonym',
                'Origin',
                'Destination',
                'Started At',
                'Ended At',
                'Status',
            ]);

            // Stream trips in chunks to avoid memory issues
            Trip::with('user')
                ->orderBy('started_at', 'desc')
                ->chunk(100, function ($trips) use ($file) {
                    foreach ($trips as $trip) {
                        fputcsv($file, [
                            $trip->id,
                            $trip->user->pseudonym ?? $trip->user->name ?? 'N/A',
                            $this->formatCoordinates($trip->origin_lat, $trip->origin_lng),
                            $this->formatCoordinates($trip->destination_lat, $trip->destination_lng),
                            $trip->started_at ? $trip->started_at->toDateTimeString() : 'N/A',
                            $trip->ended_at ? $trip->ended_at->toDateTimeString() : 'N/A',
                            $trip->status,
                        ]);
                    }
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Format coordinates for CSV output.
     *
     * @param  float|null  $lat
     * @param  float|null  $lng
     * @return string
     */
    protected function formatCoordinates($lat, $lng): string
    {
        if ($lat === null || $lng === null) {
            return 'N/A';
        }

        return sprintf('%s, %s', $lat, $lng);
    }
}
