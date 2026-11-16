<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">

            {{-- Clients --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <a href="#" class="btn btn-default"
                                   onclick="confirm('Run billing now? Due invoices are raised and sent, and reminders go out to clients with unpaid invoices.') || event.stopImmediatePropagation()"
                                   wire:click.prevent="runBilling" wire:loading.attr="disabled"><i class="fa fa-refresh"></i> Run Billing Now</a>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <p class="text-muted">
                                Billing runs by itself every day from {{ (int) config('services.subscription_billing.from_hour', 8) }}:00:
                                each client is invoiced on its invoice day, the invoice is sent to its billing contacts, and unpaid invoices are chased at the client's reminder interval.
                            </p>
                            @if (! $whatsappReady)
                                <div class="alert alert-warning">
                                    WhatsApp sending is switched off: set <code>WHATSAPP_TOKEN</code> and <code>WHATSAPP_PHONE_NUMBER_ID</code> in the environment file. Invoices and reminders go out by email only until then.
                                </div>
                            @endif
                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="th-sm">Client</th>
                                            <th class="th-sm">Package</th>
                                            <th class="th-sm" style="text-align:right;">Monthly Fee</th>
                                            <th class="th-sm">Auto Billing</th>
                                            <th class="th-sm">Invoice Day</th>
                                            <th class="th-sm">Next Invoice</th>
                                            <th class="th-sm" style="text-align:right;">Outstanding</th>
                                            <th class="th-sm" style="text-align:right;">Overdue</th>
                                            <th class="th-sm">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($companies as $company)
                                            @php $symbol = ($company->license_currency ?? $company->currency)?->symbol; @endphp
                                            <tr wire:key="billing-company-{{ $company->id }}">
                                                <td>
                                                    {{ $company->name }}
                                                    @if ($company->status != 1) <span class="badge bg-danger">Suspended</span> @endif
                                                </td>
                                                <td>{{ $company->plan_label }}</td>
                                                <td style="text-align:right;">{{ $symbol }}{{ number_format((float) $company->fee, 2) }}</td>
                                                <td><span class="badge bg-{{ $company->billing_enabled ? 'success' : 'secondary' }}">{{ $company->billing_enabled ? 'On' : 'Off' }}</span></td>
                                                <td>{{ $company->billing_enabled ? $company->billing_day : '-' }}</td>
                                                <td>{{ ($company->status == 1 && $nextDates[$company->id]) ? $nextDates[$company->id]->format('d M Y') : '-' }}</td>
                                                <td style="text-align:right;">{{ $symbol }}{{ number_format((float) $company->outstanding_balance, 2) }}</td>
                                                <td style="text-align:right; {{ $company->overdue_balance > 0 ? 'color:#d9534f;' : '' }}">{{ $symbol }}{{ number_format((float) $company->overdue_balance, 2) }}</td>
                                                <td><a href="{{ route('billing.company', $company->id) }}" class="btn btn-default btn-sm"><i class="fa fa-cog"></i> Manage</a></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="9" class="text-center text-muted">No client companies yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment instructions printed on invoices --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5><i class="fa fa-bank"></i> Payment Details on Invoices</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <form wire:submit.prevent="savePaymentDetails">
                                <div class="form-group">
                                    <label for="payment_details">How clients should pay</label>
                                    <textarea id="payment_details" wire:model.defer="payment_details" class="form-control" rows="4" placeholder="Bank, account name, account number, branch, EcoCash merchant code..."></textarea>
                                    <small class="form-text text-muted">Printed under "Payment Details" on every unpaid invoice.</small>
                                    @error('payment_details') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- All invoices --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5><i class="fa fa-file-text-o"></i> Invoices</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <div class="form-inline" style="margin-bottom:15px;">
                                <select wire:model="company_filter" class="form-control" style="margin-right:8px;">
                                    <option value="">All clients</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                <select wire:model="status" class="form-control">
                                    <option value="">All statuses</option>
                                    <option value="outstanding">Outstanding</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="paid">Paid</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="th-sm">Invoice #</th>
                                            <th class="th-sm">Client</th>
                                            <th class="th-sm">Date</th>
                                            <th class="th-sm">Due</th>
                                            <th class="th-sm" style="text-align:right;">Amount</th>
                                            <th class="th-sm" style="text-align:right;">Paid</th>
                                            <th class="th-sm" style="text-align:right;">Balance</th>
                                            <th class="th-sm">Status</th>
                                            <th class="th-sm">Sent</th>
                                            <th class="th-sm">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($invoices as $invoice)
                                            <tr wire:key="billing-all-invoice-{{ $invoice->id }}">
                                                <td>{{ $invoice->invoice_number }}</td>
                                                <td>{{ $invoice->company?->name }}</td>
                                                <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                                                <td>{{ $invoice->due_date->format('d M Y') }}</td>
                                                <td style="text-align:right;">{{ $invoice->money($invoice->amount) }}</td>
                                                <td style="text-align:right;">{{ $invoice->money($invoice->amount_paid) }}</td>
                                                <td style="text-align:right;">{{ $invoice->money($invoice->balance) }}</td>
                                                <td><span class="badge bg-{{ $invoice->status_badge }}">{{ $invoice->status_label }}</span></td>
                                                <td>
                                                    @if ($invoice->sent_at)
                                                        {{ $invoice->sent_at->format('d M Y') }}
                                                        @if ($invoice->reminder_count) <small class="text-muted">+{{ $invoice->reminder_count }} reminder(s)</small> @endif
                                                    @else
                                                        <span class="text-muted">Not sent</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('billing.invoices.pdf', $invoice->id) }}" target="_blank" class="btn btn-default btn-sm" title="View PDF"><i class="fa fa-file-pdf-o"></i></a>
                                                    <a href="{{ route('billing.company', $invoice->company_id) }}" class="btn btn-default btn-sm" title="Manage client"><i class="fa fa-cog"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="10" class="text-center text-muted">No invoices found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $invoices->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
