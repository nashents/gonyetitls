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
                                <a href="" data-toggle="modal" data-target="#noticeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Notice</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>  
            <div class="content-internal">
                <div class="content">
                    @if ($notices->count()>0)
                        @foreach ($notices as $notice)
                            <h4 id="para{{ $notice->id }}">{{ $notice->title }}</h4> 
                            <p>{!! $notice->description !!}</p>
                        @endforeach
                    @else
                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                    @endif
                  
                  
                </div>
                <!-- /.content -->
            </div>
            <!-- /.content-internal -->

            <div class="sidebar-internal" data-spy="affix" data-offset-top="140" data-offset-bottom="200" id="scrollspy-nav">
                <div class="sidebar">
                    <h4 class="mt-10">Notices</h4>
                    <ul class="nav nav-pills nav-stacked">
                        @foreach ($notices as $notice)
                            <div class="row">
                                <div class="col-md-8"> <li><span><a href="#para{{ $notice->id }}" data-scroll>{{ $notice->title }}</a> </span></li></div>
                                <div class="col-md-4"><span><a href="#"  wire:click="edit({{$notice->id}})" ><i class="fa fa-edit color-success"></i></a> <a href="#" data-toggle="modal" data-target="#noticeDeleteModal{{ $notice->id }}" ><i class="fa fa-trash color-danger"></i></a></span></div>
                            </div>
                            <br>
                            @include('notices.delete')
                        @endforeach
                      
                    </ul>
                </div>
                <!-- /.sidebar -->
            </div>
            <!-- /.sidebar-internal -->

        </div>
        <!-- /.container-fluid -->
    </section>
    {{-- <section class="section">
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
                                <a href="" data-toggle="modal" data-target="#noticeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Notice</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <div class="col-md-12">
                                <h5 class="underline">Notices History</h5>
                                <div class="panel-group acc-panels" id="collapse3" role="tablist" aria-multiselectable="false">

                                    @foreach ($notices as $notice)
                                    <div class="panel panel-danger no-border">
                                        <div class="panel-heading" role="tab" id="heading6One">
                                            <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" href="#collapse6One" aria-expanded="true" aria-controls="collapse6One">
                                         {{$notice->title}}
                                        </a>
                                      </h4>
                                        </div>
                                        <div id="collapse6One" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading6One">
                                            <div class="panel-body" style="color: black">
                                               {!!$notice->description!!}
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                </div>

                            </div>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section> --}}




    <!-- Modal -->
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="noticeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Notice <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form >
                <div class="modal-body">
                  <div class="form-group">
                      <label for="title">Title<span class="required" style="color: red">*</span></label>
                     <input type="text" wire:model.debounce.300ms="title" class="form-control" required>
                      @error('destination') <span class="error" style="color:red">{{ $message }}</span> @enderror

                    </div>
                  <div class="form-group">
                    <label for="description">Body<span class="required" style="color: red">*</span></label>
                    <div wire:ignore>
                    <textarea wire:model.debounce.300ms="body" id="body" name="body" class="form-control" cols="30" rows="10" required></textarea>
                    </div>
                    @error('body') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button wire:click.prevent="store()" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Send</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="noticeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Notice <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form >
                <div class="modal-body">
                  <div class="form-group">
                      <label for="name">Title<span class="required" style="color: red">*</span></label>
                      <input type="text" wire:model.debounce.300ms="title" class="form-control"  required>
                      @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
                  <div class="form-group">
                    <label for="description">Body<span class="required" style="color: red">*</span></label>
                    <div wire:ignore>
                    <textarea wire:model.debounce.300ms="body" id="body" name="body" class="form-control" cols="30" rows="10" required></textarea>
                    </div>
                    @error('body') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button wire:click.prevent="update()" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>

    @section('extra-js')
    <script>
        ClassicEditor
            .create(document.querySelector('#body'))
            .then(editor => {
                editor.model.document.on('change:data', () => {
                @this.set('body', editor.getData());
                })
            })
            .catch(error => {
                console.error(error);
            });
    </script>
    @endsection