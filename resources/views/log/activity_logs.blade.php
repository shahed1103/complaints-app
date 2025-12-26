<div class="container mt-5">
    <h3 class="mb-4">Activity_Log</h3>
    <div class="table-responsive">
        <table class="table table-hover table-striped shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>name</th>
                    <th>Email</th>
                    <th>description</th>
                    <th>created Time </th>
                       <th>updated Time</th>
                    <th>Address IP</th>
                       <th>log name</th>

                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td><strong>{{ $log->user->first_name ?? 'System' }}</td>
                           <td><strong>{{ $log->user->last_name ?? 'System' }}</td>
                        <td><strong>{{ $log->user->email ?? 'System' }}</strong></td>
                    <td>{{ $log->description."_id"}}</td>
                    <td>{{ $log->created_at->diffForHumans()}}</td>
                    <td>{{ $log->updated_at->diffForHumans()}}</td>
                    <td>{{ $log->ip_address}}</td>
                    <td>{{ $log->log_name}}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">لا توجد سجلات حالياً.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
