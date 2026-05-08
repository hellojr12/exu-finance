<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #0f172a; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 12px; }
        .header h1 { color: #3b82f6; font-size: 16px; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .header h2 { font-size: 13px; margin: 4px 0 0; }
        .header p { margin: 2px 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; }
        .section-header td { background: #f8fafc; font-weight: bold; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; border-top: 1px solid #e2e8f0; }
        .total-row td { font-weight: bold; border-top: 2px solid #0f172a; }
        .net-row td { font-weight: bold; font-size: 13px; background: #f0fdf4; border-top: 3px double #0f172a; }
        .text-right { text-align: right; }
        .text-green { color: #10b981; }
        .text-red { color: #ef4444; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>Exponential University</h1>
    <h2>Income Statement</h2>
    <p>{{ $start->format('F d, Y') }} to {{ $end->format('F d, Y') }}</p>
</div>

<table>
    <tr><th style="width:65%">Description</th><th class="text-right">Amount (₱)</th></tr>

    <tr class="section-header"><td colspan="2">Revenue</td></tr>
    @foreach($data['revenues'] as $rev)
    <tr><td>&nbsp;&nbsp;&nbsp;{{ $rev->eventCategory?->name ?? 'Uncategorized' }}</td>
        <td class="text-right">{{ number_format($rev->total, 2) }}</td></tr>
    @endforeach
    <tr class="total-row">
        <td>Total Revenue</td>
        <td class="text-right text-green">{{ number_format($data['totalRevenue'], 2) }}</td>
    </tr>

    <tr><td colspan="2" style="padding:5px;"></td></tr>

    <tr class="section-header"><td colspan="2">Expenses</td></tr>
    @foreach($data['expenses'] as $exp)
    <tr><td>&nbsp;&nbsp;&nbsp;{{ $exp->expenseCategory?->name ?? 'Uncategorized' }}</td>
        <td class="text-right">{{ number_format($exp->total, 2) }}</td></tr>
    @endforeach
    <tr class="total-row">
        <td>Total Expenses</td>
        <td class="text-right text-red">{{ number_format($data['totalExpense'], 2) }}</td>
    </tr>

    <tr class="net-row">
        <td>Net Income / (Loss)</td>
        <td class="text-right {{ $data['netIncome'] >= 0 ? 'text-green' : 'text-red' }}">
            {{ $data['netIncome'] < 0 ? '(' : '' }}{{ number_format(abs($data['netIncome']), 2) }}{{ $data['netIncome'] < 0 ? ')' : '' }}
        </td>
    </tr>
</table>

<div class="footer">Generated on {{ now()->format('F d, Y h:i A') }} &bull; Exponential University Finance System</div>
</body>
</html>
