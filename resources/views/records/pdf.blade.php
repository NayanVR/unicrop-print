<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Statement</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }

        .page { max-width: 900px; margin: 0 auto; padding: 32px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0; }
        .company-name { font-size: 24px; font-weight: 900; color: #0f4023; letter-spacing: -0.5px; }
        .company-sub { font-size: 11px; color: #64748b; margin-top: 2px; }
        .statement-label { text-align: right; }
        .statement-label h2 { font-size: 18px; font-weight: 700; color: #1e293b; }
        .statement-label p { font-size: 11px; color: #64748b; margin-top: 3px; }

        .period-bar { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; display: flex; gap: 32px; }
        .period-bar span { font-size: 11px; color: #64748b; }
        .period-bar strong { color: #1e293b; }

        .summary-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 28px; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; text-align: center; }
        .card-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; }
        .card-value { font-size: 20px; font-weight: 800; margin-top: 4px; color: #1e293b; }
        .card.gst .card-value { color: #d97706; }
        .card.total { background: #f0fdf4; border-color: #bbf7d0; }
        .card.total .card-value { color: #16a34a; }

        h3 { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 11px; }
        thead { background: #f8fafc; }
        th { padding: 8px 10px; text-align: left; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        th.right, td.right { text-align: right; }
        td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tr:last-child td { border-bottom: none; }
        tfoot td { font-weight: 700; background: #f8fafc; border-top: 2px solid #cbd5e1; }
        .gst-cell { color: #d97706; }
        .total-cell { color: #16a34a; font-weight: 700; }

        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="page">

        {{-- Print button (hidden on print) --}}
        <div class="no-print" style="text-align:right; margin-bottom:16px;">
            <button onclick="window.print()" style="background:#ef4444;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;">
                🖨 Print / Save as PDF
            </button>
            <button onclick="window.close()" style="background:#e2e8f0;color:#334155;border:none;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;margin-left:8px;">
                Close
            </button>
        </div>

        <div class="header">
            <div>
                <div class="company-name">Unicrop Print</div>
                <div class="company-sub">Print & Cutting Services</div>
            </div>
            <div class="statement-label">
                <h2>Billing Statement</h2>
                <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
                @if ($month !== 'all' || $year !== 'all')
                    <p style="margin-top:3px; font-weight:600; color:#1e293b;">
                        Period:
                        @if ($month !== 'all')
                            {{ \Carbon\Carbon::create(null, $month)->format('F') }}
                        @endif
                        {{ $year !== 'all' ? $year : 'All Years' }}
                    </p>
                @endif
            </div>
        </div>

        <div class="period-bar">
            <span>Period: <strong>
                @if ($month !== 'all') {{ \Carbon\Carbon::create(null, $month)->format('F') }} @endif
                {{ $year !== 'all' ? $year : 'All Years' }}
            </strong></span>
            <span>Total Jobs: <strong>{{ $jobs->count() }}</strong></span>
            <span>Subtotal: <strong>{{ number_format($totalRevenue, 2) }} Rs</strong></span>
            <span>GST ({{ $gstRate }}%): <strong>{{ number_format($gst, 2) }} Rs</strong></span>
            <span>Grand Total: <strong>{{ number_format($grandTotal, 2) }} Rs</strong></span>
        </div>

        {{-- Summary Cards --}}
        <div class="summary-cards">
            <div class="card">
                <div class="card-label">Total Jobs</div>
                <div class="card-value">{{ $jobs->count() }}</div>
            </div>
            <div class="card">
                <div class="card-label">Subtotal (ex-GST)</div>
                <div class="card-value">{{ number_format($totalRevenue, 2) }}</div>
            </div>
            <div class="card gst">
                <div class="card-label">GST {{ $gstRate }}%</div>
                <div class="card-value">{{ number_format($gst, 2) }}</div>
            </div>
            <div class="card total">
                <div class="card-label">Grand Total (inc. GST)</div>
                <div class="card-value">{{ number_format($grandTotal, 2) }}</div>
            </div>
        </div>

        {{-- Daily Summary --}}
        @if ($dailySummary->count() > 0)
            <h3>Daily Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="right">Jobs</th>
                        <th class="right">Subtotal (Rs)</th>
                        <th class="right">GST {{ $gstRate }}% (Rs)</th>
                        <th class="right">Total incl. GST (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dailySummary as $row)
                        @php $dg = round($row->subtotal * ($gstRate / 100), 2); @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}</td>
                            <td class="right">{{ $row->job_count }}</td>
                            <td class="right">{{ number_format($row->subtotal, 2) }}</td>
                            <td class="right gst-cell">{{ number_format($dg, 2) }}</td>
                            <td class="right total-cell">{{ number_format($row->subtotal + $dg, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Grand Total</td>
                        <td class="right">{{ $jobs->count() }}</td>
                        <td class="right">{{ number_format($totalRevenue, 2) }}</td>
                        <td class="right gst-cell">{{ number_format($gst, 2) }}</td>
                        <td class="right total-cell">{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        {{-- Job List --}}
        <h3>Job Details</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Note</th>
                    <th>Station</th>
                    <th class="right">Print (Rs)</th>
                    <th class="right">Cut (Rs)</th>
                    <th class="right">Subtotal (Rs)</th>
                    <th class="right">GST (Rs)</th>
                    <th class="right">Total (Rs)</th>
                    <th>Dispatched</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    @php $jg = round($job->total_amount * ($gstRate / 100), 2); @endphp
                    <tr>
                        <td>#{{ $job->id }}</td>
                        <td>{{ $job->note ?: '—' }}</td>
                        <td>{{ $job->printStation?->name ?? '—' }}</td>
                        <td class="right">{{ number_format($job->print_total, 2) }}</td>
                        <td class="right">{{ $job->cutting_total ? number_format($job->cutting_total, 2) : '—' }}</td>
                        <td class="right">{{ number_format($job->total_amount, 2) }}</td>
                        <td class="right gst-cell">{{ number_format($jg, 2) }}</td>
                        <td class="right total-cell">{{ number_format($job->total_amount + $jg, 2) }}</td>
                        <td>{{ $job->dispatched_at?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:20px;">No completed jobs.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">Grand Total</td>
                    <td class="right">{{ number_format($totalRevenue, 2) }}</td>
                    <td class="right gst-cell">{{ number_format($gst, 2) }}</td>
                    <td class="right total-cell">{{ number_format($grandTotal, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <span>Unicrop Print — Billing Statement</span>
            <span>GST Rate: {{ $gstRate }}% &bull; All amounts in INR (Rs)</span>
            <span>Page generated: {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</body>
</html>
