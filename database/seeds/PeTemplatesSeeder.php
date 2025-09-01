<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PeTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if the table exists
        if (!Schema::hasTable('pe_templates')) {
            $this->command->error('pe_templates table does not exist. Please run migrations first.');
            return;
        }

        $templates = [
            [
                'name' => 'Normal Adult',
                'content' => "Gait: normal\nArms: Normal ROM\nSpine: non tender, full ROM\nHeart: regular rate and rhythm, no murmurs\nLungs: clear to auscultation bilaterally\nAbdomen: soft, non-tender, no masses\nExtremities: no edema, normal pulses",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cardiovascular',
                'content' => "Heart: regular rate and rhythm, no murmurs, gallops, or rubs\nPeripheral pulses: 2+ throughout\nNo edema in extremities\nCapillary refill < 2 seconds",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Respiratory',
                'content' => "Lungs: clear to auscultation bilaterally\nNo wheezes, rales, or rhonchi\nRespiratory effort: normal\nNo use of accessory muscles",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Abdominal',
                'content' => "Abdomen: soft, non-tender, no masses\nNo hepatosplenomegaly\nBowel sounds: normal\nNo rebound tenderness or guarding",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Neurological',
                'content' => "Mental status: alert and oriented\nCranial nerves: intact\nMotor: 5/5 strength throughout\nSensory: intact to light touch\nReflexes: 2+ throughout\nGait: normal",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Musculoskeletal',
                'content' => "Joints: no swelling, warmth, or tenderness\nRange of motion: full and painless\nMuscle strength: 5/5 throughout\nNo deformities or contractures",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pediatric',
                'content' => "General: alert, active, well-nourished\nHEENT: normocephalic, atraumatic\nEyes: PERRLA, no discharge\nEars: tympanic membranes intact\nNose: no discharge, patent nares\nThroat: no erythema, no exudate\nHeart: regular rate and rhythm\nLungs: clear to auscultation\nAbdomen: soft, non-tender\nExtremities: no edema, normal pulses",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Geriatric',
                'content' => "General: alert, oriented to person, place, time\nHEENT: normocephalic, atraumatic\nEyes: PERRLA, no cataracts\nEars: hearing intact\nHeart: regular rate and rhythm, no murmurs\nLungs: clear to auscultation\nAbdomen: soft, non-tender\nExtremities: no edema, normal pulses\nNeurological: alert, oriented, no focal deficits",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pre-Employment',
                'content' => "General: well-developed, well-nourished, no acute distress\nHEENT: normocephalic, atraumatic\nEyes: PERRLA, vision 20/20 OU\nEars: hearing intact\nHeart: regular rate and rhythm, no murmurs\nLungs: clear to auscultation\nAbdomen: soft, non-tender, no masses\nExtremities: no edema, normal pulses, full ROM\nNeurological: alert, oriented, no focal deficits",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Post-Operative',
                'content' => "General: alert, oriented, no acute distress\nSurgical site: clean, dry, intact, no signs of infection\nHeart: regular rate and rhythm\nLungs: clear to auscultation\nAbdomen: soft, non-tender\nExtremities: no edema, normal pulses\nPain: well-controlled with current medication",
                'type' => 'default',
                'created_by' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Check if templates already exist
        if (DB::table('pe_templates')->count() > 0) {
            $this->command->info('PE templates already exist. Skipping seeding.');
            return;
        }

        foreach ($templates as $template) {
            DB::table('pe_templates')->insert($template);
        }

        $this->command->info('PE templates seeded successfully!');
    }
}
