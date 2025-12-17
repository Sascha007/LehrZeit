<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BillingPeriod;
use App\Models\TeachingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_create_teaching_session_in_open_period(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('teaching-sessions.store'), [
            'billing_period_id' => $billingPeriod->id,
            'date' => '2024-12-15',
            'start_time' => '09:00',
            'end_time' => '11:30',
            'subject' => 'Mathematics',
            'description' => 'Advanced calculus lecture',
            'location' => 'Room 101',
        ]);
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $this->assertDatabaseHas('teaching_sessions', [
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
            'subject' => 'Mathematics',
            'hours' => 2.5,
        ]);
    }

    public function test_hours_are_calculated_correctly(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('teaching-sessions.store'), [
            'billing_period_id' => $billingPeriod->id,
            'date' => '2024-12-15',
            'start_time' => '14:00',
            'end_time' => '16:45',
            'subject' => 'Physics',
            'description' => null,
            'location' => null,
        ]);
        
        $response->assertRedirect();
        $session = TeachingSession::first();
        $this->assertNotNull($session);
        $this->assertEquals(2.75, $session->hours);
    }

    public function test_lecturer_can_update_teaching_session(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        $session = TeachingSession::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
            'subject' => 'Old Subject',
        ]);
        
        $response = $this->actingAs($lecturer)->put(route('teaching-sessions.update', $session), [
            'date' => '2024-12-16',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'subject' => 'New Subject',
            'description' => null,
            'location' => null,
        ]);
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $session->refresh();
        $this->assertEquals('New Subject', $session->subject);
        $this->assertEquals(2.0, $session->hours);
    }

    public function test_lecturer_can_delete_teaching_session(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer->id,
            'status' => 'OPEN',
        ]);
        $session = TeachingSession::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($lecturer)->delete(route('teaching-sessions.destroy', $session));
        
        $response->assertRedirect(route('billing-periods.show', $billingPeriod));
        $this->assertDatabaseMissing('teaching_sessions', [
            'id' => $session->id,
        ]);
    }

    public function test_lecturer_cannot_create_session_in_submitted_period(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->submitted()->create([
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($lecturer)->post(route('teaching-sessions.store'), [
            'billing_period_id' => $billingPeriod->id,
            'date' => '2024-12-15',
            'start_time' => '09:00',
            'end_time' => '11:30',
            'subject' => 'Mathematics',
        ]);
        
        $response->assertForbidden();
    }

    public function test_lecturer_cannot_modify_session_in_submitted_period(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->submitted()->create([
            'user_id' => $lecturer->id,
        ]);
        $session = TeachingSession::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer->id,
        ]);
        
        $response = $this->actingAs($lecturer)->put(route('teaching-sessions.update', $session), [
            'date' => '2024-12-16',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'subject' => 'New Subject',
        ]);
        
        $response->assertForbidden();
    }

    public function test_lecturer_cannot_access_other_lecturers_sessions(): void
    {
        $lecturer1 = User::factory()->lecturer()->create();
        $lecturer2 = User::factory()->lecturer()->create();
        $billingPeriod = BillingPeriod::factory()->create([
            'user_id' => $lecturer2->id,
            'status' => 'OPEN',
        ]);
        $session = TeachingSession::factory()->create([
            'billing_period_id' => $billingPeriod->id,
            'user_id' => $lecturer2->id,
        ]);
        
        $response = $this->actingAs($lecturer1)->delete(route('teaching-sessions.destroy', $session));
        
        $response->assertForbidden();
    }
}
