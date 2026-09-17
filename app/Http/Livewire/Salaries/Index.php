<?php

namespace App\Http\Livewire\Salaries;

use App\Models\Salary;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
   
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
   
    
    private $salaries;
    public $salary_id;


    public function mount(){
        $this->resetPage();
    } 

    public function render()
    {
        return view('livewire.salaries.index',[
            'salaries' => Salary::whereHas('employee', function($query){
                return $query->where('status', true);
            })
            ->with([
                'salary_items.allowance:id,name',
                'salary_items.deduction:id,name',
                'salary_items.recovery:id,name,type',
                'salary_items.loan:id,loan_number,loan_type_id',
                'salary_items.loan.loan_type:id,name',
            ])
            ->latest()->paginate(10)
        ]);
    }
}
