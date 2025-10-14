<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Subscription;
use Faker\Factory as Faker;

class SubscriptionsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Solo un registro por customer
        foreach (Customer::all() as $customer) {
            Subscription::create([
                'user_id' => $customer->user_id,
                'type' => $faker->randomElement(['monthly', 'quarterly', 'annual']),
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'payment_method' => $faker->randomElement(['credit_card', 'paypal', 'apple_pay', 'google_pay']),
            ]);
        }
    }
}
