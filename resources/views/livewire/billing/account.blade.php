<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">

            {{-- Package summary --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5><i class="fa fa-cube"></i> {{ $company->name }} - Gonyeti TLS Subscription</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <div class="row">
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <small class="text-muted">Package</small>
                                    <h5 style="margin-top:4px;">{{ $company->plan_label }}</h5>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <small class="text-muted">Monthly charge</small>
                                    <h5 style="margin-top:4px;">{{ $currency?->symbol }}{{ number_format((float) $company->fee, 2) }} {{ $currency?->name }}</h5>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <small class="text-muted">Invoiced on</small>
                                    <h5 style="margin-top:4px;">
                                        @if ($company->billing_enabled)
                                            Day {{ $company->billing_day }} of each month
                                        @else
                                            <span class="text-muted">Not scheduled</span>
                                        @endif
                                    </h5>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <small class="text-muted">Next invoice</small>
                                    <h5 style="margin-top:4px;">{{ $nextInvoiceDate ? $nextInvoiceDate->format('d M Y') : '-' }}</h5>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <small class="text-muted">Outstanding</small>
                                    <h5 style="margin-top:4px;">{{ $currency?->symbol }}{{ number_format($outstanding, 2) }}</h5>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <small class="text-muted">Overdue</small>
                                    <h5 style="margin-top:4px; {{ $overdue > 0 ? 'color:#d9534f;' : '' }}">{{ $currency?->symbol }}{{ number_format($overdue, 2) }}</h5>
                                </div>
                            </div>
                            @if (! $canManage)
                                <p class="text-muted" style="margin:16px 0 0 0;">
                                    Payment terms: {{ $company->billing_due_days }} day(s) from the invoice date.
                                    To change your package or billing contacts, please contact Gonyeti TLS support.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invoices --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5><i class="fa fa-file-text-o"></i> Invoices</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            @if ($canManage)
                                <form wire:submit.prevent="generateInvoice" class="form-inline" style="margin-bottom:15px;">
                                    <label for="invoice_month">Raise invoice for</label>
                                    <input type="month" id="invoice_month" wire:model.defer="invoice_month" class="form-control" style="margin:0 8px;" required>
                                    <button type="submit" class="btn btn-default" wire:loading.attr="disabled"><i class="fa fa-plus-square-o"></i> Raise Invoice</button>
                                    @error('invoice_month') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @error('invoice') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </form>
                            @endif

                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="th-sm">Invoice #</th>
                                            <th class="th-sm">Date</th>
                                            <th class="th-sm">Period</th>
                                            <th class="th-sm">Due</th>
                                            <th class="th-sm" style="text-align:right;">Amount</th>
                                            <th class="th-sm" style="text-align:right;">Paid</th>
                                            <th class="th-sm" style="text-align:right;">Balance</th>
                                            <th class="th-sm">Status</th>
                                            @if ($canManage)
                                                <th class="th-sm">Sent</th>
                                            @endif
                                            <th class="th-sm">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($invoices as $invoice)
                                            <tr wire:key="billing-invoice-{{ $invoice->id }}">
                                                <td>{{ $invoice->invoice_number }}</td>
                                                <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                                                <td>{{ $invoice->period_start->format('d M Y') }} - {{ $invoice->period_end->format('d M Y') }}</td>
                                                <td>{{ $invoice->due_date->format('d M Y') }}</td>
                                                <td style="text-align:right;">{{ $invoice->money($invoice->amount) }}</td>
                                                <td style="text-align:right;">{{ $invoice->money($invoice->amount_paid) }}</td>
                                                <td style="text-align:right;">{{ $invoice->money($invoice->balance) }}</td>
                                                <td><span class="badge bg-{{ $invoice->status_badge }}">{{ $invoice->status_label }}</span></td>
                                                @if ($canManage)
                                                    <td>
                                                        @if ($invoice->sent_at)
                                                            {{ $invoice->sent_at->format('d M Y H:i') }}
                                                            @if ($invoice->reminder_count)
                                                                <small class="d-block text-muted">{{ $invoice->reminder_count }} reminder(s), last {{ $invoice->last_reminder_at?->format('d M Y') }}</small>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">Not sent</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="w-10 line-height-35 table-dropdown">
                                                    @if ($canManage)
                                                        <div class="dropdown">
                                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa fa-bars"></i>
                                                                <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-right">
                                                                <li><a href="{{ route('billing.invoices.pdf', $invoice->id) }}" target="_blank"><i class="fa fa-file-pdf-o color-default"></i> View PDF</a></li>
                                                                @if ($invoice->isOutstanding())
                                                                    <li><a href="#" wire:click.prevent="openPayment({{ $invoice->id }})"><i class="fa fa-money color-success"></i> Record Payment</a></li>
                                                                    <li><a href="#" wire:click.prevent="sendInvoice({{ $invoice->id }}, 'invoice')"><i class="fa fa-paper-plane color-default"></i> {{ $invoice->sent_at ? 'Resend Invoice' : 'Send Invoice' }}</a></li>
                                                                    <li><a href="#" wire:click.prevent="sendInvoice({{ $invoice->id }}, 'reminder')"><i class="fa fa-bell color-default"></i> Send Reminder</a></li>
                                                                    @if ($invoice->amount_paid <= 0)
                                                                        <li><a href="#" onclick="confirm('Cancel invoice {{ $invoice->invoice_number }}? This month will not be invoiced again.') || event.stopImmediatePropagation()" wire:click.prevent="cancelInvoice({{ $invoice->id }})"><i class="fa fa-ban color-danger"></i> Cancel Invoice</a></li>
                                                                    @endif
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <a href="{{ route('billing.invoices.pdf', $invoice->id) }}" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-file-pdf-o"></i> PDF</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $canManage ? 10 : 9 }}" class="text-center text-muted">No invoices have been raised yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payments --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5><i class="fa fa-money"></i> Payments</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="th-sm">Date</th>
                                            <th class="th-sm">Invoice #</th>
                                            <th class="th-sm">Method</th>
                                            <th class="th-sm">Reference</th>
                                            <th class="th-sm" style="text-align:right;">Amount</th>
                                            @if ($canManage)
                                                <th class="th-sm">Notes</th>
                                                <th class="th-sm">Recorded by</th>
                                            @endif
                                            <th class="th-sm">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payments as $payment)
                                            <tr wire:key="billing-payment-{{ $payment->id }}">
                                                <td>{{ $payment->date->format('d M Y') }}</td>
                                                <td>{{ $payment->invoice?->invoice_number }}</td>
                                                <td>{{ $payment->method }}</td>
                                                <td>{{ $payment->reference }}</td>
                                                <td style="text-align:right;">{{ $payment->invoice ? $payment->invoice->money($payment->amount) : number_format($payment->amount, 2) }}</td>
                                                @if ($canManage)
                                                    <td>{{ $payment->notes }}</td>
                                                    <td>{{ $payment->recorder?->name }}</td>
                                                @endif
                                                <td style="white-space:nowrap;">
                                                    @if ($payment->invoice)
                                                        <a href="{{ route('billing.payments.receipt', $payment->id) }}" class="btn btn-default btn-sm" title="Download receipt"><i class="fa fa-download"></i> Receipt</a>
                                                    @endif
                                                    @if ($canManage)
                                                        <a href="#" class="btn btn-default btn-sm" title="Remove payment" onclick="confirm('Remove this payment? The invoice balance will be restored.') || event.stopImmediatePropagation()" wire:click.prevent="deletePayment({{ $payment->id }})"><i class="fa fa-trash color-danger"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $canManage ? 8 : 6 }}" class="text-center text-muted">No payments have been recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Billing contacts --}}
            <div class="row">
                <div class="{{ $canManage ? 'col-md-7' : 'col-md-12' }}">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5><i class="fa fa-address-book-o"></i> Invoices &amp; reminders are sent to</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            @if ($canManage)
                                <a href="#" wire:click.prevent="newContact" class="btn btn-default" style="margin-bottom:15px;"><i class="fa fa-plus-square-o"></i> Contact</a>
                                @if (! $whatsappReady)
                                    <div class="alert alert-warning" style="margin-bottom:15px;">
                                        WhatsApp sending is switched off: set <code>WHATSAPP_TOKEN</code> and <code>WHATSAPP_PHONE_NUMBER_ID</code> in the environment file. Invoices go out by email only until then.
                                    </div>
                                @endif
                            @endif
                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="th-sm">Name</th>
                                            <th class="th-sm">Email</th>
                                            <th class="th-sm">WhatsApp</th>
                                            @if ($canManage)
                                                <th class="th-sm">Status</th>
                                                <th class="th-sm">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($canManage ? $contacts : $contacts->where('status', true) as $contact)
                                            <tr wire:key="billing-contact-{{ $contact->id }}">
                                                <td>{{ $contact->name }}</td>
                                                <td>
                                                    {{ $contact->email ?: '-' }}
                                                    @if ($contact->email && ! $contact->send_email) <small class="text-muted">(off)</small> @endif
                                                </td>
                                                <td>
                                                    {{ $contact->phonenumber ?: '-' }}
                                                    @if ($contact->phonenumber && ! $contact->send_whatsapp) <small class="text-muted">(off)</small> @endif
                                                </td>
                                                @if ($canManage)
                                                    <td><span class="badge bg-{{ $contact->status ? 'success' : 'secondary' }}">{{ $contact->status ? 'Active' : 'Paused' }}</span></td>
                                                    <td>
                                                        <a href="#" class="btn btn-default btn-sm" wire:click.prevent="editContact({{ $contact->id }})" title="Edit"><i class="fa fa-edit color-success"></i></a>
                                                        <a href="#" class="btn btn-default btn-sm" wire:click.prevent="toggleContact({{ $contact->id }})" title="{{ $contact->status ? 'Pause' : 'Resume' }}"><i class="fa fa-{{ $contact->status ? 'pause' : 'play' }}"></i></a>
                                                        <a href="#" class="btn btn-default btn-sm" onclick="confirm('Remove {{ addslashes($contact->name) }} from the billing list?') || event.stopImmediatePropagation()" wire:click.prevent="deleteContact({{ $contact->id }})" title="Remove"><i class="fa fa-trash color-danger"></i></a>
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $canManage ? 5 : 3 }}" class="text-muted">
                                                    No billing contacts set - invoices go to the company's own details:
                                                    {{ $company->email ?: 'no email' }} / {{ $company->phonenumber ?: 'no phone number' }}.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing settings (Gonyeti admins only) --}}
                @if ($canManage)
                    <div class="col-md-5">
                        <div class="panel">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <h5><i class="fa fa-cog"></i> Billing Settings</h5>
                                </div>
                            </div>
                            <div class="panel-body p-20">
                                <form wire:submit.prevent="saveSettings">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" wire:model.defer="billing_enabled">
                                            Raise and send this company's invoice automatically every month
                                        </label>
                                    </div>
                                    @error('billing_enabled') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="billing_day">Invoice day of month<span class="required" style="color: red">*</span></label>
                                                <input type="number" min="1" max="31" id="billing_day" wire:model.defer="billing_day" class="form-control" required>
                                                <small class="form-text text-muted">29-31 use the last day in shorter months.</small>
                                                @error('billing_day') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="billing_due_days">Payment terms (days)<span class="required" style="color: red">*</span></label>
                                                <input type="number" min="0" id="billing_due_days" wire:model.defer="billing_due_days" class="form-control" required>
                                                @error('billing_due_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="billing_reminder_days">Remind every (days)<span class="required" style="color: red">*</span></label>
                                                <input type="number" min="0" id="billing_reminder_days" wire:model.defer="billing_reminder_days" class="form-control" required>
                                                <small class="form-text text-muted">While unpaid. 0 = no reminders.</small>
                                                @error('billing_reminder_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="billing_start_date">First invoice not before</label>
                                                <input type="date" id="billing_start_date" wire:model.defer="billing_start_date" class="form-control">
                                                @error('billing_start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted">The package and monthly fee are set on the company (Companies &gt; Manage Companies &gt; Edit).</p>
                                    <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Delivery log (Gonyeti admins only) --}}
            @if ($canManage && $deliveries->isNotEmpty())
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <h5><i class="fa fa-paper-plane-o"></i> Delivery Log <small>(last 25)</small></h5>
                                </div>
                            </div>
                            <div class="panel-body p-20">
                                <div style="overflow-x:auto;">
                                    <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="th-sm">When</th>
                                                <th class="th-sm">Invoice #</th>
                                                <th class="th-sm">Type</th>
                                                <th class="th-sm">Channel</th>
                                                <th class="th-sm">Recipient</th>
                                                <th class="th-sm">Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($deliveries as $delivery)
                                                <tr wire:key="billing-delivery-{{ $delivery->id }}">
                                                    <td>{{ $delivery->created_at->format('d M Y H:i') }}</td>
                                                    <td>{{ $delivery->invoice?->invoice_number }}</td>
                                                    <td>{{ ucfirst($delivery->type) }}</td>
                                                    <td>{{ $delivery->channel === 'whatsapp' ? 'WhatsApp' : 'Email' }}</td>
                                                    <td>{{ $delivery->recipient }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $delivery->status === 'sent' ? 'success' : 'danger' }}">{{ ucfirst($delivery->status) }}</span>
                                                        @if ($delivery->error) <small class="text-muted">{{ \Illuminate\Support\Str::limit($delivery->error, 140) }}</small> @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </section>

    @if ($canManage)
        {{-- Record payment --}}
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="billingPaymentModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fa fa-money"></i> Record Payment {{ $payInvoice ? '- ' . $payInvoice->invoice_number : '' }} <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="savePayment">
                        <div class="modal-body">
                            @if ($payInvoice)
                                <p class="text-muted">Outstanding balance: <strong>{{ $payInvoice->money($payInvoice->balance) }}</strong></p>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pay_date">Payment date<span class="required" style="color: red">*</span></label>
                                        <input type="date" id="pay_date" wire:model.defer="pay_date" class="form-control" required>
                                        @error('pay_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pay_amount">Amount<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="0.01" min="0.01" id="pay_amount" wire:model.defer="pay_amount" class="form-control" required>
                                        @error('pay_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pay_method">Method</label>
                                        <select id="pay_method" wire:model.defer="pay_method" class="form-control">
                                            @foreach (\App\Models\SubscriptionPayment::METHODS as $method)
                                                <option value="{{ $method }}">{{ $method }}</option>
                                            @endforeach
                                        </select>
                                        @error('pay_method') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pay_reference">Reference</label>
                                        <input type="text" id="pay_reference" wire:model.defer="pay_reference" class="form-control" placeholder="Bank / transaction reference">
                                        @error('pay_reference') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pay_notes">Notes</label>
                                <textarea id="pay_notes" wire:model.defer="pay_notes" class="form-control" rows="2"></textarea>
                                @error('pay_notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled"><i class="fa fa-save"></i>Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Billing contact --}}
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="billingContactModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fa fa-address-book-o"></i> {{ $contact_id ? 'Edit' : 'Add' }} Billing Contact <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="saveContact">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="contact_name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" id="contact_name" wire:model.defer="contact_name" class="form-control" placeholder="e.g. Accounts Department" required>
                                @error('contact_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_email">Email</label>
                                        <input type="email" id="contact_email" wire:model.defer="contact_email" class="form-control" placeholder="accounts@client.com">
                                        @error('contact_email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        <div class="checkbox">
                                            <label><input type="checkbox" wire:model.defer="contact_send_email"> Send by email</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_phonenumber">WhatsApp number</label>
                                        <input type="text" id="contact_phonenumber" wire:model.defer="contact_phonenumber" class="form-control" placeholder="+263772123456">
                                        @error('contact_phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        <div class="checkbox">
                                            <label><input type="checkbox" wire:model.defer="contact_send_whatsapp"> Send by WhatsApp</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled"><i class="fa fa-save"></i>Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
