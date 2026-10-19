{{--
    Invoice preview toolbar (screen only — never printed or exported).
    Shared by every invoice layout's Livewire view; uses the HasFiscalDocument
    state ($fiscalStatus, $fiscalPdfFile, $fiscalMessage…) of the component.
--}}
<div class="toolbar hidden-print">
    <div class="text-end">
        <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded">
            <i class="fa fa-arrow-left" style="color:black"></i> Back
        </button>

        @if ($fiscalStatus === 'approved')
            <span class="btn btn-success btn-wide btn-rounded" style="cursor:default">
                <i class="fa fa-check-circle"></i> Fiscalised
            </span>
        @elseif ($fiscalStatus === 'pending')
            <button type="button" wire:click="pollFiscalStatus" wire:loading.attr="disabled" wire:target="pollFiscalStatus"
                    class="btn btn-warning btn-wide btn-rounded">
                <span wire:loading.remove wire:target="pollFiscalStatus"><i class="fa fa-refresh"></i> Check Fiscal Status</span>
                <span wire:loading wire:target="pollFiscalStatus"><span class="spinner-border spinner-border-sm"></span> Checking...</span>
            </button>
        @else
            <button type="button" wire:click="fiscalizeInvoice({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="fiscalizeInvoice"
                    class="btn {{ $fiscalStatus === 'failed' ? 'btn-danger' : 'btn-default border-primary' }} btn-wide btn-rounded">
                <span wire:loading.remove wire:target="fiscalizeInvoice">
                    @if ($fiscalStatus === 'failed')
                        <i class="fa fa-refresh"></i> Retry Fiscalize
                    @else
                        <i class="fa fa-receipt" style="color:red"></i> Fiscalize Invoice
                    @endif
                </span>
                <span wire:loading wire:target="fiscalizeInvoice"><span class="spinner-border spinner-border-sm" role="status"></span> Fiscalizing...</span>
            </button>
        @endif

        <a href="javascript:void(0)" onclick="printSection()" class="btn btn-default border-primary btn-wide btn-rounded">
            <i class="fa fa-print" style="color: black"></i> Print
        </a>

        <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn btn-default border-primary btn-wide btn-rounded">
            <i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF
        </a>

        {{-- The fiscal provider's own copy, in its layout (our layouts above already carry the fiscal data). --}}
        @if ($fiscalStatus === 'approved' && $fiscalPdfFile)
            <button type="button" wire:click="downloadFiscalPdf" class="btn btn-default border-success btn-wide btn-rounded"
                    title="The fiscal provider's own copy of this invoice, in its standard layout">
                <i class="fa fa-file-pdf-o" style="color:green"></i> Provider Fiscal Copy
            </button>
        @endif
    </div>

    @if ($fiscalMessage)
        <div class="alert mt-2 mb-0 py-2 {{ $fiscalMessageType === 'success' ? 'alert-success' : ($fiscalMessageType === 'error' ? 'alert-danger' : 'alert-warning') }}" role="alert">
            @if ($fiscalMessageType === 'success') <i class="fa fa-check-circle"></i>
            @elseif ($fiscalMessageType === 'error') <i class="fa fa-times-circle"></i>
            @else <i class="fa fa-clock-o"></i>
            @endif
            {{ $fiscalMessage }}
        </div>
    @elseif ($fiscalStatus === 'failed' && $fiscalError)
        <div class="alert mt-2 mb-0 py-2 alert-danger" role="alert">
            <i class="fa fa-times-circle"></i> Fiscalisation failed: {{ $fiscalError }}
        </div>
    @endif

    <hr>
</div>
