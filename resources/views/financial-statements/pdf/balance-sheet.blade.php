<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #0f172a; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #10b981; padding-bottom: 12px; }
        .header h1 { color: #10b981; font-size: 16px; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .header h2 { font-size: 13px; margin: 4px 0 0; }
        .header p { margin: 2px 0; color: #64748b; }
        .cols { display: flex; gap: 20px; }
        .col { flex: 1; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 5px 10px; border-bottom: 1px solid #f1f5f9; }
        .section-header { background: #f8fafc; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .subtotal { font-weight: bold; border-top: 1px solid #dee2e6; }
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
    <h2>Balance Sheet</h2>
    <p>As of {{ $asOf->format('F d, Y') }}</p>
</div>

<table>
    <tr>
        <th style="width:50%">ASSETS</th>
        <th class="text-right">Amount (₱)</th>
        <th style="width:5%;"></th>
        <th style="width:30%">LIABILITIES & EQUITY</th>
        <th class="text-right">Amount (₱)</th>
    </tr>
    <tr>
        <td class="section-header">Current Assets</td><td></td>
        <td></td>
        <td class="section-header">Liabilities</td><td></td>
    </tr>
    <tr>
        <td>&nbsp;&nbsp;Cash & Bank Balances</td><td class="text-right">{{ number_format($data['cashInBank'], 2) }}</td>
        <td></td>
        <td>&nbsp;&nbsp;Accounts Payable</td><td class="text-right text-red">{{ number_format($data['accountsPayable'], 2) }}</td>
    </tr>
    <tr>
        <td>&nbsp;&nbsp;Accounts Receivable</td><td class="text-right">{{ number_format($data['accountsReceivable'], 2) }}</td>
        <td></td>
        <td class="subtotal">Total Liabilities</td><td class="text-right text-red subtotal">{{ number_format($data['totalLiabilities'], 2) }}</td>
    </tr>
    <tr>
        <td class="subtotal">Total Current Assets</td><td class="text-right subtotal">{{ number_format($data['totalCurrentAssets'], 2) }}</td>
        <td></td>
        <td class="section-header">Equity</td><td></td>
    </tr>
    <tr>
        <td class="section-header">Non-Current Assets</td><td></td>
        <td></td>
        <td>&nbsp;&nbsp;Retained Earnings</td><td class="text-right {{ $data['retainedEarnings']>=0?'text-green':'text-red' }}">{{ number_format($data['retainedEarnings'], 2) }}</td>
    </tr>
    <tr>
        <td>&nbsp;&nbsp;Staff Loans Receivable</td><td class="text-right">{{ number_format($data['staffLoansTotal'], 2) }}</td>
        <td></td>
        <td class="subtotal">Total Equity</td><td class="text-right subtotal">{{ number_format($data['totalEquity'], 2) }}</td>
    </tr>
    <tr>
        <td class="subtotal">Total Non-Current Assets</td><td class="text-right subtotal">{{ number_format($data['totalNonCurrentAssets'], 2) }}</td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td class="total">TOTAL ASSETS</td><td class="text-right total text-green">{{ number_format($data['totalAssets'], 2) }}</td>
        <td></td>
        <td class="total">TOTAL LIABILITIES & EQUITY</td><td class="text-right total text-green">{{ number_format($data['totalLiabilitiesAndEquity'], 2) }}</td>
    </tr>
</table>

<div class="footer">Generated on {{ now()->format('F d, Y h:i A') }} &bull; Exponential University Finance System</div>
</body>
</html>
