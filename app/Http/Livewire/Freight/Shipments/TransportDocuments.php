<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\Shipment;
use App\Models\TransportDocument;
use App\Models\Vendor;
use Livewire\Component;

class TransportDocuments extends Component
{
    public $shipment;
    public $vendors;

    public $document_type;
    public $document_number;
    public $issue_date;
    public $carrier_vendor_id;
    public $carrier_name;
    public $place_of_issue;
    public $freight_payable_at;
    public $number_of_originals;
    public $status = 'draft';

    public function mount($shipmentId)
    {
        $this->shipment = Shipment::findOrFail($shipmentId);
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->refreshShipment();
    }

    private function refreshShipment()
    {
        $this->shipment = Shipment::with('transport_documents.carrier_vendor')->findOrFail($this->shipment->id);
    }

    protected function rules()
    {
        return [
            'document_type' => 'required|string',
            'document_number' => 'required|string|max:255',
        ];
    }

    public function store()
    {
        $this->validate();

        TransportDocument::create([
            'shipment_id' => $this->shipment->id,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'issue_date' => $this->issue_date ?: null,
            'carrier_vendor_id' => $this->carrier_vendor_id ?: null,
            'carrier_name' => $this->carrier_name,
            'place_of_issue' => $this->place_of_issue,
            'freight_payable_at' => $this->freight_payable_at,
            'number_of_originals' => $this->number_of_originals ?: null,
            'status' => $this->status ?: 'draft',
        ]);

        $this->reset(['document_type', 'document_number', 'issue_date', 'carrier_vendor_id', 'carrier_name', 'place_of_issue', 'freight_payable_at', 'number_of_originals']);
        $this->status = 'draft';

        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-addTransportDocumentModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Transport document added successfully!']);
    }

    public function render()
    {
        return view('livewire.freight.shipments.transport-documents');
    }
}
