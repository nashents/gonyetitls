<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>@yield('title')</title>
  {{-- <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}" media="screen" >
  <link rel="stylesheet" href="{{asset('css/font-awesome.min.css')}}" media="screen" > --}}
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <script src="https://kit.fontawesome.com/0154e08647.js" crossorigin="anonymous"></script>
 @if (Auth::user()->employee->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
    {{-- <link rel="stylesheet" href="{{asset('css/layout.css')}}"> --}}
    @yield('extra-css')
    @include('includes.css')
    @livewireStyles
    @stack('styles')
</head>
<body>

    @yield('content')
<script>
    function goBack() {
      window.history.back();
    }
    </script>
    @yield('extra-js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>


    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            showCloseButton: true,
            timer: 10000,
            timerProgressBar:true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        window.addEventListener('alert',({detail:{type,message}})=>{
            Toast.fire({
                icon:type,
                title:message
            })
        })
    </script>
    @livewireScripts
</body>
</html>
