<?php

namespace App\Http\Livewire\AssetDetails;

use App\Models\Asset;
use App\Models\Vendor;
use Livewire\Component;
use App\Models\Purchase;
use App\Models\VendorType;
use App\Models\AssetDetail;
use Livewire\WithFileUploads;
use App\Models\PurchaseDocument;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $asset_details;
    public $asset_detail_id;
    public $assets;
    public $asset;
    public $asset_id;



    public function mount($id){
        $this->asset = Asset::find($id);
        $this->asset_details = AssetDetail::where('asset_id', $this->asset->id)->latest()->get();
    }

    public function edit($id){

        $asset_detail = AssetDetail::find($id);
        $this->asset_id = $asset_detail->asset_id;
        $this->serial_number = $asset_detail->serial_number;
        $this->asset_detail_id = $asset_detail->id;

        $this->dispatchBrowserEvent('show-asset_detailsEditModal');

        }

        public function update()
        {
            if ($this->asset_detail_id) {
                try{

                $asset_detail = AssetDetail::find($this->asset_detail_id);
                $asset_detail->serial_number = $this->serial_number;
                $asset_detail->update();

                $this->dispatchBrowserEvent('hide-asset_detailsEditModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Serial Number Updated Successfully!!"
                ]);
            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating serial number!!"
                ]);
            }

            }
        }


    public function render()
    {
        $this->asset_details = AssetDetail::where('asset_id', $this->asset->id)->latest()->get();
        return view('livewire.asset-details.index',[
            'asset_details' => $this->asset_details
        ]);
    }
}
