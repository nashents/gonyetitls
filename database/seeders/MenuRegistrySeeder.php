<?php

namespace Database\Seeders;

use App\Models\Loss;
use App\Models\LossCategory;
use App\Models\LossGroup;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\SubModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuRegistrySeeder extends Seeder
{
    public function run(): void
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
  

       
// ---------------------------------
// Helpers: visibility builders
// ---------------------------------
$all = fn(array $flags) => ['all_flags' => $flags];
$any = fn(array $clauses) => ['any' => $clauses];

 // Common vis shortcuts
        $vInFinanceOrSuper = $any([
            $all(['inFinance']),
            $all(['isSuperAdmin']),
        ]);
        $vInFinanceStoresOrSuper = $any([
            $all(['inFinance']),
            $all(['inStores']),
            $all(['isSuperAdmin']),
        ]);

        $vInHROrSuper = $any([
            $all(['inHR']),
            $all(['isSuperAdmin']),
        ]);

        $vInTransportOrSuper = $any([
            $all(['inTransport']),
            $all(['isSuperAdmin']),
        ]);

        $vInHSEQOrSuper = $any([
            $all(['inHSEQ']),
            $all(['isSuperAdmin']),
        ]);

        $vInSecurityOrSuper = $any([
            $all(['inSecurity']),
            $all(['isSuperAdmin']),
        ]);

// ---------------------------------
// Helpers: preserve is_active if customized
// ---------------------------------
$finalIsActive = function (?object $existing, bool $seededIsActive): bool {
    return ($existing && (bool) $existing->is_customized)
        ? (bool) $existing->is_active
        : (bool) $seededIsActive;
};

// sort_order fallback: if not provided, use index
$finalSortOrder = function (array $data, int $indexSort = 0): int {
    return array_key_exists('sort_order', $data)
        ? (int) $data['sort_order']
        : (int) $indexSort;
};

// ---------------------------------
// Index counters (imperative seeder-friendly)
// ---------------------------------
$gIndex = 0;                     // group index
$mIndex = [];                    // module index per group slug
$sIndex = [];                    // submodule index per module key (groupSlug::moduleSlug)

// ---------------------------------
// Upserts
// ---------------------------------
$upsertGroup = function (array $g, ?int $indexSort = null) use (
    &$gIndex, &$mIndex, $finalIsActive, $finalSortOrder
) {
    $slug = $g['slug'] ?? Str::slug($g['name']);

    $existing = ModuleGroup::where('slug', $slug)->first();

    $seededIsActive = (bool) ($g['is_active'] ?? true);
    $isActive       = $finalIsActive($existing, $seededIsActive);

    // index fallback if sort_order not provided
    $indexSort = $indexSort ?? (++$gIndex * 10);

    $group = ModuleGroup::updateOrCreate(
        ['slug' => $slug],
        [
            'name'       => $g['name'],
            'icon'       => $g['icon'] ?? null,
            'sort_order' => $finalSortOrder($g, $indexSort),
            'is_active'  => $isActive,
            'visibility' => $g['visibility'] ?? null,
        ]
    );

    // reset module indexing for this group
    $mIndex[$slug] = 0;

    return $group;
};

$upsertModule = function (ModuleGroup $group, array $m, ?int $indexSort = null) use (
    &$mIndex, &$sIndex, $finalIsActive, $finalSortOrder
) {
    $slug = $m['slug'] ?? Str::slug($m['name']);

    $existing = Module::where('module_group_id', $group->id)
        ->where('slug', $slug)
        ->first();

    $seededIsActive = (bool) ($m['is_active'] ?? true);
    $isActive       = $finalIsActive($existing, $seededIsActive);

    $groupSlug = $group->slug ?? (ModuleGroup::find($group->id)?->slug);
    $mIndex[$groupSlug] = ($mIndex[$groupSlug] ?? 0) + 1;

    // index fallback if sort_order not provided
    $indexSort = $indexSort ?? ($mIndex[$groupSlug] * 10);

    $module = Module::updateOrCreate(
        ['module_group_id' => $group->id, 'slug' => $slug],
        [
            'module_group_id' => $group->id,
            'name'            => $m['name'],
            'icon'            => $m['icon'] ?? null,
            'route_name'      => $m['route_name'] ?? null,
            'url'             => $m['url'] ?? null,
            'route_params'    => $m['route_params'] ?? null,
            'badge_key'       => $m['badge_key'] ?? null,
            'sort_order'      => $finalSortOrder($m, $indexSort),
            'is_active'       => $isActive,
            'visibility'      => $m['visibility'] ?? null,
        ]
    );

    // reset submodule index for this module
    $sKey = ($groupSlug ?? 'group') . '::' . $slug;
    $sIndex[$sKey] = 0;

    return $module;
};

$upsertSub = function (Module $module, array $s, ?int $indexSort = null) use (
    &$sIndex, $finalIsActive, $finalSortOrder
) {
    $slug = $s['slug'] ?? Str::slug($s['name']);

    $existing = SubModule::where('module_id', $module->id)
        ->where('slug', $slug)
        ->first();

    $seededIsActive = (bool) ($s['is_active'] ?? true);
    $isActive       = $finalIsActive($existing, $seededIsActive);

    // build a stable module key for indexing
    $parent = $module->module_group_id . '::' . $module->id;
    $sIndex[$parent] = ($sIndex[$parent] ?? 0) + 1;

    // index fallback if sort_order not provided
    $indexSort = $indexSort ?? ($sIndex[$parent] * 10);

    return SubModule::updateOrCreate(
        ['module_id' => $module->id, 'slug' => $slug],
        [
            'module_id'     => $module->id,
            'name'          => $s['name'],
            'icon'          => $s['icon'] ?? null,
            'route_name'    => $s['route_name'] ?? null,
            'url'           => $s['url'] ?? null,
            'route_params'  => $s['route_params'] ?? null,
            'badge_key'     => $s['badge_key'] ?? null,
            'sort_order'    => $finalSortOrder($s, $indexSort),
            'is_active'     => $isActive,

            // null means inherit
            'visibility'    => $s['visibility'] ?? null,
        ]
    );
};

        // ----------------------------
        // GROUP: Main Category
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Main Category',
            'slug' => 'main-category',
            'sort_order' => 10,
            'visibility' => null,
        ]);

        // Dashboard
        $upsertModule($g, [
            'name' => 'Dashboard',
            'slug' => 'dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'route_name' => 'dashboard.index',
            'sort_order' => 10,
        ]);

        // Companies (only system admin + (admin or super))
        $m = $upsertModule($g, [
            'name' => 'Companies',
            'slug' => 'companies',
            'icon' => 'fas fa-building',
            'sort_order' => 20,
            'visibility' => $any([
                $all(['isSystemAdmin', 'isAdmin']),
                $all(['isSystemAdmin', 'isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, [
            'name' => 'Manage Companies',
            'slug' => 'manage-companies',
            'icon' => 'fas fa-list',
            'route_name' => 'companies.index',
            'sort_order' => 10,
            'visibility' => null, // inherit
        ]);

        // Reminders
         $m = $upsertModule($g, [
            'name' => 'Reminders',
            'slug' => 'reminders',
            'icon' => 'fas fa-bell',
            'sort_order' => 30,
              'visibility' => null,
        ]);
     
        $upsertSub($m, [
            'name' => 'Manage Reminders',
            'slug' => 'manage-reminders',
            'icon' => 'fas fa-list',
            'route_name' => 'reminders.index',
            'sort_order' => 10,
        ]);
         $upsertSub($m, [
            'name' => 'Reminder Copies',
            'slug' => 'reminder-copies',
            'icon' => 'fas fa-list',
            'route_name' => 'reminders.copy',
            'sort_order' => 20,
        ]);
        // Attendance Register
         $m = $upsertModule($g, [
            'name' => 'Attendance Register',
            'slug' => 'attendance-register',
            'icon' => 'fas fa-calendar',
            'sort_order' => 35,
            'visibility' => null,
        ]);

           $upsertSub($m, [
            'name' => 'Manage Register',
            'slug' => 'manage-reminders',
            'icon' => 'fas fa-list',
             'is_active' => false,
            'route_name' => '#',
            'sort_order' => 10,
        ]);
     
        $upsertSub($m, ['name'=>'Manage Attendances','slug'=>'manage-attendances','icon'=>'fas fa-list','route_name'=>'attendances.index','sort_order'=>15]);
        $upsertSub($m, ['name'=>'Pending Attendances','slug'=>'pending-attendances','icon'=>'fas fa-clock','route_name'=>'attendances.pending','sort_order'=>20,'badge_key'=>'attendances_pending_count']);
        $upsertSub($m, ['name'=>'Approved Attendances','slug'=>'approved-attendances','icon'=>'fas fa-check','route_name'=>'attendances.approved','sort_order'=>30,'badge_key'=>'attendances_approved_count']);
        $upsertSub($m, ['name'=>'Rejected Attendances','slug'=>'rejected-attendances','icon'=>'fas fa-ban','route_name'=>'attendances.rejected','sort_order'=>40,'badge_key'=>'attendances_rejected_count']);
        


        // Inbox (was global)
        $upsertModule($g, [
            'name' => 'Inbox',
            'slug' => 'inbox',
            'icon' => 'fas fa-envelope',
            'route_name' => 'emails.index',
            'sort_order' => 40,
        ]);

        // ----------------------------
        // GROUP: Human Resource
        // Visible for HR or Super
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Human Resource',
            'slug' => 'human-resource',
            'sort_order' => 20,
            'visibility' => null,
        ]);

        // HR Master (Admin+HR) OR Super
        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'hr-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
            'visibility' => $any([
                $all(['isAdmin', 'inHR']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $hrMasterSubs = [
            ['Allowances','allowances.index','fas fa-list'],
            ['Branches','branches.index','fas fa-list'],
            ['Departments','departments.index','fas fa-list', $any([$all(['isSystemAdmin']), $all(['isSuperAdmin'])])],
            ['Deductions','deductions.index','fas fa-list'],
            ['Earnings','earnings.index','fas fa-list'],
            ['Grades','grades.index','fas fa-list'],
            ['Job Titles','job_titles.index','fas fa-list'],
            ['Qualifications','qualifications.index','fas fa-list'],
            ['Leave Types','leave_types.index','fas fa-list'],
        ];

        $i = 10;
        foreach ($hrMasterSubs as $row) {
            $name = $row[0]; $route = $row[1]; $icon = $row[2];
            $vis = $row[3] ?? null;
            $upsertSub($m, [
                'name' => $name,
                'slug' => Str::slug($name),
                'icon' => $icon,
                'route_name' => $route,
                'sort_order' => $i,
                'visibility' => $vis, // null inherits module visibility
            ]);
            $i += 10;
        }

      
        $m = $upsertModule($g, [
            'name' => 'Recruitment',
            'slug' => 'recruitment',
            'icon' => 'fas fa-user-tie',
            'sort_order' => 15,
            'route_name' => Null,
            'visibility' => $any([
                $all(['inHR']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $upsertSub($m, ['name'=>'Manage Postings','slug'=>'manage-job-postings','icon'=>'fas fa-list','route_name'=>'job_postings.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Applications','slug'=>'manage-applications','icon'=>'fas fa-list','route_name'=>'applications.index','sort_order'=>20]);
    

        $m = $upsertModule($g, [
            'name' => 'Employees',
            'slug' => 'employees',
            'icon' => 'fas fa-users',
            'sort_order' => 20,
            'route_name' => 'employees.*',
            'visibility' => $any([
                $all(['inHR']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $upsertSub($m, ['name'=>'Create Employee','slug'=>'create-employee','icon'=>'fas fa-plus','route_name'=>'employees.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Employees','slug'=>'manage-employees','icon'=>'fas fa-list','route_name'=>'employees.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Manage Leave Days','slug'=>'manage-leave-days','icon'=>'fas fa-list','route_name'=>'employees.leaves.index','sort_order'=>30]);
        $upsertSub($m, ['name'=>'Archived Employees','slug'=>'archived-employees','icon'=>'fas fa-archive','route_name'=>'employees.archived','sort_order'=>40]);
        $upsertSub($m, ['name'=>'Deleted Employees','slug'=>'deleted-employees','icon'=>'fas fa-trash','route_name'=>'employees.deleted','sort_order'=>50]);

        // Head of Departments
        $upsertModule($g, [
            'name' => 'Head of Departments',
            'slug' => 'department-heads',
            'icon' => 'fas fa-user-plus',
            'route_name' => 'department_heads.index',
            'sort_order' => 30,
            'visibility' => $any([
                $all(['inHR']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        // Drivers (Transport OR HR OR Super)
        $m = $upsertModule($g, [
            'name' => 'Drivers',
            'slug' => 'drivers',
            'icon' => 'fas fa-users',
            'route_name' => 'drivers.*',
            'sort_order' => 40,
            'visibility' => $any([
                $all(['inTransport']),
                $all(['inHR']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $upsertSub($m, ['name'=>'Create Driver','slug'=>'create-driver','icon'=>'fas fa-plus','route_name'=>'drivers.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Drivers','slug'=>'manage-drivers','icon'=>'fas fa-list','route_name'=>'drivers.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Archived Employees','slug'=>'archived-drivers','icon'=>'fas fa-archive','route_name'=>'drivers.archived','sort_order'=>30]);

        // Leave Management (public, but management sub-items restricted)
        $m = $upsertModule($g, [
            'name' => 'Leave Management',
            'slug' => 'leave-management',
            'icon' => 'fas fa-calendar',
            'route_name' => 'leaves.*',
            'sort_order' => 50,
            'visibility' => [], // explicit public, even if HR group hidden for some companies
        ]);

        $upsertSub($m, ['name'=>'Apply for leave','slug'=>'apply-for-leave','icon'=>'fas fa-plus','route_name'=>'leaves.index','sort_order'=>10, 'visibility'=>[]]);
        $upsertSub($m, ['name'=>'My Team','slug'=>'my-team','icon'=>'fas fa-users','route_name'=>'leaves.myteam','sort_order'=>20, 'visibility'=>[]]);

        $leaveManageVis = $any([
            $all(['hasHRDeptHead']),
            $all(['isAdmin','inHR']),
            $all(['isManagement','inHR']),
            $all(['isSuperAdmin']),
        ]);

        $upsertSub($m, ['name'=>'Manage Applications','slug'=>'manage-applications','icon'=>'fas fa-list','route_name'=>'leaves.manage','sort_order'=>30,'visibility'=>$leaveManageVis]);
        $upsertSub($m, ['name'=>'Pending Applications','slug'=>'pending-applications','icon'=>'fas fa-clock','route_name'=>'leaves.pending','sort_order'=>40,'badge_key'=>'leaves_pending_count','visibility'=>$leaveManageVis]);
        $upsertSub($m, ['name'=>'Approved Applications','slug'=>'approved-applications','icon'=>'fas fa-check','route_name'=>'leaves.approved','sort_order'=>50,'badge_key'=>'leaves_approved_count','visibility'=>$leaveManageVis]);
        $upsertSub($m, ['name'=>'Rejected Applications','slug'=>'rejected-applications','icon'=>'fas fa-ban','route_name'=>'leaves.rejected','sort_order'=>60,'badge_key'=>'leaves_rejected_count','visibility'=>$leaveManageVis]);

        // ----------------------------
        // GROUP: Salaries & Payroll
        // (header existed always) -> keep group public
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Salaries & Payroll',
            'slug' => 'salaries-payroll',
            'sort_order' => 30,
            'visibility' => null,
        ]);

        // Master (Admin+HR) OR Super
        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'salary-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
            'visibility' => $any([
                $all(['isAdmin','inHR']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $upsertSub($m, ['name'=>'Allowances','slug'=>'allowances','icon'=>'fas fa-list','route_name'=>'allowances.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Deductions','slug'=>'deductions','icon'=>'fas fa-list','route_name'=>'deductions.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Earnings','slug'=>'earnings','icon'=>'fas fa-list','route_name'=>'earnings.index','sort_order'=>30]);
        $upsertSub($m, ['name'=>'Loan Type','slug'=>'loan-type','icon'=>'fas fa-list','route_name'=>'loan_types.index','sort_order'=>40]);
        $upsertSub($m, [
            'name'=>'Tax Table',
            'slug'=>'tax-table',
            'icon'=>'fas fa-list',
            'route_name'=>'tax_brackets.index',
            'sort_order'=>50,
            'visibility' => $any([$all(['isSystemAdmin'])]),
        ]);

        // My Payslip (public)
        $upsertModule($g, [
            'name' => 'My Payslip',
            'slug' => 'my-payslip',
            'icon' => 'fas fa-file',
            'route_name' => 'payslips.index',
            'sort_order' => 20,
            'visibility' => null,
        ]);

        // Loans (public module, restricted management sub-items)
        $m = $upsertModule($g, [
            'name' => 'Loans',
            'slug' => 'loans',
            'icon' => 'fas fa-credit-card',
            'route_name' => 'loans.*',
            'sort_order' => 30,
            'visibility' => null,
        ]);

        $upsertSub($m, ['name'=>'My Applications','slug'=>'my-applications','icon'=>'fas fa-arrow-right','route_name'=>'loans.myloans','sort_order'=>10,'visibility'=>[]]);

        $loanManageVis = $any([
            $all(['hasFinanceDeptHead']),
            $all(['isManagement','inHR']),
            $all(['isManagement','inFinance']),
            $all(['isSuperAdmin']),
        ]);

        $upsertSub($m, ['name'=>'Manage Loans','slug'=>'manage-loans','icon'=>'fas fa-list','route_name'=>'loans.index','sort_order'=>20,'visibility'=>$loanManageVis]);
        $upsertSub($m, ['name'=>'Pending Loans','slug'=>'pending-loans','icon'=>'fas fa-clock','route_name'=>'loans.pending','sort_order'=>30,'badge_key'=>'loans_pending_count','visibility'=>$loanManageVis]);
        $upsertSub($m, ['name'=>'Approved Loans','slug'=>'approved-loans','icon'=>'fas fa-check','route_name'=>'loans.approved','sort_order'=>40,'badge_key'=>'loans_approved_count','visibility'=>$loanManageVis]);
        $upsertSub($m, ['name'=>'Rejected Loans','slug'=>'rejected-loans','icon'=>'fas fa-ban','route_name'=>'loans.rejected','sort_order'=>50,'badge_key'=>'loans_rejected_count','visibility'=>$loanManageVis]);

        // Salaries (Admin+HR) OR Super
        $m = $upsertModule($g, [
            'name' => 'Salaries',
            'slug' => 'salaries',
            'icon' => 'fas fa-donate',
            'route_name' => 'salaries.*',
            'sort_order' => 40,
            'visibility' => $any([
                $all(['isAdmin','inHR']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Create Salary','slug'=>'create-salary','icon'=>'fas fa-plus','route_name'=>'salaries.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Salaries','slug'=>'manage-salaries','icon'=>'fas fa-list','route_name'=>'salaries.index','sort_order'=>20]);

        // Payroll (Admin+HR) OR Super
        $m = $upsertModule($g, [
            'name' => 'Payroll',
            'slug' => 'payroll',
            'icon' => 'fas fa-file',
            'route_name' => 'payrolls.*',
            'sort_order' => 50,
            'visibility' => $any([
                $all(['isAdmin','inHR']),
                $all(['isManagement','inHR']),
                $all(['hasHRDeptHead']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $upsertSub($m, ['name'=>'Manage Payrolls','slug'=>'manage-payrolls','icon'=>'fas fa-list','route_name'=>'payrolls.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Pending Payrolls','slug'=>'pending-payrolls','icon'=>'fas fa-clock','route_name'=>'payrolls.pending','sort_order'=>20,'badge_key'=>'payrolls_pending_count']);
        $upsertSub($m, ['name'=>'Approved Payrolls','slug'=>'approved-payrolls','icon'=>'fas fa-check','route_name'=>'payrolls.approved','sort_order'=>30,'badge_key'=>'payrolls_approved_count']);
        $upsertSub($m, ['name'=>'Rejected Payrolls','slug'=>'rejected-payrolls','icon'=>'fas fa-ban','route_name'=>'payrolls.rejected','sort_order'=>40,'badge_key'=>'payrolls_rejected_count']);

        // ----------------------------
        // GROUP: Sales & Payments
        // Finance or Super
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Sales & Payments',
            'slug' => 'sales-payments',
            'sort_order' => 40,
            'visibility' => $vInFinanceOrSuper,
        ]);

        // Master (Admin & Finance & HR) OR Super
        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'sales-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
            'visibility' => $any([
                $all(['isAdmin','inFinance']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Currencies','slug'=>'currencies','icon'=>'fas fa-money-bill-alt','route_name'=>'currencies.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Payment Methods','slug'=>'payment-methods','icon'=>'fas fa-list','route_name'=>'payment_methods.index','sort_order'=>20]);

        // Quotations
        $m = $upsertModule($g, [
            'name' => 'Quotations',
            'slug' => 'quotations',
            'icon' => 'fas fa-file-invoice',
            'route_name' => 'quotations.*',
            'sort_order' => 20,
        ]);
        $upsertSub($m, ['name'=>'Create Quotation','slug'=>'create-quotation','icon'=>'fas fa-plus','route_name'=>'quotations.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Quotations','slug'=>'manage-quotations','icon'=>'fas fa-list','route_name'=>'quotations.index','sort_order'=>20]);

        // Invoices
        $m = $upsertModule($g, [
            'name' => 'Invoices',
            'slug' => 'invoices',
            'icon' => 'fas fa-file-invoice-dollar',
            'route_name' => 'invoices.*',
            'sort_order' => 30,
        ]);
        $upsertSub($m, ['name'=>'Create Invoice','slug'=>'create-invoice','icon'=>'fas fa-plus','route_name'=>'invoices.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Invoices','slug'=>'manage-invoices','icon'=>'fas fa-list','route_name'=>'invoices.index','sort_order'=>20]);

        $invoiceManageVis = $any([
            $all(['hasFinanceDeptHead']),
            $all(['isAdmin','inFinance']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Invoices','slug'=>'pending-invoices','icon'=>'fas fa-clock','route_name'=>'invoices.pending','sort_order'=>30,'badge_key'=>'invoices_pending_count','visibility'=>$invoiceManageVis]);
        $upsertSub($m, ['name'=>'Approved Invoices','slug'=>'approved-invoices','icon'=>'fas fa-check','route_name'=>'invoices.approved','sort_order'=>40,'badge_key'=>'invoices_approved_count','visibility'=>$invoiceManageVis]);
        $upsertSub($m, ['name'=>'Rejected Invoices','slug'=>'rejected-invoices','icon'=>'fas fa-ban','route_name'=>'invoices.rejected','sort_order'=>50,'badge_key'=>'invoices_rejected_count','visibility'=>$invoiceManageVis]);
        $upsertSub($m, ['name'=>'Deleted Invoices','slug'=>'deleted-invoices','icon'=>'fas fa-trash','route_name'=>'invoices.deleted','sort_order'=>60,'badge_key'=>'invoices_deleted_count','visibility'=>$invoiceManageVis]);

        // Customer Statements
        $m = $upsertModule($g, [
            'name' => 'Customer Statements',
            'slug' => 'customer-statements',
            'icon' => 'fas fa-file-invoice-dollar',
            'route_name' => 'customer_statements.index',
            'sort_order' => 40,
        ]);
        $upsertSub($m, ['name'=>'Manage Statements','slug'=>'manage-statements','icon'=>'fas fa-list','route_name'=>'customer_statements.index','sort_order'=>10]);

        // Credit Notes
        $m = $upsertModule($g, [
            'name' => 'Credit Notes',
            'slug' => 'credit-notes',
            'icon' => 'fas fa-file-invoice-dollar',
            'route_name' => 'credit_notes.*',
            'sort_order' => 50,
        ]);
        $upsertSub($m, ['name'=>'Create','slug'=>'create-credit-note','icon'=>'fas fa-plus','route_name'=>'credit_notes.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage C Notes','slug'=>'manage-credit-notes','icon'=>'fas fa-list','route_name'=>'credit_notes.index','sort_order'=>20]);

        $cnManageVis = $any([
            $all(['hasFinanceDeptHead']),
            $all(['isAdmin','inFinance']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending C Notes','slug'=>'pending-credit-notes','icon'=>'fas fa-clock','route_name'=>'credit_notes.pending','sort_order'=>30,'badge_key'=>'credit_notes_pending_count','visibility'=>$cnManageVis]);
        $upsertSub($m, ['name'=>'Approved C Notes','slug'=>'approved-credit-notes','icon'=>'fas fa-check','route_name'=>'credit_notes.approved','sort_order'=>40,'badge_key'=>'credit_notes_approved_count','visibility'=>$cnManageVis]);
        $upsertSub($m, ['name'=>'Rejected C Notes','slug'=>'rejected-credit-notes','icon'=>'fas fa-ban','route_name'=>'credit_notes.rejected','sort_order'=>50,'badge_key'=>'credit_notes_rejected_count','visibility'=>$cnManageVis]);
        $upsertSub($m, ['name'=>'Deleted C Notes','slug'=>'deleted-credit-notes','icon'=>'fas fa-trash','route_name'=>'credit_notes.deleted','sort_order'=>60,'badge_key'=>'credit_notes_deleted_count','visibility'=>$cnManageVis]);

        // Payments
        $m = $upsertModule($g, [
            'name' => 'Payments',
            'slug' => 'payments',
            'icon' => 'fas fa-credit-card',
            'route_name' => 'payments.index',
            'sort_order' => 60,
        ]);
        $upsertSub($m, ['name'=>'Manage Payments','slug'=>'manage-payments','icon'=>'fas fa-list','route_name'=>'payments.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Receipts','slug'=>'manage-receipts','icon'=>'fas fa-list','route_name'=>'receipts.index','sort_order'=>20]);

        // Products & Services (Invoices)
        $m = $upsertModule($g, [
            'name' => 'Products & Services',
            'slug' => 'products-services-invoices',
            'icon' => 'fas fa-boxes',
            'route_name' => 'product_services.all',
            'route_params' => ['category' => 'invoices'],
            'sort_order' => 70,
        ]);
        $upsertSub($m, [
            'name'=>'Manage P & S',
            'slug'=>'manage-ps-invoices',
            'icon'=>'fas fa-list',
            'route_name'=>'product_services.all',
            'route_params'=>['category'=>'invoices'],
            'sort_order'=>10,
        ]);

        // Customers
        $upsertModule($g, [
            'name' => 'Customers',
            'slug' => 'customers',
            'icon' => 'fas fa-user-friends',
            'route_name' => 'customers.index',
            'sort_order' => 80,
        ]);

        // // Accounts Receivable
        // $upsertModule($g, [
        //     'name' => 'Accounts Receivable',
        //     'slug' => 'accounts-receivable',
        //     'icon' => 'fas fa-list',
        //     'route_name' => 'accounts.receivable',
        //     'sort_order' => 90,
        // ]);

        // ----------------------------
        // GROUP: Purchases (header existed always) -> group public
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Purchases',
            'slug' => 'purchases',
            'sort_order' => 50,
            'visibility' => null,
        ]);

        // Bills (Finance or Super)
        $m = $upsertModule($g, [
            'name' => 'Bills',
            'slug' => 'bills',
            'icon' => 'fas fa-th-list',
            'route_name' => 'bills.*',
            'sort_order' => 10,
            'visibility' => $vInFinanceOrSuper,
        ]);
        $upsertSub($m, ['name'=>'Create Bill','slug'=>'create-bill','icon'=>'fas fa-plus','route_name'=>'bills.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Bills','slug'=>'manage-bills','icon'=>'fas fa-list','route_name'=>'bills.index','sort_order'=>20]);

        $billManageVis = $any([
            $all(['hasFinanceDeptHead']),
            $all(['isAdmin','inFinance']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Bills','slug'=>'pending-bills','icon'=>'fas fa-clock','route_name'=>'bills.pending','sort_order'=>30,'badge_key'=>'bills_pending_count','visibility'=>$billManageVis]);
        $upsertSub($m, ['name'=>'Approved Bills','slug'=>'approved-bills','icon'=>'fas fa-check','route_name'=>'bills.approved','sort_order'=>40,'badge_key'=>'bills_approved_count','visibility'=>$billManageVis]);
        $upsertSub($m, ['name'=>'Rejected Bills','slug'=>'rejected-bills','icon'=>'fas fa-ban','route_name'=>'bills.rejected','sort_order'=>50,'badge_key'=>'bills_rejected_count','visibility'=>$billManageVis]);

        // Vendor Statements (Finance or Super)
        $m = $upsertModule($g, [
            'name' => 'Vendor Statements',
            'slug' => 'vendor-statements',
            'icon' => 'fas fa-file-invoice-dollar',
            'route_name' => 'vendor_statements.index',
            'sort_order' => 20,
            'visibility' => $vInFinanceOrSuper,
        ]);
        $upsertSub($m, ['name'=>'Manage Statements','slug'=>'manage-vendor-statements','icon'=>'fas fa-list','route_name'=>'vendor_statements.index','sort_order'=>10]);

        // Products & Services (Bills)
        $m = $upsertModule($g, [
            'name' => 'Products & Services',
            'slug' => 'products-services-bills',
            'icon' => 'fas fa-boxes',
            'route_name' => 'product_services.all',
            'route_params' => ['category' => 'bills'],
            'sort_order' => 30,
            'visibility' => $vInFinanceOrSuper,
        ]);
        $upsertSub($m, [
            'name'=>'Manage P & S',
            'slug'=>'manage-ps-bills',
            'icon'=>'fas fa-list',
            'route_name'=>'product_services.all',
            'route_params'=>['category'=>'bills'],
            'sort_order'=>10,
        ]);

        // Vendors
        $upsertModule($g, [
            'name' => 'Vendors',
            'slug' => 'vendors',
            'icon' => 'fas fa-user-friends',
            'route_name' => 'vendors.index',
            'sort_order' => 40,
            'visibility' => $vInFinanceOrSuper,
        ]);

        // // Accounts Payable
        // $upsertModule($g, [
        //     'name' => 'Accounts Payable',
        //     'slug' => 'accounts-payable',
        //     'icon' => 'fas fa-list',
        //     'route_name' => 'accounts.payable',
        //     'sort_order' => 50,
        //     'visibility' => $vInFinanceOrSuper,
        // ]);

        // Requisitions (was global)
        $m = $upsertModule($g, [
            'name' => 'Requisitions',
            'slug' => 'requisitions',
            'icon' => 'fas fa-hand-holding-usd',
            'route_name' => 'requisitions.*',
            'sort_order' => 60,
            'visibility' => null,
        ]);
        $upsertSub($m, ['name'=>'Manage Requisitions','slug'=>'manage-requisitions','icon'=>'fas fa-list','route_name'=>'requisitions.index','sort_order'=>10]);

        $reqManageVis = $any([
            $all(['hasFinanceDeptHead']),
            $all(['isAdmin','inFinance']),
            $all(['isAdmin','inStores']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Requisitions','slug'=>'pending-requisitions','icon'=>'fas fa-clock','route_name'=>'requisitions.pending','sort_order'=>20,'badge_key'=>'requisitions_pending_count','visibility'=>$reqManageVis]);
        $upsertSub($m, ['name'=>'Approved Requisitions','slug'=>'approved-requisitions','icon'=>'fas fa-check','route_name'=>'requisitions.approved','sort_order'=>30,'badge_key'=>'requisitions_approved_count','visibility'=>$reqManageVis]);
        $upsertSub($m, ['name'=>'Rejected Requisitions','slug'=>'rejected-requisitions','icon'=>'fas fa-ban','route_name'=>'requisitions.rejected','sort_order'=>40,'badge_key'=>'requisitions_rejected_count','visibility'=>$reqManageVis]);

        // ----------------------------
        // GROUP: Accounting (Finance or Super)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Accounting',
            'slug' => 'accounting',
            'sort_order' => 60,
            'visibility' => $vInFinanceOrSuper,
        ]);

        $upsertModule($g, [
            'name' => 'Transactions',
            'slug' => 'transactions',
            'icon' => 'fas fa-money-check',
            'route_name' => 'transactions.index',
            'sort_order' => 10,
        ]);

        $m = $upsertModule($g, [
            'name' => 'Charts of Accounts',
            'slug' => 'charts-of-accounts',
            'icon' => 'fas fa-balance-scale',
            'route_name' => 'accounts.index',
            'sort_order' => 20,
        ]);
        $upsertSub($m, ['name'=>'Manage Accounts','slug'=>'manage-accounts','icon'=>'fas fa-list','route_name'=>'accounts.index','sort_order'=>10]);
        $upsertSub($m, [
            'name'=>'Manage Sales Taxes',
            'slug'=>'manage-sales-taxes',
            'icon'=>'fas fa-list',
            'route_name'=>'accounts.tax',
            'sort_order'=>20,
            'visibility'=>$any([$all(['isAdmin','inFinance']), $all(['isSuperAdmin'])]),
        ]);

        $upsertModule($g, [
            'name' => 'Bank Accounts',
            'slug' => 'bank-accounts',
            'icon' => 'fas fa-bank',
            'route_name' => 'bank_accounts.index',
            'sort_order' => 30,
        ]);

        $upsertModule($g, [
            'name' => 'Currency Exchange Rates',
            'slug' => 'exchange-rates',
            'icon' => 'fas fa-exchange',
            'route_name' => 'exchange_rates.index',
            'sort_order' => 40,
        ]);

        // ----------------------------
        // GROUP: Asset Management (Finance or Super)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Asset Management',
            'slug' => 'asset-management',
            'sort_order' => 70,
            'visibility' => $vInFinanceStoresOrSuper,
        ]);

        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'asset-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
        ]);
        $upsertSub($m, ['name'=>'Manage Categories','slug'=>'asset-categories','icon'=>'fas fa-list','route_name'=>'categories.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Attributes','slug'=>'asset-attributes','icon'=>'fas fa-list','route_name'=>'attributes.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Manage Brands','slug'=>'asset-brands','icon'=>'fas fa-list','route_name'=>'brands.index','sort_order'=>30]);

        $m = $upsertModule($g, [
            'name' => 'Products',
            'slug' => 'asset-products',
            'icon' => 'fas fa-boxes',
            'route_name' => 'products.*',
            'sort_order' => 20,
        ]);
        $upsertSub($m, ['name'=>'Create Product','slug'=>'create-product','icon'=>'fas fa-plus','route_name'=>'products.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Products','slug'=>'manage-products','icon'=>'fas fa-list','route_name'=>'products.index','sort_order'=>20]);

        $m = $upsertModule($g, [
            'name' => 'Purchase Orders',
            'slug' => 'asset-purchase-orders',
            'icon' => 'fas fa-hand-holding-usd',
            'route_name' => 'purchases.*',
            'sort_order' => 30,
        ]);
        $upsertSub($m, ['name'=>'Manage Orders','slug'=>'manage-asset-orders','icon'=>'fas fa-list','route_name'=>'purchases.index','sort_order'=>10]);

        $assetPOVis = $any([
            $all(['hasFinanceDeptHead']),
            $all(['isAdmin','inFinance']),
            $all(['isAdmin','inStores']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Orders','slug'=>'pending-asset-orders','icon'=>'fas fa-clock','route_name'=>'purchases.pending','sort_order'=>20,'badge_key'=>'asset_purchases_pending_count','visibility'=>$assetPOVis]);
        $upsertSub($m, ['name'=>'Approved Orders','slug'=>'approved-asset-orders','icon'=>'fas fa-check','route_name'=>'purchases.approved','sort_order'=>30,'badge_key'=>'asset_purchases_approved_count','visibility'=>$assetPOVis]);
        $upsertSub($m, ['name'=>'Rejected Orders','slug'=>'rejected-asset-orders','icon'=>'fas fa-ban','route_name'=>'purchases.rejected','sort_order'=>40,'badge_key'=>'asset_purchases_rejected_count','visibility'=>$assetPOVis]);
        $upsertSub($m, ['name'=>'Deleted Orders','slug'=>'deleted-asset-orders','icon'=>'fas fa-trash','route_name'=>'purchases.deleted','sort_order'=>50,'badge_key'=>'asset_purchases_deleted_count','visibility'=>$assetPOVis]);

        $m = $upsertModule($g, [
            'name' => 'GRV (Assets)',
            'slug' => 'grv-assets',
            'icon' => 'fas fa-th-list',
            'route_name' => 'goods_receiveds.assets',
            'sort_order' => 40,
        ]);
        $upsertSub($m, ['name'=>'Manage Assets GRVs','slug'=>'manage-assets-grvs','icon'=>'fas fa-list','route_name'=>'goods_receiveds.assets','sort_order'=>10]);

        $m = $upsertModule($g, [
            'name' => 'Assets',
            'slug' => 'assets',
            'icon' => 'fas fa-th-list',
            'route_name' => 'assets.*',
            'sort_order' => 50,
        ]);
        $upsertSub($m, ['name'=>'Create Asset','slug'=>'create-asset','icon'=>'fas fa-plus','route_name'=>'assets.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Assets','slug'=>'manage-assets','icon'=>'fas fa-list','route_name'=>'assets.index','sort_order'=>20]);

        $m = $upsertModule($g, [
            'name' => 'Dispatches (Assets)',
            'slug' => 'asset-dispatches',
            'icon' => 'fas fa-list',
            'route_name' => 'asset_dispatches.*',
            'sort_order' => 60,
        ]);
        $upsertSub($m, ['name'=>'Manage Dispatches','slug'=>'manage-asset-dispatches','icon'=>'fas fa-list','route_name'=>'asset_dispatches.index','sort_order'=>10]);

        $assetDispatchVis = $any([
            $all(['isAdmin','inFinance']),
            $all(['isAdmin','inStores']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Dispatches','slug'=>'pending-asset-dispatches','icon'=>'fas fa-clock','route_name'=>'asset_dispatches.pending','sort_order'=>20,'badge_key'=>'asset_dispatches_pending_count','visibility'=>$assetDispatchVis]);
        $upsertSub($m, ['name'=>'Approved Dispatches','slug'=>'approved-asset-dispatches','icon'=>'fas fa-check','route_name'=>'asset_dispatches.approved','sort_order'=>30,'badge_key'=>'asset_dispatches_approved_count','visibility'=>$assetDispatchVis]);
        $upsertSub($m, ['name'=>'Rejected Dispatches','slug'=>'rejected-asset-dispatches','icon'=>'fas fa-ban','route_name'=>'asset_dispatches.rejected','sort_order'=>40,'badge_key'=>'asset_dispatches_rejected_count','visibility'=>$assetDispatchVis]);

        // ----------------------------
        // GROUP: SHEQ (HSEQ or Super)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'SHEQ',
            'slug' => 'sheq',
            'sort_order' => 80,
            'visibility' => $vInHSEQOrSuper,
        ]);

        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'sheq-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
            'visibility' => $any([
                $all(['isAdmin','inHSEQ']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Cause Categories','slug'=>'cause-categories','icon'=>'fas fa-list','route_name'=>'loss_categories.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Cause Groups','slug'=>'cause-groups','icon'=>'fas fa-list','route_name'=>'loss_groups.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Loss Causes','slug'=>'loss-causes','icon'=>'fas fa-list','route_name'=>'losses.index','sort_order'=>30]);
        $upsertSub($m, ['name'=>'Waste Receptacles','slug'=>'waste-receptacles','icon'=>'fas fa-list','route_name'=>'waste_receptacles.index','sort_order'=>35]);
        $upsertSub($m, ['name'=>'Waste Types','slug'=>'waste-types','icon'=>'fas fa-list','route_name'=>'waste_types.index','sort_order'=>40]);
       

        $m = $upsertModule($g, [
            'name' => 'Incidents',
            'slug' => 'incidents',
            'icon' => 'fas fa-exclamation-triangle',
            'route_name' => 'incidents.*',
            'sort_order' => 20,
        ]);
        $upsertSub($m, ['name'=>'Create Incidents','slug'=>'create-incidents','icon'=>'fas fa-plus','route_name'=>'incidents.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Incidents','slug'=>'manage-incidents','icon'=>'fas fa-list','route_name'=>'incidents.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Pending Incidents','slug'=>'pending-incidents','icon'=>'fas fa-clock','route_name'=>'incidents.pending','sort_order'=>30,'badge_key'=>'incidents_pending_count']);
        $upsertSub($m, ['name'=>'Approved Incidents','slug'=>'approved-incidents','icon'=>'fas fa-check','route_name'=>'incidents.approved','sort_order'=>40,'badge_key'=>'incidents_approved_count']);
        $upsertSub($m, ['name'=>'Rejected Incidents','slug'=>'rejected-incidents','icon'=>'fas fa-ban','route_name'=>'incidents.rejected','sort_order'=>50,'badge_key'=>'incidents_rejected_count']);
        
        $m = $upsertModule($g, [
            'name' => 'Waste Collection',
            'slug' => 'waste-collections',
            'icon' => 'fas fa-tasks',
            'route_name' => 'waste_collections.*',
            'sort_order' => 25,
        ]);
        $upsertSub($m, ['name'=>'Manage Collection','slug'=>'manage-waste-collections','icon'=>'fas fa-list','route_name'=>'waste_collections.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Pending Collections','slug'=>'pending-waste-collections','icon'=>'fas fa-clock','route_name'=>'waste_collections.pending','sort_order'=>20,'badge_key'=>'waste_collections_pending_count']);
        $upsertSub($m, ['name'=>'Approved Collections','slug'=>'approved-waste-collections','icon'=>'fas fa-check','route_name'=>'waste_collections.approved','sort_order'=>30,'badge_key'=>'waste_collections_approved_count']);
        $upsertSub($m, ['name'=>'Rejected Collections','slug'=>'rejected-waste-collections','icon'=>'fas fa-ban','route_name'=>'waste_collections.rejected','sort_order'=>40,'badge_key'=>'waste_collections_rejected_count']);
        
        $m = $upsertModule($g, [
            'name' => 'Waste Disposal',
            'slug' => 'waste-disposals',
            'icon' => 'fas fa-remove',
            'route_name' => 'waste_disposal.*',
            'sort_order' => 26,
        ]);
        $upsertSub($m, ['name'=>'Manage Disposals','slug'=>'manage-waste-disposals','icon'=>'fas fa-list','route_name'=>'waste_disposals.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Pending Disposals','slug'=>'pending-waste-disposals','icon'=>'fas fa-clock','route_name'=>'waste_disposals.pending','sort_order'=>20,'badge_key'=>'waste_disposals_pending_count']);
        $upsertSub($m, ['name'=>'Approved Disposals','slug'=>'approved-waste-disposals','icon'=>'fas fa-check','route_name'=>'waste_disposals.approved','sort_order'=>30,'badge_key'=>'waste_disposals_pending_count']);
        $upsertSub($m, ['name'=>'Rejected Disposals','slug'=>'rejected-waste-disposals','icon'=>'fas fa-ban','route_name'=>'waste_disposals.rejected','sort_order'=>40,'badge_key'=>'waste_disposals_pending_count']);


        $m = $upsertModule($g, [
            'name' => 'Age Pyramid',
            'slug' => 'age-pyramid',
            'icon' => 'fas fa-hourglass',
            'sort_order' => 30,
        ]);
        $upsertSub($m, ['name'=>'Customers','slug'=>'age-customers','icon'=>'fas fa-list','route_name'=>'customers.age','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Drivers','slug'=>'age-drivers','icon'=>'fas fa-list','route_name'=>'drivers.age','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Employees','slug'=>'age-employees','icon'=>'fas fa-list','route_name'=>'employees.age','sort_order'=>30]);
        $upsertSub($m, ['name'=>'Horses','slug'=>'age-horses','icon'=>'fas fa-list','route_name'=>'horses.age','sort_order'=>40]);
        $upsertSub($m, ['name'=>'Trailers','slug'=>'age-trailers','icon'=>'fas fa-list','route_name'=>'trailers.age','sort_order'=>50]);
        $upsertSub($m, ['name'=>'Vehicles','slug'=>'age-vehicles','icon'=>'fas fa-list','route_name'=>'vehicles.age','sort_order'=>60]);
        $upsertSub($m, ['name'=>'Vendors','slug'=>'age-vendors','icon'=>'fas fa-list','route_name'=>'vendors.age','sort_order'=>70]);

        $m = $upsertModule($g, [
            'name' => 'Compliance',
            'slug' => 'compliance',
            'icon' => 'fas fa-check',
            'route_name' => 'compliances.index',
            'sort_order' => 40,
        ]);
        $upsertSub($m, ['name'=>'Driver - Route Compliance','slug'=>'driver-route-compliance','icon'=>'fas fa-list','route_name'=>'compliances.index','sort_order'=>10]);

        $m = $upsertModule($g, [
            'name' => 'Training Workshops',
            'slug' => 'training-workshops',
            'icon' => 'fas fa-school',
            'sort_order' => 50,
        ]);
        $upsertSub($m, ['name'=>'What to train?','slug'=>'training-items','icon'=>'fas fa-list','route_name'=>'training_items.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Who to train?','slug'=>'training-departments','icon'=>'fas fa-list','route_name'=>'training_departments.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Who needs training?','slug'=>'training-requirements','icon'=>'fas fa-list','route_name'=>'training_requirements.index','sort_order'=>30]);
        $upsertSub($m, ['name'=>'Training Plan','slug'=>'training-plans','icon'=>'fas fa-list','route_name'=>'training_plans.index','sort_order'=>40]);
        $upsertSub($m, ['name'=>'Training Program','slug'=>'trainings','icon'=>'fas fa-list','route_name'=>'trainings.index','sort_order'=>50]);

        // Documents (only when has HSEQ department context)
        $m = $upsertModule($g, [
            'name' => 'Documents',
            'slug' => 'hseq-documents',
            'icon' => 'fas fa-file',
            'sort_order' => 60,
            'visibility' => $any([
                $all(['inHSEQ']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, [
            'name' => 'Manage Documents',
            'slug' => 'manage-documents',
            'icon' => 'fas fa-list',
            'route_name' => 'documents.all',
            'route_params' => ['id' => '{hseq_department_id}', 'category' => 'department'],
            'sort_order' => 10,
        ]);

        // ----------------------------
        // GROUP: General Access (Security or Super)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'General Access',
            'slug' => 'general-access',
            'sort_order' => 90,
            'visibility' => $vInSecurityOrSuper,
        ]);

        $m = $upsertModule($g, [
            'name' => 'Gatepass',
            'slug' => 'gatepass-security',
            'icon' => 'fas fa-door-open',
            'sort_order' => 10,
            'route_name' => 'gate_passes.*',
        ]);
        $upsertSub($m, ['name'=>'Manage Gatepasses','slug'=>'manage-gatepasses','icon'=>'fas fa-list','route_name'=>'gate_passes.index','sort_order'=>10]);
        $upsertSub($m, [
            'name'=>'Pending Gatepasses','slug'=>'pending-gatepasses-security','icon'=>'fas fa-clock',
            'route_name'=>'gate_passes.pending','route_params'=>['department'=>'security'],
            'sort_order'=>20,'badge_key'=>'gate_passes_pending_count'
        ]);
        $upsertSub($m, [
            'name'=>'Approved Gatepasses','slug'=>'approved-gatepasses-security','icon'=>'fas fa-check',
            'route_name'=>'gate_passes.approved','route_params'=>['department'=>'security'],
            'sort_order'=>30,'badge_key'=>'gate_passes_approved_count'
        ]);
        $upsertSub($m, [
            'name'=>'Rejected Gatepasses','slug'=>'rejected-gatepasses-security','icon'=>'fas fa-ban',
            'route_name'=>'gate_passes.rejected','route_params'=>['department'=>'security'],
            'sort_order'=>40,'badge_key'=>'gate_passes_rejected_count'
        ]);

        $m = $upsertModule($g, [
            'name' => 'Groups',
            'slug' => 'security-groups',
            'icon' => 'fas fa-users',
            'sort_order' => 20,
            'route_name' => 'groups.index',
        ]);
        $upsertSub($m, ['name'=>'Manage Groups','slug'=>'manage-groups','icon'=>'fas fa-list','route_name'=>'groups.index','sort_order'=>10]);

        $m = $upsertModule($g, [
            'name' => 'Visitors',
            'slug' => 'visitors',
            'icon' => 'fas fa-user-friends',
            'sort_order' => 30,
            'route_name' => 'visitors.index',
        ]);
        $upsertSub($m, ['name'=>'Manage Visitors','slug'=>'manage-visitors','icon'=>'fas fa-list','route_name'=>'visitors.index','sort_order'=>10]);

        // ----------------------------
        // GROUP: Fleet Management
        // (Transport OR Workshop Dept OR Super) AND NOT driver
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Fleet Management',
            'slug' => 'fleet-management',
            'sort_order' => 100,
            'visibility' => $any([
                $all(['isNotDriver','inTransport']),
                $all(['isNotDriver','inWorkshop']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        // Fleet Master (Admin OR Super)
        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'fleet-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
            'visibility' => $any([$all(['isAdmin']), $all(['isSuperAdmin'])]),
        ]);

        $fleetMaster = [
            ['Fleet Clusters','clusters.index','fas fa-list'],
            ['Horse Groups','horse_groups.index','fas fa-list'],
            ['Horse Makes','horse_makes.index','fas fa-list'],
            ['Horse Types','horse_types.index','fas fa-list'],
            ['Trailer Groups','trailer_groups.index','fas fa-list'],
            ['Trailer Types','trailer_types.index','fas fa-list'],
            ['Vehicle Groups','vehicle_groups.index','fas fa-list'],
            ['Vehicle Makes','vehicle_makes.index','fas fa-list'],
            ['Vehicle Types','vehicle_types.index','fas fa-list'],
            ['Checklists','checklist_categories.index','fas fa-list'],
            ['Checklist Items Groups','checklist_sub_categories.index','fas fa-list'],
            ['Checklist Items','checklist_items.index','fas fa-list'],
        ];

        $i = 10;
        foreach ($fleetMaster as $row) {
            $upsertSub($m, [
                'name' => $row[0],
                'slug' => Str::slug($row[0]),
                'icon' => $row[2],
                'route_name' => $row[1],
                'sort_order' => $i,
            ]);
            $i += 10;
        }

        // Horses
        $m = $upsertModule($g, [
            'name' => 'Horses',
            'slug' => 'horses',
            'icon' => 'fas fa-truck',
            'route_name' => 'horses.*',
            'sort_order' => 20,
        ]);
        $upsertSub($m, ['name'=>'Create Horse','slug'=>'create-horse','icon'=>'fas fa-plus','route_name'=>'horses.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Horses','slug'=>'manage-horses','icon'=>'fas fa-list','route_name'=>'horses.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Archived Horses','slug'=>'archived-horses','icon'=>'fas fa-archive','route_name'=>'horses.archived','sort_order'=>30]);

        // Trailers
        $m = $upsertModule($g, [
            'name' => 'Trailers',
            'slug' => 'trailers',
            'icon' => 'fas fa-trailer',
            'route_name' => 'trailers.*',
            'sort_order' => 30,
        ]);
        $upsertSub($m, ['name'=>'Manage Trailers','slug'=>'manage-trailers','icon'=>'fas fa-list','route_name'=>'trailers.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Trailer Links','slug'=>'trailer-links','icon'=>'fas fa-list','route_name'=>'trailer_links.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Archived Trailers','slug'=>'archived-trailers','icon'=>'fas fa-archive','route_name'=>'trailers.archived','sort_order'=>30]);

        // Vehicles
        $m = $upsertModule($g, [
            'name' => 'Vehicles',
            'slug' => 'vehicles',
            'icon' => 'fas fa-car',
            'route_name' => 'vehicles.*',
            'sort_order' => 40,
        ]);
        $upsertSub($m, ['name'=>'Create Vehicle','slug'=>'create-vehicle','icon'=>'fas fa-plus','route_name'=>'vehicles.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Vehicles','slug'=>'manage-vehicles','icon'=>'fas fa-list','route_name'=>'vehicles.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Archived Vehicles','slug'=>'archived-vehicles','icon'=>'fas fa-archive','route_name'=>'vehicles.archived','sort_order'=>30]);

        // Assignments
        $m = $upsertModule($g, [
            'name' => 'Assignments',
            'slug' => 'assignments',
            'icon' => 'fas fa-user-plus',
            'sort_order' => 50,
        ]);
        $upsertSub($m, ['name'=>'Driver - Horse','slug'=>'driver-horse','icon'=>'fas fa-plus','route_name'=>'assignments.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Horse - Trailer','slug'=>'horse-trailer','icon'=>'fas fa-plus','route_name'=>'trailer_assignments.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Employee - Vehicle','slug'=>'employee-vehicle','icon'=>'fas fa-plus','route_name'=>'vehicle_assignments.index','sort_order'=>30]);

        // Fleet Inspections (manage)
        $m = $upsertModule($g, [
            'name' => 'Fleet Inspections',
            'slug' => 'fleet-inspections',
            'icon' => 'fas fa-search',
            'route_name' => 'checklists.index',
            'sort_order' => 60,
        ]);
        $upsertSub($m, ['name'=>'Manage Inspections','slug'=>'manage-inspections','icon'=>'fas fa-tasks','route_name'=>'checklists.index','sort_order'=>10]);

        // ----------------------------
        // GROUP: Fuel Management (header existed always) -> group public
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Fuel Management',
            'slug' => 'fuel-management',
            'sort_order' => 110,
            'visibility' => null,
        ]);

        // Fueling Stations (Transport or Super) AND not driver
        $m = $upsertModule($g, [
            'name' => 'Fueling Stations',
            'slug' => 'fuel-stations',
            'icon' => 'fas fa-gas-pump',
            'sort_order' => 10,
            'visibility' => $any([
                $all(['isNotDriver','inTransport']),
                $all(['isNotDriver','isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Manage Stations','slug'=>'manage-stations','icon'=>'fas fa-list','route_name'=>'containers.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Fuel Transfers','slug'=>'fuel-transfers','icon'=>'fas fa-list','route_name'=>'transfers.fuel','sort_order'=>20]);

        // Fuel Stations TopUps (Transport or Super) AND not driver
        $m = $upsertModule($g, [
            'name' => 'Fuel Stations TopUps',
            'slug' => 'fuel-topups',
            'icon' => 'fas fa-oil-can',
            'sort_order' => 20,
            'route_name' => 'top_ups.*',
            'visibility' => $any([
                $all(['isNotDriver','inTransport']),
                $all(['isNotDriver','isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Fuel Top Ups','slug'=>'fuel-top-ups','icon'=>'fas fa-list','route_name'=>'top_ups.index','sort_order'=>10]);

        $topupManageVis = $any([
            $all(['inTransport','isAdmin']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Top Ups','slug'=>'pending-top-ups','icon'=>'fas fa-clock','route_name'=>'top_ups.pending','sort_order'=>20,'badge_key'=>'top_ups_pending_count','visibility'=>$topupManageVis]);
        $upsertSub($m, ['name'=>'Approved Top Ups','slug'=>'approved-top-ups','icon'=>'fas fa-check','route_name'=>'top_ups.approved','sort_order'=>30,'badge_key'=>'top_ups_approved_count','visibility'=>$topupManageVis]);
        $upsertSub($m, ['name'=>'Rejected Top Ups','slug'=>'rejected-top-ups','icon'=>'fas fa-ban','route_name'=>'top_ups.rejected','sort_order'=>40,'badge_key'=>'top_ups_rejected_count','visibility'=>$topupManageVis]);

        // Fuel Orders (Transport or Super) AND not driver
        $m = $upsertModule($g, [
            'name' => 'Fuel Orders',
            'slug' => 'fuel-orders',
            'icon' => 'fas fa-clipboard-list',
            'sort_order' => 30,
            'route_name' => 'fuels.*',
            'visibility' => $any([
                $all(['isNotDriver','inTransport']),
                $all(['isNotDriver','isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Manage Fuel Orders','slug'=>'manage-fuel-orders','icon'=>'fas fa-list','route_name'=>'fuels.index','sort_order'=>10]);

        $fuelOrderManageVis = $any([
            $all(['isAdmin','inTransport']),
            $all(['hasTLDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Fuel Orders','slug'=>'pending-fuel-orders','icon'=>'fas fa-clock','route_name'=>'fuels.pending','sort_order'=>20,'badge_key'=>'fuels_pending_count','visibility'=>$fuelOrderManageVis]);
        $upsertSub($m, ['name'=>'Approved Fuel Orders','slug'=>'approved-fuel-orders','icon'=>'fas fa-check','route_name'=>'fuels.approved','sort_order'=>30,'badge_key'=>'fuels_approved_count','visibility'=>$fuelOrderManageVis]);
        $upsertSub($m, ['name'=>'Rejected Fuel Orders','slug'=>'rejected-fuel-orders','icon'=>'fas fa-ban','route_name'=>'fuels.rejected','sort_order'=>40,'badge_key'=>'fuels_rejected_count','visibility'=>$fuelOrderManageVis]);
        $upsertSub($m, ['name'=>'Deleted Fuel Orders','slug'=>'deleted-fuel-orders','icon'=>'fas fa-trash','route_name'=>'fuels.deleted','sort_order'=>50,'badge_key'=>'fuels_deleted_count','visibility'=>$fuelOrderManageVis]);

        // Fuel Allocations (was global)
        $m = $upsertModule($g, [
            'name' => 'Fuel Allocations',
            'slug' => 'fuel-allocations',
            'icon' => 'fas fa-chart-pie',
            'sort_order' => 40,
            'route_name' => 'allocations.*',
            'visibility' => null,
        ]);
        $upsertSub($m, [
            'name'=>'My Allocation',
            'slug'=>'my-allocation',
            'icon'=>'fas fa-arrow-right',
            'route_name'=>'allocations.myallocations',
            'route_params'=>['id' => '{employee_id}'],
            'sort_order'=>10,
            'badge_key'=>'my_allocation_count',
            'visibility'=>[],
        ]);

        $allocManageVis = $any([
            $all(['inTransport','isAdmin']),
            $all(['hasTLDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, [
            'name'=>'Manage Allocation',
            'slug'=>'manage-allocation',
            'icon'=>'fas fa-list',
            'route_name'=>'allocations.index',
            'sort_order'=>20,
            'visibility'=>$allocManageVis,
        ]);

        // Fuel Requisitions
        $m = $upsertModule($g, [
            'name' => 'Fuel Requisitions',
            'slug' => 'fuel-requisitions',
            'icon' => 'fas fa-hand-holding-usd',
            'sort_order' => 50,
            'route_name' => 'fuel_requests.*',
            'visibility' => null,
        ]);
        $upsertSub($m, [
            'name'=>'My Requests',
            'slug'=>'my-requests',
            'icon'=>'fas fa-arrow-right',
            'route_name'=>'fuel_requests.myrequests',
            'route_params'=>['id' => '{employee_id}'],
            'sort_order'=>10,
            'visibility'=>[],
        ]);

        $fuelReqManageVis = $any([
            $all(['hasTLDeptHead']),
            $all(['inTransport','isAdmin']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Requests','slug'=>'pending-fuel-requests','icon'=>'fas fa-clock','route_name'=>'fuel_requests.pending','sort_order'=>20,'badge_key'=>'fuel_requisition_pending_count','visibility'=>$fuelReqManageVis]);
        $upsertSub($m, ['name'=>'Approved Requests','slug'=>'approved-fuel-requests','icon'=>'fas fa-check','route_name'=>'fuel_requests.approved','sort_order'=>30,'badge_key'=>'fuel_requisition_approved_count','visibility'=>$fuelReqManageVis]);
        $upsertSub($m, ['name'=>'Rejected Requests','slug'=>'rejected-fuel-requests','icon'=>'fas fa-ban','route_name'=>'fuel_requests.rejected','sort_order'=>40,'badge_key'=>'fuel_requisition_rejected_count','visibility'=>$fuelReqManageVis]);

        // ----------------------------
        // GROUP: Trip Management (Finance or Transport or Super)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Trip Management',
            'slug' => 'trip-management',
            'sort_order' => 120,
            'visibility' => $any([
                $all(['inFinance']),
                $all(['inTransport']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        // Trip Master (Admin+Transport) OR Super
        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'trip-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
            'visibility' => $any([
                $all(['isAdmin','inTransport']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $tripMasterSubs = [
            ['Agents','agents.index','fas fa-list', null],
            ['Borders','borders.index','fas fa-bars', null],
            ['Brokers','brokers.index','fas fa-list', null],
            ['Cargos','cargos.index','fas fa-truck-loading', null],
            ['Clearing Agents','clearing_agents.index','fas fa-building', null],
            ['Countries','countries.index','fas fa-globe-africa', null],
            ['Consignees','consignees.index','fas fa-users', null],
            ['Corridors','corridors.index','fas fa-road', null],
            ['Deductions','deductions.index','fas fa-list', null],
            ['Destinations','destinations.index','fas fa-map-pin', null],
            ['Expenses','expenses.index','fas fa-list', null],
            ['Loading Points','loading_points.index','fas fa-map-marker', null],
            ['Offloading Points','offloading_points.index','fas fa-map-marker', null],
            ['Provinces','provinces.index','fas fa-globe-africa', null],
            ['Rehandling Jobs','works.index','fas fa-list', null],
            ['Road Routes','routes.index','fas fa-road', null],
            ['Teams','teams.index','fas fa-users', $any([$all(['isSuperAdmin'])])],
            ['Trip Rates','rates.index','fas fa-list', $any([$all(['inFinance']), $all(['isSuperAdmin'])])],
            ['Trip Types','trip_types.index','fas fa-road', null],
            ['Truck Stops','truck_stops.index','fas fa-stop', null],
            ['Worksites','locations.index','fas fa-map-marker', null],
        ];

        $i=10;
        foreach ($tripMasterSubs as $row) {
            $upsertSub($m, [
                'name'=>$row[0],
                'slug'=>Str::slug($row[0]),
                'icon'=>$row[2],
                'route_name'=>$row[1],
                'sort_order'=>$i,
                'visibility'=>$row[3] ?? null, // null inherits trip master visibility
            ]);
            $i+=10;
        }

        // Log Book (vehicle assignment OR super)
        $upsertModule($g, [
            'name' => 'Log Book',
            'slug' => 'log-book',
            'icon' => 'fas fa-book',
            'route_name' => 'logs.index',
            'sort_order' => 20,
            'visibility' => $any([
                $all(['hasVehicleAssignment']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        // Car Rental (not driver)
        $m = $upsertModule($g, [
            'name' => 'Car Rental',
            'slug' => 'car-rental',
            'icon' => 'fas fa-car',
            'route_name' => 'rentals.*',
            'sort_order' => 25,
            'is_active' => false,
            'visibility' => $any([
                $all(['isNotDriver']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Manage Rentals','slug'=>'manage-rentals','icon'=>'fas fa-list','route_name'=>'rentals.index','sort_order'=>10,'is_active' => false,]);

        
        $rentalManageVis = $any([
            $all(['isAdmin','inTransport']),
            $all(['hasTLDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Rentals','slug'=>'pending-rentals','icon'=>'fas fa-clock','route_name'=>'rentals.pending','sort_order'=>20,'badge_key'=>'rentals_pending_count', 'is_active' => false,'visibility'=>$rentalManageVis]);
        $upsertSub($m, ['name'=>'Approved Rentals','slug'=>'approved-rentals','icon'=>'fas fa-check','route_name'=>'rentals.approved','sort_order'=>30,'badge_key'=>'rentals_approved_count', 'is_active' => false,'visibility'=>$rentalManageVis]);
        $upsertSub($m, ['name'=>'Rejected Rentals','slug'=>'rejected-rentals','icon'=>'fas fa-ban','route_name'=>'rentals.rejected','sort_order'=>40,'badge_key'=>'rentals_rejected_count', 'is_active' => false,'visibility'=>$rentalManageVis]);

        // Transporters (not driver)
        $m = $upsertModule($g, [
            'name' => 'Transporters',
            'slug' => 'transporters',
            'icon' => 'fas fa-truck',
            'route_name' => 'transporters.*',
            'sort_order' => 30,
            'visibility' => $any([
                $all(['isNotDriver']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Manage Transporters','slug'=>'manage-transporters','icon'=>'fas fa-list','route_name'=>'transporters.index','sort_order'=>10]);

        $transporterManageVis = $any([
            $all(['isAdmin','inTransport']),
            $all(['hasTLDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Transporters','slug'=>'pending-transporters','icon'=>'fas fa-clock','route_name'=>'transporters.pending','sort_order'=>20,'badge_key'=>'transporters_pending_count','visibility'=>$transporterManageVis]);
        $upsertSub($m, ['name'=>'Approved Transporters','slug'=>'approved-transporters','icon'=>'fas fa-check','route_name'=>'transporters.approved','sort_order'=>30,'badge_key'=>'transporters_approved_count','visibility'=>$transporterManageVis]);
        $upsertSub($m, ['name'=>'Rejected Transporters','slug'=>'rejected-transporters','icon'=>'fas fa-ban','route_name'=>'transporters.rejected','sort_order'=>40,'badge_key'=>'transporters_rejected_count','visibility'=>$transporterManageVis]);

        // Shifts (not driver)
        $m = $upsertModule($g, [
            'name' => 'Shifts',
            'slug' => 'shifts',
            'icon' => 'fas fa-clock',
            'route_name' => 'shifts.*',
            'is_active'  => false,
            'sort_order' => 40,
            'visibility' => $any([
                $all(['isNotDriver']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Manage Shifts','slug'=>'manage-shifts','is_active'  => false,'icon'=>'fas fa-list','route_name'=>'shifts.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Shifts Reports','slug'=>'shifts-reports',  'is_active'  => false,'icon'=>'fas fa-line-chart','route_name'=>'shifts.reports','sort_order'=>20]);

        // Trips
        $m = $upsertModule($g, [
            'name' => 'Trips',
            'slug' => 'trips',
            'icon' => 'fas fa-road',
            'route_name' => 'trips.*',
            'sort_order' => 50,
        ]);
        $upsertSub($m, 
        ['name'=>'Create Trip',
        'slug'=>'create-trip',
        'icon'=>'fas fa-plus',
        'route_name'=>'trips.create',
        'sort_order'=>10,
        'visibility' => $any([
                $all(['isNotDriver']),
            ])
        ]);
        $upsertSub($m, ['name'=>'Manage Trips','slug'=>'manage-trips','icon'=>'fas fa-list','route_name'=>'trips.index','sort_order'=>20]);

        $tripManageVis = $any([
            $all(['isAdmin']),
            $all(['hasTLDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Trips','slug'=>'pending-trips','icon'=>'fas fa-clock','route_name'=>'trips.pending','sort_order'=>30,'badge_key'=>'trips_pending_count','visibility'=>$tripManageVis]);
        $upsertSub($m, ['name'=>'Approved Trips','slug'=>'approved-trips','icon'=>'fas fa-check','route_name'=>'trips.approved','sort_order'=>40,'badge_key'=>'trips_approved_count','visibility'=>$tripManageVis]);
        $upsertSub($m, ['name'=>'Rejected Trips','slug'=>'rejected-trips','icon'=>'fas fa-ban','route_name'=>'trips.rejected','sort_order'=>50,'badge_key'=>'trips_rejected_count','visibility'=>$tripManageVis]);
        $upsertSub($m, ['name'=>'Deleted Trips','slug'=>'deleted-trips','icon'=>'fas fa-trash','route_name'=>'trips.deleted','sort_order'=>60,'badge_key'=>'trips_deleted_count','visibility'=>$tripManageVis]);

        $upsertSub($m, ['name'=>'Tracking Groups','slug'=>'tracking-groups','icon'=>'fas fa-list','route_name'=>'trip_groups.index','sort_order'=>70]);

        // Gatepass (Logistics) - not driver
        $m = $upsertModule($g, [
            'name' => 'Gatepass',
            'slug' => 'gatepass-logistics',
            'icon' => 'fas fa-door-open',
            'sort_order' => 60,
            'visibility' => $any([
                $all(['isNotDriver']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, [
            'name'=>'Pending Gatepasses','slug'=>'pending-gatepasses-logistics','icon'=>'fas fa-clock',
            'route_name'=>'gate_passes.pending','route_params'=>['department'=>'logistics'],
            'sort_order'=>10,'badge_key'=>'logistics_gate_passes_pending_count'
        ]);
        $upsertSub($m, [
            'name'=>'Approved Gatepasses','slug'=>'approved-gatepasses-logistics','icon'=>'fas fa-check',
            'route_name'=>'gate_passes.approved','route_params'=>['department'=>'logistics'],
            'sort_order'=>20,'badge_key'=>'logistics_gate_passes_approved_count'
        ]);
        $upsertSub($m, [
            'name'=>'Rejected Gatepasses','slug'=>'rejected-gatepasses-logistics','icon'=>'fas fa-ban',
            'route_name'=>'gate_passes.rejected','route_params'=>['department'=>'logistics'],
            'sort_order'=>30,'badge_key'=>'logistics_gate_passes_rejected_count'
        ]);

        // Recoveries (not driver)
        $m = $upsertModule($g, [
            'name' => 'Recoveries',
            'slug' => 'recoveries',
            'icon' => 'fas fa-hand-holding-usd',
            'route_name' => 'recoveries.*',
            'sort_order' => 70,
            'visibility' => $any([
                $all(['isNotDriver']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Create Recovery','slug'=>'create-recovery','icon'=>'fas fa-plus','route_name'=>'recoveries.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Recoveries','slug'=>'manage-recoveries','icon'=>'fas fa-list','route_name'=>'recoveries.index','sort_order'=>20]);

        $recoveryManageVis = $any([
            $all(['isAdmin']),
            $all(['hasTLDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Recoveries','slug'=>'pending-recoveries','icon'=>'fas fa-clock','route_name'=>'recoveries.pending','sort_order'=>30,'badge_key'=>'recoveries_pending_count','visibility'=>$recoveryManageVis]);
        $upsertSub($m, ['name'=>'Approved Recoveries','slug'=>'approved-recoveries','icon'=>'fas fa-check','route_name'=>'recoveries.approved','sort_order'=>40,'badge_key'=>'recoveries_approved_count','visibility'=>$recoveryManageVis]);
        $upsertSub($m, ['name'=>'Rejected Recoveries','slug'=>'rejected-recoveries','icon'=>'fas fa-ban','route_name'=>'recoveries.rejected','sort_order'=>50,'badge_key'=>'recoveries_rejected_count','visibility'=>$recoveryManageVis]);

        // ----------------------------
        // GROUP: Workshop Management
        // (Finance OR Workshop OR Stores OR Super)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Workshop Management',
            'slug' => 'workshop-management',
            'sort_order' => 130,
            'visibility' => $any([
                // $all(['inFinance']),
                $all(['inWorkshop']),
                // $all(['inStores']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'workshop-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
            'visibility' => $any([
                $all(['hasWorkshopDeptHead']),
                $all(['isAdmin','inWorkshop']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Job Types','slug'=>'job-types','icon'=>'fas fa-list','route_name'=>'service_types.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Inspection Item Groups','slug'=>'inspection-item-groups','icon'=>'fas fa-list','route_name'=>'inspection_groups.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Inspection Items','slug'=>'inspection-items','icon'=>'fas fa-list','route_name'=>'inspection_types.index','sort_order'=>30]);
        $upsertSub($m, ['name'=>'Workshop Stations','slug'=>'workshop-stations','icon'=>'fas fa-list','route_name'=>'stations.index','sort_order'=>40]);

        $m = $upsertModule($g, [
            'name' => 'Bookings',
            'slug' => 'bookings',
            'icon' => 'fas fa-tasks',
            'route_name' => 'bookings.*',
            'sort_order' => 20,
        ]);
        $upsertSub($m, ['name'=>'Create Booking','slug'=>'create-booking','icon'=>'fas fa-plus','route_name'=>'bookings.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Bookings','slug'=>'manage-bookings','icon'=>'fas fa-list','route_name'=>'bookings.index','sort_order'=>20]);

        $bookingManageVis = $any([
            $all(['hasWorkshopDeptHead']),
            $all(['isAdmin','inWorkshop']),
            $all(['isAdmin','inTransport']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Bookings','slug'=>'pending-bookings','icon'=>'fas fa-clock','route_name'=>'bookings.pending','sort_order'=>30,'badge_key'=>'bookings_pending_count','visibility'=>$bookingManageVis]);
        $upsertSub($m, ['name'=>'Approved Bookings','slug'=>'approved-bookings','icon'=>'fas fa-check','route_name'=>'bookings.approved','sort_order'=>40,'badge_key'=>'bookings_approved_count','visibility'=>$bookingManageVis]);
        $upsertSub($m, ['name'=>'Rejected Bookings','slug'=>'rejected-bookings','icon'=>'fas fa-ban','route_name'=>'bookings.rejected','sort_order'=>50,'badge_key'=>'bookings_rejected_count','visibility'=>$bookingManageVis]);

        // Tickets
        $m = $upsertModule($g, [
            'name' => 'Tickets',
            'slug' => 'tickets',
            'icon' => 'fas fa-file-invoice',
            'route_name' => 'tickets.index',
            'sort_order' => 30,
        ]);

        $ticketManageVis = $any([
            // $all(['hasStoresDeptHead']),
            $all(['hasWorkshopDeptHead']),
            $all(['isAdmin','inWorkshop']),
            // $all(['isAdmin','inStores']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Manage Tickets','slug'=>'manage-tickets','icon'=>'fas fa-tasks','route_name'=>'tickets.index','sort_order'=>10,'visibility'=>$ticketManageVis]);

        $upsertSub($m, [
            'name'=>'My Tickets',
            'slug'=>'my-tickets',
            'icon'=>'fas fa-tasks',
            'route_name'=>'tickets.cards',
            'route_params'=> ['id' => '{employee_id}'], // ✅ MUST match {id}
            'sort_order'=>20,
            'badge_key'=>'job_cards_count',
            // 'visibility'=>$any([$all(['inWorkshop']), $all(['isSuperAdmin'])]),
        ]);

        // Ticket Inspections
        $m = $upsertModule($g, [
            'name' => 'Ticket Inspections',
            'slug' => 'ticket-inspections',
            'icon' => 'fas fa-search',
            'route_name' => 'inspections.index',
            'sort_order' => 40,
        ]);
        $upsertSub($m, ['name'=>'Manage Inspections','slug'=>'manage-ticket-inspections','icon'=>'fas fa-tasks','route_name'=>'inspections.index','sort_order'=>10,'visibility'=>$ticketManageVis]);
        $upsertSub($m, [
            'name'=>'My Inspections',
            'slug'=>'my-inspections',
            'icon'=>'fas fa-tasks',
            'route_name'=>'inspections.my-inspections',
            'route_params'=>['id' => '{employee_id}'],
            'sort_order'=>20,
            'badge_key'=>'inspections_count',
            // 'visibility'=>$any([$all(['inWorkshop']), $all(['isSuperAdmin'])]),
        ]);

        // Gatepass (Workshop) - Admin or Super
        $m = $upsertModule($g, [
            'name' => 'Gatepass',
            'slug' => 'gatepass-workshop',
            'icon' => 'fas fa-door-open',
            'sort_order' => 50,
            'visibility' => $any([$all(['isAdmin']), $all(['isSuperAdmin'])]),
        ]);
        $upsertSub($m, [
            'name'=>'Pending Gatepasses','slug'=>'pending-gatepasses-workshop','icon'=>'fas fa-clock',
            'route_name'=>'gate_passes.pending','route_params'=>['department'=>'workshop'],
            'sort_order'=>10,'badge_key'=>'workshop_gate_passes_pending_count'
        ]);
        $upsertSub($m, [
            'name'=>'Approved Gatepasses','slug'=>'approved-gatepasses-workshop','icon'=>'fas fa-check',
            'route_name'=>'gate_passes.approved','route_params'=>['department'=>'workshop'],
            'sort_order'=>20,'badge_key'=>'workshop_gate_passes_approved_count'
        ]);
        $upsertSub($m, [
            'name'=>'Rejected Gatepasses','slug'=>'rejected-gatepasses-workshop','icon'=>'fas fa-ban',
            'route_name'=>'gate_passes.rejected','route_params'=>['department'=>'workshop'],
            'sort_order'=>30,'badge_key'=>'workshop_gate_passes_rejected_count'
        ]);

        // ----------------------------
        // GROUP: Stores & Inventory Management (Stores or Super)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Inventory Management',
            'slug' => 'stores-inventory',
            'sort_order' => 140,
            'visibility' => $any([
                $all(['inStores']),
                $all(['isSuperAdmin']),
            ]),
        ]);

        $m = $upsertModule($g, [
            'name' => 'Master',
            'slug' => 'stores-master',
            'icon' => 'fas fa-cog',
            'sort_order' => 10,
            'visibility' => $any([
                $all(['hasStoresDeptHead']),
                $all(['isAdmin','inStores']),
                $all(['isSuperAdmin']),
            ]),
        ]);
        $upsertSub($m, ['name'=>'Attributes','slug'=>'store-attributes','icon'=>'fas fa-list','route_name'=>'attributes.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Bins','slug'=>'bins','icon'=>'fas fa-list','route_name'=>'bins.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Brands','slug'=>'store-brands','icon'=>'fas fa-list','route_name'=>'brands.index','sort_order'=>30]);
        $upsertSub($m, ['name'=>'Categories','slug'=>'store-categories','icon'=>'fas fa-list','route_name'=>'categories.index','sort_order'=>40]);
        $upsertSub($m, ['name'=>'Racks','slug'=>'racks','icon'=>'fas fa-list','route_name'=>'racks.index','sort_order'=>50]);
        $upsertSub($m, ['name'=>'Stores','slug'=>'stores','icon'=>'fas fa-list','route_name'=>'stores.index','sort_order'=>60]);

        $m = $upsertModule($g, [
            'name' => 'Inventory Transfers',
            'slug' => 'inventory-transfers',
            'icon' => 'fas fa-exchange',
            'sort_order' => 20,
        ]);
        $upsertSub($m, ['name'=>'Manage Transfers','slug'=>'manage-inventory-transfers','icon'=>'fas fa-list','route_name'=>'inventory_transfers.index','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Pending Transfers','slug'=>'pending-inventory-transfers','icon'=>'fas fa-clock','route_name'=>'inventory_transfers.pending','sort_order'=>20,'badge_key'=>'inventory_transfers_pending_count']);
        $upsertSub($m, ['name'=>'Approved Transfers','slug'=>'approved-inventory-transfers','icon'=>'fas fa-check','route_name'=>'inventory_transfers.approved','sort_order'=>30,'badge_key'=>'inventory_transfers_approved_count']);
        $upsertSub($m, ['name'=>'Rejected Transfers','slug'=>'rejected-inventory-transfers','icon'=>'fas fa-ban','route_name'=>'inventory_transfers.rejected','sort_order'=>40,'badge_key'=>'inventory_transfers_rejected_count']);

        $m = $upsertModule($g, [
            'name' => 'Products',
            'slug' => 'inventory-products',
            'icon' => 'fas fa-boxes',
            'route_name' => 'inventory_products.*',
            'sort_order' => 30,
        ]);
        $upsertSub($m, ['name'=>'Create Product','slug'=>'create-inventory-product','icon'=>'fas fa-plus','route_name'=>'inventory_products.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Products','slug'=>'manage-inventory-products','icon'=>'fas fa-list','route_name'=>'inventory_products.index','sort_order'=>20]);

        $m = $upsertModule($g, [
            'name' => 'Purchase Orders',
            'slug' => 'inventory-purchase-orders',
            'icon' => 'fas fa-hand-holding-usd',
            'route_name' => 'inventory_purchases.*',
            'sort_order' => 40,
        ]);
        $upsertSub($m, ['name'=>'Manage Orders','slug'=>'manage-inventory-orders','icon'=>'fas fa-list','route_name'=>'inventory_purchases.index','sort_order'=>10]);

        $invPOVis = $any([
            $all(['isAdmin']),
            $all(['hasStoresDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Orders','slug'=>'pending-inventory-orders','icon'=>'fas fa-clock','route_name'=>'inventory_purchases.pending','sort_order'=>20,'badge_key'=>'inventory_purchases_pending_count','visibility'=>$invPOVis]);
        $upsertSub($m, ['name'=>'Approved Orders','slug'=>'approved-inventory-orders','icon'=>'fas fa-check','route_name'=>'inventory_purchases.approved','sort_order'=>30,'badge_key'=>'inventory_purchases_approved_count','visibility'=>$invPOVis]);
        $upsertSub($m, ['name'=>'Rejected Orders','slug'=>'rejected-inventory-orders','icon'=>'fas fa-ban','route_name'=>'inventory_purchases.rejected','sort_order'=>40,'badge_key'=>'inventory_purchases_rejected_count','visibility'=>$invPOVis]);
        $upsertSub($m, ['name'=>'Deleted Orders','slug'=>'deleted-inventory-orders','icon'=>'fas fa-trash','route_name'=>'inventory_purchases.deleted','sort_order'=>50,'badge_key'=>'inventory_purchases_deleted_count','visibility'=>$invPOVis]);

        $m = $upsertModule($g, [
            'name' => 'GRV (Inventory)',
            'slug' => 'grv-inventory',
            'icon' => 'fas fa-th-list',
            'route_name' => 'goods_receiveds.index',
            'sort_order' => 50,
        ]);
        $upsertSub($m, ['name'=>'Manage Inventory GRVs','slug'=>'manage-inventory-grvs','icon'=>'fas fa-list','route_name'=>'goods_receiveds.index','sort_order'=>10]);

        $m = $upsertModule($g, [
            'name' => 'Inventory',
            'slug' => 'inventory',
            'icon' => 'fas fa-th-list',
            'route_name' => 'inventories.*',
            'sort_order' => 60,
        ]);
        $upsertSub($m, ['name'=>'Create Inventory','slug'=>'create-inventory','icon'=>'fas fa-plus','route_name'=>'inventories.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Inventory','slug'=>'manage-inventory','icon'=>'fas fa-list','route_name'=>'inventories.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Disposed Items','slug'=>'disposed-items','icon'=>'fas fa-list','route_name'=>'disposes.index','sort_order'=>30]);

        $m = $upsertModule($g, [
            'name' => 'Dispatches (Inventory)',
            'slug' => 'inventory-dispatches',
            'icon' => 'fas fa-list',
            'route_name' => 'inventory_dispatches.*',
            'sort_order' => 70,
        ]);
        $upsertSub($m, ['name'=>'Manage Dispatches','slug'=>'manage-inventory-dispatches','icon'=>'fas fa-list','route_name'=>'inventory_dispatches.index','sort_order'=>10]);

        $invDispatchVis = $any([$all(['isAdmin']), $all(['isSuperAdmin'])]);
        $upsertSub($m, ['name'=>'Pending Dispatches','slug'=>'pending-inventory-dispatches','icon'=>'fas fa-clock','route_name'=>'inventory_dispatches.pending','sort_order'=>20,'badge_key'=>'inventory_dispatches_pending_count','visibility'=>$invDispatchVis]);
        $upsertSub($m, ['name'=>'Approved Dispatches','slug'=>'approved-inventory-dispatches','icon'=>'fas fa-check','route_name'=>'inventory_dispatches.approved','sort_order'=>30,'badge_key'=>'inventory_dispatches_approved_count','visibility'=>$invDispatchVis]);
        $upsertSub($m, ['name'=>'Rejected Dispatches','slug'=>'rejected-inventory-dispatches','icon'=>'fas fa-ban','route_name'=>'inventory_dispatches.rejected','sort_order'=>40,'badge_key'=>'inventory_dispatches_rejected_count','visibility'=>$invDispatchVis]);

        // ----------------------------
        // GROUP: Tyre Management (same visibility as Stores)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Tyre Management',
            'slug' => 'tyre-management',
            'sort_order' => 150,
            'visibility' => $any([$all(['inStores']), $all(['isSuperAdmin'])]),
        ]);

        $m = $upsertModule($g, [
            'name' => 'Tyre Transfers',
            'slug' => 'tyre-transfers',
            'icon' => 'fas fa-exchange',
            'sort_order' => 10,
        ]);
        $upsertSub($m, ['name'=>'Manage Transfers','slug'=>'manage-tyre-transfers','icon'=>'fas fa-list','route_name'=>'tyre_transfers.index','sort_order'=>10]);

        $upsertSub($m, ['name'=>'Pending Transfers','slug'=>'pending-tyre-transfers','icon'=>'fas fa-clock','route_name'=>'tyre_transfers.pending','sort_order'=>20,'badge_key'=>'tyre_transfers_pending_count','visibility'=>$invDispatchVis]);
        $upsertSub($m, ['name'=>'Approved Transfers','slug'=>'approved-tyre-transfers','icon'=>'fas fa-check','route_name'=>'tyre_transfers.approved','sort_order'=>30,'badge_key'=>'tyre_transfers_approved_count','visibility'=>$invDispatchVis]);
        $upsertSub($m, ['name'=>'Rejected Transfers','slug'=>'rejected-tyre-transfers','icon'=>'fas fa-ban','route_name'=>'tyre_transfers.rejected','sort_order'=>40,'badge_key'=>'tyre_transfers_rejected_count','visibility'=>$invDispatchVis]);

        $m = $upsertModule($g, [
            'name' => 'Products',
            'slug' => 'tyre-products',
            'icon' => 'fas fa-boxes',
            'route_name' => 'tyre_products.*',
            'sort_order' => 20,
        ]);
        $upsertSub($m, ['name'=>'Create Product','slug'=>'create-tyre-product','icon'=>'fas fa-plus','route_name'=>'tyre_products.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Products','slug'=>'manage-tyre-products','icon'=>'fas fa-list','route_name'=>'tyre_products.index','sort_order'=>20]);

        $m = $upsertModule($g, [
            'name' => 'Purchase Orders',
            'slug' => 'tyre-purchase-orders',
            'icon' => 'fas fa-hand-holding-usd',
            'route_name' => 'tyre_purchases.*',
            'sort_order' => 30,
        ]);
        $upsertSub($m, ['name'=>'Manage Orders','slug'=>'manage-tyre-orders','icon'=>'fas fa-list','route_name'=>'tyre_purchases.index','sort_order'=>10]);

        $tyrePOVis = $any([
            $all(['isAdmin']),
            $all(['hasStoresDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Orders','slug'=>'pending-tyre-orders','icon'=>'fas fa-clock','route_name'=>'tyre_purchases.pending','sort_order'=>20,'badge_key'=>'tyre_purchases_pending_count','visibility'=>$tyrePOVis]);
        $upsertSub($m, ['name'=>'Approved Orders','slug'=>'approved-tyre-orders','icon'=>'fas fa-check','route_name'=>'tyre_purchases.approved','sort_order'=>30,'badge_key'=>'tyre_purchases_approved_count','visibility'=>$tyrePOVis]);
        $upsertSub($m, ['name'=>'Rejected Orders','slug'=>'rejected-tyre-orders','icon'=>'fas fa-ban','route_name'=>'tyre_purchases.rejected','sort_order'=>40,'badge_key'=>'tyre_purchases_rejected_count','visibility'=>$tyrePOVis]);
        $upsertSub($m, ['name'=>'Deleted Orders','slug'=>'deleted-tyre-orders','icon'=>'fas fa-trash','route_name'=>'tyre_purchases.deleted','sort_order'=>50,'badge_key'=>'tyre_purchases_deleted_count','visibility'=>$tyrePOVis]);

        $m = $upsertModule($g, [
            'name' => 'GRV (Tyres)',
            'slug' => 'grv-tyres',
            'icon' => 'fas fa-th-list',
            'route_name' => 'goods_receiveds.tyres',
            'sort_order' => 40,
        ]);
        $upsertSub($m, ['name'=>'Manage Tyre GRVs','slug'=>'manage-tyre-grvs','icon'=>'fas fa-list','route_name'=>'goods_receiveds.tyres','sort_order'=>10]);

        $m = $upsertModule($g, [
            'name' => 'Tyres',
            'slug' => 'tyres',
            'icon' => 'fas fa-th-list',
            'route_name' => 'tyres.*',
            'sort_order' => 50,
        ]);
        $upsertSub($m, ['name'=>'Create Tyre','slug'=>'create-tyre','icon'=>'fas fa-plus','route_name'=>'tyres.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Tyres','slug'=>'manage-tyres','icon'=>'fas fa-list','route_name'=>'tyres.index','sort_order'=>20]);
        $upsertSub($m, ['name'=>'Tyre Assignments','slug'=>'tyre-assignments','icon'=>'fas fa-list','route_name'=>'tyre_assignments.index','sort_order'=>30]);
        $upsertSub($m, ['name'=>'Disposed Items','slug'=>'tyre-disposed-items','icon'=>'fas fa-list','route_name'=>'disposes.index','sort_order'=>40]);

        $m = $upsertModule($g, [
            'name' => 'Retreads',
            'slug' => 'retreads',
            'icon' => 'fas fa-th-list',
            'route_name' => 'retreads.*',
            'sort_order' => 60,
        ]);
        $upsertSub($m, ['name'=>'Create Retread','slug'=>'create-retread','icon'=>'fas fa-plus','route_name'=>'retreads.create','sort_order'=>10]);
        $upsertSub($m, ['name'=>'Manage Retread','slug'=>'manage-retread','icon'=>'fas fa-list','route_name'=>'retreads.index','sort_order'=>20]);

        $retreadVis = $any([
            $all(['isAdmin']),
            $all(['hasStoresDeptHead']),
            $all(['isSuperAdmin']),
        ]);
        $upsertSub($m, ['name'=>'Pending Retreads','slug'=>'pending-retreads','icon'=>'fas fa-clock','route_name'=>'retreads.pending','sort_order'=>30,'badge_key'=>'retreads_pending_count','visibility'=>$retreadVis]);
        $upsertSub($m, ['name'=>'Approved Retreads','slug'=>'approved-retreads','icon'=>'fas fa-check','route_name'=>'retreads.approved','sort_order'=>40,'badge_key'=>'retreads_approved_count','visibility'=>$retreadVis]);
        $upsertSub($m, ['name'=>'Rejected Retreads','slug'=>'rejected-retreads','icon'=>'fas fa-ban','route_name'=>'retreads.rejected','sort_order'=>50,'badge_key'=>'retreads_rejected_count','visibility'=>$retreadVis]);

        $m = $upsertModule($g, [
            'name' => 'Dispatches (Tyres)',
            'slug' => 'tyre-dispatches',
            'icon' => 'fas fa-list',
            'route_name' => 'tyre_dispatches.*',
            'sort_order' => 70,
        ]);
        $upsertSub($m, ['name'=>'Manage Dispatches','slug'=>'manage-tyre-dispatches','icon'=>'fas fa-list','route_name'=>'tyre_dispatches.index','sort_order'=>10]);

        $tyreDispatchVis = $any([$all(['isAdmin']), $all(['isSuperAdmin'])]);
        $upsertSub($m, ['name'=>'Pending Dispatches','slug'=>'pending-tyre-dispatches','icon'=>'fas fa-clock','route_name'=>'tyre_dispatches.pending','sort_order'=>20,'badge_key'=>'tyre_dispatches_pending_count','visibility'=>$tyreDispatchVis]);
        $upsertSub($m, ['name'=>'Approved Dispatches','slug'=>'approved-tyre-dispatches','icon'=>'fas fa-check','route_name'=>'tyre_dispatches.approved','sort_order'=>30,'badge_key'=>'tyre_dispatches_approved_count','visibility'=>$tyreDispatchVis]);
        $upsertSub($m, ['name'=>'Rejected Dispatches','slug'=>'rejected-tyre-dispatches','icon'=>'fas fa-ban','route_name'=>'tyre_dispatches.rejected','sort_order'=>40,'badge_key'=>'tyre_dispatches_rejected_count','visibility'=>$tyreDispatchVis]);

        // ----------------------------
        // GROUP: Business Settings
        // (Management OR Director OR Super)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Business Settings',
            'slug' => 'business-settings',
            'sort_order' => 160,
            'visibility' => $any([
                $all(['isSuperAdmin']),
            ]),
        ]);

        // Company Profile (dynamic company_id at runtime)
        $upsertModule($g, [
            'name' => 'Company Profile',
            'slug' => 'company-profile',
            'icon' => 'fas fa-cog',
            'route_name' => 'company-profile',
            'route_params'=> ['company' => '{company_id}'], // ✅ MUST match {company}
            'sort_order' => 10,
            'visibility' => null,
        ]);

        // // Create new business
        // $upsertModule($g, [
        //     'name' => 'Create new business',
        //     'slug' => 'create-new-business',
        //     'icon' => 'fas fa-plus-circle',
        //     'route_name' => 'companies.index',
        //     'sort_order' => 20,
        //     'visibility' => null,
        // ]);

        // ----------------------------
        // GROUP: Profile Settings (public)
        // ----------------------------
        $g = $upsertGroup([
            'name' => 'Profile Settings',
            'slug' => 'profile-settings',
            'sort_order' => 170,
            'visibility' => null,
        ]);

        $upsertModule($g, [
            'name' => 'My Profile',
            'slug' => 'my-profile',
            'icon' => 'fas fa-user',
            'route_name' => 'profile',
            'route_params' => ['id' => '{user_id}'],
            'sort_order' => 10,
        ]);

        $upsertModule($g, [
            'name' => 'Audits',
            'slug' => 'audits',
            'icon' => 'fas fa-history',
            'route_name' => 'audits.index',
            'sort_order' => 20,
            'visibility' => $any([$all(['isSuperAdmin'])]),
        ]);

        $upsertModule($g, [
            'name' => 'Logout',
            'slug' => 'logout',
            'icon' => 'fas fa-sign-out-alt',
            'route_name' => 'logout',
            'sort_order' => 30,
        ]);
    }
}