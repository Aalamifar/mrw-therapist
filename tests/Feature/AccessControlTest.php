<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_therapist_cannot_open_another_therapists_class(): void
    {
        $owner = $this->user('therapist');
        $otherTherapist = $this->user('therapist');
        $class = SchoolClass::create([
            'name' => 'کلاس خصوصی',
            'subject' => 'ریاضی',
            'therapist_id' => $owner->id,
        ]);

        $this->actingAs($otherTherapist)
            ->get(route('therapist.classes.show', $class))
            ->assertNotFound();
    }

    public function test_a_student_cannot_open_a_class_they_do_not_belong_to(): void
    {
        $therapist = $this->user('therapist');
        $student = $this->user('student');
        $class = SchoolClass::create([
            'name' => 'کلاس خصوصی',
            'subject' => 'ریاضی',
            'therapist_id' => $therapist->id,
        ]);

        $this->actingAs($student)
            ->get(route('student.classes.show', $class))
            ->assertForbidden();
    }

    public function test_a_conversation_returns_the_other_participant(): void
    {
        $therapist = $this->user('therapist');
        $student = $this->user('student');
        $conversation = Conversation::create();
        $conversation->users()->attach([$therapist->id, $student->id]);

        $this->assertTrue($conversation->otherUser($therapist)->is($student));
        $this->assertTrue($conversation->otherUser($student)->is($therapist));
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => "{$role} user",
            'phone' => fake()->unique()->numerify('09#########'),
            'role' => $role,
            'password' => Hash::make('password'),
        ]);
    }
}
