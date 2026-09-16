<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\ShippingLine;
use App\Models\Vendor;
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
        $this->vendor_id = $shippingLine->vendor_id;
        $this->is_active = $shippingLine->is_active;
    }

    public function save()
    {
        $this->validate();

        ShippingLine::updateOrCreate(
            ['id' => $this->shipping_line_id],
            [
                'name' => $this->name,
                'vendor_id' => $this->vendor_id ?: null,
                'is_active' => $this->is_active,
            ]
        );

        $this->reset(['shipping_line_id', 'name', 'vendor_id']);
        $this->is_active = true;
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Shipping line saved.']);
    }

    public function delete($id)
    {
        ShippingLine::findOrFail($id)->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Shipping line removed.']);
    }

    public function render()
    {
        return view('livewire.freight.settings.shipping-lines', [
            'shippingLines' => ShippingLine::with('vendor')->orderBy('name', 'asc')->get(),
        ]);
    }
}
