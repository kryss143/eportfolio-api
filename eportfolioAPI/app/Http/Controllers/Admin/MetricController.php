<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Metric;
use App\Services\MockDataService;
use App\Support\MongoProbe;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    use HandlesMongoFallback;

    public function index()
    {
        if (! $this->isMongoAvailable()) {
            $metrics = collect(
                array_map(fn (array $item) => $this->toModel($item), MockDataService::get('metrics'))
            );

            return view('admin.metrics.index', compact('metrics'));
        }

        $metrics = Metric::all();

        return view('admin.metrics.index', compact('metrics'));
    }

    public function create()
    {
        return view('admin.metrics.create');
    }

    public function store(Request $request)
    {
        // Write-path guard: strict primary check (a reachable secondary does
        // not make inserts possible).
        if (! MongoProbe::writeAvailable()) {
            return redirect()->route('admin.metrics.index')
                ->with('error', 'MongoDB writes are unavailable right now (primary unreachable). Please try again shortly.');
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'integer', 'min:0'],
            'suffix' => ['nullable', 'string', 'max:10'],
            'metricDescription' => ['required', 'string'],
        ]);

        Metric::create($validated);

        return redirect()->route('admin.metrics.index')
            ->with('success', 'Metric created successfully.');
    }

    public function edit(Metric $metric)
    {
        return view('admin.metrics.edit', compact('metric'));
    }

    public function update(Request $request, Metric $metric)
    {
        if ($redirect = $this->denyWhenMongoDown('admin.metrics.index')) {
            return $redirect;
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'integer', 'min:0'],
            'suffix' => ['nullable', 'string', 'max:10'],
            'metricDescription' => ['required', 'string'],
        ]);

        $metric->update($validated);

        return redirect()->route('admin.metrics.index')
            ->with('success', 'Metric updated successfully.');
    }
}
