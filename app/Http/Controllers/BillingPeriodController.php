<?php

namespace App\Http\Controllers;

use App\Models\BillingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BillingPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $billingPeriods = BillingPeriod::with('user')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(15);
        } else {
            $billingPeriods = BillingPeriod::where('user_id', $user->id)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(15);
        }
        
        return view('billing-periods.index', compact('billingPeriods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', BillingPeriod::class);
        return view('billing-periods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', BillingPeriod::class);
        
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);
        
        // Check if billing period already exists
        $exists = BillingPeriod::where('user_id', Auth::id())
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['month' => 'Billing period for this month already exists.'])->withInput();
        }
        
        BillingPeriod::create([
            'user_id' => Auth::id(),
            'month' => $validated['month'],
            'year' => $validated['year'],
            'status' => 'OPEN',
        ]);
        
        return redirect()->route('billing-periods.index')
            ->with('success', 'Billing period created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BillingPeriod $billingPeriod)
    {
        $this->authorize('view', $billingPeriod);
        
        $billingPeriod->load(['teachingSessions', 'expenses']);
        
        return view('billing-periods.show', compact('billingPeriod'));
    }

    /**
     * Submit the billing period.
     */
    public function submit(BillingPeriod $billingPeriod)
    {
        $this->authorize('submit', $billingPeriod);
        
        $billingPeriod->update([
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);
        
        $billingPeriod->customAuditLog('submitted');
        
        return redirect()->route('billing-periods.show', $billingPeriod)
            ->with('success', 'Billing period submitted successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillingPeriod $billingPeriod)
    {
        $this->authorize('delete', $billingPeriod);
        
        $billingPeriod->delete();
        
        return redirect()->route('billing-periods.index')
            ->with('success', 'Billing period deleted successfully.');
    }
}
