<div>
    <div class="row mt-50 ">
        <x-loading/>
        <div class="col-md-11">
            <div class="panel">
                <div class="panel-heading">
                    <div class="panel-title text-center">
                        <h4>Password Reset?</h4>
                    </div>
                </div>
                <div class="panel-body p-20">  
                    <div class="section-title">
                       <center> <p class="sub-title">Just one more step :-). <br> Click the button below to reset your pin</p></center>
                    </div>
                    <div class="form-group mt-20">
                        <div class="col-sm-offset-2 col-sm-10">
                            <a href="{{ route('login') }}" class="form-link"><small class="muted-text">Login?</small></a>
                            <button type="button" wire:click.prevent="resetPassword()" class="btn btn-success btn-labeled pull-right">Reset Pin<span class="btn-label btn-label-right"><i class="fa fa-refresh"></i></span></button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.panel -->
            <p class="text-muted text-center mb-n"><small>Copyright © Gonyeti TLS {{date('Y')}}</small></p>
        </div>
        <!-- /.col-md-11 -->
    </div>
</div>
