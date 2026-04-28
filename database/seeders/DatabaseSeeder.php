<?php

namespace Database\Seeders;

use App\Models\Measurement;
use App\Models\PublicHoliday;
use App\Models\UnitsOfMeasure;
use Database\Seeders\AccountSeeder;
use Database\Seeders\AccountTypeGroupSeeder;
use Database\Seeders\AccountTypeSeeder;
use Database\Seeders\AllowanceSeeder;
use Database\Seeders\BranchSeeder;
use Database\Seeders\CargoSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ChecklistCategorySeeder;
use Database\Seeders\ChecklistItemSeeder;
use Database\Seeders\ChecklistSubCategorySeeder;
use Database\Seeders\CheckSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CriterionSeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\DeductionSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\ExpenseCategorySeeder;
use Database\Seeders\ExpenseSeeder;
use Database\Seeders\FolderSeeder;
use Database\Seeders\HorseMakeSeeder;
use Database\Seeders\HorseModelSeeder;
use Database\Seeders\HorseTypeSeeder;
use Database\Seeders\InspectionGroupSeeder;
use Database\Seeders\InspectionTypeSeeder;
use Database\Seeders\JobTitleSeeder;
use Database\Seeders\JobTypeSeeder;
use Database\Seeders\LeaveTypeSeeder;
use Database\Seeders\LoanTypeSeeder;
use Database\Seeders\LossCategorySeeder;
use Database\Seeders\LossCauseSeeder;
use Database\Seeders\LossGroupSeeder;
use Database\Seeders\MeasurementSeeder;
use Database\Seeders\MenuRegistrySeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProblemCategorySeeder;
use Database\Seeders\PublicHolidaySeeder;
use Database\Seeders\RankSeeder;
use Database\Seeders\ReminderItemSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SalaryItemSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\StageSeeder;
use Database\Seeders\TaxBracketSeeder;
use Database\Seeders\TaxSeeder;
use Database\Seeders\TrailerTypeSeeder;
use Database\Seeders\TrainingDepartmentSeeder;
use Database\Seeders\TrainingItemSeeder;
use Database\Seeders\TransactionTypeSeeder;
use Database\Seeders\TripTypeSeeder;
use Database\Seeders\UnitsOfMeasureSeeder;
use Database\Seeders\VehicleGroupSeeder;
use Database\Seeders\VehicleTypeSeeder;
use Database\Seeders\VendorTypeSeeder;
use Database\Seeders\WasteTypeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(RoleSeeder::class);
        $this->call(RankSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(VehicleGroupSeeder::class);
        $this->call(VehicleTypeSeeder::class);
        $this->call(HorseTypeSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(TripTypeSeeder::class);
        $this->call(TrailerTypeSeeder::class);
        $this->call(VendorTypeSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(JobTitleSeeder::class);
        $this->call(ServiceTypeSeeder::class);
        $this->call(LeaveTypeSeeder::class);
        $this->call(JobTypeSeeder::class);
        $this->call(InspectionGroupSeeder::class);
        $this->call(InspectionTypeSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(AccountTypeGroupSeeder::class);
        $this->call(AccountTypeSeeder::class);
        $this->call(AccountSeeder::class);
        $this->call(ExpenseSeeder::class);
        $this->call(LoanTypeSeeder::class);
        $this->call(DeductionSeeder::class);
        $this->call(AllowanceSeeder::class);
        $this->call(HorseMakeSeeder::class);
        $this->call(HorseModelSeeder::class);
        $this->call(CargoSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(MeasurementSeeder::class);
        $this->call(ChecklistCategorySeeder::class);
        $this->call(ChecklistSubCategorySeeder::class);
        $this->call(ChecklistItemSeeder::class);
        $this->call(FolderSeeder::class);
        $this->call(LossCategorySeeder::class);
        $this->call(LossGroupSeeder::class);
        $this->call(TrainingItemSeeder::class);
        $this->call(TrainingDepartmentSeeder::class);
        $this->call(TransactionTypeSeeder::class);
        $this->call(TaxBracketSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        $this->call(WasteTypeSeeder::class);
        $this->call(UnitsOfMeasureSeeder::class);
        $this->call(LossCauseSeeder::class);
        $this->call(StageSeeder::class);
        $this->call(CriterionSeeder::class);
        $this->call(CheckSeeder::class);
        $this->call(PublicHolidaySeeder::class);
        $this->call(ProblemCategorySeeder::class);
        $this->call(MenuRegistrySeeder::class);
     
    }
}
