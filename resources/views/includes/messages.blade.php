
@if (count($errors)>0)
@foreach ($errors->all() as $error)
<div class='alert alert-danger' style="padding-top:10px ">
    <button type='button' class='close' data-dismiss='alert'>×</button>
   <center><strong>{{$error}}</strong></center> 
</div>
@endforeach
@endif

@if (session('success'))
<div class='alert alert-success' style="padding-top:10px ">
    <button type='button' class='close' data-dismiss='alert'>×</button>
    <center><strong>{{session('success')}}</strong></center>
</div>
@endif

@if (session('error'))
<div class='alert alert-danger' style="padding-top:10px ">
        <button type='button' class='close' data-dismiss='alert'>×</button>
       <center><strong>{{session('error')}}</strong></center> 
</div>
@endif

@section('timeout-js')
<script>
$(".alert").fadeTo(2000, 1000).slideUp(1000, function(){
$(".alert").slideUp(1000);
});
</script>

@endsection


