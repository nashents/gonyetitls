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
                                <h4>{{$sheq_audit_template->name}} <small>{{$sheq_audit_template->standard}}</small>
                                    <span class="label label-primary">Total Possible Marks: {{$sheq_audit_template->possibleTotal()}}</span>
                                </h4>
                                <a href="" data-toggle="modal" data-target="#sectionModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Section</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            @forelse ($sheq_audit_template->sections as $section)
                                <div class="panel panel-default" style="margin-bottom:15px">
                                    <div class="panel-heading">
                                        <strong>{{$section->code}} {{$section->title}}</strong>
                                        <span class="label label-default">Possible: {{$section->possibleTotal()}}</span>
                                        <span class="pull-right">
                                            <a href="#" wire:click.prevent="addItem({{$section->id}})" class="btn btn-xs btn-default"><i class="fa fa-plus"></i> Requirement</a>
                                            <a href="#" wire:click.prevent="editSection({{$section->id}})" class="btn btn-xs btn-default"><i class="fa fa-edit color-success"></i></a>
                                            <a href="#" wire:click.prevent="deleteSection({{$section->id}})" class="btn btn-xs btn-default"><i class="fa fa-trash color-danger"></i></a>
                                        </span>
                                    </div>
                                    <div class="panel-body" style="padding:10px">
                                        <table class="table table-striped table-bordered table-sm" width="100%">
                                            <thead>
                                                <tr>
                                                    <th style="width:8%">Ref</th>
                                                    <th>Requirement</th>
                                                    <th style="width:25%">Guidance / What to check</th>
                                                    <th style="width:8%">Possible Mark</th>
                                                    <th style="width:10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($section->items as $item)
                                                    <tr>
                                                        <td>{{$item->code}}</td>
                                                        <td>{{$item->requirement}}</td>
                                                        <td>{{$item->guidance}}</td>
                                                        <td>{{$item->possible_mark}}</td>
                                                        <td>
                                                            <a href="#" wire:click.prevent="editItem({{$item->id}})" class="btn btn-xs btn-default"><i class="fa fa-edit color-success"></i></a>
                                                            <a href="#" wire:click.prevent="deleteItem({{$item->id}})" class="btn btn-xs btn-default"><i class="fa fa-trash color-danger"></i></a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="5"><center>No requirements in this section yet.</center></td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align:center; padding:20px; font-size:17px">
                                    No sections yet. Add your first section (e.g. "4.0 Organisational Context").
                                </div>
                            @endforelse

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sectionDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Section and all its requirements?</strong> </center>
                </div>
                <form wire:submit.prevent="destroySection()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="itemDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Requirement?</strong> </center>
                </div>
                <form wire:submit.prevent="destroyItem()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sectionModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Section <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeSection()" >
                <div class="modal-body">
                    @include('livewire.sheq-audit-templates.section-form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sectionEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Section <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateSection()" >
                <div class="modal-body">
                    @include('livewire.sheq-audit-templates.section-form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="itemModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Requirement <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeItem()" >
                <div class="modal-body">
                    @include('livewire.sheq-audit-templates.item-form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="itemEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Requirement <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateItem()" >
                <div class="modal-body">
                    @include('livewire.sheq-audit-templates.item-form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
