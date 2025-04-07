
    <!-- Modal -->
    <div  class="modal" id="profilePictureModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title" id="modal4Label"><i class="fa fa-picture-o"></i>Upload your picture <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
              </div>
              <form method="POST" action="{{route('postProfile', Auth::user()->id)}}" enctype="multipart/form-data">
                  {{ csrf_field() }}
                  <input type="hidden" name="category" value="school">
              <div class="modal-body">
                  <div class="form-group">
                      <label for="address">Profile Picture</label>
                     <input type="file" name="file" placeholder="Upload profile image" class="form-control">
                      @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                  </div>
              
              </div>
              <div class="modal-footer">
                  <div class="btn-group" role="group">
                      <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                      <button onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; " class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                  </div>
                  <!-- /.btn-group -->
              </div>
          </form>
          </div>
      </div>
  </div>


