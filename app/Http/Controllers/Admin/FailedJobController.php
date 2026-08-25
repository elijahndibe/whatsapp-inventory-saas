<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FailedJobController extends Controller
{
    public function index(): View
    {
        $failedJobs = DB::table('failed_jobs')->orderByDesc('id')->paginate(25);

        return view('admin.failed-jobs.index', compact('failedJobs'));
    }

    public function retry(string $uuid): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('status', "Job {$uuid} queued for retry.");
    }

    public function destroy(string $uuid): RedirectResponse
    {
        Artisan::call('queue:forget', ['id' => $uuid]);

        return back()->with('status', "Job {$uuid} deleted.");
    }
}
