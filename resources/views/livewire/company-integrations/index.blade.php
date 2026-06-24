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
                                    <tr>
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

                        @if (count($required_credentials))
                            <hr>
                            <h5><i class="fa fa-key"></i> Credentials</h5>
                            <div class="row">
                                @foreach ($required_credentials as $field)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ ucwords(str_replace('_', ' ', $field)) }}<span class="required" style="color:red">*</span></label>
                                            <input type="{{ in_array($field, ['password','secret','api_key','token']) ? 'password' : 'text' }}"
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

                        @if (count($required_credentials))
                            <hr>
                            <h5><i class="fa fa-key"></i> Credentials</h5>
                            <div class="row">
                                @foreach ($required_credentials as $field)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ ucwords(str_replace('_', ' ', $field)) }}<span class="required" style="color:red">*</span></label>
                                            <input type="{{ in_array($field, ['password','secret','api_key','token']) ? 'password' : 'text' }}"
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
</div>