<?php

namespace App\Http\Controllers;

use App\Models\TeachingSession;
use App\Models\BillingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeachingSessionController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', TeachingSession::class);
        
        $billingPeriodId = $request->input('billing_period_id');
        $billingPeriod = BillingPeriod::findOrFail($billingPeriodId);
        
        $this->authorize('update', $billingPeriod);
        
        return view('teaching-sessions.create', compact('billingPeriod'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', TeachingSession::class);
        
        $validated = $request->validate([
            'billing_period_id' => 'required|exists:billing_periods,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);
        
        $billingPeriod = BillingPeriod::findOrFail($validated['billing_period_id']);
        $this->authorize('update', $billingPeriod);
        
        // Calculate hours
        $start = Carbon::createFromFormat('H:i', $validated['start_time']);
        $end = Carbon::createFromFormat('H:i', $validated['end_time']);
        $hours = $end->diffInMinutes($start) / 60;
        
        TeachingSession::create([
            'billing_period_id' => $validated['billing_period_id'],
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'hours' => $hours,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'location' => $validated['location'],
        ]);
        
        return redirect()->route('billing-periods.show', $billingPeriod)
            ->with('success', 'Teaching session added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeachingSession $teachingSession)
    {
        $this->authorize('update', $teachingSession);
        
        $billingPeriod = $teachingSession->billingPeriod;
        
        return view('teaching-sessions.edit', compact('teachingSession', 'billingPeriod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeachingSession $teachingSession)
    {
        $this->authorize('update', $teachingSession);
        
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);
        
        // Calculate hours
        $start = Carbon::createFromFormat('H:i', $validated['start_time']);
        $end = Carbon::createFromFormat('H:i', $validated['end_time']);
        $hours = $end->diffInMinutes($start) / 60;
        
        $teachingSession->update([
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'hours' => $hours,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'location' => $validated['location'],
        ]);
        
        return redirect()->route('billing-periods.show', $teachingSession->billing_period_id)
            ->with('success', 'Teaching session updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeachingSession $teachingSession)
    {
        $this->authorize('delete', $teachingSession);
        
        $billingPeriodId = $teachingSession->billing_period_id;
        $teachingSession->delete();
        
        return redirect()->route('billing-periods.show', $billingPeriodId)
            ->with('success', 'Teaching session deleted successfully.');
    }
}
