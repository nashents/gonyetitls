<?php

namespace App\Http\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\FiscalDocument;
use App\Services\FiscalHarmonyService;
use Livewire\Component;
use Barryvdh\DomPDF\PDF;
use App\Mail\SendingInvoiceMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Preview extends Component
{
    public $invoice;
    public $invoice_items;
    public $company;
    public $email;

    // ── Fiscal UI state (loaded from fiscal_documents, never stored on invoices) ──
    public bool    $fiscalLoading     = false;
    public string  $fiscalMessage     = '';
    public string  $fiscalMessageType = '';      // success | error | warning
    public string  $fiscalStatus      = 'not_fiscalised';
    public ?string $fiscalRequestId   = null;
    public ?string $fiscalPdfFile     = null;
    public ?string $fiscalQrUrl       = null;
    public ?string $fiscalVerifyCode  = null;
    public ?string $fiscalDay         = null;
    public ?string $fiscalRaInvoiceNo = null;
    public ?string $fiscalError       = null;
    public bool    $fiscalActionable  = false;

    public function mount($invoice, $invoice_items, $company)
    {
        $this->invoice       = $invoice;
        $this->invoice_items = $invoice_items;
        $this->company       = $company;
        $this->loadFiscalState();
    }

    // ─────────────────────────────────────────────────────────────
    // Load state from fiscal_documents table
    // ─────────────────────────────────────────────────────────────

    protected function loadFiscalState(): void
    {
        $doc = FiscalDocument::forInvoice($this->invoice->id)->latest()->first();

        if (! $doc) {
            $this->fiscalStatus = 'not_fiscalised';
            return;
        }

        $this->fiscalStatus      = $doc->statusLabel();
        $this->fiscalRequestId   = $doc->request_id;
        $this->fiscalPdfFile     = $doc->fiscal_invoice_pdf;
        $this->fiscalQrUrl       = $doc->qr_code_url;
        $this->fiscalVerifyCode  = $doc->verification_code;
        $this->fiscalDay         = $doc->fiscal_day;
        $this->fiscalRaInvoiceNo = $doc->ra_invoice_number;
        $this->fiscalError       = $doc->error_message;
        $this->fiscalActionable  = (bool) $doc->is_actionable;
    }

    // ─────────────────────────────────────────────────────────────
    // FISCALIZE INVOICE
    // ─────────────────────────────────────────────────────────────

    public function fiscalizeInvoice($id)
    {
        $this->fiscalLoading    = true;
        $this->fiscalMessage    = '';
        $this->fiscalError      = null;
        $this->fiscalActionable = false;

        try {
            // 1. Load invoice fresh with all needed relationships
            $invoice = Invoice::with(['invoice_items.product', 'customer', 'currency'])
                ->findOrFail($id);

            // 2. Guard: already approved?
            $existing = FiscalDocument::forInvoice($id)->latest()->first();
            if ($existing?->isApproved()) {
                $this->setFiscalMessage('This invoice has already been fiscalised.', 'warning');
                return;
            }

            // 3. New external UUID for this attempt
            $externalId = (string) Str::uuid();

            // 4. Build payload
            $payload = $this->buildFiscalPayload($invoice, $externalId);

            // 5. Upsert fiscal_documents row as "pending"
            $fiscalDoc = FiscalDocument::updateOrCreate(
                ['document_type' => 'invoice', 'source_id' => $invoice->id],
                [
                    'external_document_id' => $externalId,
                    'document_number'      => $invoice->invoice_number,
                    'request_id'           => null,
                    'success'              => null,
                    'error_message'        => null,
                    'request_payload'      => $payload,
                    'fiscalized_at'        => null,
                ]
            );

            // 6. Submit to Fiscal Harmony
            $fiscal = app(FiscalHarmonyService::class);
            $result = $fiscal->submitInvoice($payload);
            \Log::debug('Fiscal Harmony raw result', $result);

            if (! $result['success']) {
                $err = $result['error'] ?? ('HTTP ' . $result['status']);
                $fiscalDoc->update(['error_message' => $err, 'success' => false]);
                $this->setFiscalMessage('Submission failed: ' . $err, 'error');
                return;
            }

            // 7. Save request ID
            $requestId = is_string($result['data'])
                ? trim($result['data'], '"')
                : ($result['data'] ?? null);

            $fiscalDoc->update([
                'request_id'       => $requestId,
                'response_payload' => $result['data'],
            ]);

            $this->fiscalRequestId = $requestId;
            $this->setFiscalMessage('Submitted to Fiscal Harmony. Checking status...', 'warning');

            // 8. Immediately poll for result
            if ($requestId) {
                $this->checkFiscalStatus($fiscal, $fiscalDoc);
            }

        } catch (\Exception $e) {
            $this->setFiscalMessage('Error: ' . $e->getMessage(), 'error');
        } finally {
            $this->fiscalLoading = false;
            $this->loadFiscalState();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // POLL STATUS  (wire:click="pollFiscalStatus")
    // ─────────────────────────────────────────────────────────────

    public function pollFiscalStatus()
    {
        if (! $this->fiscalRequestId) {
            $this->setFiscalMessage('No request ID found. Please fiscalise first.', 'error');
            return;
        }

        $fiscal    = app(FiscalHarmonyService::class);
        $fiscalDoc = FiscalDocument::where('request_id', $this->fiscalRequestId)->first();

        if (! $fiscalDoc) {
            $this->setFiscalMessage('Fiscal record not found.', 'error');
            return;
        }

        $this->checkFiscalStatus($fiscal, $fiscalDoc);
        $this->loadFiscalState();
    }

    protected function checkFiscalStatus(FiscalHarmonyService $fiscal, FiscalDocument $fiscalDoc): void
    {
        $result = $fiscal->checkStatus([$this->fiscalRequestId]);

        if (! $result['success'] || empty($result['data'])) {
            $this->setFiscalMessage('Could not retrieve status. Try again shortly.', 'warning');
            return;
        }

        $item = collect($result['data'])->firstWhere('requestId', $this->fiscalRequestId)
            ?? collect($result['data'])->first();

        if (! $item) {
            $this->setFiscalMessage('Empty status response.', 'warning');
            return;
        }

        $success    = $item['success']          ?? false;
        $qrData     = $item['qrData']           ?? [];
        $pdfFile    = $item['fiscalInvoicePdf'] ?? null;
        $error      = $item['error']            ?? null;
        $actionable = $item['isActionable']     ?? false;

        if ($success) {
            $fiscalDoc->update([
                'success'            => true,
                'is_actionable'      => null,
                'error_message'      => null,
                'fiscal_invoice_pdf' => $pdfFile,
                'qr_code_url'        => $qrData['qrCodeUrl']        ?? null,
                'verification_code'  => $qrData['verificationCode'] ?? null,
                'fiscal_day'         => $qrData['fiscalDay']        ?? null,
                'device_id'          => $qrData['deviceId']         ?? null,
                'ra_invoice_number'  => $qrData['invoiceNumber']    ?? null,
                'response_payload'   => $item,
                'fiscalized_at'      => now(),
            ]);

            // Flip the invoice fiscalize flag so heading shows "FISCAL TAX INVOICE"
            Invoice::where('id', $fiscalDoc->source_id)->update(['fiscalize' => true]);
            $this->invoice = Invoice::find($this->invoice->id);

            $this->setFiscalMessage('Invoice fiscalised successfully!', 'success');
        } else {
            $fiscalDoc->update([
                'success'          => false,
                'is_actionable'    => $actionable,
                'error_message'    => $error,
                'response_payload' => $item,
            ]);

            $msg  = $error ?? 'Unknown fiscal error.';
            $msg .= $actionable ? ' — Please correct and retry.' : ' — Non-actionable error.';
            $this->setFiscalMessage($msg, 'error');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // DOWNLOAD FISCAL PDF
    // ─────────────────────────────────────────────────────────────

    public function downloadFiscalPdf()
    {
        if (! $this->fiscalPdfFile) {
            $this->setFiscalMessage('No fiscal PDF available yet.', 'error');
            return;
        }

        $fiscal = app(FiscalHarmonyService::class);
        $bytes  = $fiscal->downloadPdf($this->fiscalPdfFile);

        if (! $bytes) {
            $this->setFiscalMessage('PDF download failed.', 'error');
            return;
        }

        return response()->streamDownload(
            fn () => print($bytes),
            $this->fiscalPdfFile,
            ['Content-Type' => 'application/pdf']
        );
    }

    // ─────────────────────────────────────────────────────────────
    // PAYLOAD BUILDER
    // ─────────────────────────────────────────────────────────────

    protected function buildFiscalPayload(Invoice $invoice, string $externalId): array
    {
        return [
            'invoiceId'      => $externalId,
            'invoiceNumber'  => $invoice->invoice_number,
            'reference'      => $invoice->sales_order_number
                                ?? $invoice->purchase_order_number
                                ?? null,
            'isTaxInclusive' => false,
            'date'           => \Carbon\Carbon::parse($invoice->date)
                                    ->setTimezone('Africa/Harare')
                                    ->toIso8601String(),
            'currencyCode'   => strtoupper($invoice->currency?->name ?? 'USD'),
            'paymentMethod'  => $this->mapPaymentMethod($invoice->payment_method ?? 'Cash'),

            'buyer' => [
                'name'      => $invoice->customer?->name ?? 'Walk-in Customer',
                'tin'       => $invoice->customer?->tin_number  ?? null,
                'vatNumber' => $invoice->customer?->vat_number  ?? null,
                'phone'     => $invoice->customer?->phonenumber ?? null,
                'email'     => $invoice->customer?->email       ?? null,
                'address'   => [
                    'houseNo'  => $invoice->customer?->street_address ?? '0',
                    'street'   => trim(
                        ($invoice->customer?->street_address ?? '') . ' ' .
                        ($invoice->customer?->suburb ?? '')
                    ),
                    'city'     => $invoice->customer?->city    ?? 'Harare',
                    'province' => $invoice->customer?->country ?? 'Harare',
                ],
            ],

            'lineItems' => $invoice->invoice_items->map(function ($item) {
                $tax    = \App\Models\Account::find($item->tax_id);
                $hsCode = $tax?->hs_code ?? '00000000';

                return [
                    'description' => $item->description ?? ($item->product?->name ?? 'Service'),
                    'taxCode'     => $item->tax_id ? (string) $item->tax_id : 'EXEMPT',
                    'productCode' => $hsCode,
                    'unitAmount'  => (float) ($item->amount   ?? 0),
                    'quantity'    => (float) ($item->qty      ?? 1),
                    'lineAmount'  => (float) ($item->subtotal ?? 0),
                ];
            })->toArray(),

            'subTotal' => (float) $invoice->invoice_items->sum('subtotal'),
            'totalTax' => (float) ($invoice->tax_amount ?? 0),
            'total'    => (float) ($invoice->total      ?? 0),
        ];
    }

    protected function mapPaymentMethod(string $method): string
    {
        return match (strtolower($method)) {
            'card', 'credit_card', 'debit_card' => 'Card',
            'mobile', 'ecocash', 'innbucks'      => 'MobileWallet',
            'bank', 'bank_transfer', 'eft'        => 'BankTransfer',
            'credit'                              => 'Credit',
            'coupon'                              => 'Coupon',
            default                               => 'Cash',
        };
    }

    protected function setFiscalMessage(string $msg, string $type): void
    {
        $this->fiscalMessage     = $msg;
        $this->fiscalMessageType = $type;
    }

    // ─────────────────────────────────────────────────────────────
    // EXISTING METHODS (unchanged)
    // ─────────────────────────────────────────────────────────────

    public function sendEmail($id)
    {
        $invoice       = Invoice::find($id);
        $invoice_items = $invoice->invoice_items;
        $company       = $invoice->company;
        $this->email   = $invoice->customer?->email ?? '';

        if ($this->email !== '') {
            try {
                Mail::to($this->email)->send(new SendingInvoiceMail($invoice, $invoice_items, $company));
            } catch (\Exception $e) {
                $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Something went wrong while sending email!!']);
            }
        }

        return redirect()->back();
    }

    public function print($id)
    {
        return view('invoices.print')->with([
            'invoice'       => $this->invoice,
            'company'       => $this->company,
            'invoice_items' => $this->invoice_items,
        ]);
    }

    public function generatePdf($id)
    {
        $pdf = PDF::loadView('invoices.invoice', [
            'invoice'       => $this->invoice,
            'company'       => $this->company,
            'invoice_items' => $this->invoice_items,
        ]);
        return $pdf->download('invoice.pdf');
    }

    public function render()
    {
        return view('livewire.invoices.preview');
    }
}
