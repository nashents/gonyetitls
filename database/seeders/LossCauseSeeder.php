<?php

namespace Database\Seeders;

use App\Models\Loss;
use App\Models\LossCategory;
use App\Models\LossGroup;
use Illuminate\Database\Seeder;

class LossCauseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // If you want to stamp a system user, put their ID here.
        // Otherwise leave null and it will just be empty.
        $defaultUserId = null;

        $data = [

            // =====================
            // PERSONAL FACTORS
            // =====================
            'PERSONAL FACTORS' => [

                'Inadequate Physical/Physiological Capability' => [
                    'Inappropriate height, weight, size, strength, reach etc',
                    'Restricted range of body movement',
                    'Limited ability to sustain body position',
                    'Substance sensitivities or allergies',
                    'Sensitivities to sensory extremes (temperature, sound, etc.)',
                    'Vision deficiency',
                    'Hearing deficiency',
                    'Other sensory deficiency (touch, taste, smell, balance)',
                    'Respiratory incapacity',
                    'Other permanent physical disabilities',
                    'Temporary disabilities',
                ],

                'Inadequate Mental/Psychological Capability' => [
                    'Fears and phobias',
                    'Emotional disturbance',
                    'Mental illness',
                    'Intelligence level',
                    'Inability to comprehend',
                    'Poor judgment',
                    'Poor coordination',
                    'Slow reaction time',
                    'Low mechanical aptitude',
                    'Low learning aptitude',
                    'Memory failure',
                ],

                'Physical or Physiological Stress' => [
                    'Injury or illness',
                    'Fatigue due to task load or duration',
                    'Fatigue due to lack of rest',
                    'Fatigue due to sensory overload',
                    'Exposure to health hazards',
                    'Exposure to temperature extremes',
                    'Oxygen deficiency',
                    'Atmospheric pressure variation',
                    'Constrained movement',
                    'Blood sugar insufficiency',
                    'Drugs',
                ],

                'Mental or Psychological Stress' => [
                    'Emotional overload',
                    'Fatigue due to mental task load or speed',
                    'Extreme judgment/decision demands',
                    'Routine, monotony, demand for uneventful vigilance',
                    'Extreme concentration/perception demands',
                    '“Meaningless” or “degrading” activities',
                    'Confusing directions',
                    'Conflicting demands',
                    'Preoccupation with problems',
                    'Frustration',
                    'Mental illness',
                ],

                'Lack of Knowledge' => [
                    'Inadequate initial instruction',
                    'Inadequate practice',
                    'Infrequent performance',
                    'Lack of coaching',
                ],

                'Improper Motivation' => [
                    'Improper performance is rewarding',
                    'Proper performance is punishing',
                    'Lack of incentives',
                    'Excessive frustration',
                    'Inappropriate aggression',
                    'Improper attempt to save time or effort',
                    'Improper attempt to avoid discomfort',
                    'Improper attempt to gain attention',
                    'Inappropriate peer pressure',
                    'Improper supervisory example',
                    'Inadequate performance feedback',
                    'Inadequate reinforcement of proper behaviour',
                    'Improper production incentive',
                ],
            ],

            // =====================
            // JOB FACTORS
            // =====================
            'JOB FACTORS' => [

                'Inadequate Leadership and/or Supervision' => [
                    'Unclear or conflicting reporting relationships',
                    'Unclear or conflicting assignment of responsibility',
                    'Improper or insufficient delegation',
                    'Giving inadequate policy, procedures, practices or guidelines',
                    'Giving objectives, goals or targets that conflict',
                    'Inadequate work planning or programming',
                    'Inadequate instruction, orientation and/or training',
                    'Providing inadequate reference documents, directives & guidance publications',
                    'Inadequate identification and evaluation of loss exposures',
                    'Lack of supervisory/management job knowledge',
                    'Inadequate match of individual qualifications & job knowledge',
                    'Inadequate performance measurement and evaluation',
                    'Inadequate or incorrect performance feedback',
                ],

                'Inadequate Engineering' => [
                    'Inadequate assessment of loss exposures',
                    'Inadequate consideration of human factors/ergonomics',
                    'Inadequate standards, specifications and/or design criteria',
                    'Inadequate monitoring of construction',
                    'Inadequate assessment of operational readiness',
                    'Inadequate monitoring of initial operation',
                    'Inadequate evaluation of changes',
                ],

                'Inadequate Purchasing' => [
                    'Inadequate specification on requisitions',
                    'Inadequate research on materials/equipment',
                    'Inadequate specifications to vendors',
                    'Inadequate mode or route of shipment',
                    'Inadequate receiving inspection and acceptance',
                    'Inadequate communication of safety and health data',
                    'Inadequate handling of materials',
                    'Inadequate storage of materials',
                    'Inadequate transporting of materials',
                    'Inadequate identification of hazardous items',
                    'Improper salvage and/or waste disposal',
                ],

                'Inadequate Maintenance' => [
                    // Preventive
                    'Preventive – assessment of needs',
                    'Preventive – lubrication and servicing',
                    'Preventive – adjustment/assembly',
                    'Preventive – cleaning or resurfacing',

                    // Reparative
                    'Reparative – communication of needs',
                    'Reparative – scheduling of work',
                    'Reparative – examination of units',
                    'Reparative – part substitution',
                ],

                'Inadequate Tools and Equipment' => [
                    'Inadequate assessment of needs and risks',
                    'Inadequate human factors/ergonomics considerations',
                    'Inadequate standards or specifications',
                    'Inadequate availability',
                    'Inadequate adjustment/repair/maintenance',
                    'Inadequate salvage and reclamation',
                    'Inadequate removal and replacement of unsuitable items',
                ],

                'Inadequate Work Standards' => [
                    // Development of standards
                    'Inadequate development of standards – inventory and evaluation of exposures and needs',
                    'Inadequate development of standards – employee involvement',
                    'Inadequate development of standards – coordination with process design',
                    'Inadequate development of standards – inconsistent standards/procedures/rules',

                    // Communication of standards
                    'Inadequate communication of standards – publication',
                    'Inadequate communication of standards – distribution',
                    'Inadequate communication of standards – translation to appropriate languages',
                    'Inadequate communication of standards – training',
                    'Inadequate communication of standards – reinforcing with signs, colour codes and job aids',

                    // Maintenance of standards
                    'Inadequate maintenance of standards – tracking of work flow',
                    'Inadequate maintenance of standards – monitoring use of standards/procedures/rules',
                    'Inadequate maintenance of standards – updating',
                ],

                'Wear and Tear' => [
                    'Inadequate planning of use',
                    'Improper extension of service life',
                    'Inadequate inspection and/or monitoring',
                    'Improper loading or rate of use',
                    'Inadequate maintenance',
                    'Use by unqualified or untrained people',
                    'Use for wrong purpose',
                ],

                'Abuse or Misuse' => [
                    'Abuse or misuse condoned by supervision – intentional',
                    'Abuse or misuse condoned by supervision – unintentional',
                    'Abuse or misuse not condoned by supervision – intentional',
                    'Abuse or misuse not condoned by supervision – unintentional',
                ],

                // =====================
                // SUBSTANDARD ACTS
                // =====================
                'SUBSTANDARD ACTS' => [
                    'Operating without authority',
                    'Failure to notify/make safe',
                    'Operating or working at improper speed',
                    'Defeating or removing a safety device',
                    'Using defective equipment',
                    'Using equipment improperly',
                    'Failure to use protective equipment properly',
                    'Improper loading or positioning',
                    'Improper lifting',
                    'Improper position for task',
                    'Servicing equipment in operation',
                    'Horseplay',
                    'Alcohol or drugs',
                    'Extreme concentration/perception demands',
                    '“Meaningless” or “degrading” activities',
                    'Confusing directions',
                    'Conflicting demands',
                    'Preoccupation with problems',
                    'Frustration',
                    'Mental illness',

                    // Lack of knowledge (acts-flavoured)
                    'Lack of knowledge – inadequate initial instruction',
                    'Lack of knowledge – inadequate practice',
                    'Lack of knowledge – infrequent performance',
                    'Lack of knowledge – lack of coaching',

                    // Improper motivation (acts-flavoured)
                    'Improper motivation – improper performance is rewarding',
                    'Improper motivation – proper performance is punishing',
                    'Improper motivation – lack of incentives',
                    'Improper motivation – excessive frustration',
                    'Improper motivation – inappropriate aggression',
                    'Improper motivation – improper attempt to save time or effort',
                    'Improper motivation – improper attempt to avoid discomfort',
                    'Improper motivation – improper attempt to gain attention',
                    'Improper motivation – inappropriate peer pressure',
                    'Improper motivation – improper supervisory example',
                    'Improper motivation – inadequate performance feedback',
                    'Improper motivation – inadequate reinforcement of proper behavior',
                    'Improper motivation – improper production incentives',
                ],

                // =====================
                // SUBSTANDARD CONDITIONS
                // =====================
                'SUBSTANDARD CONDITIONS' => [
                    'Inadequate guards or barriers',
                    'Defective tools, equipment, substances',
                    'Inadequate protective equipment',
                    'Poor access',
                    'Inadequate warning system or notice',
                    'Fire and/or explosion hazards',
                    'Substandard housekeeping',
                    'Substandard gases, dust, fumes, vapours',
                    'Excessive noise',
                    'Radiation exposures',
                    'Inadequate ventilation',
                    'High or low temperature exposure',
                    'Inadequate illumination',
                    'Other substandard condition',

                    // Inadequate reparative (as conditions)
                    'Inadequate reparative – communication of needs',
                    'Inadequate reparative – scheduling of work',
                    'Inadequate reparative – examination of units',
                    'Inadequate reparative – part substitution',

                    // Tools & equipment (conditions flavour)
                    'Tools & equipment – inadequate assessment of needs and risks',
                    'Tools & equipment – inadequate human factors/ergonomics considerations',
                    'Tools & equipment – inadequate standards or specifications',
                    'Tools & equipment – inadequate availability',
                    'Tools & equipment – inadequate adjustment/repair/maintenance',
                    'Tools & equipment – inadequate salvage and reclamation',
                    'Tools & equipment – inadequate removal and replacement of unsuitable items',

                    // Work standards (conditions)
                    'Work standards – inadequate development of standards',
                    'Work standards – inventory and evaluation of exposures and needs',
                    'Work standards – employee involvement',
                    'Work standards – coordination with process design',
                    'Work standards – inconsistent standards/procedures/rules',
                    'Work standards – inadequate communication of standards: publication/distribution/training',
                    'Work standards – translation to appropriate languages',
                    'Work standards – reinforcing with signs, colour codes and job aids',
                    'Work standards – inadequate maintenance of standards: tracking of work flow',
                    'Work standards – monitoring use of standards/procedures/rules',
                    'Work standards – updating',

                    // Wear & tear (conditions flavour)
                    'Wear and tear – inadequate planning of use',
                    'Wear and tear – improper extension of service life',
                    'Wear and tear – inadequate inspection and/or monitoring',
                    'Wear and tear – improper loading or rate of use',
                    'Wear and tear – inadequate maintenance',
                    'Wear and tear – use by unqualified or untrained people',
                    'Wear and tear – use for wrong purpose',

                    // Abuse/misuse (conditions context)
                    'Abuse or misuse condoned by supervision – intentional',
                    'Abuse or misuse condoned by supervision – unintentional',
                    'Abuse or misuse not condoned by supervision – intentional',
                    'Abuse or misuse not condoned by supervision – unintentional',
                ],
            ],
        ];

        foreach ($data as $categoryName => $groups) {
            $category = LossCategory::where('name', $categoryName)->first();

            if (! $category) {
                // If category is missing, skip and keep seeding others.
                continue;
            }

            foreach ($groups as $groupName => $losses) {
                $group = LossGroup::where('name', $groupName)
                    ->where('loss_category_id', $category->id)
                    ->first();

                if (! $group) {
                    // If group is missing under this category, skip its losses.
                    continue;
                }

                foreach ($losses as $lossName) {
                    $lossName = trim($lossName);
                    if ($lossName === '') {
                        continue;
                    }

                    Loss::updateOrCreate(
                        [
                            'loss_category_id' => $category->id,
                            'loss_group_id'    => $group->id,
                            'name'             => $lossName,
                        ],
                        [
                            'user_id'          => $defaultUserId,
                        ]
                    );
                }
            }
        }
    }
}
