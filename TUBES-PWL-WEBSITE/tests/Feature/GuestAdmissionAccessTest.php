<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('non authenticated visitors are redirected to login before admission', function () {
    $this->get('/admission')
        ->assertRedirect('/login')
        ->assertSessionHas('error');
});

test('visitors with a valid guest session can access admission', function () {
    $this->withSession([
        'guest_user' => [
            'id' => 123,
            'name' => 'Guest Visitor',
        ],
    ])->get('/admission')
        ->assertOk();
});

test('guest login stores a valid guest session structure', function () {
    $this->post('/guest-login', [
        'email' => 'guest@example.com',
        'confirm_email' => 'guest@example.com',
        'first_name' => 'Guest',
        'last_name' => 'Visitor',
    ])
        ->assertRedirect(route('ticket.admission'))
        ->assertSessionHas('guest_user.id')
        ->assertSessionHas('guest_user.name', 'Guest Visitor');
});

test('guest session leftovers are cleared upon successful user login', function () {
    $user = \App\Models\User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->withSession([
        'guest_user' => [
            'id' => 123,
            'name' => 'Guest Visitor',
        ],
        'guest_id' => 123,
        'guest_name' => 'Guest',
    ])->post('/account/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ])
        ->assertRedirect(route('home'))
        ->assertSessionMissing('guest_user')
        ->assertSessionMissing('guest_id')
        ->assertSessionMissing('guest_name');
});
