<div>
    <div class="row mt-50 ">
        <x-loading/>
        <div class="col-md-11">
            <div class="panel">
                <div class="panel-heading">
                    <div class="panel-title text-center">
                        <h4>Forgot password?</h4>
                    </div>
                </div>
                <div class="panel-body p-20">

                    <div class="section-title">
                        <p class="sub-title">Please enter your username to verify if account exists</p>
                    </div>

                    <form class="form-horizontal">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Username</label>
                            <div class="col-sm-10">
                                <input type="username" wire:model.debounce.300ms="username" class="form-control" placeholder="Enter your account username">
                                @error('username') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if (isset($user))
                                <small style="color: green">{{ $message }} </small>
                                @else
                                <small style="color: red">{{ $message }} </small>
                                @endif
                               
                            </div>
                        </div>
                        {{-- @if (isset($user)) --}}
                        <div class="form-group mt-20">
                           
                            <div class="col-sm-offset-2 col-sm-10">
                                <a href="{{ route('login') }}" class="form-link"><small class="muted-text">Login?</small></a>
                                <button type="button" wire:click.prevent="findAccount()" class="btn btn-success btn-labeled pull-right">Search & Reset<span class="btn-label btn-label-right"><i class="fa fa-search"></i></span></button>
                            </div>
                        </div>
                        {{-- @endif --}}
                       
                    </form>

                    <hr>

                  
                </div>
            </div>
            <!-- /.panel -->
            <p class="text-muted text-center mb-n"><small>Copyright © Gonyeti TLS {{date('Y')}}</small></p>
        </div>
        <!-- /.col-md-11 -->
    </div>
</div>
