<?php

namespace App\Http\Livewire\Documents;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Trip;
use App\Models\Tyre;
use App\Models\Agent;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\Route;
use App\Models\Broker;
use App\Models\Folder;
use App\Models\Ticket;
use App\Models\Vendor;
use App\Models\Company;
use App\Models\Payment;
use App\Models\Retread;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Incident;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\cash_flow;
use App\Models\Consignee;
use App\Models\Inventory;
use App\Models\TruckStop;
use App\Models\Department;
use App\Models\Requisition;
use App\Models\Transporter;
use App\Models\LoadingPoint;
use App\Models\ClearingAgent;
use Livewire\WithFileUploads;
use App\Models\OffloadingPoint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithFileUploads;

    public $category;
    public $retread;
    public $retread_id;
    public $loading_point;
    public $loading_point_id;
    public $ticket;
    public $ticket_id;
    public $offloading_point;
    public $offloading_point_id;
    public $truck_stop;
    public $truck_stop_id;
    public $horse;
    public $horse_id;
    public $bill;
    public $bill_id;
    public $trip;
    public $trip_id;
    public $consignee;
    public $consignee_id;
    public $incident;
    public $incident_id;
    public $route;
    public $route_id;
    public $vehicle;
    public $vehicle_id;
    public $trailer;
    public $trailer_id;
    public $requisition;
    public $requisition_id;
    public $employee;
    public $employee_id;
    public $cash_flow;
    public $cash_flow_id;
    public $company;
    public $company_id;
    public $vendor;
    public $vendor_id;
    public $recovery;
    public $recovery_id;
    public $payment;
    public $payment_id;
    public $broker;
    public $broker_id;
    public $department;
    public $department_id;
    public $clearing_agent;
    public $clearing_agent_id;
    public $purchase;
    public $purchase_id;
    public $agent;
    public $agent_id;
    public $item_id;
    public $asset;
    public $asset_id;
    public $inventory;
    public $inventory_id;
    public $tyre;
    public $tyre_id;
    public $transporter;
    public $transporter_id;
    public $customer;
    public $customer_id;
    public $user_id;
    public $document;
    public $documents;
    public $document_id;
    public $folders;
    public $folder;
    public $folder_id;
    public $selectedFolder;
    public $is_open = FALSE;
    public $folder_title;

    public $title;
    public $expires_at;
    public $file;
    public $filename;
    public $rand;
   


    private function resetInputFields(){
        $this->title = "";
        $this->document_id = "";
        $this->folder_title = "";
        $this->folder_id = "";
        $this->selectedFolder = "";
        $this->file = null;
        $this->rand++;
        $this->expires_at = "";
      
    }

    public function setFolder($id){
        if ($id == $this->selectedFolder ) {
            if ($this->is_open == FALSE) {
                $this->selectedFolder = $id;
                $this->is_open = TRUE;
            }else{
                $this->selectedFolder = NULL;
                $this->is_open = FALSE;
            }
        }else{
            $this->selectedFolder = $id;
            $this->is_open = TRUE;
        }  
    }


    public function mount($id,$category){
        $this->category = $category;
        $this->item_id = $id;
    if ($this->category == "customer") {
        $this->customer = Customer::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('customer_id', $this->customer->id)->latest()->get();
    }
    elseif ($this->category == "employee") {
        $this->employee = Employee::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('employee_id', $this->employee->id)->latest()->get();
    }
    elseif ($this->category == "trip") {
        $this->trip = Trip::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('trip_id', $this->trip->id)->latest()->get();
    }
    elseif ($this->category == "retread") {
        $this->retread = Retread::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('retread_id', $this->retread->id)->latest()->get();
    }
    elseif ($this->category == "bill") {
        $this->bill = Bill::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('bill_id', $this->bill->id)->latest()->get();
    }
    elseif ($this->category == "consignee") {
        $this->consignee = Consignee::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('consignee_id', $this->consignee->id)->latest()->get();
    }
    elseif ($this->category == "department") {
        $this->department = Department::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('department_id', $this->department->id)->latest()->get();
    }
    elseif ($this->category == "incident") {
        $this->incident = Incident::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('incident_id', $this->incident->id)->latest()->get();
    }
    elseif ($this->category == "truck_stop") {
        $this->truck_stop = TruckStop::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('truck_stop_id', $this->truck_stop->id)->latest()->get();
    }
    elseif ($this->category == "loading_point") {
        $this->loading_point = LoadingPoint::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('loading_point_id', $this->loading_point->id)->latest()->get();
    }
    elseif ($this->category == "offloading_point") {
        $this->offloading_point = OffloadingPoint::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('offloading_point_id', $this->offloading_point->id)->latest()->get();
    }
    elseif ($this->category == "route") {
        $this->route = Route::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('route_id', $this->route->id)->latest()->get();
    }
    elseif ($this->category == "horse") {
        $this->horse = Horse::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('horse_id', $this->horse->id)->latest()->get();
    }
    elseif ($this->category == "ticket") {
        $this->ticket = Ticket::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('ticket_id', $this->ticket->id)->latest()->get();
    }
    elseif ($this->category == "trailer") {
        $this->trailer = Trailer::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('trailer_id', $this->trailer->id)->latest()->get();
    }
    elseif ($this->category == "requisition") {
        $this->requisition = Requisition::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('requisition_id', $this->requisition->id)->latest()->get();
    }
    elseif ($this->category == "vehicle") {
        $this->vehicle = Vehicle::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('vehicle_id', $this->vehicle->id)->latest()->get();
    }
    elseif ($this->category == "company") {
        $this->company = Company::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('company_id', $this->company->id)->latest()->get();
    }
    elseif ($this->category == "cash_flow") {
        $this->cash_flow = CashFlow::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('cash_flow_id', $this->cash_flow->id)->latest()->get();
    }
    elseif ($this->category == "recovery") {
        $this->recovery = Recovery::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('recovery_id', $this->recovery->id)->latest()->get();
    }
    elseif ($this->category == "payment") {
        $this->payment = Payment::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('payment_id', $this->payment->id)->latest()->get();
    }
    elseif ($this->category == "asset") {
        $this->asset = Asset::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('asset_id', $this->asset->id)->latest()->get();
    }
    elseif ($this->category == "inventory") {
        $this->inventory = Inventory::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('inventory_id', $this->inventory->id)->latest()->get();
    }
    elseif ($this->category == "tyre") {
        $this->tyre = Tyre::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('tyre_id', $this->tyre->id)->latest()->get();
    }
    elseif ($this->category == "clearing_agent") {
        $this->clearing_agent = ClearingAgent::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('clearing_agent_id', $this->clearing_agent->id)->latest()->get();
    }
    elseif ($this->category == "purchase") {
        $this->purchase = Purchase::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('purchase_id', $this->purchase->id)->latest()->get();
    }
    elseif ($this->category == "vendor") {
        $this->vendor = Vendor::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('vendor_id', $this->vendor->id)->latest()->get();
    }
    elseif ($this->category == "broker") {
        $this->broker = Broker::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('broker_id', $this->broker->id)->latest()->get();
    }
    elseif ($this->category == "transporter") {
        $this->transporter = Transporter::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('transporter_id', $this->transporter->id)->latest()->get();
    }
    elseif ($this->category == "agent") {
        $this->agent = Agent::find($id);
        $this->folders = Folder::where('category', $this->category)->latest()->get();
        $this->documents = Document::where('category', $this->category)
        ->where('agent_id', $this->agent->id)->latest()->get();
    }
    

    }

    public function updatedSelectedFolder($selected_folder_id){

        if(!is_null($selected_folder_id)){
            if ($this->category == "customer") {
                $this->customer = Customer::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('customer_id', $this->customer->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "employee") {
                $this->employee = Employee::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('employee_id', $this->employee->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "trip") {
                $this->trip = Trip::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('trip_id', $this->trip->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "department") {
                $this->department = Department::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('department_id', $this->department->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "retread") {
                $this->retread = Retread::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('retread_id', $this->retread->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "bill") {
                $this->bill = Bill::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('bill_id', $this->bill->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "consignee") {
                $this->consignee = Consignee::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('consignee_id', $this->consignee->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "incident") {
                $this->incident = Incident::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('incident_id', $this->incident->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "route") {
                $this->route = Route::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('route_id', $this->route->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "loading_point") {
                $this->loading_point = LoadingPoint::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('loading_point_id', $this->loading_point->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "offloading_point") {
                $this->offloading_point = OffloadingPoint::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('offloading_point_id', $this->offloading_point->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "truck_stop") {
                $this->truck_stop = TruckStop::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('truck_stop_id', $this->truck_stop->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "horse") {
                $this->horse = Horse::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('horse_id', $this->horse->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "ticket") {
                $this->ticket = Ticket::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('ticket_id', $this->ticket->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "trailer") {
                $this->trailer = Trailer::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('trailer_id', $this->trailer->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "requisition") {
                $this->requisition = Requisition::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('requisition_id', $this->requisition->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "vehicle") {
                $this->vehicle = Vehicle::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('vehicle_id', $this->vehicle->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "company") {
                $this->company = Company::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('company_id', $this->company->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "cash_flow") {
                $this->cash_flow = CashFlow::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('cash_flow_id', $this->cash_flow->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "recovery") {
                $this->recovery = Recovery::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('recovery_id', $this->recovery->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "payment") {
                $this->payment = Payment::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('payment_id', $this->payment->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "asset") {
                $this->asset = Asset::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('asset_id', $this->asset->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "inventory") {
                $this->inventory = Inventory::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('inventory_id', $this->inventory->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "tyre") {
                $this->tyre = Tyre::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('tyre_id', $this->tyre->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "clearing_agent") {
                $this->clearing_agent = ClearingAgent::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('clearing_agent_id', $this->clearing_agent->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "purchase") {
                $this->purchase = Purchase::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('purchase_id', $this->purchase->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "vendor") {
                $this->vendor = Vendor::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('vendor_id', $this->vendor->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "broker") {
                $this->broker = Broker::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('broker_id', $this->broker->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "transporter") {
                $this->transporter = Transporter::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('transporter_id', $this->transporter->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
            elseif ($this->category == "agent") {
                $this->agent = Agent::find($this->item_id);
                $this->documents = Document::where('category', $this->category)
                ->where('agent_id', $this->agent->id)
                ->where('folder_id', $selected_folder_id)
                ->latest()->get();
            }
        }
    }

    public function showDocumentDelete($id){
        $this->document_id = $id;
        $this->document = Document::find($id);
        $this->dispatchBrowserEvent('show-documentDeleteModal');
    }
    
    public function deleteDocument(){

        $this->document->delete();
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Document Deleted Successfully Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-documentDeleteModal');
    }

    public function showFolderDelete($id){
        $this->folder_id = $id;
        $this->folder = Folder::find($id);
        $this->dispatchBrowserEvent('show-folderDeleteModal');
    }
    public function deleteFolder(){
        $documents = $this->folder->documents;
        if (isset($documents)) {
            foreach ($documents as $document) {
                $document->delete();
            }
        }
        $this->folder->delete();
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Folder Deleted Successfully Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-folderDeleteModal');
    }
    public function showFolder(){
        $this->dispatchBrowserEvent('show-folderModal');
    }

    public function storeFolder(){
        // try{

            $folder = new Folder;
            $folder->user_id = Auth::user()->id;
            $folder->category = $this->category;
            $folder->title = $this->folder_title;
            $folder->save();

            $this->folder_id = $folder->id;

            $this->dispatchBrowserEvent('hide-folderModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Folder Created Successfully!!"
            ]);

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while creating folder!!"
        //     ]);
        // }
    }
   
    public function updateFolder(){
        // try{

            $folder = Folder::find($this->folder_id);
            $folder->user_id = Auth::user()->id;
            $folder->category = $this->category;
            $folder->title = $this->folder_title;
            $folder->update();

            $this->dispatchBrowserEvent('hide-folderEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Folder Updated Successfully!!"
            ]);

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while updating folder!!"
        //     ]);
        // }
    }
    public function store(){
        // try{

            if(isset($this->file)){
                $file = $this->file;
                // get file with ext
                $fileNameWithExt = $file->getClientOriginalName();
                //get filename
                $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                //get extention
                $extention = $file->getClientOriginalExtension();
                //file name to store
                $fileNameToStore= $filename.'_'.time().'.'.$extention;
                $file->storeAs('/documents', $fileNameToStore, 'my_files');
            }
            $document = new Document;

            if (isset($this->customer)) {
                $document->customer_id = $this->customer->id;
            }
            elseif (isset($this->employee)) {
                $document->employee_id = $this->employee->id;
            }
            elseif (isset($this->retread)) {
                $document->retread_id = $this->retread->id;
            }
            elseif (isset($this->trip)) {
                $document->trip_id = $this->trip->id;
            }
            elseif (isset($this->route)) {
                $document->route_id = $this->route->id;
            }
            elseif (isset($this->truck_stop)) {
                $document->truck_stop_id = $this->truck_stop->id;
            }
            elseif (isset($this->department)) {
                $document->department_id = $this->department->id;
            }
            elseif (isset($this->bill)) {
                $document->bill_id = $this->bill->id;
            }
            elseif (isset($this->consignee)) {
                $document->consignee_id = $this->consignee->id;
            }
            elseif (isset($this->offloading_point)) {
                $document->offloading_point_id = $this->offloading_point->id;
            }
            elseif (isset($this->loading_point)) {
                $document->loading_point_id = $this->loading_point->id;
            }
            elseif (isset($this->incident)) {
                $document->incident_id = $this->incident->id;
            }
            elseif (isset($this->recovery)) {
                $document->recovery_id = $this->recovery->id;
            }
            elseif (isset($this->horse)) {
                $document->horse_id = $this->horse->id;
            }
            elseif (isset($this->ticket)) {
                $document->ticket_id = $this->ticket->id;
            }
            elseif (isset($this->trailer)) {
                $document->trailer_id = $this->trailer->id;
            }
            elseif (isset($this->requisition)) {
                $document->requisition_id = $this->requisition->id;
            }
            elseif (isset($this->vehicle)) {
                $document->vehicle_id = $this->vehicle->id;
            }
            elseif (isset($this->cash_flow)) {
                $document->cash_flow_id = $this->cash_flow->id;
            }
            elseif (isset($this->company)) {
                $document->company_id = $this->company->id;
            }
            elseif (isset($this->payment)) {
                $document->payment_id = $this->payment->id;
            }
            elseif (isset($this->purchase)) {
                $document->purchase_id = $this->purchase->id;
            }
            elseif (isset($this->asset)) {
                $document->asset_id = $this->asset->id;
            }
            elseif (isset($this->inventory)) {
                $document->inventory_id = $this->inventory->id;
            }
            elseif (isset($this->tyre)) {
                $document->tyre_id = $this->tyre->id;
            }
            elseif (isset($this->transporter)) {
                $document->transporter_id = $this->transporter->id;
            }
            elseif (isset($this->agent)) {
                $document->agent_id = $this->agent->id;
            }
            elseif (isset($this->clearing_agent)) {
                $document->clearing_agent_id = $this->clearing_agent->id;
            }
            elseif (isset($this->broker)) {
                $document->broker_id = $this->broker->id;
            }
            elseif (isset($this->vendor)) {
                $document->vendor_id = $this->vendor->id;
            }
            $document->title = $this->title;

            if (isset($fileNameToStore)) {
                $document->filename = $fileNameToStore;
            }
            if(isset($this->expires_at)){
                $document->expires_at = Carbon::create($this->expires_at)->toDateTimeString();
                $today = now()->toDateTimeString();
                $expire = Carbon::create($this->expires_at)->toDateTimeString();
                if ($today <=  $expire) {
                    $document->status = 1;
                }else{
                    $document->status = 0;
                }
            }else {
                $document->status = 1;
              }
            $document->category = $this->category;
            $document->folder_id = $this->folder_id;
            $document->user_id = Auth::user()->id;
            $document->save();

          

            $this->dispatchBrowserEvent('hide-documentModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Document(s) Uploaded Successfully!!"
            ]);

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while uploading document(s)!!"
        //     ]);
        // }
    }

    public function editFolder($id){
        $folder = Folder::find($id);
        $this->category = $folder->category;
        $this->folder_title = $folder->title;
        $this->folder_id = $folder->id;

        $this->dispatchBrowserEvent('show-folderEditModal');

        }

    public function edit($id){

        $document = Document::find($id);
        $this->user_id = $document->user_id;
        $this->purchase_id = $document->purchase_id;
        $this->asset_id = $document->asset_id;
        $this->inventory_id = $document->inventory_id;
        $this->tyre_id = $document->tyre_id;
        $this->customer_id = $document->customer_id;
        $this->folder_id = $document->folder_id;
        $this->offloading_point_id = $document->offloading_point_id;
        $this->loading_point_id = $document->loading_point_id;
        $this->recovery_id = $document->recovery_id;
        $this->department_id = $document->department_id;
        $this->trip_id = $document->trip_id;
        $this->consignee_id = $document->consignee_id;
        $this->payment_id = $document->payment_id;
        $this->route_id = $document->route_id;
        $this->bill_id = $document->bill_id;
        $this->retread_id = $document->retread_id;
        $this->incident_id = $document->incident_id;
        $this->truck_stop_id = $document->truck_stop_id;
        $this->company_id = $document->company_id;
        $this->vehicle_id = $document->vehicle_id;
        $this->trailer_id = $document->trailer_id;
        $this->requisition_id = $document->requisition_id;
        $this->horse_id = $document->horse_id;
        $this->ticket_id = $document->ticket_id;
        $this->cash_flow_id = $document->cash_flow_id;
        $this->employee_id = $document->employee_id;
        $this->vendor_id = $document->vendor_id;
        $this->broker_id = $document->broker_id;
        $this->clearing_agent_id = $document->clearing_agent_id;
        $this->transporter_id = $document->transporter_id;
        $this->agent_id = $document->agent_id;
        $this->title = $document->title;
        $this->expires_at = $document->expiry_date;
        $this->filename = $document->filename;
        $this->document_id = $document->id;

        $this->dispatchBrowserEvent('show-documentEditModal');

        }

        public function update()
        {
            if ($this->document_id) {
                try{
                    if(isset($this->file)){
                        $file = $this->file;
                        // get file with ext
                        $fileNameWithExt = $file->getClientOriginalName();
                        //get filename
                        $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                        //get extention
                        $extention = $file->getClientOriginalExtension();
                        //file name to store
                        $fileNameToStore= $filename.'_'.time().'.'.$extention;
                        $file->storeAs('/documents', $fileNameToStore, 'my_files');

                    }
                $document = Document::find($this->document_id);
                $document->title = $this->title;
                $document->folder_id = $this->folder_id;
                if (isset($fileNameToStore)) {
                    $document->filename = $fileNameToStore;
                }
                if (isset($this->customer_id)) {
                    $document->customer_id = $this->customer_id;
                }
                elseif (isset($this->purchase_id)) {
                    $document->purchase_id = $this->purchase_id;
                }
                elseif (isset($this->truck_stop_id)) {
                    $document->truck_stop_id = $this->truck_stop_id;
                }
                elseif (isset($this->horse_id)) {
                    $document->horse_id = $this->horse_id;
                }
                elseif (isset($this->ticket_id)) {
                    $document->ticket_id = $this->ticket_id;
                }
                elseif (isset($this->trip_id)) {
                    $document->trip_id = $this->trip_id;
                }
                elseif (isset($this->retread_id)) {
                    $document->retread_id = $this->retread_id;
                }
                elseif (isset($this->incident_id)) {
                    $document->incident_id = $this->incident_id;
                }
                elseif (isset($this->trailer_id)) {
                    $document->trailer_id = $this->trailer_id;
                }
                elseif (isset($this->consignee_id)) {
                    $document->consignee_id = $this->consignee_id;
                }
                elseif (isset($this->bill_id)) {
                    $document->bill_id = $this->bill_id;
                }
                elseif (isset($this->route_id)) {
                    $document->route_id = $this->route_id;
                }
                elseif (isset($this->loading_point_id)) {
                    $document->loading_point_id = $this->loading_point_id;
                }
                elseif (isset($this->department_id)) {
                    $document->department_id = $this->department_id;
                }
                elseif (isset($this->offloading_point_id)) {
                    $document->offloading_point_id = $this->offloading_point_id;
                }
                elseif (isset($this->requisition_id)) {
                    $document->requisition_id = $this->requisition_id;
                }
                elseif (isset($this->vehicle_id)) {
                    $document->vehicle_id = $this->vehicle_id;
                }
                elseif (isset($this->recovery_id)) {
                    $document->recovery_id = $this->recovery_id;
                }
                elseif (isset($this->payment_id)) {
                    $document->payment_id = $this->payment_id;
                }
                elseif (isset($this->asset_id)) {
                    $document->asset_id = $this->asset_id;
                }
                elseif (isset($this->inventory_id)) {
                    $document->inventory_id = $this->inventory_id;
                }
                elseif (isset($this->tyre_id)) {
                    $document->tyre_id = $this->tyre_id;
                }
                elseif (isset($this->cash_flow_id)) {
                    $document->cash_flow_id = $this->cash_flow_id;
                }
                elseif (isset($this->company_id)) {
                    $document->company_id = $this->company_id;
                }
                elseif (isset($this->employee_id)) {
                    $document->employee_id = $this->employee_id;
                }
                elseif (isset($this->clearing_agent_id)) {
                    $document->clearing_agent_id = $this->clearing_agent_id;
                }
                elseif (isset($this->transporter_id)) {
                    $document->transporter_id = $this->transporter_id;
                }
                elseif (isset($this->broker_id)) {
                    $document->broker_id = $this->broker_id;
                }
                elseif (isset($this->vendor_id)) {
                    $document->vendor_id = $this->vendor_id;
                }
                elseif (isset($this->agent_id)) {
                    $document->agent_id = $this->agent_id;
                }
                if(isset($this->expires_at)){
                    $document->expires_at = Carbon::create($this->expires_at)->toDateTimeString();
                    $today = now()->toDateTimeString();
                    $expire = Carbon::create($this->expires_at)->toDateTimeString();
                    if ($today <=  $expire) {
                        $document->status = 1;
                    }else{
                        $document->status = 0;
                    }
                }
                $document->update();

                

                $this->dispatchBrowserEvent('hide-documentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Document Updated Successfully!!"
                ]);
            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating document(s)!!"
                ]);
            }

            }
        }


    public function render()
    {
        if ($this->category == "customer") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('customer_id', $this->customer->id)->latest()->get();
        }elseif ($this->category == "agent") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('agent_id', $this->agent->id)->latest()->get();
        }elseif ($this->category == "purchase") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('purchase_id', $this->purchase->id)->latest()->get();
        }elseif ($this->category == "transporter") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('transporter_id', $this->transporter->id)->latest()->get();
        }  
        elseif ($this->category == "employee") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('employee_id', $this->employee->id)->latest()->get();
        }
        elseif ($this->category == "trip") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('trip_id', $this->trip->id)->latest()->get();
        }
        elseif ($this->category == "retread") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('retread_id', $this->retread->id)->latest()->get();
        }
        elseif ($this->category == "bill") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('bill_id', $this->bill->id)->latest()->get();
        }
        elseif ($this->category == "consignee") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('consignee_id', $this->consignee->id)->latest()->get();
        }
        elseif ($this->category == "department") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('department_id', $this->department->id)->latest()->get();
        }
        elseif ($this->category == "incident") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('incident_id', $this->incident->id)->latest()->get();
        }
        elseif ($this->category == "loading_point") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('loading_point_id', $this->loading_point->id)->latest()->get();
        }
        elseif ($this->category == "offloading_point") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('offloading_point_id', $this->offloading_point->id)->latest()->get();
        }
        elseif ($this->category == "truck_stop") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('truck_stop_id', $this->truck_stop->id)->latest()->get();
        }
        elseif ($this->category == "route") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('route_id', $this->route->id)->latest()->get();
        }
        elseif ($this->category == "cash_flow") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('cash_flow_id', $this->cash_flow->id)->latest()->get();
        }
        elseif ($this->category == "recovery") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('recovery_id', $this->recovery->id)->latest()->get();
        }
        elseif ($this->category == "company") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('company_id', $this->company->id)->latest()->get();
        }
        elseif ($this->category == "payment") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('payment_id', $this->payment->id)->latest()->get();
        }
        elseif ($this->category == "asset") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('asset_id', $this->asset->id)->latest()->get();
        }
        elseif ($this->category == "inventory") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('inventory_id', $this->inventory->id)->latest()->get();
        }
        elseif ($this->category == "tyre") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('tyre_id', $this->tyre->id)->latest()->get();
        }
        elseif ($this->category == "horse") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('horse_id', $this->horse->id)->latest()->get();
        }
        elseif ($this->category == "ticket") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('ticket_id', $this->ticket->id)->latest()->get();
        }
        elseif ($this->category == "trailer") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('trailer_id', $this->trailer->id)->latest()->get();
        }
        elseif ($this->category == "requisition") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('requisition_id', $this->requisition->id)->latest()->get();
        }
        elseif ($this->category == "vehicle") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('vehicle_id', $this->vehicle->id)->latest()->get();
        }
        elseif ($this->category == "vendor") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('vendor_id', $this->vendor->id)->latest()->get();
        }
        elseif ($this->category == "broker") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('broker_id', $this->broker->id)->latest()->get();
        }
        elseif ($this->category == "clearing_agent") {
            $this->folders = Folder::where('category', $this->category)->latest()->get();
            $this->documents = Document::where('category', $this->category)
            ->where('clearing_agent_id', $this->clearing_agent->id)->latest()->get();
        }
     
        return view('livewire.documents.index',[
            'documents'=> $this->documents,
            'folders'=> $this->folders
        ]);
    }
}
