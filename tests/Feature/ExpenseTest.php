<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BillingPeriod;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_create_expense_without_receipt(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('expenses.store'), [
            'billing_period_id' => $billingPeriod->id,
            'date' => '2024-12-15',
            'category' => 'Travel',
            'description' => 'Bus fare to campus',
            'amount' => 25.50,
        ]);
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $this->assertDatabaseHas('expenses', [
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
            'category' => 'Travel',
            'amount' => 25.50,
        ]);
    }

    public function test_lecturer_can_create_expense_with_receipt(): void
    {
        Storage::fake('local');
        
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $file = UploadedFile::fake()->create('receipt.pdf', 100);
        
        $response = $this->actingAs($lecturer)->post(route('expenses.store'), [
            'billing_period_id' => $billingPeriod->id,
            'date' => '2024-12-15',
            'category' => 'Materials',
            'description' => 'Office supplies',
            'amount' => 45.00,
            'receipt' => $file,
        ]);
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $expense = Expense::first();
        $this->assertNotNull($expense->receipt_path);
        Storage::disk('local')->assertExists($expense->receipt_path);
    }

    public function test_lecturer_can_update_expense(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        $expense = Expense::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
            'amount' => 100.00,
        ]);
        
        $response = $this->actingAs($lecturer)->put(route('expenses.update', $expense), [
            'date' => '2024-12-16',
            'category' => 'Books',
            'description' => 'Updated description',
            'amount' => 150.00,
        ]);
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $expense->refresh();
        $this->assertEquals(150.00, $expense->amount);
        $this->assertEquals('Books', $expense->category);
    }

    public function test_lecturer_can_update_receipt(): void
    {
        Storage::fake('local');
        
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $oldFile = UploadedFile::fake()->create('old-receipt.pdf', 100);
        $expense = Expense::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
            'receipt_path' => $oldFile->store('receipts'),
        ]);
        
        $oldPath = $expense->receipt_path;
        $newFile = UploadedFile::fake()->create('new-receipt.pdf', 100);
        
        $response = $this->actingAs($lecturer)->put(route('expenses.update', $expense), [
            'date' => '2024-12-16',
            'category' => 'Materials',
            'description' => 'Updated expense',
            'amount' => 50.00,
            'receipt' => $newFile,
        ]);
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $expense->refresh();
        $this->assertNotEquals($oldPath, $expense->receipt_path);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($expense->receipt_path);
    }

    public function test_lecturer_can_delete_expense(): void
    {
        Storage::fake('local');
        
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $file = UploadedFile::fake()->create('receipt.pdf', 100);
        $expense = Expense::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
            'receipt_path' => $file->store('receipts'),
        ]);
        
        $receiptPath = $expense->receipt_path;
        
        $response = $this->actingAs($lecturer)->delete(route('expenses.destroy', $expense));
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
        Storage::disk('local')->assertMissing($receiptPath);
    }

    public function test_lecturer_cannot_create_expense_in_submitted_period(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->submitted()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('expenses.store'), [
            'billing_period_id' => $billingPeriod->id,
            'date' => '2024-12-15',
            'category' => 'Travel',
            'description' => 'Bus fare',
            'amount' => 25.50,
        ]);
        
        $response->assertForbidden();
    }

    public function test_lecturer_cannot_access_other_lecturers_expenses(): void
    {
        $lecturer1 = User::factory()->lecturer()->create();
        $lecturer2 = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer2->id,
            'status' => 'OPEN',
        ]);
        $expense = Expense::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer2->id,
        ]);
        
        $response = $this->actingAs($lecturer1)->delete(route('expenses.destroy', $expense));
        
        $response->assertForbidden();
    }
}
