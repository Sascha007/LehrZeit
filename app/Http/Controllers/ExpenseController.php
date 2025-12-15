<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\BillingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Expense::class);
        
        $billingPeriodId = $request->input('billing_period_id');
        $billingPeriod = BillingPeriod::findOrFail($billingPeriodId);
        
        $this->authorize('update', $billingPeriod);
        
        return view('expenses.create', compact('billingPeriod'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Expense::class);
        
        $validated = $request->validate([
            'billing_period_id' => 'required|exists:billing_periods,id',
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);
        
        $billingPeriod = BillingPeriod::findOrFail($validated['billing_period_id']);
        $this->authorize('update', $billingPeriod);
        
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts');
        }
        
        Expense::create([
            'billing_period_id' => $validated['billing_period_id'],
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'receipt_path' => $receiptPath,
        ]);
        
        return redirect()->route('billing-periods.show', $billingPeriod)
            ->with('success', 'Expense added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $this->authorize('update', $expense);
        
        $billingPeriod = $expense->billingPeriod;
        
        return view('expenses.edit', compact('expense', 'billingPeriod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense);
        
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        
        $data = [
            'date' => $validated['date'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
        ];
        
        if ($request->hasFile('receipt')) {
            // Delete old receipt
            if ($expense->receipt_path) {
                Storage::delete($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('receipts');
        }
        
        $expense->update($data);
        
        return redirect()->route('billing-periods.show', $expense->billing_period_id)
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);
        
        $billingPeriodId = $expense->billing_period_id;
        
        // Delete receipt file
        if ($expense->receipt_path) {
            Storage::delete($expense->receipt_path);
        }
        
        $expense->delete();
        
        return redirect()->route('billing-periods.show', $billingPeriodId)
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Download receipt file.
     */
    public function downloadReceipt(Expense $expense)
    {
        $this->authorize('view', $expense);
        
        if (!$expense->receipt_path || !Storage::exists($expense->receipt_path)) {
            abort(404);
        }
        
        return Storage::download($expense->receipt_path);
    }
}
