<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            <div class="panel-title">
                                <a href="#" wire:click.prevent="create" class="btn btn-default">
                                    <i class="fa fa-plus-square-o"></i> Integration
                                </a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <table id="company_integrationsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Provider</th>
                                        <th class="th-sm">Type</th>
                                        <th class="th-sm">Status</th>
                                        <th class="th-sm">Last Tested</th>
                                        <th class="th-sm">Last Sync</th>
                                        <th class="th-sm">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($company_integrations as $integration)
                                    <tr wire:key="{{ '9b589e-31-' . ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($integration->id ?? '') }}">
                                        <td>{{ $integration->integration_provider->name ?? '' }}</td>
                                        <td>{{ ucfirst($integration->integration_provider->type ?? '') }}</td>
                                        <td>
                                            @php
                                                $badge = [
                                                    'active'    => 'success',
                                                    'inactive'  => 'secondary',
                                                    'error'     => 'danger',
                                                    'suspended' => 'warning',
                                                ][$integration->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ ucfirst($integration->status) }}</span>
                                        </td>
                                        <td>{{ $integration->last_tested_at ? $integration->last_tested_at->diffForHumans() : '-' }}</td>
                                        <td>{{ $integration->last_sync_at ? $integration->last_sync_at->diffForHumans() : '-' }}</td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="#" wire:click.prevent="testConnection({{ $integration->id }})"><i class="fa fa-plug color-info"></i> Test</a></li>
                                                    @if (($integration->integration_provider->type ?? null) === 'fiscal')
                                                        <li><a href="#" wire:click.prevent="fiscalDeviceStatus({{ $integration->id }})"><i class="fa fa-info-circle color-info"></i> Device Status</a></li>
                                                        @if (($integration->integration_provider->key ?? null) === 'fiscal_harmony')
                                                            <li><a href="#" wire:click.prevent="fiscalReadiness({{ $integration->id }})"><i class="fa fa-check-square-o color-success"></i> Readiness Check</a></li>
                                                        @endif
                                                        @if (in_array($integration->id, $fiscal_day_integration_ids))
                                                            <li><a href="#" wire:click.prevent="openFiscalDay({{ $integration->id }})"><i class="fa fa-sun-o color-success"></i> Open Fiscal Day</a></li>
                                                            <li>
                                                                <a href="#"
                                                                   wire:click.prevent="closeFiscalDay({{ $integration->id }})"
                                                                   onclick="return confirm('Close the current fiscal day? This produces the Z report for the day; a new day must be opened (or is opened automatically on the next fiscalisation) before further receipts.')">
                                                                    <i class="fa fa-moon-o color-danger"></i> Close Fiscal Day
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endif
                                                    <li><a href="#" wire:click.prevent="edit({{ $integration->id }})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li>
                                                        <a href="#"
                                                           wire:click.prevent="delete({{ $integration->id }})"
                                                           onclick="return confirm('Delete this integration?')">
                                                            <i class="fa fa-trash color-danger"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach

                                    @if ($company_integrations->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center">No integrations configured yet.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ADD MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="createModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-plus"></i> Add Integration
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Provider<span class="required" style="color:red">*</span></label>
                                    <select class="form-control" wire:model="integration_provider_id" required>
                                        <option value="">Select Provider</option>
                                        @foreach ($integration_providers as $provider)
                                            <option value="{{ $provider->id }}">{{ $provider->name }} ({{ ucfirst($provider->type) }})</option>
                                        @endforeach
                                    </select>
                                    @error('integration_provider_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status<span class="required" style="color:red">*</span></label>
                                    <select class="form-control" wire:model="status" required>
                                        <option value="inactive">Inactive</option>
                                        <option value="active">Active</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="error">Error</option>
                                    </select>
                                    @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if (count($required_credentials) || count($optional_credentials))
                            <hr>
                            <h5><i class="fa fa-key"></i> Credentials</h5>
                            <div class="row">
                                @foreach (array_merge($required_credentials, $optional_credentials) as $field)
                                    @php $isRequired = in_array($field, $required_credentials, true); @endphp
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ ucwords(str_replace('_', ' ', $field)) }}
                                                @if ($isRequired)<span class="required" style="color:red">*</span>@else <small class="text-muted">(optional)</small>@endif
                                            </label>
                                            <input type="{{ \Illuminate\Support\Str::contains($field, ['password', 'secret', 'api_key', 'token']) ? 'password' : 'text' }}"
                                                   class="form-control"
                                                   wire:model.defer="credentials.{{ $field }}"
                                                   placeholder="Enter {{ str_replace('_', ' ', $field) }}" />
                                            @error('credentials.' . $field) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (count($config))
                            <hr>
                            <h5><i class="fa fa-cogs"></i> Config</h5>
                            <div class="row">
                                @foreach ($config as $key => $val)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                                            <input type="text" class="form-control" wire:model.defer="config.{{ $key }}" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="company_integrationEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Integration
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="update()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Provider</label>
                                    <select class="form-control" wire:model="integration_provider_id" disabled>
                                        @foreach ($integration_providers as $provider)
                                            <option value="{{ $provider->id }}">{{ $provider->name }} ({{ ucfirst($provider->type) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status<span class="required" style="color:red">*</span></label>
                                    <select class="form-control" wire:model="status" required>
                                        <option value="inactive">Inactive</option>
                                        <option value="active">Active</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="error">Error</option>
                                    </select>
                                    @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if (count($required_credentials) || count($optional_credentials))
                            <hr>
                            <h5><i class="fa fa-key"></i> Credentials</h5>
                            <div class="row">
                                @foreach (array_merge($required_credentials, $optional_credentials) as $field)
                                    @php $isRequired = in_array($field, $required_credentials, true); @endphp
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ ucwords(str_replace('_', ' ', $field)) }}
                                                @if ($isRequired)<span class="required" style="color:red">*</span>@else <small class="text-muted">(optional)</small>@endif
                                            </label>
                                            <input type="{{ \Illuminate\Support\Str::contains($field, ['password', 'secret', 'api_key', 'token']) ? 'password' : 'text' }}"
                                                   class="form-control"
                                                   wire:model.defer="credentials.{{ $field }}"
                                                   placeholder="Enter {{ str_replace('_', ' ', $field) }}" />
                                            @error('credentials.' . $field) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (count($config))
                            <hr>
                            <h5><i class="fa fa-cogs"></i> Config</h5>
                            <div class="row">
                                @foreach ($config as $key => $val)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                                            <input type="text" class="form-control" wire:model.defer="config.{{ $key }}" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i> Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- FISCAL HARMONY READINESS MODAL (same report as `php artisan fiscalharmony:check`) --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fiscalReadinessModal" tabindex="-1" role="dialog" aria-labelledby="fiscalReadinessLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="fiscalReadinessLabel"><i class="fa fa-check-square-o"></i> {{ $readiness_provider ?? 'Fiscal Harmony' }} Readiness Check
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div wire:loading wire:target="fiscalReadiness" class="text-center p-3">
                        <span class="spinner-border spinner-border-sm"></span> Checking Fiscal Harmony…
                    </div>

                    @if ($readiness)
                        <div wire:loading.remove wire:target="fiscalReadiness">
                            @php
                                $icon  = ['ok' => 'fa-check-circle text-success', 'warn' => 'fa-exclamation-triangle text-warning', 'fail' => 'fa-times-circle text-danger'];
                                $badge = ['ok' => 'success', 'warn' => 'warning', 'fail' => 'danger'];
                            @endphp

                            @if (! empty($readiness['url']))
                                <p class="text-muted mb-2"><small>{{ $readiness['url'] }}</small></p>
                            @endif

                            @if ($readiness['fatal'] ?? null)
                                <div class="alert alert-danger">{{ $readiness['fatal'] }}</div>
                            @elseif ($readiness['ready'])
                                <div class="alert alert-success mb-3"><i class="fa fa-check-circle"></i> Ready to fiscalise.</div>
                            @else
                                <div class="alert alert-warning mb-3"><i class="fa fa-exclamation-triangle"></i> {{ $readiness['problems'] }} issue(s) to resolve before fiscalising.</div>
                            @endif

                            @if (! empty($readiness['checks']))
                                <table class="table table-sm table-bordered">
                                    <tbody>
                                        @foreach ($readiness['checks'] as $check)
                                            <tr wire:key="readiness-check-{{ $loop->index }}">
                                                <td style="width: 160px"><i class="fa {{ $icon[$check['status']] }}"></i> <strong>{{ $check['label'] }}</strong></td>
                                                <td>{{ $check['detail'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if (! empty($readiness['taxes']))
                                <h5 class="mt-3"><i class="fa fa-percent"></i> Tax mappings</h5>
                                <p class="text-muted mb-1"><small>Every invoice line needs a mapped tax and an HS code.</small></p>
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr><th>Tax code</th><th class="text-right">Rate</th><th>HS code</th><th>Fiscal Harmony mapping</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($readiness['taxes'] as $tax)
                                            <tr wire:key="readiness-tax-{{ $tax['id'] }}">
                                                <td>{{ $tax['code'] }}</td>
                                                <td class="text-right">{{ $tax['rate'] }}%</td>
                                                <td>
                                                    @if ($tax['hs_code'])
                                                        {{ $tax['hs_code'] }}
                                                    @else
                                                        <span class="text-danger">Missing</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge bg-{{ $badge[$tax['status']] }}">{{ $tax['mapping'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if (! empty($readiness['device_taxes']))
                                <details class="mb-3">
                                    <summary class="text-muted">Taxes configured on the fiscal device (ZIMRA)</summary>
                                    <table class="table table-sm table-bordered mt-2">
                                        <thead><tr><th>Tax ID</th><th>Name</th><th class="text-right">Rate</th></tr></thead>
                                        <tbody>
                                            @foreach ($readiness['device_taxes'] as $deviceTax)
                                                <tr wire:key="readiness-devtax-{{ $loop->index }}">
                                                    <td>{{ $deviceTax['id'] }}</td>
                                                    <td>{{ $deviceTax['name'] }}</td>
                                                    <td class="text-right">{{ $deviceTax['percent'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </details>
                            @endif

                            @if (! empty($readiness['currencies']))
                                <h5><i class="fa fa-money"></i> Currencies</h5>
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th>Local</th><th>Sent as</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach ($readiness['currencies'] as $currency)
                                            <tr wire:key="readiness-cur-{{ $loop->index }}">
                                                <td>{{ $currency['local'] }}</td>
                                                <td>{{ $currency['sent'] }}</td>
                                                <td><span class="badge bg-{{ $badge[$currency['status']] }}">{{ $currency['status'] === 'ok' ? 'Supported' : 'Not supported' }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if (! empty($readiness['webhook_url']))
                                <p class="mb-0"><strong>Webhook URL for the API key:</strong> <code>{{ $readiness['webhook_url'] }}</code>
                                    @unless (str_starts_with($readiness['webhook_url'], 'https://'))
                                        <br><small class="text-warning">Fiscal Harmony only accepts HTTPS — set this from a server with SSL.</small>
                                    @endunless
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        @if ($readiness_integration_id)
                            <button type="button" class="btn btn-default border-primary btn-wide btn-rounded"
                                    wire:click="fiscalReadiness({{ $readiness_integration_id }})" wire:loading.attr="disabled" wire:target="fiscalReadiness">
                                <i class="fa fa-refresh"></i> Re-run
                            </button>
                            @if (! empty($readiness['unmapped']))
                                <button type="button" class="btn bg-success btn-wide btn-rounded"
                                        wire:click="fiscalReadiness({{ $readiness_integration_id }}, true)" wire:loading.attr="disabled" wire:target="fiscalReadiness"
                                        onclick="confirm('Create the missing tax mappings on Fiscal Harmony, matching each tax by rate to the fiscal device taxes?') || event.stopImmediatePropagation()">
                                    <i class="fa fa-link"></i> Map Missing Taxes
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('show-fiscalReadinessModal', () => $('#fiscalReadinessModal').modal('show'));
    </script>
</div>
