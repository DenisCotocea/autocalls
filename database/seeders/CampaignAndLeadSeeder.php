<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\Lead;

class CampaignAndLeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Campaign::factory(3)->create()->each(function ($campaign) {
            Lead::factory(5)->create([
                'campaign_id' => $campaign->id,
            ]);
        });
    }
}
