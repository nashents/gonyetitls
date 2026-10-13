<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\ShippingLine;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShippingLines extends Component
{
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
        $name = mb_strtolower(trim((string) $name));
        if ($name === '') {
            return null;
        }

        return Vendor::whereRaw('LOWER(TRIM(name)) = ?', [$name])->first();
    }

    // Attach the same-named vendor, or create one, so every shipping line can be billed
    private function resolveVendor(): array
    {
        if ($this->vendor_id) {
            return [$this->vendor_id, false];
        }

        if ($vendor = $this->matchingVendor($this->name)) {
            return [$vendor->id, false];
        }

        $vendor = new Vendor;
        $vendor->creator_id = Auth::id();
        $vendor->company_id = optional(optional(Auth::user()->employee)->company)->id
            ?? optional(Auth::user()->company)->id;
        $vendor->vendor_number = Vendor::nextVendorNumber();
        $vendor->name = trim($this->name);
        $vendor->status = 1;
        $vendor->save();

        return [$vendor->id, true];
    }

    public function save()
    {
        $this->validate();

        [$vendorId, $vendorCreated] = $this->resolveVendor();
        $name = trim($this->name);

        ShippingLine::updateOrCreate(
            ['id' => $this->shipping_line_id],
            [
                'name' => $this->name,
                'vendor_id' => $vendorId,
                'is_active' => $this->is_active,
            ]
        );

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
            'shippingLines' => ShippingLine::with('vendor')->orderBy('name', 'asc')->get(),
        ]);
    }
}
