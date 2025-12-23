<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BillingPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_view_their_billing_periods(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create(['user_id' => $lecturer->id]);
        
        $response = $this->actingAs($lecturer)->get(route('billing-periods.index'));
        
        $response->assertOk();
        $response->assertSee($billingPeriod->month);
        $response->assertSee($billingPeriod->year);
    }

    public function test_admin_can_view_all_billing_periods(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create(['user_id' => $lecturer->id]);
        
        $response = $this->actingAs($admin)->get(route('billing-periods.index'));
        
        $response->assertOk();
        $response->assertSee($billingPeriod->month);
        $response->assertSee($lecturer->name);
    }

    public function test_lecturer_can_create_billing_period(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        
        $response = $this->actingAs($lecturer)->post(route('billing-periods.store'), [
            'month' => 12,
            'year' => 2024,
        ]);
        
        $response->assertRedirect(route('billing-periods.index'));
        $this->assertDatabaseHas('billing_periods', [
            'user_id' => $lecturer->id,
            'month' => 12,
            'year' => 2024,
            'status' => 'OPEN',
        ]);
    }

    public function test_lecturer_cannot_create_duplicate_billing_period(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'month' => 12,
            'year' => 2024,
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('billing-periods.store'), [
            'month' => 12,
            'year' => 2024,
        ]);
        
        $response->assertSessionHasErrors('month');
    }

    public function test_lecturer_can_view_billing_period_details(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create(['user_id' => $lecturer->id]);
        
        $response = $this->actingAs($lecturer)->get(route('billing-periods.show', $billingPeriod));
        
        $response->assertOk();
        $response->assertSee($billingPeriod->month);
        $response->assertSee($billingPeriod->year);
    }

    public function test_lecturer_can_submit_open_billing_period(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('billing-periods.submit', $billingPeriod));
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $billingPeriod->refresh();
        $this->assertEquals('SUBMITTED', $billingPeriod->status);
        $this->assertNotNull($billingPeriod->submitted_at);
    }

    public function test_lecturer_can_delete_open_billing_period(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $response = $this->actingAs($lecturer)->delete(route('billing-periods.destroy', $billingPeriod));
        
        $response->assertRedirect(route('billing-periods.index'));
        $this->assertDatabaseMissing('billing_periods', [
            'id' => $billingPeriod->id,
        ]);
    }

    public function test_lecturer_cannot_view_other_lecturers_billing_period(): void
    {
        $lecturer1 = User::factory()->lecturer()->create();
        $lecturer2 = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create(['user_id' => $lecturer2->id]);
        
        $response = $this->actingAs($lecturer1)->get(route('billing-periods.show', $billingPeriod));
        
        $response->assertForbidden();
    }

    public function test_guest_cannot_access_billing_periods(): void
    {
        $response = $this->get(route('billing-periods.index'));
        
        $response->assertRedirect(route('login'));
    }

    public function test_billing_period_total_hours_calculation(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()
            ->hasTeachingSessions(3, ['hours' => 2.5])
            ->create(['user_id' => $lecturer->id]);
        
        $this->assertEquals(7.5, $billingPeriod->total_hours);
    }

    public function test_billing_period_total_expenses_calculation(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()
            ->hasExpenses(2, ['amount' => 50.00])
            ->create(['user_id' => $lecturer->id]);
        
        $this->assertEquals(100.00, $billingPeriod->total_expenses);
    }
}
