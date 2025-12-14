<?php

namespace App\Http\Controllers;

use App\Models\BillingPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BillingPeriodExport;

class ExportController extends Controller
{
    /**
     * Export a billing period to CSV.
     */
    public function exportCsv(BillingPeriod $billingPeriod)
    {
        $this->authorize('view', $billingPeriod);
        
        $billingPeriod->load(['teachingSessions', 'expenses', 'user']);
        
        return Excel::download(
            new BillingPeriodExport($billingPeriod),
            'billing-period-' . $billingPeriod->year . '-' . str_pad($billingPeriod->month, 2, '0', STR_PAD_LEFT) . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    /**
     * Export a billing period to XLSX.
     */
    public function exportXlsx(BillingPeriod $billingPeriod)
    {
        $this->authorize('view', $billingPeriod);
        
        $billingPeriod->load(['teachingSessions', 'expenses', 'user']);
        
        return Excel::download(
            new BillingPeriodExport($billingPeriod),
            'billing-period-' . $billingPeriod->year . '-' . str_pad($billingPeriod->month, 2, '0', STR_PAD_LEFT) . '.xlsx'
        );
    }
}
