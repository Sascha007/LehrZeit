<?php

namespace App\Http\Controllers;

use App\Models\BillingPeriod;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }

    /**
     * Show admin dashboard with statistics.
     */
    public function dashboard()
    {
        $stats = [
            'total_lecturers' => User::where('role', 'lecturer')->count(),
            'pending_submissions' => BillingPeriod::where('status', 'SUBMITTED')->count(),
            'approved_periods' => BillingPeriod::where('status', 'APPROVED')->count(),
            'exported_periods' => BillingPeriod::where('status', 'EXPORTED')->count(),
        ];
        
        $recentSubmissions = BillingPeriod::with('user')
            ->where('status', 'SUBMITTED')
            ->orderBy('submitted_at', 'desc')
            ->take(10)
            ->get();
        
        return view('admin.dashboard', compact('stats', 'recentSubmissions'));
    }

    /**
     * Approve a billing period.
     */
    public function approve(BillingPeriod $billingPeriod)
    {
        $this->authorize('approve', $billingPeriod);
        
        $billingPeriod->update([
            'status' => 'APPROVED',
            'approved_at' => now(),
        ]);
        
        $billingPeriod->customAuditLog('approved');
        
        return back()->with('success', 'Billing period approved successfully.');
    }

    /**
     * Reopen a billing period.
     */
    public function reopen(BillingPeriod $billingPeriod)
    {
        $this->authorize('reopen', $billingPeriod);
        
        $billingPeriod->update([
            'status' => 'OPEN',
            'submitted_at' => null,
            'approved_at' => null,
        ]);
        
        $billingPeriod->customAuditLog('reopened');
        
        return back()->with('success', 'Billing period reopened successfully.');
    }

    /**
     * Mark a billing period as exported.
     */
    public function markExported(BillingPeriod $billingPeriod)
    {
        $this->authorize('export', $billingPeriod);
        
        $billingPeriod->update([
            'status' => 'EXPORTED',
            'exported_at' => now(),
        ]);
        
        $billingPeriod->customAuditLog('exported');
        
        return back()->with('success', 'Billing period marked as exported.');
    }
}
