<?php

namespace App\Http\Livewire\Documents;

use App\Models\Agent;
use App\Models\Application;
use App\Models\Asset;
use App\Models\Bill;
use App\Models\Broker;
use App\Models\CashFlow;
use App\Models\ClearingAgent;
use App\Models\Company;
use App\Models\Consignee;
use App\Models\Customer;
use App\Models\CustomsDeclaration;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Folder;
use App\Models\FreightJob;
use App\Models\Horse;
use App\Models\Incident;
use App\Models\Inventory;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Requisition;
use App\Models\Retread;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\ShippingContainer;
use App\Models\Ticket;
use App\Models\Trailer;
use App\Models\Training;
use App\Models\Transporter;
use App\Models\Trip;
use App\Models\TruckStop;
use App\Models\Tyre;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\WasteCollection;
use App\Models\WasteDisposal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $perPage = 25;
    public $category;
    public $item_id;

    public $customer;
    public $employee;
    public $trip;
    public $retread;
    public $application;
    public $bill;
    public $consignee;
    public $department;
    public $incident;
    public $truck_stop;
    public $loading_point;
    public $offloading_point;
    public $waste_disposal;
    public $waste_collection;
    public $route;
    public $horse;
    public $ticket;
    public $training;
    public $trailer;
    public $requisition;
    public $vehicle;
    public $company;
    public $cash_flow;
    public $recovery;
    public $payment;
    public $asset;
    public $inventory;
    public $tyre;
    public $clearing_agent;
    public $purchase;
    public $vendor;
    public $broker;
    public $transporter;
    public $agent;
    public $freight_job;
    public $shipment;
    public $shipping_container;
    public $customs_declaration;

    public $customer_id;
    public $employee_id;
    public $trip_id;
    public $retread_id;
    public $application_id;
    public $bill_id;
    public $consignee_id;
    public $department_id;
    public $incident_id;
    public $truck_stop_id;
    public $loading_point_id;
    public $offloading_point_id;
    public $waste_disposal_id;
    public $waste_collection_id;
    public $route_id;
    public $horse_id;
    public $ticket_id;
    public $training_id;
    public $trailer_id;
    public $requisition_id;
    public $vehicle_id;
    public $company_id;
    public $cash_flow_id;
    public $recovery_id;
    public $payment_id;
    public $asset_id;
    public $inventory_id;
    public $tyre_id;
    public $clearing_agent_id;
    public $purchase_id;
    public $vendor_id;
    public $broker_id;
    public $transporter_id;
    public $agent_id;
    public $freight_job_id;
    public $shipment_id;
    public $shipping_container_id;
    public $customs_declaration_id;
    public $user_id;

    public $document;
    private $documents;
    public $document_id;
    public $folders;
    public $folder;
    public $folder_id;
    public $selectedFolder;
    public $is_open = false;
    public $folder_title;

    public $title;
    public $expires_at;
    public $file;
    public $filename;
    public $rand;

    public bool $expiredDocumentsOnly = false;

    protected $queryString = [
        'expiredDocumentsOnly' => ['as' => 'expired_documents', 'except' => false],
    ];

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'file' => $this->document_id ? 'nullable|file|max:10240' : 'required|file|max:10240',
            'expires_at' => 'nullable|date',
            'folder_id' => 'nullable|integer',
        ];
    }

    private function categoryMap(): array
    {
        return [
            'customer' => ['model' => Customer::class, 'property' => 'customer', 'column' => 'customer_id'],
            'employee' => ['model' => Employee::class, 'property' => 'employee', 'column' => 'employee_id'],
            'trip' => ['model' => Trip::class, 'property' => 'trip', 'column' => 'trip_id'],
            'retread' => ['model' => Retread::class, 'property' => 'retread', 'column' => 'retread_id'],
            'application' => ['model' => Application::class, 'property' => 'application', 'column' => 'application_id'],
            'bill' => ['model' => Bill::class, 'property' => 'bill', 'column' => 'bill_id'],
            'consignee' => ['model' => Consignee::class, 'property' => 'consignee', 'column' => 'consignee_id'],
            'department' => ['model' => Department::class, 'property' => 'department', 'column' => 'department_id'],
            'incident' => ['model' => Incident::class, 'property' => 'incident', 'column' => 'incident_id'],
            'truck_stop' => ['model' => TruckStop::class, 'property' => 'truck_stop', 'column' => 'truck_stop_id'],
            'loading_point' => ['model' => LoadingPoint::class, 'property' => 'loading_point', 'column' => 'loading_point_id'],
            'offloading_point' => ['model' => OffloadingPoint::class, 'property' => 'offloading_point', 'column' => 'offloading_point_id'],
            'waste_disposal' => ['model' => WasteDisposal::class, 'property' => 'waste_disposal', 'column' => 'waste_disposal_id'],
            'waste_collection' => ['model' => WasteCollection::class, 'property' => 'waste_collection', 'column' => 'waste_collection_id'],
            'route' => ['model' => Route::class, 'property' => 'route', 'column' => 'route_id'],
            'horse' => ['model' => Horse::class, 'property' => 'horse', 'column' => 'horse_id'],
            'ticket' => ['model' => Ticket::class, 'property' => 'ticket', 'column' => 'ticket_id'],
            'training' => ['model' => Training::class, 'property' => 'training', 'column' => 'training_id'],
            'trailer' => ['model' => Trailer::class, 'property' => 'trailer', 'column' => 'trailer_id'],
            'requisition' => ['model' => Requisition::class, 'property' => 'requisition', 'column' => 'requisition_id'],
            'vehicle' => ['model' => Vehicle::class, 'property' => 'vehicle', 'column' => 'vehicle_id'],
            'company' => ['model' => Company::class, 'property' => 'company', 'column' => 'company_id'],
            'cash_flow' => ['model' => CashFlow::class, 'property' => 'cash_flow', 'column' => 'cash_flow_id'],
            'recovery' => ['model' => Recovery::class, 'property' => 'recovery', 'column' => 'recovery_id'],
            'payment' => ['model' => Payment::class, 'property' => 'payment', 'column' => 'payment_id'],
            'asset' => ['model' => Asset::class, 'property' => 'asset', 'column' => 'asset_id'],
            'inventory' => ['model' => Inventory::class, 'property' => 'inventory', 'column' => 'inventory_id'],
            'tyre' => ['model' => Tyre::class, 'property' => 'tyre', 'column' => 'tyre_id'],
            'clearing_agent' => ['model' => ClearingAgent::class, 'property' => 'clearing_agent', 'column' => 'clearing_agent_id'],
            'purchase' => ['model' => Purchase::class, 'property' => 'purchase', 'column' => 'purchase_id'],
            'vendor' => ['model' => Vendor::class, 'property' => 'vendor', 'column' => 'vendor_id'],
            'broker' => ['model' => Broker::class, 'property' => 'broker', 'column' => 'broker_id'],
            'transporter' => ['model' => Transporter::class, 'property' => 'transporter', 'column' => 'transporter_id'],
            'agent' => ['model' => Agent::class, 'property' => 'agent', 'column' => 'agent_id'],
            'freight_job' => ['model' => FreightJob::class, 'property' => 'freight_job', 'column' => 'freight_job_id'],
            'shipment' => ['model' => Shipment::class, 'property' => 'shipment', 'column' => 'shipment_id'],
            'shipping_container' => ['model' => ShippingContainer::class, 'property' => 'shipping_container', 'column' => 'shipping_container_id'],
            'customs_declaration' => ['model' => CustomsDeclaration::class, 'property' => 'customs_declaration', 'column' => 'customs_declaration_id'],
        ];
    }

    public function mount(?int $id = null, ?string $category = null): void
    {
        $this->category = $category ?? 'all';
        $this->item_id = $id;
        $this->rand = rand();
        $this->folders = collect();
        $this->documents = collect();

        $this->loadCategoryRecord();
    }

    private function loadCategoryRecord(): void
    {
        if ($this->category === 'all') {
            return;
        }

        $map = $this->categoryMap()[$this->category] ?? null;

        if (!$map || !$this->item_id) {
            return;
        }

        $this->{$map['property']} = $map['model']::find($this->item_id);
    }

    private function baseDocumentsQuery(): Builder
    {
        $query = Document::query()->with('user');

        if ($this->category !== 'all') {
            $map = $this->categoryMap()[$this->category] ?? null;

            if ($map) {
                $query->where('category', $this->category)
                    ->where($map['column'], $this->item_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($this->selectedFolder) {
            $query->where('folder_id', $this->selectedFolder);
        }

        if ($this->expiredDocumentsOnly || $this->category === 'all') {
            $query->whereNotNull('expires_at')
                ->where('expires_at', '<', now());
        }

        return $query;
    }

    private function applyDocumentVisibility($query)
    {
        $user = Auth::user();

        $roleNames = $user->roles->pluck('name')->toArray();

        if (in_array('Super Admin', $roleNames) || in_array('Admin', $roleNames)) {
            return $query;
        }

        $departmentNames = optional($user->employee)->departments
            ? $user->employee->departments->pluck('name')->toArray()
            : [];

        $isHR = in_array('Human Resource', $departmentNames);

        if ($isHR) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('category', '!=', 'employee')
            ->orWhere('employee_id', optional($user->employee)->id);
        });
    }

    private function refreshDocuments(): void
    {
        $this->folders = $this->category === 'all'
            ? collect()
            : Folder::where('category', $this->category)->latest()->get();
        
        $this->documents = $this->applyDocumentVisibility($this->baseDocumentsQuery())->latest()->paginate($this->perPage);
    }

    private function resetInputFields(): void
    {
        $this->title = '';
        $this->document_id = '';
        $this->folder_title = '';
        $this->folder_id = '';
        $this->selectedFolder = '';
        $this->file = null;
        $this->filename = '';
        $this->expires_at = '';
        $this->rand = rand();
    }

    public function setFolder($id): void
    {
        if ($id == $this->selectedFolder) {
            $this->selectedFolder = $this->is_open ? null : $id;
            $this->is_open = !$this->is_open;
        } else {
            $this->selectedFolder = $id;
            $this->is_open = true;
        }
    }

    public function updatedSelectedFolder($selectedFolderId): void
    {
        $this->selectedFolder = $selectedFolderId;
        $this->refreshDocuments();
    }

    public function showDocumentDelete($id): void
    {
        $this->document_id = $id;
        $this->document = Document::find($id);
        $this->dispatchBrowserEvent('show-documentDeleteModal');
    }

    public function deleteDocument(): void
    {
        if ($this->document) {
            $this->document->delete();
        }

        $this->resetInputFields();
        $this->refreshDocuments();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Document Deleted Successfully!!',
        ]);
        $this->dispatchBrowserEvent('hide-documentDeleteModal');
    }

    public function showFolderDelete($id): void
    {
        $this->folder_id = $id;
        $this->folder = Folder::find($id);
        $this->dispatchBrowserEvent('show-folderDeleteModal');
    }

    public function deleteFolder(): void
    {
        if ($this->folder && $this->folder->is_locked) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'This folder is a core system folder and cannot be deleted.',
            ]);
            $this->dispatchBrowserEvent('hide-folderDeleteModal');
            return;
        }

        if ($this->folder) {
            $this->folder->documents()->delete();
            $this->folder->delete();
        }

        $this->resetInputFields();
        $this->refreshDocuments();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Folder Deleted Successfully!!',
        ]);
        $this->dispatchBrowserEvent('hide-folderDeleteModal');
    }

    public function showFolder(): void
    {
        $this->dispatchBrowserEvent('show-folderModal');
    }

    public function storeFolder(): void
    {
        if ($this->category === 'all') {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Folders can only be created from a specific module.',
            ]);
            return;
        }

        $this->validateOnly('folder_title', [
            'folder_title' => 'required|string|max:255',
        ]);

        $folder = Folder::create([
            'user_id' => Auth::id(),
            'category' => $this->category,
            'title' => $this->folder_title,
        ]);

        $this->folder_id = $folder->id;
        $this->dispatchBrowserEvent('hide-folderModal');
        $this->resetInputFields();
        $this->refreshDocuments();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Folder Created Successfully!!',
        ]);
    }

    public function editFolder($id): void
    {
        $folder = Folder::find($id);

        if (!$folder) {
            return;
        }

        $this->category = $folder->category;
        $this->folder_title = $folder->title;
        $this->folder_id = $folder->id;

        $this->dispatchBrowserEvent('show-folderEditModal');
    }

    public function updateFolder(): void
    {
        $this->validateOnly('folder_title', [
            'folder_title' => 'required|string|max:255',
        ]);

        $folder = Folder::find($this->folder_id);

        if (!$folder) {
            return;
        }

        if ($folder->is_locked && $this->folder_title !== $folder->title) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'This folder is a core system folder - its name cannot be changed.',
            ]);
            return;
        }

        $folder->update([
            'user_id' => Auth::id(),
            'category' => $this->category,
            'title' => $this->folder_title,
        ]);

        $this->dispatchBrowserEvent('hide-folderEditModal');
        $this->resetInputFields();
        $this->refreshDocuments();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Folder Updated Successfully!!',
        ]);
    }

    public function store(): void
    {
        if ($this->category === 'all') {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Documents can only be uploaded from a specific module.',
            ]);
            return;
        }

        $this->validate();

        $fileNameToStore = null;

        if ($this->file) {
            $fileNameToStore = $this->storeUploadedFile();
        }

        $document = new Document;
        $this->assignCurrentCategoryToDocument($document);

        $document->title = $this->title;
        $document->filename = $fileNameToStore;
        $document->expires_at = $this->expires_at ? Carbon::parse($this->expires_at)->toDateTimeString() : null;
        $document->status = $this->calculateStatus($this->expires_at);
        $document->category = $this->category;
        $document->folder_id = $this->folder_id ?: null;
        $document->user_id = Auth::id();
        $document->save();

        $this->dispatchBrowserEvent('hide-documentModal');
        $this->resetInputFields();
        $this->refreshDocuments();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Document(s) Uploaded Successfully!!',
        ]);
    }

    public function edit($id): void
    {
        $document = Document::find($id);

        if (!$document) {
            return;
        }

        $this->document = $document;
        $this->document_id = $document->id;
        $this->user_id = $document->user_id;
        $this->folder_id = $document->folder_id;
        $this->title = $document->title;
        $this->filename = $document->filename;
        $this->expires_at = $document->expires_at ? Carbon::parse($document->expires_at)->format('Y-m-d') : null;

        foreach ($this->categoryMap() as $map) {
            $column = $map['column'];
            $this->{$column} = $document->{$column};
        }

        $this->dispatchBrowserEvent('show-documentEditModal');
    }

    public function update(): void
    {
        if (!$this->document_id) {
            return;
        }

        $this->validate();

        try {
            $document = Document::find($this->document_id);

            if (!$document) {
                return;
            }

            $document->title = $this->title;
            $document->folder_id = $this->folder_id ?: null;

            if ($this->file) {
                $document->filename = $this->storeUploadedFile();
            }

            foreach ($this->categoryMap() as $map) {
                $column = $map['column'];
                if (!empty($this->{$column})) {
                    $document->{$column} = $this->{$column};
                }
            }

            $document->expires_at = $this->expires_at ? Carbon::parse($this->expires_at)->toDateTimeString() : null;
            $document->status = $this->calculateStatus($this->expires_at);
            $document->update();

            $this->dispatchBrowserEvent('hide-documentEditModal');
            $this->resetInputFields();
            $this->refreshDocuments();

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Document Updated Successfully!!',
            ]);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Something went wrong while updating document(s)!!',
            ]);
        }
    }

    private function storeUploadedFile(): string
    {
        $fileNameWithExt = $this->file->getClientOriginalName();
        $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
        $extension = $this->file->getClientOriginalExtension();
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

        $this->file->storeAs('/documents', $fileNameToStore, 'my_files');

        return $fileNameToStore;
    }

    private function assignCurrentCategoryToDocument(Document $document): void
    {
        $map = $this->categoryMap()[$this->category] ?? null;

        if ($map) {
            $document->{$map['column']} = $this->item_id;
        }
    }

    private function calculateStatus($expiresAt): int
    {
        if (!$expiresAt) {
            return 1;
        }

        return Carbon::parse($expiresAt)->endOfDay()->isPast() ? 0 : 1;
    }

    public function render()
    {
        $this->refreshDocuments();

        return view('livewire.documents.index', [
            'documents' => $this->documents,
            'folders' => $this->folders,
        ]);
    }
}
