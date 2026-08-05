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
                                Mechanical Details
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <form wire:submit.prevent="update()">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Horse Model Name</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="name" required>
                                            @error('name') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>

                                @include('livewire.partials.mechanical-details-fields')

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                                        <a href="{{ route('horse_makes.index') }}" class="btn btn-gray btn-wide btn-rounded"><i class="fa fa-times"></i>Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
