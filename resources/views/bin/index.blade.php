<x-app-layout>
    <x-slot name="header">Bin</x-slot>

    <div class="mb-6 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h2 style="font-family:'Bebas Neue',sans-serif;font-size:48px;letter-spacing:0.06em;color:#111;line-height:1;display:flex;align-items:center;gap:12px;">
                Bin
                @if ($jobs->isNotEmpty())
                    <span style="font-family:'DM Sans',sans-serif;font-size:14px;font-weight:700;background:#EF4444;color:#fff;padding:3px 10px;border-radius:999px;letter-spacing:0;">{{ $jobs->count() }}</span>
                @endif
            </h2>
            <p style="font-size:13px;color:#717171;margin-top:4px;">Deleted jobs — restore them or let them auto-purge.</p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <form method="POST" action="{{ route('bin.days') }}" style="display:flex;align-items:center;gap:8px;background:#fff;border:1.5px solid #E5E5E5;border-radius:10px;padding:8px 14px;">
                @csrf @method('PATCH')
                <i class="fa-solid fa-clock" style="color:#A0A0A0;font-size:13px;"></i>
                <label style="font-size:13px;color:#555;font-weight:500;">Auto-delete after</label>
                <input type="number" name="bin_days" value="{{ $binDays }}" min="1" max="365"
                    style="width:52px;text-align:center;font-weight:700;border:1.5px solid #E5E5E5;border-radius:7px;padding:4px 6px;font-size:13px;">
                <span style="font-size:13px;color:#555;">days</span>
                <button type="submit" style="background:#111;color:#fff;border:none;padding:6px 14px;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;">Save</button>
            </form>

            @if ($jobs->isNotEmpty())
                <form method="POST" action="{{ route('bin.purge') }}"
                    onsubmit="return confirm('Purge all jobs older than {{ $binDays }} days? This cannot be undone.')">
                    @csrf
                    <button type="submit" style="background:#EF4444;color:#fff;border:none;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;">
                        <i class="fa-solid fa-fire"></i> Purge Expired ({{ $jobs->where('deleted_at', '<=', now()->subDays($binDays))->count() }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($jobs->isEmpty())
        <div style="background:#fff;border:1.5px dashed #E5E5E5;border-radius:14px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:80px 20px;text-align:center;">
            <div style="width:60px;height:60px;background:#F5F5F3;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                <i class="fa-solid fa-trash-can" style="font-size:24px;color:#D0D0D0;"></i>
            </div>
            <p style="font-weight:600;color:#555;font-size:14px;">Bin is empty</p>
            <p style="font-size:13px;color:#A0A0A0;margin-top:4px;">Deleted jobs will appear here.</p>
        </div>
    @else
        @php
            $statusConfig = [
                'pending'   => ['label' => 'Pending',   'bg' => '#FFF7ED', 'color' => '#C2410C'],
                'cutting'   => ['label' => 'Cutting',   'bg' => '#FAF5FF', 'color' => '#7E22CE'],
                'dispatch'  => ['label' => 'Dispatch',  'bg' => '#F0F9FF', 'color' => '#0369A1'],
                'completed' => ['label' => 'Completed', 'bg' => '#F0FDF4', 'color' => '#15803D'],
            ];
        @endphp

        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach ($jobs as $job)
                @php
                    $st = $statusConfig[$job->status->value] ?? ['label' => $job->status->value, 'bg' => '#F5F5F3', 'color' => '#717171'];
                    $expired = $job->deleted_at->lte(now()->subDays($binDays));
                    $daysLeft = max(0, $binDays - (int) $job->deleted_at->diffInDays(now()));
                @endphp
                <div style="background:{{ $expired ? '#FFF5F5' : '#fff' }};border:1.5px solid {{ $expired ? '#FECACA' : '#E5E5E5' }};border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:14px;">

                    {{-- Thumb --}}
                    @if ($job->fileUrl() && str_contains($job->mime_type ?? '', 'pdf'))
                        <div style="width:48px;height:48px;flex-shrink:0;border-radius:9px;background:#FFF0F0;border:1px solid #FECACA;display:flex;align-items:center;justify-content:center;color:#EF4444;">
                            <i class="fa-solid fa-file-pdf" style="font-size:20px;"></i>
                        </div>
                    @elseif ($job->fileUrl() && str_contains($job->mime_type ?? '', 'image'))
                        <img src="{{ $job->fileUrl() }}" alt="{{ $job->file_name }}"
                            style="width:48px;height:48px;flex-shrink:0;border-radius:9px;object-fit:cover;border:1px solid #E5E5E5;">
                    @else
                        <div style="width:48px;height:48px;flex-shrink:0;border-radius:9px;background:#F5F5F3;border:1px solid #E5E5E5;display:flex;align-items:center;justify-content:center;color:#CCC;">
                            <i class="fa-solid fa-file" style="font-size:20px;"></i>
                        </div>
                    @endif

                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-bottom:3px;">
                            <span style="font-weight:700;font-size:13.5px;color:#111;">#{{ $job->id }}</span>
                            <span style="background:{{ $st['bg'] }};color:{{ $st['color'] }};font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.06em;">{{ $st['label'] }}</span>
                            <span style="background:#F5F5F3;color:#555;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:5px;border:1px solid #E5E5E5;">{{ $job->printStation?->name ?? '—' }}</span>
                            <span style="background:#F5F5F3;color:#777;font-size:10.5px;padding:2px 8px;border-radius:5px;border:1px solid #E5E5E5;">{{ $job->size?->name ?? '—' }}</span>
                        </div>
                        <div style="font-size:12px;color:#717171;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $job->file_name }} · {{ $job->uploader?->name ?? '—' }}</div>
                        <div style="display:flex;align-items:center;gap:12px;margin-top:2px;">
                            <span style="font-size:11.5px;color:#A0A0A0;">Deleted {{ $job->deleted_at->diffForHumans() }}</span>
                            @if ($expired)
                                <span style="font-size:11.5px;font-weight:700;color:#EF4444;display:flex;align-items:center;gap:4px;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Expired
                                </span>
                            @else
                                <span style="font-size:11.5px;color:#A0A0A0;">Auto-delete in <strong style="color:#555;">{{ $daysLeft }}d</strong></span>
                            @endif
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                        <form method="POST" action="{{ route('bin.restore', $job) }}">
                            @csrf @method('PATCH')
                            <button type="submit" style="background:#111;color:#fff;border:none;padding:7px 14px;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-rotate-left"></i> Restore
                            </button>
                        </form>
                        <form method="POST" action="{{ route('bin.destroy', $job) }}"
                            onsubmit="return confirm('Permanently delete Job #{{ $job->id }}? Cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:#EF4444;color:#fff;border:none;padding:7px 14px;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
