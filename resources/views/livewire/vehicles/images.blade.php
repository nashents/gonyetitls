<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h5>
        <i class="fa fa-image text-primary"></i>
        Vehicle Images
        <span class="badge badge-primary">{{ $vehicle_images->count() }}</span>
    </h5>

    <button data-toggle="modal"
            data-target="#imageModal"
            class="btn btn-primary btn-sm">
        <i class="fa fa-plus"></i> Upload Images
    </button>
</div>

@if($vehicle_images->count())

<div class="row">
    @foreach($vehicle_images as $image)
    <div class="col-md-4 col-lg-3 mb-4 mt-10">
        <div class="card shadow-sm">

            <div style="height:220px; overflow:hidden;">
                <a href="{{ asset('images/uploads/'.$image->filename) }}"
                   target="_blank">

                    <img src="{{ asset('images/uploads/'.$image->filename) }}"
                         class="img-fluid"
                         style="width:100%;height:220px;object-fit:cover;transition:0.3s;">
                </a>
            </div>

            <div class="card-body p-2">

                <small class="text-muted d-block">
                    Uploaded:
                    {{ $image->created_at ? $image->created_at->format('d M Y') : '' }}
                </small>

                <div class="mt-2 d-flex justify-content-between">

                    <a href="{{ asset('images/uploads/'.$image->filename) }}"
                       target="_blank"
                       class="btn btn-info btn-xs">
                        <i class="fa fa-eye"></i> View
                    </a>

                    <button
                        wire:click="deleteImage({{ $image->id }})"
                        onclick="confirm('Delete this image?') || event.stopImmediatePropagation()"
                        class="btn btn-danger btn-xs">

                        <i class="fa fa-trash"></i> Delete
                    </button>

                </div>

            </div>
        </div>
    </div>
    @endforeach
</div>

@else

<div class="text-center p-5">
    <img src="{{ asset('images/nodata.png') }}"
         style="max-width:250px;"
         class="img-fluid">

    <h5 class="mt-3 text-muted">
        No vehicle images uploaded
    </h5>

    <button data-toggle="modal"
            data-target="#imageModal"
            class="btn btn-primary">
        <i class="fa fa-upload"></i> Upload First Image
    </button>
</div>

@endif

<div wire:ignore.self
     class="modal fade"
     id="imageModal"
     tabindex="-1"
     role="dialog">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-image"></i>
                    Upload Vehicle Images
                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form wire:submit.prevent="store">

                <div class="modal-body">

                    <div class="form-group">

                        <label>
                            Select Images
                        </label>

                        <input type="file"
                               wire:model="images"
                               class="form-control"
                               accept="image/jpeg,image/png,image/webp,image/jpg"
                               multiple>

                        <small class="text-muted">
                            Allowed formats: JPG, PNG, WEBP
                        </small>

                        @error('images')
                        <span class="text-danger d-block">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div wire:loading wire:target="images">
                        <div class="alert alert-info">
                            <i class="fa fa-spinner fa-spin"></i>
                            Uploading images...
                        </div>
                    </div>

                    @if($images)
                    <div class="row mt-3">
                        @foreach($images as $photo)
                        <div class="col-md-3 mb-3">
                            <img src="{{ $photo->temporaryUrl() }}"
                                 class="img-thumbnail"
                                 style="height:150px;width:100%;object-fit:cover;">
                        </div>
                        @endforeach
                    </div>
                    @endif

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            wire:loading.attr="disabled">

                        <i class="fa fa-save"></i>
                        Save Images

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

</div>
