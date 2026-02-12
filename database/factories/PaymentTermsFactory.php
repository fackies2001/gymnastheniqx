<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentTerms>
 */
class PaymentTermsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $days = fake()->randomElement([0, 15, 30]);
        $discount = fake()->optional(0.3)->randomFloat(2, 1, 5);
        $discountDays = $discount ? fake()->numberBetween(5, 10) : null;

        return [
            'name' => fake()->randomElement([
                'Net 15',
                'Net 30',
                'Net 45',
                'Net 60',
                'Due on receipt',
                'Cash on delivery',
                '2/10 Net 30',
                'Prepaid'
            ]),
            'description' => $discount
                ? "Payment due in {$days} days with {$discount}% discount if paid within {$discountDays} days"
                : "Payment due in {$days} days",
            'days' => $days,
            'discount_rate' => $discount,
            'discount_days' => $discountDays,
        ];
    }
}
