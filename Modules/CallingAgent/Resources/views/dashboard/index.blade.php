<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold">Calling Agent</h1>
        <p class="text-sm text-gray-500">Recent calls, SMS/WhatsApp handoffs, and front-desk activity.</p>
        <table class="mt-4 w-full"><thead><tr><th>SID</th><th>From</th><th>To</th><th>Status</th><th>Duration</th></tr></thead><tbody>@foreach($calls as $call)<tr><td>{{ $call->call_sid }}</td><td>{{ $call->from }}</td><td>{{ $call->to }}</td><td>{{ $call->status }}</td><td>{{ $call->duration }}</td></tr>@endforeach</tbody></table>
    </div>
</x-app-layout>
