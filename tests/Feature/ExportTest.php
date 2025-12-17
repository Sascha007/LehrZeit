<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BillingPeriod;
use App\Models\TeachingSession;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_export_own_billing_period_as_csv(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'month' => 12,
            'year' => 2024,
        ]);
        
        TeachingSession::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
            'subject' => 'Mathematics',
            'hours' => 2.5,
        ]);
        
        $response = $this->actingAs($lecturer)->get(route('billing-periods.export.csv', $billingPeriod));
        
        $response->assertOk();
    }

    public function test_lecturer_can_export_own_billing_period_as_xlsx(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'month' => 12,
            'year' => 2024,
        ]);
        
        TeachingSession::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
            'subject' => 'Physics',
        ]);
        
        $response = $this->actingAs($lecturer)->get(route('billing-periods.export.xlsx', $billingPeriod));
        
        $response->assertOk();
    }

    public function test_lecturer_cannot_export_other_lecturers_billing_period(): void
    {
        $lecturer1 = User::factory()->lecturer()->create();
        $lecturer2 = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer2->id,
        ]);
        
        $response = $this->actingAs($lecturer1)->get(route('billing-periods.export.csv', $billingPeriod));
        
        $response->assertForbidden();
    }

    public function test_admin_can_export_any_billing_period(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($admin)->get(route('billing-periods.export.csv', $billingPeriod));
        
        $response->assertOk();
    }

    public function test_guest_cannot_export_billing_periods(): void
    {
        $billingPeriod = BillingPeriod::factory()->create();
        
        $response = $this->get(route('billing-periods.export.csv', $billingPeriod));
        
        $response->assertRedirect(route('login'));
    }
}
