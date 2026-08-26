<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Models\TripStatus;
use App\Models\DeliveryNote;
use App\Mail\TripUpdatesMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TripStatusUpdateService
{

    public function preloadTripData($id){
      
        $trip = Trip::withTrashed()->with(['cargo', 'delivery_note', 'trip_destinations'])->find($id);
        if (!$trip) return null;

        $delivery_note = $trip->delivery_note;

        // Build data array that you can assign easily in your component
        return [
            'trip_id' => $trip->id,
            'trip_status' => $trip->trip_status,
            'selectedStatus' => $trip->trip_status,
            'currency_id' => $trip->currency_id,
            'turnover' => $trip->freight,
            'cost_of_sales' => $trip->transporter_freight,
            'calculation_measurement' => $trip->calculation_measurement,
            'trip_status_date' => $trip->trip_status_date,
            'selectedDeliveryNote' => in_array($trip->trip_status, ['Offloaded', 'Loaded']),
            'trip_status_description' => $trip->trip_status_description,
            'customer_updates' => $trip->customer_updates,
            'freight_calculation' => $trip->freight_calculation,
            'cargo_type' => $trip->cargo ? $trip->cargo->type : '',
            'ending_mileage' => $trip->ending_mileage,
            'starting_mileage' => $trip->starting_mileage,
            // Delivery note fields
            'measurement' => $delivery_note->measurement ?? null,
            'distance' => $delivery_note->distance ?? null,
            'loaded_quantity' => $delivery_note->loaded_quantity ?? null,
            'loaded_litreage' => $delivery_note->loaded_litreage ?? null,
            'loaded_litreage_at_20' => $delivery_note->loaded_litreage_at_20 ?? null,
            'loaded_weight' => $delivery_note->loaded_weight ?? null,
            'loaded_rate' => $delivery_note->loaded_rate ?? null,
            'loaded_freight' => $delivery_note->loaded_freight ?? null,
            'transporter_loaded_rate' => $delivery_note->transporter_loaded_rate ?? $trip->transporter_rate,
            'transporter_loaded_freight' => $delivery_note->transporter_loaded_freight ?? $trip->transporter_freight,
            'loaded_date' => $delivery_note->loaded_date ?? null,
            'offloaded_quantity' => $delivery_note->offloaded_quantity ?? null,
            'offloaded_litreage' => $delivery_note->offloaded_litreage ?? null,
            'offloaded_litreage_at_20' => $delivery_note->offloaded_litreage_at_20 ?? null,
            'offloaded_weight' => $delivery_note->offloaded_weight ?? null,
            'offloaded_distance' => $delivery_note->offloaded_distance ?? null,
            'offloaded_rate' => $delivery_note->status ? $delivery_note->offloaded_rate : $delivery_note->loaded_rate,
            'offloaded_freight' => $delivery_note->status ? $delivery_note->offloaded_freight : $delivery_note->loaded_freight,
            'transporter_offloaded_rate' => $delivery_note->status ? $delivery_note->transporter_offloaded_rate : $delivery_note->transporter_loaded_rate,
            'transporter_offloaded_freight' => $delivery_note->status ? $delivery_note->transporter_offloaded_freight : $delivery_note->transporter_loaded_freight,
            'offloaded_date' => $delivery_note->offloaded_date ?? null,
            // Aggregated values from trip_destinations
            'offloaded_weight_total' => $trip->trip_destinations->sum('weight'),
            'offloaded_quantity_total' => $trip->trip_destinations->sum('quantity'),
            'offloaded_litreage_total' => $trip->trip_destinations->sum('litreage'),
            'offloaded_litreage_at_20_total' => $trip->trip_destinations->sum('litreage_at_20'),
        ];
        
      }


    public function update(){

        $trip = Trip::withTrashed()->find($this->trip_id);
        $trip->trip_status = $this->selectedStatus;
        $trip->trip_status_date = $this->trip_status_date;
        $trip->trip_status_description = $this->trip_status_description;
        if ($this->selectedStatus == "Offloaded") {
        $trip->end_date = $this->offloaded_date;
        }
        $trip->ending_mileage = $this->ending_mileage;
        $trip->starting_mileage = $this->starting_mileage;
        $trip->update();

        if ($this->selectedStatus == "Offloaded") {
            app(\App\Services\Accounting\AdvanceInvoiceReclassService::class)->handleTripOffloaded($trip);
        }

        if (isset($trip->vehicle_id)) {
            $vehicle = Vehicle::find($trip->vehicle_id);
            $current_mileage = $vehicle->mileage;
            if($this->ending_mileage > $current_mileage){
                $vehicle->mileage = $this->ending_mileage;
            }
            $vehicle->update();

        }elseif(isset($trip->horse_id)){

            $horse = Horse::find($trip->horse_id);
            $current_mileage = $horse->mileage;
            if($this->ending_mileage > $current_mileage){
                $horse->mileage = $this->ending_mileage;
            }
            $horse->update();
        }

        $trip_status = new TripStatus;
        $trip_status->user_id = Auth::user()->id;
        $trip_status->trip_id = $trip->id;
        $trip_status->status = $this->selectedStatus;
        $trip_status->date = $this->trip_status_date;
        $trip_status->description = $this->trip_status_description;
        $trip_status->save();
        
        if ($this->selectedStatus == "Offloaded" || $this->selectedStatus == "Loaded") {

            $delivery_note = $trip->delivery_note;
            if (isset($delivery_note)) {
                $delivery_note->measurement = $this->measurement;
                $delivery_note->loaded_quantity = $this->loaded_quantity;
                $delivery_note->distance = $this->distance;
                $delivery_note->loaded_litreage = $this->loaded_litreage;
                $delivery_note->loaded_litreage_at_20 = $this->loaded_litreage_at_20;
                $delivery_note->loaded_rate = $this->loaded_rate;
                $delivery_note->loaded_weight = $this->loaded_weight;
                $delivery_note->loaded_freight = $this->loaded_freight;
                $delivery_note->transporter_loaded_rate = $this->transporter_loaded_rate;
                $delivery_note->transporter_loaded_freight = $this->transporter_loaded_freight;
                $delivery_note->loaded_date = $this->loaded_date;
                $delivery_note->offloaded_quantity = $this->offloaded_quantity;
                $delivery_note->offloaded_litreage = $this->offloaded_litreage;
                $delivery_note->offloaded_litreage_at_20 = $this->offloaded_litreage_at_20;
                $delivery_note->offloaded_weight = $this->offloaded_weight;
                $delivery_note->offloaded_rate = $this->offloaded_rate;
                $delivery_note->offloaded_freight = $this->offloaded_freight;
                $delivery_note->offloaded_distance = $this->offloaded_distance;
                $delivery_note->transporter_offloaded_rate = $this->transporter_offloaded_rate;
                $delivery_note->transporter_offloaded_freight = $this->transporter_offloaded_freight;
                $delivery_note->offloaded_date = $this->offloaded_date;
                $delivery_note->comments = $this->comments;
                $delivery_note->status = 1;
                $delivery_note->update();
            }else {
                $delivery_note = new DeliveryNote;
                $delivery_note->user_id = Auth::user()->id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->measurement = $this->measurement;
                $delivery_note->loaded_quantity = $this->loaded_quantity;
                $delivery_note->distance = $this->distance;
                $delivery_note->loaded_litreage = $this->loaded_litreage;
                $delivery_note->loaded_litreage_at_20 = $this->loaded_litreage_at_20;
                $delivery_note->loaded_rate = $this->loaded_rate;
                $delivery_note->loaded_weight = $this->loaded_weight;
                $delivery_note->loaded_freight = $this->loaded_freight;
                $delivery_note->transporter_loaded_rate = $this->transporter_loaded_rate;
                $delivery_note->transporter_loaded_freight = $this->transporter_loaded_freight;
                $delivery_note->loaded_date = $this->loaded_date;
                $delivery_note->offloaded_quantity = $this->offloaded_quantity;
                $delivery_note->offloaded_litreage = $this->offloaded_litreage;
                $delivery_note->offloaded_litreage_at_20 = $this->offloaded_litreage_at_20;
                $delivery_note->offloaded_weight = $this->offloaded_weight;
                $delivery_note->offloaded_rate = $this->offloaded_rate;
                $delivery_note->offloaded_freight = $this->offloaded_freight;
                $delivery_note->offloaded_distance = $this->offloaded_distance;
                $delivery_note->transporter_offloaded_rate = $this->transporter_offloaded_rate;
                $delivery_note->transporter_offloaded_freight = $this->transporter_offloaded_freight;
                $delivery_note->offloaded_date = $this->offloaded_date;
                $delivery_note->comments = $this->comments;
                $delivery_note->status = 1;
                $delivery_note->save();
            }



        
        if ($this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled" || $this->selectedStatus == "Scheduled") {
            
            $horse = Horse::withTrashed()->find($trip->horse_id);
            $vehicle = Vehicle::withTrashed()->find($trip->vehicle_id);
            if (isset($horse)) {
                $horse->status = 1;

                if ($this->selectedStatus == "Offloaded") {
                    if ($horse->mileage != NULL && $trip->distance != NULL) {
                  
                        if ($trip->breakdown_assignments->count() <= 0) {
                            if((is_numeric($horse->mileage) && $horse->mileage != "" && $horse->mileage != Null && $horse->mileage > 0) && (is_numeric($trip->distance) && $trip->distance != "" && $trip->distance != Null && $trip->distance > 0)){
                                $horse->mileage = $horse->mileage + $trip->distance; 
                            }
                          
                        }
                      
                    }
                    if ((isset($horse->fuel_balance) && $horse->fuel_balance > 0) && $trip->trip_fuel != NULL) {
                        if ($trip->breakdown_assignments->count() <= 0) {
                            if((is_numeric($horse->fuel_balance) && $horse->fuel_balance != "" && $horse->fuel_balance != Null && $horse->fuel_balance > 0) && (is_numeric($trip->trip_fuel) && $trip->trip_fuel != "" && $trip->trip_fuel != Null && $trip->trip_fuel > 0 )){
                                $horse->fuel_balance = $horse->fuel_balance - $trip->trip_fuel;
                            }
                           
                        }
                      
                    }
                }
          
                $horse->update();
            }

            if (isset($vehicle)) {
                $vehicle->status = 1;
                if ($this->selectedStatus == "Offloaded") {
                    if ($vehicle->mileage != NULL && $trip->distance != NULL) {
                        if ($trip->breakdown_assignments->count() <= 0) {

                            if((is_numeric($vehicle->mileage) && $vehicle->mileage >0 ) && (is_numeric($trip->distance) && $trip->distance >0 ) ){
                                $vehicle->mileage = $vehicle->mileage + $trip->distance; 
                            }
                           
                        }
                      
                    }
                    if ((isset($vehicle->fuel_balance) && $vehicle->fuel_balance > 0) && $trip->trip_fuel != NULL) {
                        if ($trip->breakdown_assignments->count() <= 0) {
                           
                            if((is_numeric($vehicle->fuel_balance) && $vehicle->fuel_balance >0 ) && (is_numeric($trip->trip_fuel) && $trip->trip_fuel >0 ) ){
                                $vehicle->fuel_balance = $vehicle->fuel_balance - $trip->trip_fuel;
                            }
                          
                        }
                      
                    }
                }
          
                $vehicle->update();
            }
 

            $driver = Driver::withTrashed()->find($trip->driver_id);
            if (isset($driver)) {
                $driver->status = 1;
                $driver->update();
            }
           
            if ($trip->trailers->count()>0) {
                foreach ($trip->trailers as $trailer) {
                    $trailer = Trailer::withTrashed()->find($trailer->id);
                    $trailer->status = 1;
                    $trailer->update();
                }
            }

            $breakdown_assignments = $trip->breakdown_assignments;
            if ($breakdown_assignments->count()>0) {
            
            foreach ($breakdown_assignments as $breakdown_assignment) {
                $horse = Horse::withTrashed()->find($breakdown_assignment->horse_id);
                $horse->status = 1;
                $horse->update();
    
                $driver = Driver::withTrashed()->find($breakdown_assignment->driver_id);
                $driver->status = 1;
                $driver->update();
    
                if ($breakdown_assignment->trailers->count()>0) {
                    foreach ($trip->trailers as $trailer) {
                        $trailer = Trailer::withTrashed()->find($trailer->id);
                        $trailer->status = 1;
                        $trailer->update();
                    }
                }
            }
                # code...
            }
        }

        
        if (isset(Auth::user()->company)) {
            $this->company = Auth::user()->company;
            }elseif (isset(Auth::user()->employee->company)) {
                $this->company = Auth::user()->employee->company;
            }
          
            if ($this->customer_updates == TRUE) {
                $this->customer_email = $trip->customer ? $trip->customer->email : "";
                if (isset($this->customer_email) && $this->customer_email != "") {
                Mail::to($this->customer_email)->send(new TripUpdatesMail($this->trip, $this->company));
                    }
                }

      }

      $this->resetInputFields();
      $this->dispatchBrowserEvent('hide-statusModal');
      $this->dispatchBrowserEvent('alert',[
          'type'=>'success',
          'message'=>"Trip Status Updated Successfully!!"
      ]);
    //   return redirect(request()->header('Referer'));

    }

}