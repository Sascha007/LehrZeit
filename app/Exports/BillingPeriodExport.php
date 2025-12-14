<?php

namespace App\Exports;

use App\Models\BillingPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BillingPeriodExport implements WithMultipleSheets
{
    protected $billingPeriod;

    public function __construct(BillingPeriod $billingPeriod)
    {
        $this->billingPeriod = $billingPeriod;
    }

    public function sheets(): array
    {
        return [
            new BillingPeriodSummarySheet($this->billingPeriod),
            new TeachingSessionsSheet($this->billingPeriod),
            new ExpensesSheet($this->billingPeriod),
        ];
    }
}

class BillingPeriodSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    protected $billingPeriod;

    public function __construct(BillingPeriod $billingPeriod)
    {
        $this->billingPeriod = $billingPeriod;
    }

    public function collection()
    {
        return collect([
            [
                'Lecturer' => $this->billingPeriod->user->name,
                'Month' => $this->billingPeriod->month,
                'Year' => $this->billingPeriod->year,
                'Status' => $this->billingPeriod->status,
                'Total Hours' => $this->billingPeriod->total_hours,
                'Total Expenses' => $this->billingPeriod->total_expenses,
                'Submitted At' => $this->billingPeriod->submitted_at?->format('Y-m-d H:i:s'),
                'Approved At' => $this->billingPeriod->approved_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function headings(): array
    {
        return ['Lecturer', 'Month', 'Year', 'Status', 'Total Hours', 'Total Expenses', 'Submitted At', 'Approved At'];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

class TeachingSessionsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $billingPeriod;

    public function __construct(BillingPeriod $billingPeriod)
    {
        $this->billingPeriod = $billingPeriod;
    }

    public function collection()
    {
        return $this->billingPeriod->teachingSessions->map(function ($session) {
            return [
                'Date' => $session->date->format('Y-m-d'),
                'Start Time' => $session->start_time,
                'End Time' => $session->end_time,
                'Hours' => $session->hours,
                'Subject' => $session->subject,
                'Description' => $session->description,
                'Location' => $session->location,
            ];
        });
    }

    public function headings(): array
    {
        return ['Date', 'Start Time', 'End Time', 'Hours', 'Subject', 'Description', 'Location'];
    }

    public function title(): string
    {
        return 'Teaching Sessions';
    }
}

class ExpensesSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $billingPeriod;

    public function __construct(BillingPeriod $billingPeriod)
    {
        $this->billingPeriod = $billingPeriod;
    }

    public function collection()
    {
        return $this->billingPeriod->expenses->map(function ($expense) {
            return [
                'Date' => $expense->date->format('Y-m-d'),
                'Category' => $expense->category,
                'Description' => $expense->description,
                'Amount' => $expense->amount,
                'Has Receipt' => $expense->receipt_path ? 'Yes' : 'No',
            ];
        });
    }

    public function headings(): array
    {
        return ['Date', 'Category', 'Description', 'Amount', 'Has Receipt'];
    }

    public function title(): string
    {
        return 'Expenses';
    }
}
