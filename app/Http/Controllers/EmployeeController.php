<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::latest()->get();
        $departments = Department::all();
        $branches = Branch::all();
        return view('employees.index')->with([
            'departments' => $departments,
            'branches' => $branches,
            'employees' => $employees,
        ]);
    }
    public function archived()
    {
        return view('employees.archived');
    }
    public function leave()
    {
        return view('employees.leaves.index');
    }
    public function leaveEdit($id)
    {
        $employee = Employee::find($id);
        return view('employees.leaves.edit')->with('employee', $employee);
    }

    public function age()
    {
        return view('employees.age');
    }

    public function deleted()
    {
        return view('employees.deleted');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('employees.create');
    }

    public function getProfile($id)
    {
        $user = User::find($id);
        $employee = $user->employee;
        return view('profile')->with([
           'user' => $user,
           'employee' => $employee
        ]);
    }

    public function profile(Request $request, $id){
      
        $this->validate($request,[
            'file'=> 'required|image|max:2048'
        ]);

        if($request->hasFile('file')){
            $image =  $request->file('file');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(300,300,function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path('/images/uploads/' . $filename));
            $user = User::find($id);
            $user->profile = $filename;
            $user->update();
            Session::flash('success','Profile picture successfully uploaded');
            return redirect()->back();

        }
        else{
            return redirect()->back();
        }
    }

    
    public function reports(){
        return view('employees.reports');
    }

    public function deactivate(Employee $employee){
        $driver = $employee->driver;
        if (isset($driver)) {
            $driver->status = 0;
            $driver->update();
        }

        $employee->status = 0;
        $employee->update();

        $user = $employee->user;
        $user->active = 0 ;
        $user->update();
        Session::flash('success','User Account Suspended Successfully!!');
        return redirect()->back();
    }

    public function activate(Employee $employee){

        $driver = $employee->driver;
        if (isset($driver)) {
            $driver->status = 1;
            $driver->update();
        }

        $employee->status = 1;
        $employee->update();

        $user = $employee->user;
        $user->active = 1 ;
        $user->update();
        Session::flash('success','User Account Unsuspended Successfully!!');
        return redirect()->back();
    }
    public function archive($id){
        $employee = Employee::find($id);
        $employee->archive = 1;
        $employee->status = 0;
        $employee->update();
        Session::flash('success','Employee Archived Successfully!!');
        return redirect(route('employees.archived'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        $documents = $employee->documents;
        return view('employees.show')->with([
            'employee' => $employee,
            'documents '=> $documents
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit')->with('employee',$employee);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        $user = $employee->user;
        if ($user) {
            $user->delete();
        }
        $employee->delete();
        Session::flash('success','Employee Deleted Successfully!!');
        return redirect()->route('employees.index');
    }
}
