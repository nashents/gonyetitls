<div>
    <div id="print-area">
        <div id="invoice">
            <x-loading/>
            @include('invoices.partials.toolbar')

            {{-- Same document partial as Export as PDF, so print / PDF / screen match. --}}
            @include('invoices.layouts.transport', ['fiscalDocument' => $this->fiscalDocumentRecord, 'pdf' => false])
        </div>
    </div>
</div>
