<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #0f172a; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #06b6d4; padding-bottom: 12px; }
        .header h1 { color: #06b6d4; font-size: 16px; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .header h2 { font-size: 13px; margin: 4px 0 0; }
        .header p { margin: 2px 0; color: #64748b; }
        table { width: 65%; margin: 0 auto; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        td { padding: 5px 10px; border-bottom: 1px solid #f1f5f9; }
        .section-header { background: #f8fafc; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .subtotal { font-weight: bold; border-top: 2px solid #e2e8f0; }
        .total { font-weight: bold; font-size: 12px; background: #f0fdf4; border-top: 3px double #0f172a; }
        .text-right { text-align: right; }
        .text-green { color: #10b981; }
        .text-red { color: #ef4444; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>Exponential University</h1>
    <h2>Statement of Cash Flows</h2>
    <p>{{ $start->format('F d, Y') }} to {{ $end->format('F d, Y') }}</p>
</div>

<table>
    <tr><th style="width:70%">Description</th><th class="text-right">Amount (₱)</th></tr>

    <tr><td colspan="2" class="section-header">Operating Activities</td></tr>
    <tr><td>&nbsp;&nbsp;Revenue Collected</td><td class="text-right text-green">{{ number_format($data['revenueCollected'], 2) }}</td></tr>
    <tr><td>&nbsp;&nbsp;AR Collections</td><td class="text-right text-green">{{ number_format($data['invoicePayments'], 2) }}</td></tr>
    <tr><td>&nbsp;&nbsp;Expenses Paid</td><td class="text-right text-red">({{ number_format($data['expensesPaid'], 2) }})</td></tr>
    <tr><td>&nbsp;&nbsp;AP Payments</td><td class="text-right text-red">({{ number_format($data['billPayments'], 2) }})</td></tr>
    <tr class="subtotal"><td>Net Cash from Operating</td><td class="text-right {{ $data['netOperating']>=0?'text-green':'text-red' }}">{{ $data['netOperating']<0?'(':'' }}{{ number_format(abs($data['netOperating']),2) }}{{ $data['netOperating']<0?')':'' }}</td></tr>

    <tr><td colspan="2" style="padding:5px;"></td></tr>

    <tr><td colspan="2" class="section-header">Investing Activities</td></tr>
    <tr><td>&nbsp;&nbsp;Staff Loans Disbursed</td><td class="text-right text-red">({{ number_format($data['loansDisbursed'], 2) }})</td></tr>
    <tr><td>&nbsp;&nbsp;Loan Repayments</td><td class="text-right text-green">{{ number_format($data['loanRepayments'], 2) }}</td></tr>
    <tr class="subtotal"><td>Net Cash from Investing</td><td class="text-right {{ $data['netInvesting']>=0?'text-green':'text-red' }}">{{ $data['netInvesting']<0?'(':'' }}{{ number_format(abs($data['netInvesting']),2) }}{{ $data['netInvesting']<0?')':'' }}</td></tr>

    <tr><td colspan="2" style="padding:5px;border-top:3px double #0f172a;"></td></tr>
    <tr class="total"><td>Net Change in Cash</td><td class="text-right {{ $data['netCashFlow']>=0?'text-green':'text-red' }}">{{ $data['netCashFlow']<0?'(':'' }}{{ number_format(abs($data['netCashFlow']),2) }}{{ $data['netCashFlow']<0?')':'' }}</td></tr>
    <tr><td>&nbsp;&nbsp;Opening Balance</td><td class="text-right">{{ number_format($data['openingCash'], 2) }}</td></tr>
    <tr class="total"><td>Ending Cash Balance</td><td class="text-right text-green">{{ number_format($data['endingCash'], 2) }}</td></tr>
</table>

<div class="footer">Generated on {{ now()->format('F d, Y h:i A') }} &bull; Exponential University Finance System</div>
</body>
</html>
