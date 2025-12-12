<?php

namespace App\Http\Livewire\Transfers;

use Carbon\Carbon;
use App\Models\Tyre;
use Livewire\Component;
use App\Models\Transfer;
use App\Models\Inventory;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\SendtransferOrderUpdateMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;

class Pending extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    private $transfers;
    public $transfer_filter;
    public $transfer;
    public $transfer_id;
    public $department;
    public $authorize;
    public $comments;

        public function authorize($id){
        $transfer = Transfer::find($id);
        $this->transfer_id = $transfer->id;
        $this->transfer = $transfer;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

    

      public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

      DB::transaction(function () {


        $transfer = Transfer::find($this->transfer_id);
        if ($transfer->authorization == "approved") {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Transfer Order Approved Already"
            ]);
        }else {
            
            $transfer->authorized_by_id = Auth::user()->id;
            $transfer->authorization = $this->authorize;
            $transfer->authorization_comments = $this->comments;
            $transfer->authorization_date = date('Y-m-d');
            $transfer->update();
            
        if ($this->authorize == "approved") {

            $transfer_items = $transfer->transfer_items;
            foreach ($transfer_items as $transfer_item) {
                if ($this->department == "inventory") {
                    $inventory = Inventory::find($transfer_item->inventory_id);
                    $inventory->balance -= $transfer_item->qty;
                    if ($inventory->balance <= 0) {
                        $inventory->status = 0;
                    }
                    $inventory->save();
                }elseif ($this->department == "tyre") {
                    $tyre = Tyre::find($transfer_item->tyre_id);
                    $tyre->balance -= $transfer_item->qty;
                    if ($tyre->balance <= 0) {
                        $tyre->status = 0;
                    }
                    $tyre->save();
                }
            }

            $this->dispatchBrowserEvent('hide-transferAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transfer Order Approved Successfully"
            ]);
            if ($this->department == "tyre") {
                return redirect()->route('tyre_transfers.approved');
            }elseif($this->department == "inventory"){
                return redirect()->route('inventory_transfers.approved');
            }
           
        }else {
            $this->dispatchBrowserEvent('hide-transferAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transfer Order Approved Successfully"
            ]);
            if ($this->department == "tyre") {
                return redirect()->route('tyre_transfers.rejected');
            }elseif($this->department == "inventory"){
                return redirect()->route('inventory_transfers.rejected');
            }
          
        }
        }
        });
    }

    public function render()
    {
            $from   = filled($this->from) ? Carbon::parse($this->from)->startOfDay() : null;
            $to     = filled($this->to)   ? Carbon::parse($this->to)->endOfDay()   : null;
            $search = trim((string) ($this->search ?? ''));

            $query = Transfer::query()
                ->with([
                    'inventory.product.brand',
                    'tyre.product.brand',
                ])
                ->where('authorization','pending')
                ->when($from && $to, fn (Builder $q) => $q->whereBetween('created_at', [$from, $to]))
                ->when(!($from && $to), fn (Builder $q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
                ->when($search !== '', function (Builder $q) use ($search) {
                    // IMPORTANT: group OR conditions so they don't break your date filters
                    $q->where(function (Builder $qq) use ($search) {
                        $like = "%{$search}%";

                        $qq->where('date', 'like', $like)
                        ->orWhere('comments', 'like', $like)

                        ->orWhereHas('inventory', function (Builder $inv) use ($like) {
                            $inv->where(function (Builder $i) use ($like) {
                                $i->where('inventory_number', 'like', $like)
                                    ->orWhere('serial_number', 'like', $like);
                            });
                        })

                        ->orWhereHas('inventory.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('inventory.product.brand', fn (Builder $b) => $b->where('name', 'like', $like))

                        ->orWhereHas('tyre', function (Builder $t) use ($like) {
                            $t->where(function (Builder $tt) use ($like) {
                                $tt->where('tyre_number', 'like', $like)
                                    ->orWhere('width', 'like', $like)
                                    ->orWhere('diameter', 'like', $like)
                                    ->orWhere('aspect_ratio', 'like', $like)
                                    ->orWhere('serial_number', 'like', $like);
                            });
                        })

                        ->orWhereHas('tyre.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('tyre.product.brand', fn (Builder $b) => $b->where('name', 'like', $like));
                    });
                })
                ->orderByDesc('created_at');

            return view('livewire.transfers.pending', [
                'transfers' => $query->paginate(10),
            ]);
    }
}
