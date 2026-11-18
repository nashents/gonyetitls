<div>
    <section class="section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>
                                    <i class="fas fa-lock"></i> Module Access — {{ $employee->name }} {{ $employee->surname }}
                                </h5>
                            </div>
                        </div>
                        <div class="panel-body">

                            <p class="text-muted">
                                Departments:
                                <strong>{{ $employee->departments->pluck('name')->join(', ') ?: 'None' }}</strong>.
                                Listed below are only the modules this user can currently see in their sidebar (from their
                                departments and roles) — modules outside their departments are already hidden and blocked by URL.
                                Tick a group or module to <strong>lock this user out of it</strong> as well.
                                Locked items are hidden from the sidebar and their pages return "403 — no access".
                            </p>

                            @if ($groups->isEmpty())
                                <div class="alert alert-info">This user has no lockable modules in their sidebar.</div>
                            @endif

                            @if ($isExempt)
                                <div class="alert alert-warning">
                                    This user is a system admin or Super Admin. Locks are saved but have no effect while they keep that access.
                                </div>
                            @endif

                            <form wire:submit.prevent="save">
                                <div class="row">
                                    @foreach ($groups as $group)
                                        @php $groupLocked = in_array((string) $group->id, array_map('strval', $blockedGroups), true); @endphp
                                        <div class="col-md-4" wire:key="mag-{{ $group->id }}">
                                            <div class="panel panel-default" style="border:1px solid #ddd; margin-bottom:15px; {{ $groupLocked ? 'background:#fdf0f0;' : '' }}">
                                                <div class="panel-heading" style="padding:8px 12px;">
                                                    <label style="margin:0; font-weight:bold; cursor:pointer;">
                                                        <input type="checkbox" wire:model="blockedGroups" value="{{ $group->id }}">
                                                        Lock all of <u>{{ $group->name }}</u>
                                                    </label>
                                                </div>
                                                <div class="panel-body" style="padding:8px 12px;">
                                                    @foreach ($group->modules as $module)
                                                        @if (in_array($module->slug, $exemptSlugs, true))
                                                            @continue
                                                        @endif
                                                        <div wire:key="mam-{{ $module->id }}">
                                                            <label style="font-weight:normal; cursor:pointer; {{ $groupLocked ? 'color:#aaa;' : '' }}">
                                                                <input type="checkbox" wire:model.defer="blockedModules" value="{{ $module->id }}" @if ($groupLocked) disabled @endif>
                                                                @if ($module->icon)<i class="{{ $module->icon }}"></i>@endif
                                                                {{ $module->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @if ($loop->iteration % 3 === 0)
                                            <div class="clearfix"></div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="btn-group pull-right mt-10">
                                    <button type="button" class="btn btn-default btn-rounded" wire:click="clearAll">
                                        <i class="fa fa-unlock"></i> Clear all locks
                                    </button>
                                    <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled">
                                        <i class="fa fa-save"></i> Save
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
