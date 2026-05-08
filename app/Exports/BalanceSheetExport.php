<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceSheetExport implements FromArray, WithStyles, WithTitle
{
    public function __construct(
        private array $data,
        private Carbon $asOf
    ) {}

    public function title(): string
    {
        return 'Balance Sheet';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['EXPONENTIAL UNIVERSITY'];
        $rows[] = ['BALANCE SHEET'];
        $rows[] = ['As of: ' . $this->asOf->format('F d, Y')];
        $rows[] = [];

        $rows[] = ['ASSETS', ''];
        $rows[] = ['Current Assets', ''];
        $rows[] = ['  Cash & Bank Balances', number_format($this->data['cashInBank'], 2)];
        $rows[] = ['  Accounts Receivable', number_format($this->data['accountsReceivable'], 2)];
        $rows[] = ['  Total Current Assets', number_format($this->data['totalCurrentAssets'], 2)];
        $rows[] = [];
        $rows[] = ['Non-Current Assets', ''];
        $rows[] = ['  Staff Loans Receivable', number_format($this->data['staffLoansTotal'], 2)];
        $rows[] = ['  Total Non-Current Assets', number_format($this->data['totalNonCurrentAssets'], 2)];
        $rows[] = [];
        $rows[] = ['TOTAL ASSETS', number_format($this->data['totalAssets'], 2)];
        $rows[] = [];

        $rows[] = ['LIABILITIES & EQUITY', ''];
        $rows[] = ['Current Liabilities', ''];
        $rows[] = ['  Accounts Payable', number_format($this->data['accountsPayable'], 2)];
        $rows[] = ['  Total Liabilities', number_format($this->data['totalLiabilities'], 2)];
        $rows[] = [];
        $rows[] = ['Equity', ''];
        $rows[] = ['  Retained Earnings', number_format($this->data['retainedEarnings'], 2)];
        $rows[] = ['  Total Equity', number_format($this->data['totalEquity'], 2)];
        $rows[] = [];
        $rows[] = ['TOTAL LIABILITIES & EQUITY', number_format($this->data['totalLiabilitiesAndEquity'], 2)];
        $rows[] = [];
        $rows[] = ['Generated: ' . now()->format('F d, Y h:i A')];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
