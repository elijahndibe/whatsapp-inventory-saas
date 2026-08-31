<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Failed Jobs') }}</h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
        @if ($failedJobs->isEmpty())
            <x-empty-state icon="check-circle" :title="__('No failed jobs')" :description="__('Everything is running smoothly.')" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Job') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Queue') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Failed At') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($failedJobs as $job)
                            @php $payload = json_decode($job->payload, true); @endphp
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-mono text-xs">{{ $payload['displayName'] ?? 'Unknown' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $job->queue }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $job->failed_at }}</td>
                                <td class="px-4 py-3 text-right space-x-3">
                                    <form method="POST" action="{{ route('admin.failed-jobs.retry', $job->uuid) }}" class="inline">
                                        @csrf
                                        <button class="text-brand-600 dark:text-brand-400 hover:underline text-xs">{{ __('Retry') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.failed-jobs.destroy', $job->uuid) }}" class="inline" onsubmit="return confirm('{{ __('Delete this failed job?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 dark:text-red-400 hover:underline text-xs">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">{{ $failedJobs->links() }}</div>
</x-admin-layout>
