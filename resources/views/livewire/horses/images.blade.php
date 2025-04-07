<div>
    <section class="section">
        <div class="container-fluid">
    <a href="" data-toggle="modal" data-target="#imageModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Image(s)</a>
    <br>
    <br>
    <x-loading/>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <section></section>
        @if ($horse_images->count()>0)
        @foreach ($horse_images as $image)
        <div class="col-md-4">
            <a href="{{asset('images/uploads/'.$image->filename)}}"><img src="{{asset('images/uploads/'.$image->filename)}}" alt="horse Avatar" class="img-responsive" style="width:100% ; height:100% ;"></a>
        </div>
        @endforeach
        @else
        <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
        @endif
    </div>
    </section>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-image"></i> Add Horse Images(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                        <div class="form-group">
                            <label for="file">Upload File</label>
                            <input type="file" accept="image" wire:model.debounce.300ms="images" class="form-control" multiple required>
                            @error('images') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
