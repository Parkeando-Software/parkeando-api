<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Subscription;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PaymentsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        foreach (Subscription::all() as $subscription) {
            Payment::create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'provider_id' => 'pi_' . $faker->bothify('##########'),
                'amount' => match ($subscription->type) {
                    'monthly' => 4.99,
                    'quarterly' => 13.99,
                    'annual' => 49.99,
                },
                'currency' => 'EUR',
                'status' => 'successful',
                'payment_method' => $faker->randomElement(['card', 'paypal']),
                'paid_at' => $subscription->start_date,
            ]);
        }
    }
}
