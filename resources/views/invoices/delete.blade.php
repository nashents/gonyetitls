<div data-backdrop="static" data-keyboard="false" class="modal fade" id="invoiceDeleteModal{{ $invoice->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to delete this Invoice</strong> </center>
               @if ($invoice->payments->isNotEmpty())
               <center>
                   <small>
                       This invoice has existing payments totalling {{ number_format($invoice->payments->sum('amount'), 2) }}.
                       Deleting it will reverse those transactions and restore the affected account/wallet balances.
                   </small>
               </center>
               @endif
               @if ($invoice->bills->isNotEmpty())
               <center>
                   <small>{{ $invoice->bills->count() }} bill(s) raised against this invoice will also be deleted and reversed.</small>
               </center>
               @endif
            </div>
            <form action="{{route('invoices.destroy', $invoice->id)}}" method="POST" >
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="DELETE">
            <div class="modal-footer no-border">
                <div class="btn-group" role="group">
                    <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    <button onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; " class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                </div>
                <!-- /.btn-group -->
            </div>
        </form>
        </div>
    </div>
</div>
