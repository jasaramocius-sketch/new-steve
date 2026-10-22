{{-- Reusable order status history timeline.
     Usage: @include('partials.order-status-timeline', ['order' => $order])
--}}
@php
    $timeline = $order->getStatusHistory();
    $badgeMap = [
        'placed'    => ['bg' => '#6c757d', 'icon' => 'fa-shopping-cart'],
        'pending'   => ['bg' => '#f39c12', 'icon' => 'fa-hourglass-half'],
        'processing'=> ['bg' => '#17a2b8', 'icon' => 'fa-cogs'],
        'shipped'   => ['bg' => '#007bff', 'icon' => 'fa-truck'],
        'delivered' => ['bg' => '#28a745', 'icon' => 'fa-check-circle'],
        'cancelled' => ['bg' => '#dc3545', 'icon' => 'fa-times-circle'],
    ];
@endphp
@once
<style>
    .status-timeline { display: flex; flex-direction: column; gap: 0; position: relative; list-style: none; margin: 0; padding: 0; }
    .status-timeline .st-item { position: relative; padding: 0 0 1.5rem 2.25rem; }
    .status-timeline .st-item:last-child { padding-bottom: .25rem; }
    .status-timeline .st-item::before { content: ""; position: absolute; left: .65rem; top: 1.35rem; bottom: 0; width: 2px; background: #e2e6ea; }
    .status-timeline .st-item:last-child::before { display: none; }
    .status-timeline .st-dot { position: absolute; left: 0; top: .15rem; width: 1.35rem; height: 1.35rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .7rem; }
    .status-timeline .st-title { font-weight: 600; font-size: .95rem; color: #212529; display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .status-timeline .st-note { color: #6c757d; font-size: .85rem; margin: .15rem 0 0; }
    .status-timeline .st-time { color: #adb5bd; font-size: .8rem; margin-top: .15rem; }
</style>
@endonce
<ul class="status-timeline">
    @foreach($timeline as $entry)
        @php
            $entryStatus = $entry['status'] ?? 'pending';
            $entryBadge = $badgeMap[$entryStatus] ?? $badgeMap['pending'];
        @endphp
        <li class="st-item">
            <span class="st-dot" style="background: {{ $entryBadge['bg'] }};" aria-hidden="true">
                <i class="fas {{ $entryBadge['icon'] }}"></i>
            </span>
            <div class="st-title">
                {{ $entry['label'] ?? ucfirst($entryStatus) }}
            </div>
            @if(!empty($entry['note']))
            <p class="st-note">{{ $entry['note'] }}</p>
            @endif
            <div class="st-time">
                {{ \Illuminate\Support\Carbon::parse($entry['at'] ?? now())->format('M d, Y H:i') }}
            </div>
        </li>
    @endforeach
</ul>