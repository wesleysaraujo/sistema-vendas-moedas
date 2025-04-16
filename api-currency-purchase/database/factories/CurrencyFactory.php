<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$']
        ];
        
        $currency = $this->faker->randomElement($currencies);
        
        return [
            'code' => $currency['code'],
            'name' => $currency['name'],
            'symbol' => $currency['symbol'],
            'exchange_rate' => $this->faker->randomFloat(4, 1, 10),
        ];
    }
    
    /**
     * Define a specific currency (USD)
     *
     * @return Factory
     */
    public function usd(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'code' => 'USD',
                'name' => 'United States Dollar',
                'symbol' => '$',
                'exchange_rate' => 5.1234,
            ];
        });
    }
    
    /**
     * Define a specific currency (EUR)
     *
     * @return Factory
     */
    public function eur(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'exchange_rate' => 6.2345,
            ];
        });
    }
}