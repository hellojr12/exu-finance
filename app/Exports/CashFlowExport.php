<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashFlowExport implements FromArray, WithStyles, WithTitle
{
    public function __construct(
        private array $data,
        private Carbon $start,
        private Carbon $end
    ) {}

    public function title(): string
    {
        return 'Cash Flow';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['EXPONENTIAL UNIVERSITY'];
        $rows[] = ['STATEMENT OF CASH FLOWS'];
        $rows[] = ['For the period: ' . $this->start->format('F d, Y') . ' to ' . $this->end->format('F d, Y')];
        $rows[] = [];

        $rows[] = ['OPERATING ACTIVITIES', ''];
        $rows[] = ['  Revenue Collected', number_format($this->data['revenueCollected'], 2)];
        $rows[] = ['  AR Collections', number_format($this->data['invoicePayments'], 2)];
        $rows[] = ['  Expenses Paid', '(' . number_format($this->data['expensesPaid'], 2) . ')'];
        $rows[] = ['  AP Payments (Bills)', '(' . number_format($this->data['billPayments'], 2) . ')'];
        $rows[] = ['Net Cash from Operating Activities', number_format($this->data['netOperating'], 2)];
        $rows[] = [];

        $rows[] = ['INVESTING ACTIVITIES', ''];
        $rows[] = ['  Staff Loans Disbursed', '(' . number_format($this->data['loansDisbursed'], 2) . ')'];
        $rows[] = ['  Loan Repayments Collected', number_format($this->data['loanRepayments'], 2)];
        $rows[] = ['Net Cash from Investing Activities', number_format($this->data['netInvesting'], 2)];
        $rows[] = [];

        $rows[] = ['Net Increase/(Decrease) in Cash', number_format($this->data['netCashFlow'], 2)];
        $rows[] = ['Opening Cash Balance', number_format($this->data['openingCash'], 2)];
        $rows[] = ['Ending Cash Balance', number_format($this->data['endingCash'], 2)];
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
