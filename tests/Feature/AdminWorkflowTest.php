<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BillingPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        
        $response->assertOk();
    }

    public function test_admin_dashboard_shows_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->lecturer()->count(5)->create();
        BillingPeriod::factory()->submitted()->count(3)->create();
        BillingPeriod::factory()->approved()->count(2)->create();
        BillingPeriod::factory()->exported()->count(1)->create();
        
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        
        $response->assertOk();
        $response->assertSee('5'); // total lecturers
        $response->assertSee('3'); // pending submissions
    }

    public function test_admin_can_approve_submitted_billing_period(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->submitted()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($admin)->post(route('admin.billing-periods.approve', $billingPeriod));
        
        $response->assertRedirect();
        $billingPeriod->refresh();
        $this->assertEquals('APPROVED', $billingPeriod->status);
        $this->assertNotNull($billingPeriod->approved_at);
    }

    public function test_admin_cannot_approve_open_billing_period(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $response = $this->actingAs($admin)->post(route('admin.billing-periods.approve', $billingPeriod));
        
        $response->assertForbidden();
    }

    public function test_admin_can_reopen_submitted_billing_period(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->submitted()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($admin)->post(route('admin.billing-periods.reopen', $billingPeriod));
        
        $response->assertRedirect();
        $billingPeriod->refresh();
        $this->assertEquals('OPEN', $billingPeriod->status);
        $this->assertNull($billingPeriod->submitted_at);
        $this->assertNull($billingPeriod->approved_at);
    }

    public function test_admin_can_reopen_approved_billing_period(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->approved()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($admin)->post(route('admin.billing-periods.reopen', $billingPeriod));
        
        $response->assertRedirect();
        $billingPeriod->refresh();
        $this->assertEquals('OPEN', $billingPeriod->status);
    }

    public function test_admin_can_mark_approved_period_as_exported(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->approved()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($admin)->post(route('admin.billing-periods.mark-exported', $billingPeriod));
        
        $response->assertRedirect();
        $billingPeriod->refresh();
        $this->assertEquals('EXPORTED', $billingPeriod->status);
        $this->assertNotNull($billingPeriod->exported_at);
    }

    public function test_lecturer_cannot_access_admin_dashboard(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        
        $response = $this->actingAs($lecturer)->get(route('admin.dashboard'));
        
        $response->assertForbidden();
    }

    public function test_lecturer_cannot_approve_billing_periods(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->submitted()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('admin.billing-periods.approve', $billingPeriod));
        
        $response->assertForbidden();
    }

    public function test_lecturer_cannot_reopen_billing_periods(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->submitted()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('admin.billing-periods.reopen', $billingPeriod));
        
        $response->assertForbidden();
    }

    public function test_lecturer_cannot_mark_periods_as_exported(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->approved()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('admin.billing-periods.mark-exported', $billingPeriod));
        
        $response->assertForbidden();
    }

    public function test_billing_period_state_workflow(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->lecturer()->create();
        
        // Create open period
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        $this->assertEquals('OPEN', $billingPeriod->status);
        
        // Lecturer submits
        $this->actingAs($lecturer)->post(route('billing-periods.submit', $billingPeriod));
        $billingPeriod->refresh();
        $this->assertEquals('SUBMITTED', $billingPeriod->status);
        
        // Admin approves
        $this->actingAs($admin)->post(route('admin.billing-periods.approve', $billingPeriod));
        $billingPeriod->refresh();
        $this->assertEquals('APPROVED', $billingPeriod->status);
        
        // Admin marks as exported
        $this->actingAs($admin)->post(route('admin.billing-periods.mark-exported', $billingPeriod));
        $billingPeriod->refresh();
        $this->assertEquals('EXPORTED', $billingPeriod->status);
    }
}
