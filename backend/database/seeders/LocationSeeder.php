<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'city' => 'New York',
                'state' => 'New York',
                'country' => 'United States',
                'slug' => 'new-york-new-york-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'Los Angeles',
                'state' => 'California',
                'country' => 'United States',
                'slug' => 'los-angeles-california-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'Chicago',
                'state' => 'Illinois',
                'country' => 'United States',
                'slug' => 'chicago-illinois-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'Houston',
                'state' => 'Texas',
                'country' => 'United States',
                'slug' => 'houston-texas-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'Phoenix',
                'state' => 'Arizona',
                'country' => 'United States',
                'slug' => 'phoenix-arizona-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'Philadelphia',
                'state' => 'Pennsylvania',
                'country' => 'United States',
                'slug' => 'philadelphia-pennsylvania-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'San Antonio',
                'state' => 'Texas',
                'country' => 'United States',
                'slug' => 'san-antonio-texas-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'San Diego',
                'state' => 'California',
                'country' => 'United States',
                'slug' => 'san-diego-california-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'Dallas',
                'state' => 'Texas',
                'country' => 'United States',
                'slug' => 'dallas-texas-united-states',
                'is_active' => true,
            ],
            [
                'city' => 'San Jose',
                'state' => 'California',
                'country' => 'United States',
                'slug' => 'san-jose-california-united-states',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
