<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plain-language descriptions for the seeded SHEQ lookups (Loss Causation
 * Model categories / groups / causes, Training Items, Training Departments).
 *
 * Only fills descriptions that are still blank, so text edited in the app is
 * never overwritten. Safe to re-run (DatabaseSeeder, data migration, reset).
 */
class SheqLookupDescriptionSeeder extends Seeder
{
    public const CATEGORIES = [
        'Personal Factors' => 'BASIC CAUSE (the person). Underlying reasons inside the individual that led to the substandard act - capability, stress, knowledge or motivation. Ask: "Why did this person act the way they did?"',
        'Job Factors' => 'BASIC CAUSE (the system/workplace). Underlying reasons in the work system that management controls - leadership, engineering, purchasing, maintenance, tools and standards. Ask: "Why did the workplace allow this to happen?"',
        'Substandard Acts' => 'IMMEDIATE CAUSE (what someone did). Unsafe behaviour seen at the time of the event, e.g. speeding, not using PPE, bypassing a guard. Immediate causes are only symptoms - trace them back to Personal / Job Factors.',
        'Substandard Conditions' => 'IMMEDIATE CAUSE (what was wrong in the environment). Unsafe physical conditions present at the time of the event, e.g. worn tyres, poor road, missing guard, defective tool. Trace them back to Job Factors.',
    ];

    public const GROUPS = [
        'Personal Factors' => [
            'Inadequate Physical/Physiological Capability' => 'The person\'s body was not suited to the task (size, strength, reach, senses, disability).',
            'Inadequate Mental/Psychological Capability' => 'The person\'s mental abilities did not match the task (judgement, comprehension, reaction time, memory, fears).',
            'Physical or Physiological stress' => 'The body was under strain at the time - fatigue, illness, heat, substances, low blood sugar.',
            'Mental or Psychological Stress' => 'The mind was under strain - emotional overload, monotony, conflicting demands, personal problems, frustration.',
            'Lack of Knowledge' => 'The person did not know how to do the job correctly - poor induction, little practice, infrequent task, no coaching.',
            'Improper Motivation' => 'The person knew the right way but chose otherwise - shortcuts rewarded, poor example, peer pressure, no feedback.',
        ],
        'Job Factors' => [
            'Inadequate Leadership and/or Supervision' => 'Management/supervisors did not set clear roles, procedures, plans, training or feedback.',
            'Inadequate Engineering' => 'Hazards were not designed out - poor design standards, ergonomics, commissioning or change evaluation.',
            'Inadequate Purchasing' => 'Wrong or unsafe items were bought, received, stored or moved - poor specs, inspection, hazard information.',
            'Inadequate Maintenance' => 'Equipment was not properly serviced (preventive) or repaired (reparative).',
            'Inadequate Tools and Equipment' => 'The right tools/equipment were not available, suitable or in good condition.',
            'Inadequate Work Standards' => 'Procedures/rules were missing, unclear, not communicated or not kept up to date.',
            'Wear and Tear' => 'Equipment deteriorated through use - extended service life, overloading, poor inspection.',
            'Abuse or Misuse' => 'Equipment or materials were used in ways not intended - with or without supervision knowing.',
        ],
        'Substandard Acts' => [
            'Lack of Knowledge' => 'Unsafe act committed because the person did not know the correct method (immediate-cause view).',
            'Improper Motivation' => 'Unsafe act committed deliberately or carelessly, e.g. shortcut to save time (immediate-cause view).',
        ],
        'Substandard Conditions' => [
            'Inadequate Tools and Equipment' => 'Unsafe condition caused by missing, unsuitable or defective tools/equipment at the scene.',
            'Inadequate Work Standards' => 'Unsafe condition because the work area/process did not meet a defined standard.',
            'Wear and Tear' => 'Unsafe condition due to worn or deteriorated equipment, vehicles or structures.',
            'Abuse or Misuse' => 'Unsafe condition created by equipment or materials having been abused or misused.',
        ],
    ];

    public const CAUSES = [
        'Personal Factors' => [
            'Inadequate Physical/Physiological Capability' => [
                'Inappropriate height, weight, size, strength, reach etc' => 'Person physically too small/large/weak for the task, e.g. cannot reach truck controls or lift a tyre safely.',
                'Restricted range of body movement' => 'Stiffness or injury limiting movement needed for the job, e.g. checking mirrors or climbing onto a trailer.',
                'Limited ability to sustain body position' => 'Cannot hold a required posture for long, e.g. working under a vehicle.',
                'Substance sensitivities or allergies' => 'Reaction to chemicals, dust or materials handled (e.g. cement, solvents).',
                'Sensitivities to sensory extremes (temperature, sound, etc.)' => 'Person poorly tolerates heat, cold, noise or bright light present in the job.',
                'Vision deficiency' => 'Poor eyesight, colour blindness or night-vision problems - critical for drivers.',
                'Hearing deficiency' => 'Cannot hear alarms, reversing signals, radio instructions or warnings.',
                'Other sensory deficiency (touch, taste, smell, balance)' => 'E.g. cannot smell a gas leak, poor balance working at height.',
                'Respiratory incapacity' => 'Lung/breathing condition aggravated by dust, fumes or exertion.',
                'Other permanent physical disabilities' => 'Long-term disability that the job design did not take into account.',
                'Temporary disabilities' => 'Short-term injury or condition (e.g. sprained wrist) while still doing normal duties.',
            ],
            'Inadequate Mental/Psychological Capability' => [
                'Fears and phobias' => 'Fear of heights, confined spaces etc. affecting safe performance.',
                'Emotional disturbance' => 'Emotional state making the person unpredictable or distracted.',
                'Mental illness' => 'Diagnosed or suspected mental health condition affecting work capability.',
                'Intelligence level' => 'Task complexity beyond the person\'s ability to process information.',
                'Inability to comprehend' => 'Could not understand instructions (including language/literacy barriers).',
                'Poor judgment' => 'Misjudged speed, distance, load or risk, e.g. overtaking with insufficient gap.',
                'Poor coordination' => 'Difficulty coordinating hands/feet/eyes, e.g. when reversing a rig.',
                'Slow reaction time' => 'Too slow to respond to hazards, e.g. sudden braking ahead.',
                'Low mechanical aptitude' => 'Difficulty understanding how machines/vehicles work.',
                'Low learning aptitude' => 'Takes longer to learn tasks than the training allowed.',
                'Memory failure' => 'Forgot a step, e.g. did not engage the trailer lock or reconnect air lines.',
            ],
            'Physical or Physiological stress' => [
                'Injury or illness' => 'Working while sick or injured.',
                'Fatigue due to task load or duration' => 'Tired from long or heavy work, e.g. very long driving shift.',
                'Fatigue due to lack of rest' => 'Insufficient sleep/rest between shifts or trips.',
                'Fatigue due to sensory overload' => 'Exhausted by constant noise, vibration, glare.',
                'Exposure to health hazards' => 'Effects of dust, fumes, chemicals during the task.',
                'Exposure to temperature extremes' => 'Heat stress or cold affecting performance.',
                'Oxygen deficiency' => 'Low oxygen, e.g. confined spaces or tanks.',
                'Atmospheric pressure variation' => 'Effects of altitude or pressure changes.',
                'Constrained movement' => 'Working in cramped positions for long periods.',
                'Blood sugar insufficiency' => 'Not eaten - dizziness, poor concentration.',
                'Drugs' => 'Under the influence of alcohol, illegal drugs or impairing medication.',
            ],
            'Mental or Psychological Stress' => [
                'Emotional overload' => 'Overwhelmed by emotional pressures at or outside work.',
                'Fatigue due to mental task load or speed' => 'Mentally drained by too many decisions or fast pace.',
                'Extreme judgment/decision demands' => 'Required to make critical decisions under pressure.',
                'Routine, monotony, demand for uneventful vigilance' => 'Boredom on long, monotonous routes reduces alertness.',
                'Extreme concentration/perception demands' => 'Task requires intense focus for too long.',
                '“Meaningless” or “degrading” activities' => 'Work seen as pointless or demeaning, reducing care and attention.',
                'Confusing directions' => 'Instructions unclear or contradictory.',
                'Conflicting demands' => 'E.g. told to deliver on time and also obey speed limits that make it impossible.',
                'Preoccupation with problems' => 'Distracted by personal, family or financial worries.',
                'Frustration' => 'Frustrated by delays, equipment problems or management - leading to rash acts.',
                'Mental illness' => 'Mental health condition causing stress that affects work.',
            ],
            'Lack of Knowledge' => [
                'Inadequate initial instruction' => 'Induction/training did not cover the task properly.',
                'Inadequate practice' => 'Trained but not enough hands-on practice to be competent.',
                'Infrequent performance' => 'Task done so rarely that the method was forgotten.',
                'Lack of coaching' => 'No supervisor or mentor follow-up after training.',
            ],
            'Improper Motivation' => [
                'Improper performance is rewarding' => 'Unsafe shortcut gets rewarded (e.g. faster trips earn more).',
                'Proper performance is punishing' => 'Doing it right brings penalties (e.g. late delivery blamed on following rest rules).',
                'Lack of incentives' => 'No recognition for safe behaviour.',
                'Excessive frustration' => 'Frustration leads to deliberate disregard of rules.',
                'Inappropriate aggression' => 'Aggressive behaviour, e.g. road rage.',
                'Improper attempt to save time or effort' => 'Shortcut taken to finish faster.',
                'Improper attempt to avoid discomfort' => 'PPE or procedure skipped because it is uncomfortable.',
                'Improper attempt to gain attention' => 'Showing off or risk-taking for attention.',
                'Inappropriate peer pressure' => 'Colleagues encourage unsafe practice ("everyone does it").',
                'Improper supervisory example' => 'Supervisor breaks the rules, so others follow.',
                'Inadequate performance feedback' => 'Nobody tells workers when they do it wrong (or right).',
                'Inadequate reinforcement of proper behaviour' => 'Safe behaviour is not praised or reinforced.',
                'Improper production incentive' => 'Bonuses based only on output/loads encourage risk-taking.',
            ],
        ],
        'Job Factors' => [
            'Inadequate Leadership and/or Supervision' => [
                'Unclear or conflicting reporting relationships' => 'Workers unsure who they report to, or have two bosses.',
                'Unclear or conflicting assignment of responsibility' => 'Nobody clearly responsible for a task or check.',
                'Improper or insufficient delegation' => 'Tasks delegated without authority, resources or guidance.',
                'Giving inadequate policy, procedures, practices or guidelines' => 'Rules missing or not fit for purpose.',
                'Giving objectives, goals or targets that conflict' => 'E.g. production targets that clash with safety requirements.',
                'Inadequate work planning or programming' => 'Poor scheduling, unrealistic trip plans, rushed jobs.',
                'Inadequate instruction, orientation and/or training' => 'Supervisor did not brief or train the worker properly.',
                'Providing inadequate reference documents, directives & guidance publications' => 'Manuals, SOPs or job aids missing or outdated.',
                'Inadequate identification and evaluation of loss exposures' => 'Hazards/risks not assessed before work started.',
                'Lack of supervisory/management job knowledge' => 'Supervisor does not understand the work well enough.',
                'Inadequate match of individual qualifications & job knowledge' => 'Wrong person assigned (e.g. unlicensed or inexperienced driver).',
                'Inadequate performance measurement and evaluation' => 'Performance not monitored (e.g. tracker data not reviewed).',
                'Inadequate or incorrect performance feedback' => 'Feedback missing, late or wrong.',
            ],
            'Inadequate Engineering' => [
                'Inadequate assessment of loss exposures' => 'Design did not consider what could go wrong.',
                'Inadequate consideration of human factors/ergonomics' => 'Equipment/workplace designed without considering the people using it.',
                'Inadequate standards, specifications and/or design criteria' => 'Design criteria missing or too weak.',
                'Inadequate monitoring of construction' => 'Building/installation not checked against the design.',
                'Inadequate assessment of operational readiness' => 'Equipment put into use before being proven ready.',
                'Inadequate monitoring of initial operation' => 'Teething problems not watched during first use.',
                'Inadequate evaluation of changes' => 'Changes made without a risk assessment (see Change Management/MOC).',
            ],
            'Inadequate Purchasing' => [
                'Inadequate specification on requisitions' => 'Requisition did not specify safety requirements.',
                'Inadequate research on materials/equipment' => 'Product chosen without checking suitability/safety.',
                'Inadequate specifications to vendors' => 'Supplier not told the required standard.',
                'Inadequate mode or route of shipment' => 'Goods transported in an unsafe way or route.',
                'Inadequate receiving inspection and acceptance' => 'Deliveries accepted without checking (GRV inspection).',
                'Inadequate communication of safety and health data' => 'SDS / hazard information not passed on.',
                'Inadequate handling of materials' => 'Materials handled unsafely in stores.',
                'Inadequate storage of materials' => 'Incorrect storage, e.g. chemicals not bunded or segregated.',
                'Inadequate transporting of materials' => 'Unsafe movement of materials on site.',
                'Inadequate identification of hazardous items' => 'Hazardous goods not labelled or identified.',
                'Improper salvage and/or waste disposal' => 'Scrap/waste disposed of incorrectly.',
            ],
            'Inadequate Maintenance' => [
                'Preventive – assessment of needs' => 'Service needs not properly determined (wrong intervals).',
                'Preventive – lubrication and servicing' => 'Greasing/servicing missed or done poorly.',
                'Preventive – adjustment/assembly' => 'Parts not adjusted or assembled correctly (e.g. brakes).',
                'Preventive – cleaning or resurfacing' => 'Cleaning/resurfacing not done, e.g. slippery steps.',
                'Reparative – communication of needs' => 'Defects not reported or not passed on to the workshop.',
                'Reparative – scheduling of work' => 'Repairs delayed or badly scheduled.',
                'Reparative – examination of units' => 'Faulty units not properly inspected before repair/release.',
                'Reparative – part substitution' => 'Wrong or substandard replacement parts used.',
            ],
            'Inadequate Tools and Equipment' => [
                'Inadequate assessment of needs and risks' => 'Did not work out what tools the job needs.',
                'Inadequate human factors/ergonomics considerations' => 'Tools awkward or unsafe to use.',
                'Inadequate standards or specifications' => 'No standard defining acceptable tools/equipment.',
                'Inadequate availability' => 'Right tool not available when needed.',
                'Inadequate adjustment/repair/maintenance' => 'Tools not kept in good working order.',
                'Inadequate salvage and reclamation' => 'Damaged tools not removed or repaired.',
                'Inadequate removal and replacement of unsuitable items' => 'Defective tools left in use.',
            ],
            'Inadequate Work Standards' => [
                'Inadequate development of standards – inventory and evaluation of exposures and needs' => 'Standards written without first identifying the hazards.',
                'Inadequate development of standards – employee involvement' => 'Workers not consulted when writing procedures.',
                'Inadequate development of standards – coordination with process design' => 'Procedures do not match how the process actually works.',
                'Inadequate development of standards – inconsistent standards/procedures/rules' => 'Different rules contradict each other.',
                'Inadequate communication of standards – publication' => 'Procedure exists but was never published.',
                'Inadequate communication of standards – distribution' => 'Procedure not distributed to the people who need it.',
                'Inadequate communication of standards – translation to appropriate languages' => 'Not available in a language workers understand.',
                'Inadequate communication of standards – training' => 'Workers not trained on the procedure.',
                'Inadequate communication of standards – reinforcing with signs, colour codes and job aids' => 'No signs, labels or job aids reminding people.',
                'Inadequate maintenance of standards – tracking of work flow' => 'Nobody tracks whether work follows the standard.',
                'Inadequate maintenance of standards – monitoring use of standards/procedures/rules' => 'Compliance with procedures not checked.',
                'Inadequate maintenance of standards – updating' => 'Procedures outdated after changes.',
            ],
            'Wear and Tear' => [
                'Inadequate planning of use' => 'Equipment use not planned against its capacity/life.',
                'Improper extension of service life' => 'Kept in service past its safe life (e.g. old tyres, slings).',
                'Inadequate inspection and/or monitoring' => 'Wear not picked up by inspections.',
                'Improper loading or rate of use' => 'Overloaded or used too intensively.',
                'Inadequate maintenance' => 'Wear accelerated by poor maintenance.',
                'Use by unqualified or untrained people' => 'Untrained users wear out or damage equipment.',
                'Use for wrong purpose' => 'Equipment used for a job it was not designed for.',
            ],
            'Abuse or Misuse' => [
                'Abuse or misuse condoned by supervision – intentional' => 'Supervisor knowingly allows deliberate misuse.',
                'Abuse or misuse condoned by supervision – unintentional' => 'Supervisor allows misuse without realising it is wrong.',
                'Abuse or misuse not condoned by supervision – intentional' => 'Worker deliberately misuses equipment against instructions.',
                'Abuse or misuse not condoned by supervision – unintentional' => 'Worker misuses equipment without realising.',
            ],
        ],
    ];

    public const TRAINING_ITEMS = [
        'Defensive Driving' => 'Techniques for anticipating hazards and other road users\' mistakes, safe following distances, speed management and fatigue awareness for heavy-vehicle drivers.',
        'First Aid and CPR' => 'Basic life support, CPR and emergency first aid so staff can respond to injuries on site or at roadside accidents until medical help arrives.',
        'Hooking and Offhooking' => 'Safe coupling and uncoupling of trailers: fifth-wheel checks, kingpin lock, air and electrical lines, landing legs and tug test.',
        'Loading and Offloading' => 'Safe loading/offloading practice: weight distribution, load securing and tarping, seals, site rules and staying clear of moving equipment.',
    ];

    public const TRAINING_DEPARTMENTS = [
        'Drivers' => 'Truck/horse drivers - defensive driving, hooking/offhooking, loading, first aid and route rules.',
        'Mechanics' => 'Workshop staff - safe maintenance, tyre fitment, lifting equipment, hot work and chemical handling.',
        'Operations & SHEQ Manager' => 'Responsible managers - legal compliance, incident investigation, risk assessment and audit skills.',
        'Ass Ops' => 'Assistant operations staff/controllers - trip planning, tracking, emergency response and journey management.',
        'General Manager' => 'Senior leadership - SHEQ leadership, legal duties and management review.',
        'Finance & Admin Officers' => 'Office staff - office safety, fire evacuation and general SHEQ awareness.',
        'General Hands' => 'General workers - manual handling, PPE, housekeeping and loading area safety.',
    ];

    public function run(): void
    {
        $blank = fn ($q) => $q->where(fn ($w) => $w->whereNull('description')->orWhere('description', ''));

        if (Schema::hasColumn('loss_categories', 'description')) {
            foreach (self::CATEGORIES as $name => $text) {
                $blank(DB::table('loss_categories')->where('name', $name))->update(['description' => $text]);
            }
        }

        if (Schema::hasColumn('loss_groups', 'description')) {
            foreach (self::GROUPS as $category => $groups) {
                $categoryIds = DB::table('loss_categories')->where('name', $category)->pluck('id');
                foreach ($groups as $name => $text) {
                    $blank(DB::table('loss_groups')->whereIn('loss_category_id', $categoryIds)->where('name', $name))
                        ->update(['description' => $text]);
                }
            }
        }

        if (Schema::hasColumn('losses', 'description')) {
            foreach (self::CAUSES as $category => $groups) {
                $categoryIds = DB::table('loss_categories')->where('name', $category)->pluck('id');
                foreach ($groups as $group => $causes) {
                    $groupIds = DB::table('loss_groups')->whereIn('loss_category_id', $categoryIds)->where('name', $group)->pluck('id');
                    foreach ($causes as $name => $text) {
                        $blank(DB::table('losses')->whereIn('loss_group_id', $groupIds)->where('name', $name))
                            ->update(['description' => $text]);
                    }
                }
            }
        }

        if (Schema::hasColumn('training_items', 'description')) {
            foreach (self::TRAINING_ITEMS as $name => $text) {
                $blank(DB::table('training_items')->where('name', $name))->update(['description' => $text]);
            }
        }

        if (Schema::hasColumn('training_departments', 'description')) {
            foreach (self::TRAINING_DEPARTMENTS as $name => $text) {
                $blank(DB::table('training_departments')->where('name', $name))->update(['description' => $text]);
            }
        }
    }
}
