<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeStatementExport implements FromArray, WithStyles, WithTitle
{
    public function __construct(
        private array $data,
        private Carbon $start,
        private Carbon $end
    ) {}

    public function title(): string
    {
        return 'Income Statement';
    }

    public function array(): array
    {
        $rows = [];

        // Header
        $rows[] = ['EXPONENTIAL UNIVERSITY'];
        $rows[] = ['INCOME STATEMENT'];
        $rows[] = ['For the period: ' . $this->start->format('F d, Y') . ' to ' . $this->end->format('F d, Y')];
        $rows[] = [];

        // Revenue
        $rows[] = ['REVENUE', ''];
        foreach ($this->data['revenues'] as $rev) {
            $rows[] = ['  ' . ($rev->eventCategory?->name ?? 'Uncategorized'), number_format($rev->total, 2)];
        }
        $rows[] = ['TOTAL REVENUE', number_format($this->data['totalRevenue'], 2)];
        $rows[] = [];

        // Expenses
        $rows[] = ['EXPENSES', ''];
        foreach ($this->data['expenses'] as $exp) {
            $rows[] = ['  ' . ($exp->expenseCategory?->name ?? 'Uncategorized'), number_format($exp->total, 2)];
        }
        $rows[] = ['TOTAL EXPENSES', number_format($this->data['totalExpense'], 2)];
        $rows[] = [];

        // Net
        $rows[] = ['NET INCOME / (LOSS)', number_format($this->data['netIncome'], 2)];
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
