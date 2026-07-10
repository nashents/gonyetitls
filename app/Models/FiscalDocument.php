<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class FiscalDocument extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'document_type', 'source_id', 'external_document_id', 'document_number',
        'request_id', 'success', 'is_actionable', 'error_message',
        'fiscal_invoice_pdf', 'verification_code', 'qr_code_url',
        'fiscal_day', 'device_id', 'ra_invoice_number',
        'request_payload', 'response_payload', 'fiscalized_at',
    ];

    protected $casts = [
        'success'          => 'boolean',
        'is_actionable'    => 'boolean',
        'request_payload'  => 'array',
        'response_payload' => 'array',
        'fiscalized_at'    => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'source_id');
    }

    public function scopeForInvoice($query, int $invoiceId)
    {
        return $query->where('document_type', 'invoice')->where('source_id', $invoiceId);
    }

    public function scopeForCreditNote($query, int $creditNoteId)
    {
        return $query->where('document_type', 'credit_note')->where('source_id', $creditNoteId);
    }

    public function isPending(): bool  { return !is_null($this->request_id) && is_null($this->success); }
    public function isApproved(): bool { return $this->success === true; }
    public function isFailed(): bool   { return $this->success === false; }

    public function statusLabel(): string
    {
        if ($this->isApproved()) return 'approved';
        if ($this->isFailed())   return 'failed';
        if ($this->isPending())  return 'pending';
        return 'not_fiscalised';
    }
}
