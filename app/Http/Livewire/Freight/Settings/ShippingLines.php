<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\ShippingLine;
use App\Models\Vendor;
use App\Services\Freight\ShippingLineVendorService;
use Livewire\Component;
use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;

class ShippingLines extends Component
{
    use FreightEmbeddedPagination;

    public $shipping_line_id;
    public $name;
    public $vendor_id;
    public $is_active = true;

    public $vendors = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
        ];
    }

    public function mount()
    {
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
    }

    public function edit($id)
    {
        $shippingLine = ShippingLine::findOrFail($id);
        $this->shipping_line_id = $shippingLine->id;
        $this->name = $shippingLine->name;
        // Not linked yet: pre-select a vendor with the same name, if one exists
        $this->vendor_id = $shippingLine->vendor_id ?: optional($this->matchingVendor($shippingLine->name))->id;
        $this->is_active = $shippingLine->is_active;
    }

    public function updatedName($value)
    {
        if (!$this->vendor_id) {
            $this->vendor_id = optional($this->matchingVendor($value))->id;
        }
    }

    private function matchingVendor($name)
    {
        return app(ShippingLineVendorService::class)->matchingVendor($name);
    }

    public function save()
    {
        $this->validate();

        $name = trim($this->name);

        // No vendor picked: the model attaches the same-named vendor or creates one,
        // and a rename is copied to the linked vendor (ShippingLineVendorService).
        ShippingLineVendorService::$lastCreatedVendorId = null;
        ShippingLine::updateOrCreate(
            ['id' => $this->shipping_line_id],
            [
                'name' => $name,
                'vendor_id' => $this->vendor_id ?: null,
                'is_active' => $this->is_active,
            ]
        );
        $vendorCreated = ShippingLineVendorService::$lastCreatedVendorId !== null;

        $this->reset(['shipping_line_id', 'name', 'vendor_id']);
        $this->is_active = true;

        if ($vendorCreated) {
            $this->vendors = Vendor::orderBy('name', 'asc')->get();
        }

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $vendorCreated
            ? 'Shipping line saved and vendor "' . $name . '" created.'
            : 'Shipping line saved.']);
    }

    public function delete($id)
    {
        ShippingLine::findOrFail($id)->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Shipping line removed.']);
    }

    public function refresh($category)
    {
        if ($category === 'vendors') {
            $this->vendors = Vendor::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Vendors Refreshed Successfully!!.']);
        }
    }

    public function render()
    {
        return view('livewire.freight.settings.shipping-lines', [
            'shippingLines' => ShippingLine::with('vendor')->orderBy('name', 'asc')->orderBy('id')->paginate(10),
        ]);
    }
}
