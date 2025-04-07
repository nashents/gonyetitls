<div>
    <div class="col-md-8 col-md-offset-2">
        @if (isset($company->logo))
        <img src="{{asset('images/uploads/'.$company->logo)}}" alt="{{$company->name}} " class="img-responsive">
        @else
        <img src="{{asset('images/uploads/'.$company->logo)}}" alt="{{$company->name}} " class="img-responsive">
        @endif

        <div class="text-center">
            <button type="button" class="btn btn-primary btn-xs btn-labeled mt-10" data-toggle="modal" data-target="#logoModal">Update Logo<span class="btn-label btn-label-right"><i class="fa fa-pencil"></i></span></button>
        </div>
    </div>

    <!-- Modal -->
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="logoModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-picture-o"></i>Upload your logo <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="upload()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="address">Company Logo</label>
                       <input type="file" wire:model.debounce.300ms="logo" placeholder="Upload profile image" class="form-control">
                        @error('logo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>
