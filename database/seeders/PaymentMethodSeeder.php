<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payment_methods = [
            ['user_id' => 3, 'name'=>'Bank transfer'],
            ['user_id' => 3, 'name'=>'Cash'],
            ['user_id' => 3, 'name'=>'IceCash'],
            ['user_id' => 3, 'name'=>'Korridor'],
        ];

        foreach ($payment_methods as $payment_method) {
            PaymentMethod::updateOrCreate(
                ['name' => $payment_method['name']],
                $payment_method + ['is_locked' => true]
            );
        }
    }
}
