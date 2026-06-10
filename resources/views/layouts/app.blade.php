<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title')</title>

        <!-- ========== COMMON STYLES ========== -->
        {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}
        <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}" media="screen" >
       
        <link rel="stylesheet" href="{{asset('css/animate-css/animate.min.css')}}" media="screen" >
        <link rel="stylesheet" href="{{asset('css/lobipanel/lobipanel.min.css')}}" media="screen" >

        <!-- ========== PAGE STYLES ========== -->
        <link rel="stylesheet" href="{{asset('css/prism/prism.css')}}" media="screen" > <!-- USED FOR DEMO HELP - YOU CAN REMOVE IT -->
        <link rel="stylesheet" href="{{asset('css/toastr/toastr.min.css')}}" media="screen" >
        <link rel="stylesheet" href="{{asset('css/icheck/skins/line/blue.css')}}" >
        <link rel="stylesheet" href="{{asset('css/icheck/skins/line/red.css')}}" >
        <link rel="stylesheet" href="{{asset('css/icheck/skins/line/green.css')}}" >
        <link rel="stylesheet" href="{{asset('css/bootstrap-tour/bootstrap-tour.css')}}" >
          <link rel="stylesheet" href="{{asset('css/font-awesome.min.css')}}" media="screen" >
        <script src="https://kit.fontawesome.com/0154e08647.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

       

        <!-- ========== THEME CSS ========== -->
        <link rel="stylesheet" href="{{asset('css/main.css')}}" media="screen" >
        <!-- ========== MODERNIZR ========== -->
        <script src="{{asset('js/modernizr/modernizr.min.js')}}"></script>
        <link rel="shortcut icon" type = "image/png" href="{!! asset('images/basilmark-favicon.png')!!}">
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
        <script src="https://cdn.ckeditor.com/ckeditor5/38.1.1/classic/ckeditor.js"></script>
        @livewireStyles
        
        {{-- <link rel="stylesheet" href="{{asset('css/pace/pace-flash.css')}}" media="screen" > --}}
        @yield('extra-css')
        {{-- <link rel="stylesheet" href="{{asset('css/pace/pace-minimal.css')}}" media="screen" > --}}
        @stack('styles')

        <style>
            .modal { overflow: auto !important; }
        </style>
       

    </head>
   @yield('body-id')
        <div class="main-wrapper">
                 <!-- ========== TOP NAVBAR ========== -->
                    @if (Auth::user())
                        @if (Auth::user()->employee || Auth::user()->company)
                            @include('includes.navbar')
                        @else
                            @include('includes.third_party_navbar')
                        @endif
                    @endif
             

            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">
                    <!-- ========== LEFT SIDEBAR ========== -->
                    @if (Auth::user())
                        @if (Auth::user()->employee)
                            @include('includes.sidebar')
                        @elseif(Auth::user()->company)
                            @include('includes.company_sidebar')
                        @else
                            @include('includes.third_party_sidebar')
                        @endif
                    @endif
                  
                 
                    <!-- /.left-sidebar -->
                    @yield('content')
                </div>
            </div>
        </div>
        <!-- /.main-wrapper -->

        <!-- ========== COMMON JS FILES ========== -->
        <script src="{{asset('js/jquery/jquery-2.2.4.min.js')}}"></script>
        <script src="{{asset('js/jquery-ui/jquery-ui.min.js')}}"></script>
        <script src="{{asset('js/bootstrap/bootstrap.min.js')}}"></script>
        <script src="{{asset('js/pace/pace.min.js')}}"></script>
        <script src="{{asset('js/lobipanel/lobipanel.min.js')}}"></script>
        <script src="{{asset('js/iscroll/iscroll.js')}}"></script>

        <!-- ========== PAGE JS FILES ========== -->
        <script src="{{asset('js/prism/prism.js')}}"></script>
        <script src="{{asset('js/waypoint/waypoints.min.js')}}"></script>
        <script src="{{asset('js/counterUp/jquery.counterup.min.js')}}"></script>
        <script src="{{asset('js/amcharts/amcharts.js')}}"></script>
        <script src="{{asset('js/amcharts/serial.js')}}"></script>
        <script src="{{asset('js/amcharts/plugins/export/export.min.js')}}"></script>
        <link rel="stylesheet" href="{{asset('js/amcharts/plugins/export/export.css')}}" type="text/css" media="all" />
        <script src="{{asset('js/amcharts/themes/light.js')}}"></script>
        <script src="{{asset('js/toastr/toastr.min.js')}}"></script>
        <script src="{{asset('js/icheck/icheck.min.js')}}"></script>
        <script src="{{asset('js/bootstrap-tour/bootstrap-tour.js')}}"></script>

        


        <!-- ========== THEME JS ========== -->
        <script src="{{asset('js/main.js')}}"></script>
        <script src="{{asset('js/production-chart.js')}}"></script>
        <script src="{{asset('js/traffic-chart.js')}}"></script>
        <script src="{{asset('js/task-list.js')}}"></script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
        <script>
            $(function(){

                // Counter for dashboard stats
                $('.counter').counterUp({
                    delay: 10,
                    time: 1000
                });
            });
        </script>
    @yield('timeout-js')
    @yield('extra-js')


        {{-- <script type="text/javascript">
            Livewire.emit('showCustomerStatement');
        </script> --}}

@stack('scripts')
    
    <script type="text/javascript">
            window.addEventListener('show-importModal', event => {
                $('#importModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-importModal', event => {
                $('#importModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-closeModal', event => {
                $('#closeModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-closeModal', event => {
                $('#closeModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-dealModal', event => {
                $('#dealModal').modal('show');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('hide-dealModal', event => {
                $('#dealModal').modal('hide');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('show-dealEditModal', event => {
                $('#dealEditModal').modal('show');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('hide-dealEditModal', event => {
                $('#dealEditModal').modal('hide');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('show-reportsModal', event => {
                $('#reportsModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-reportsModal', event => {
                $('#reportsModal').modal('hide');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('show-trip_originModal', event => {
                $('#trip_originModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-trip_originModal', event => {
                $('#trip_originModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-trip_originEditModal', event => {
                $('#trip_originEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-trip_originEditModal', event => {
                $('#trip_originEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-budgetModal', event => {
                $('#budgetModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-budgetModal', event => {
                $('#budgetModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-budgetEditModal', event => {
                $('#budgetEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-budgetEditModal', event => {
                $('#budgetEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-completedModal', event => {
                $('#completedModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-completedModal', event => {
                $('#completedModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-goods_returnedModal', event => {
                $('#goods_returnedModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-goods_returnedModal', event => {
                $('#goods_returnedModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-goods_returnedEditModal', event => {
                $('#goods_returnedEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-goods_returnedEditModal', event => {
                $('#goods_returnedEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-deleteModal', event => {
                $('#deleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-deleteModal', event => {
                $('#deleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-reminderCopyDeleteModal', event => {
                $('#reminderCopyDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-reminderCopyDeleteModal', event => {
                $('#reminderCopyDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-reminderCopyModal', event => {
                $('#reminderCopyModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-reminderCopyModal', event => {
                $('#reminderCopyModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-reminderCopyEditModal', event => {
                $('#reminderCopyEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-reminderCopyEditModal', event => {
                $('#reminderCopyEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-decisionModal', event => {
                $('#decisionModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-decisionModal', event => {
                $('#decisionModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-decisionEditModal', event => {
                $('#decisionEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-decisionEditModal', event => {
                $('#decisionEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-decisionDeleteModal', event => {
                $('#decisionDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-decisionDeleteModal', event => {
                $('#decisionDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-stageModal', event => {
                $('#stageModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-stageModal', event => {
                $('#stageModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-stageEditModal', event => {
                $('#stageEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-stageEditModal', event => {
                $('#stageEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-stageDeleteModal', event => {
                $('#stageDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-stageDeleteModal', event => {
                $('#stageDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-checkModal', event => {
                $('#checkModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-checkModal', event => {
                $('#checkModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-checkEditModal', event => {
                $('#checkEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-checkEditModal', event => {
                $('#checkEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-checkDeleteModal', event => {
                $('#checkDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-checkDeleteModal', event => {
                $('#checkDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-criterionModal', event => {
                $('#criterionModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-criterionModal', event => {
                $('#criterionModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-criterionEditModal', event => {
                $('#criterionEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-criterionEditModal', event => {
                $('#criterionEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-criterionDeleteModal', event => {
                $('#criterionDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-criterionDeleteModal', event => {
                $('#criterionDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-applicationModal', event => {
                $('#applicationModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-applicationModal', event => {
                $('#applicationModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-applicationEditModal', event => {
                $('#applicationEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-applicationEditModal', event => {
                $('#applicationEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-applicationDeleteModal', event => {
                $('#applicationDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-applicationDeleteModal', event => {
                $('#applicationDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-job_postingModal', event => {
                $('#job_postingModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-job_postingModal', event => {
                $('#job_postingModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-job_postingEditModal', event => {
                $('#job_postingEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-job_postingEditModal', event => {
                $('#job_postingEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-job_postingDeleteModal', event => {
                $('#job_postingDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-job_postingDeleteModal', event => {
                $('#job_postingDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-workDoneModal', event => {
                $('#workDoneModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-workDoneModal', event => {
                $('#workDoneModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-waste_receptacleModal', event => {
                $('#waste_receptacleModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_receptacleModal', event => {
                $('#waste_receptacleModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-waste_receptacleDeleteModal', event => {
                $('#waste_receptacleDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_receptacleDeleteModal', event => {
                $('#waste_receptacleDeleteModal').modal('hide');
            })
    </script>
        <script type="text/javascript">
                window.addEventListener('show-waste_receptacleEditModal', event => {
                    $('#waste_receptacleEditModal').modal('show');
                })
        </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_receptacleEditModal', event => {
                $('#waste_receptacleEditModal').modal('hide');
            })
    </script>
 
    <script type="text/javascript">
            window.addEventListener('show-waste_disposalDeleteModal', event => {
                $('#waste_disposalDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_disposalDeleteModal', event => {
                $('#waste_disposalDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-waste_collectionDeleteModal', event => {
                $('#waste_collectionDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_collectionDeleteModal', event => {
                $('#waste_collectionDeleteModal').modal('hide');
            })
    </script>
   
    <script type="text/javascript">
            window.addEventListener('show-waste_typeDeleteModal', event => {
                $('#waste_typeDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_typeDeleteModal', event => {
                $('#waste_typeDeleteModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-payrollDeleteModal', event => {
                $('#payrollDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-payrollDeleteModal', event => {
                $('#payrollDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-waste_typeModal', event => {
                $('#waste_typeModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_typeModal', event => {
                $('#waste_typeModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-waste_typeEditModal', event => {
                $('#waste_typeEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_typeEditModal', event => {
                $('#waste_typeEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-waste_collectionModal', event => {
                $('#waste_collectionModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_collectionModal', event => {
                $('#waste_collectionModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-waste_collectionEditModal', event => {
                $('#waste_collectionEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_collectionEditModal', event => {
                $('#waste_collectionEditModal').modal('hide');
            })
    </script>
  
 
 <script type="text/javascript">
            window.addEventListener('show-waste_disposalModal', event => {
                $('#waste_disposalModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_disposalModal', event => {
                $('#waste_disposalModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-waste_disposalEditModal', event => {
                $('#waste_disposalEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-waste_disposalEditModal', event => {
                $('#waste_disposalEditModal').modal('hide');
            })
    </script>
    


    <script type="text/javascript">
            window.addEventListener('show-paymentDeleteModal', event => {
                $('#paymentDeleteModal').modal('show');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('hide-paymentDeleteModal', event => {
                $('#paymentDeleteModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-reminderCopyModal', event => {
                $('#reminderCopyModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-reminderCopyModal', event => {
                $('#reminderCopyModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-reminderEditModal', event => {
                $('#reminderEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-reminderEditModal', event => {
                $('#reminderEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-reminderDeleteModal', event => {
                $('#reminderDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-reminderDeleteModal', event => {
                $('#reminderDeleteModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-attendanceDeleteModal', event => {
                $('#attendanceDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-attendanceDeleteModal', event => {
                $('#attendanceDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-attendanceModal', event => {
                $('#attendanceModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-attendanceModal', event => {
                $('#attendanceModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-attendanceEditModal', event => {
                $('#attendanceEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-attendanceEditModal', event => {
                $('#attendanceEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-rentalDeleteModal', event => {
                $('#rentalDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-rentalDeleteModal', event => {
                $('#rentalDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-rentalModal', event => {
                $('#rentalModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-rentalModal', event => {
                $('#rentalModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-rentalEditModal', event => {
                $('#rentalEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-rentalEditModal', event => {
                $('#rentalEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-dispatchDeleteModal', event => {
                $('#dispatchDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-dispatchDeleteModal', event => {
                $('#dispatchDeleteModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-closeQuoteModal', event => {
                $('#closeQuoteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-closeQuoteModal', event => {
                $('#closeQuoteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-removeCostModal', event => {
                $('#removeCostModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-removeCostModal', event => {
                $('#removeCostModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-costModal', event => {
                $('#costModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-costModal', event => {
                $('#costModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-category_checklistDeleteModal', event => {
                $('#category_checklistDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-category_checklistDeleteModal', event => {
                $('#category_checklistDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-ticket_requestModal', event => {
                $('#ticket_requestModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-ticket_requestModal', event => {
                $('#ticket_requestModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-ticket_requestEditModal', event => {
                $('#ticket_requestEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-ticket_requestEditModal', event => {
                $('#ticket_requestEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-payment_methodModal', event => {
                $('#payment_methodModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-payment_methodModal', event => {
                $('#payment_methodModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-payment_methodEditModal', event => {
                $('#payment_methodEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-payment_methodEditModal', event => {
                $('#payment_methodEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-expenseImportModal', event => {
                $('#expenseImportModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-expenseImportModal', event => {
                $('#expenseImportModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-exchangeModal', event => {
                $('#exchangeModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-exchangeModal', event => {
                $('#exchangeModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-accrualModal', event => {
                $('#accrualModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-accrualModal', event => {
                $('#accrualModal').modal('hide');
            })
    </script>


    <script type="text/javascript">
            window.addEventListener('show-initialDiagnosisModal', event => {
                $('#initialDiagnosisModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-initialDiagnosisModal', event => {
                $('#initialDiagnosisModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-stationModal', event => {
                $('#stationModal').modal('show');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('hide-stationModal', event => {
                $('#stationModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-stationEditModal', event => {
                $('#stationEditModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-stationEditModal', event => {
                $('#stationEditModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-earningModal', event => {
                $('#earningModal').modal('show');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('hide-earningModal', event => {
                $('#earningModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-earningEditModal', event => {
                $('#earningEditModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-earningEditModal', event => {
                $('#earningEditModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-qualificationModal', event => {
                $('#qualificationModal').modal('show');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('hide-qualificationModal', event => {
                $('#qualificationModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-qualificationEditModal', event => {
                $('#qualificationEditModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-qualificationEditModal', event => {
                $('#qualificationEditModal').modal('hide');
            })
    </script>
   
   <script type="text/javascript">
            window.addEventListener('show-changePositionModal', event => {
                $('#changePositionModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-changePositionModal', event => {
                $('#changePositionModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-gradeModal', event => {
                $('#gradeModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-gradeModal', event => {
                $('#gradeModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-gradeEditModal', event => {
                $('#gradeEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-gradeEditModal', event => {
                $('#gradeEditModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-closeShiftModal', event => {
                $('#closeShiftModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-closeShiftModal', event => {
                $('#closeShiftModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-clusterModal', event => {
                $('#clusterModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-clusterModal', event => {
                $('#clusterModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-clusterEditModal', event => {
                $('#clusterEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-clusterEditModal', event => {
                $('#clusterEditModal').modal('hide');
            })
    </script>

<script type="text/javascript">
            window.addEventListener('show-teamModal', event => {
                $('#teamModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-teamModal', event => {
                $('#teamModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-teamEditModal', event => {
                $('#teamEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-teamEditModal', event => {
                $('#teamEditModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-importModal', event => {
                $('#importModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-importModal', event => {
                $('#importModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-dispatchModal', event => {
                $('#dispatchModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-dispatchModal', event => {
                $('#dispatchModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-dispatchEditModal', event => {
                $('#dispatchEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-dispatchEditModal', event => {
                $('#dispatchEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-shiftsImportModal', event => {
                $('#shiftsImportModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-exchange_rateModal', event => {
                $('#exchange_rateModal').modal('show');
            })
    </script>


    <script type="text/javascript">
            window.addEventListener('hide-exchange_rateModal', event => {
                $('#exchange_rateModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-exchange_rateEditModal', event => {
                $('#exchange_rateEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-exchange_rateEditModal', event => {
                $('#exchange_rateEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-closeRetreadModal', event => {
                $('#closeRetreadModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-closeRetreadModal', event => {
                $('#closeRetreadModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-grvCloseModal', event => {
                $('#grvCloseModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-grvCloseModal', event => {
                $('#grvCloseModal').modal('hide');
            })
    </script>

<script type="text/javascript">
            window.addEventListener('show-goods_receivedModal', event => {
                $('#goods_receivedModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-goods_receivedModal', event => {
                $('#goods_receivedModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-goods_receivedEditModal', event => {
                $('#goods_receivedEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-goods_receivedEditModal', event => {
                $('#goods_receivedEditModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-dependantModal', event => {
                $('#dependantModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-dependantModal', event => {
                $('#dependantModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-dependantEditModal', event => {
                $('#dependantEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-dependantEditModal', event => {
                $('#dependantEditModal').modal('hide');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('show-rehandlingModal', event => {
                $('#rehandlingModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-rehandlingModal', event => {
                $('#rehandlingModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-rehandlingEditModal', event => {
                $('#rehandlingEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-rehandlingEditModal', event => {
                $('#rehandlingEditModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-shiftModal', event => {
                $('#shiftModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-shiftModal', event => {
                $('#shiftModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-shiftEditModal', event => {
                $('#shiftEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-shiftEditModal', event => {
                $('#shiftEditModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-workModal', event => {
                $('#workModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-workModal', event => {
                $('#workModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-workEditModal', event => {
                $('#workEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-workEditModal', event => {
                $('#workEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-locationModal', event => {
                $('#locationModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-locationModal', event => {
                $('#locationModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-locationEditModal', event => {
                $('#locationEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-locationEditModal', event => {
                $('#locationEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-rackModal', event => {
                $('#rackModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-rackModal', event => {
                $('#rackModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-rackEditModal', event => {
                $('#rackEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-rackEditModal', event => {
                $('#rackEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-binModal', event => {
                $('#binModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-binModal', event => {
                $('#binModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-binEditModal', event => {
                $('#binEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-binEditModal', event => {
                $('#binEditModal').modal('hide');
            })
    </script>


    <script type="text/javascript">
            window.addEventListener('show-updateInvoicesModal', event => {
                $('#updateInvoicesModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-updateInvoicesModal', event => {
                $('#updateInvoicesModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-expenseDeleteModal', event => {
                $('#expenseDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-expenseDeleteModal', event => {
                $('#expenseDeleteModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-requisitionStatusModal', event => {
                $('#requisitionStatusModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-requisitionStatusModal', event => {
                $('#requisitionStatusModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-requisitionPaymentModal', event => {
                $('#requisitionPaymentModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-requisitionPaymentModal', event => {
                $('#requisitionPaymentModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-tax_bracketModal', event => {
                $('#tax_bracketModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-tax_bracketModal', event => {
                $('#tax_bracketModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-tax_bracketEditModal', event => {
                $('#tax_bracketEditModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-tax_bracketEditModal', event => {
                $('#tax_bracketEditModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-leaveDaysModal', event => {
                $('#leaveDaysModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-leaveDaysModal', event => {
                $('#leaveDaysModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-restoreModal', event => {
                $('#restoreModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-restoreModal', event => {
                $('#restoreModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-purchaseRestoreModal', event => {
                $('#purchaseRestoreModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-purchaseRestoreModal', event => {
                $('#purchaseRestoreModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-inventoryImportModal', event => {
                $('#inventoryImportModal').modal('show');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('hide-inventoryImportModal', event => {
                $('#inventoryImportModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-tripsImportModal', event => {
                $('#tripsImportModal').modal('show');
            })
    </script>
    
    <script type="text/javascript">
            window.addEventListener('hide-tripsImportModal', event => {
                $('#tripsImportModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-reverseModal', event => {
                $('#reverseModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-reverseModal', event => {
                $('#reverseModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-editModal', event => {
                $('#editModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-editModal', event => {
                $('#editModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-transferModal', event => {
                $('#transferModal').modal('show');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('hide-transferModal', event => {
                $('#transferModal').modal('hide');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('show-disposeModal', event => {
                $('#disposeModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-disposeModal', event => {
                $('#disposeModal').modal('hide');
            })
    </script>
  
  <script type="text/javascript">
            window.addEventListener('show-bill_expenseDeleteModal', event => {
                $('#bill_expenseDeleteModal').modal('show');
            })
    </script>
    <script type="text/javascript">
            window.addEventListener('hide-bill_expenseDeleteModal', event => {
                $('#bill_expenseDeleteModal').modal('hide');
            })
    </script>

    <script type="text/javascript">
            window.addEventListener('show-bulkInvoicesModal', event => {
                $('#bulkInvoicesModal').modal('show');
            })
        </script>
    <script type="text/javascript">
            window.addEventListener('hide-bulkInvoicesModal', event => {
                $('#bulkInvoicesModal').modal('hide');
            })
        </script>
  
  <script type="text/javascript">
            window.addEventListener('show-bulkyCloseTicketModal', event => {
                $('#bulkyCloseTicketModal').modal('show');
            })
        </script>
    <script type="text/javascript">
            window.addEventListener('hide-bulkyCloseTicketModal', event => {
                $('#bulkyCloseTicketModal').modal('hide');
            })
        </script>
    <script type="text/javascript">
            window.addEventListener('show-mileageModal', event => {
                $('#mileageModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-mileageModal', event => {
                $('#mileageModal').modal('hide');
            })
        </script>
    <script type="text/javascript">
            window.addEventListener('show-complianceModal', event => {
                $('#complianceModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-complianceModal', event => {
                $('#complianceModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-complianceEditModal', event => {
                $('#complianceEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-complianceEditModal', event => {
                $('#complianceEditModal').modal('hide');
            })
        </script>

    <script type="text/javascript">
            window.addEventListener('show-notificationModal', event => {
                $('#notificationModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-notificationModal', event => {
                $('#notificationModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-notificationEditModal', event => {
                $('#notificationEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-notificationEditModal', event => {
                $('#notificationEditModal').modal('hide');
            })
        </script>

    <script type="text/javascript">
            window.addEventListener('show-training_requirementModal', event => {
                $('#training_requirementModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-training_requirementModal', event => {
                $('#training_requirementModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-training_requirementEditModal', event => {
                $('#training_requirementEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-training_requirementEditModal', event => {
                $('#training_requirementEditModal').modal('hide');
            })
        </script>

    <script type="text/javascript">
            window.addEventListener('show-training_planModal', event => {
                $('#training_planModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-training_planModal', event => {
                $('#training_planModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-training_planEditModal', event => {
                $('#training_planEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-training_planEditModal', event => {
                $('#training_planEditModal').modal('hide');
            })
        </script>
 
 <script type="text/javascript">
            window.addEventListener('show-training_departmentModal', event => {
                $('#training_departmentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-training_departmentModal', event => {
                $('#training_departmentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-training_departmentEditModal', event => {
                $('#training_departmentEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-training_departmentEditModal', event => {
                $('#training_departmentEditModal').modal('hide');
            })
        </script>

    <script type="text/javascript">
            window.addEventListener('show-training_itemModal', event => {
                $('#training_itemModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-training_itemModal', event => {
                $('#training_itemModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-training_itemEditModal', event => {
                $('#training_itemEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-training_itemEditModal', event => {
                $('#training_itemEditModal').modal('hide');
            })
        </script>

    <script type="text/javascript">
            window.addEventListener('show-trainingModal', event => {
                $('#trainingModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trainingModal', event => {
                $('#trainingModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-trainingEditModal', event => {
                $('#trainingEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trainingEditModal', event => {
                $('#trainingEditModal').modal('hide');
            })
        </script>

<script type="text/javascript">
            window.addEventListener('show-rate_cardModal', event => {
                $('#rate_cardModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-rate_cardModal', event => {
                $('#rate_cardModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-rate_cardEditModal', event => {
                $('#rate_cardEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-rate_cardEditModal', event => {
                $('#rate_cardEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-product_serviceModal', event => {
                $('#product_serviceModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-product_serviceModal', event => {
                $('#product_serviceModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-product_serviceEditModal', event => {
                $('#product_serviceEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-product_serviceEditModal', event => {
                $('#product_serviceEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-contractModal', event => {
                $('#contractModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-contractModal', event => {
                $('#contractModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-contractEditModal', event => {
                $('#contractEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-contractEditModal', event => {
                $('#contractEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-taxModal', event => {
                $('#taxModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-taxModal', event => {
                $('#taxModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-taxEditModal', event => {
                $('#taxEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-taxEditModal', event => {
                $('#taxEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-invoiceRestoreModal', event => {
                $('#invoiceRestoreModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-invoiceRestoreModal', event => {
                $('#invoiceRestoreModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-workshop_serviceModal', event => {
                $('#workshop_serviceModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-workshop_serviceModal', event => {
                $('#workshop_serviceModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-workshop_serviceEditModal', event => {
                $('#workshop_serviceEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-workshop_serviceEditModal', event => {
                $('#workshop_serviceEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-consigneeModal', event => {
                $('#consigneeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-consigneeModal', event => {
                $('#consigneeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-consigneeEditModal', event => {
                $('#consigneeEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-consigneeEditModal', event => {
                $('#consigneeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-folderDeleteModal', event => {
                $('#folderDeleteModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-folderDeleteModal', event => {
                $('#folderDeleteModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-documentDeleteModal', event => {
                $('#documentDeleteModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-documentDeleteModal', event => {
                $('#documentDeleteModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addHorseModal', event => {
                $('#addHorseModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addHorseModal', event => {
                $('#addHorseModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addDriverModal', event => {
                $('#addDriverModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addDriverModal', event => {
                $('#addDriverModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addTrailerModal', event => {
                $('#addTrailerModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addTrailerModal', event => {
                $('#addTrailerModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-lossModal', event => {
                $('#lossModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-lossModal', event => {
                $('#lossModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-lossEditModal', event => {
                $('#lossEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-lossEditModal', event => {
                $('#lossEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-loss_groupModal', event => {
                $('#loss_groupModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-loss_groupModal', event => {
                $('#loss_groupModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-loss_groupEditModal', event => {
                $('#loss_groupEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-loss_groupEditModal', event => {
                $('#loss_groupEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-loss_categoryModal', event => {
                $('#loss_categoryModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-loss_categoryModal', event => {
                $('#loss_categoryModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-loss_categoryEditModal', event => {
                $('#loss_categoryEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-loss_categoryEditModal', event => {
                $('#loss_categoryEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-provinceModal', event => {
                $('#provinceModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-provinceModal', event => {
                $('#provinceModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-provinceEditModal', event => {
                $('#provinceEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-provinceEditModal', event => {
                $('#provinceEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-bulkyAuthorizationModal', event => {
                $('#bulkyAuthorizationModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-bulkyAuthorizationModal', event => {
                $('#bulkyAuthorizationModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addTripsModal', event => {
                $('#addTripsModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addTripsModal', event => {
                $('#addTripsModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addTripsEditModal', event => {
                $('#addTripsEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addTripsEditModal', event => {
                $('#addTripsEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-paymentDrawdownModal', event => {
                $('#paymentDrawdownModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-paymentDrawdownModal', event => {
                $('#paymentDrawdownModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checkingModal', event => {
                $('#checkingModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checkingModal', event => {
                $('#checkingModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-folderModal', event => {
                $('#folderModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-folderModal', event => {
                $('#folderModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-folderEditModal', event => {
                $('#folderEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-folderEditModal', event => {
                $('#folderEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-reminder_itemModal', event => {
                $('#reminder_itemModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-reminder_itemModal', event => {
                $('#reminder_itemModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-reminder_itemEditModal', event => {
                $('#reminder_itemEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-reminder_itemEditModal', event => {
                $('#reminder_itemEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-requisition_itemDeleteModal', event => {
                $('#requisition_itemDeleteModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-requisition_itemDeleteModal', event => {
                $('#requisition_itemDeleteModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-requisition_itemModal', event => {
                $('#requisition_itemModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-requisition_itemModal', event => {
                $('#requisition_itemModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-requisition_itemEditModal', event => {
                $('#requisition_itemEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-requisition_itemEditModal', event => {
                $('#requisition_itemEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-requisitionModal', event => {
                $('#requisitionModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-requisitionModal', event => {
                $('#requisitionModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-requisitionEditModal', event => {
                $('#requisitionEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-requisitionEditModal', event => {
                $('#requisitionEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-category_checklistModal', event => {
                $('#category_checklistModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-category_checklistEditModal', event => {
                $('#category_checklistEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-category_checklistEditModal', event => {
                $('#category_checklistEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checklist_sub_categoryModal', event => {
                $('#checklist_sub_categoryModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checklist_sub_categoryModal', event => {
                $('#checklist_sub_categoryModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checklist_sub_categoryEditModal', event => {
                $('#checklist_sub_categoryEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checklist_sub_categoryEditModal', event => {
                $('#checklist_sub_categoryEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checklist_categoryModal', event => {
                $('#checklist_categoryModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checklist_categoryModal', event => {
                $('#checklist_categoryModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checklist_categoryEditModal', event => {
                $('#checklist_categoryEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checklist_categoryEditModal', event => {
                $('#checklist_categoryEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inspection_serviceModal', event => {
                $('#inspection_serviceModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inspection_serviceModal', event => {
                $('#inspection_serviceModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inspection_serviceEditModal', event => {
                $('#inspection_serviceEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inspection_serviceEditModal', event => {
                $('#inspection_serviceEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-rateModal', event => {
                $('#rateModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-rateModal', event => {
                $('#rateModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-rateEditModal', event => {
                $('#rateEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-rateEditModal', event => {
                $('#rateEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checklistModal', event => {
                $('#checklistModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checklistModal', event => {
                $('#checklistModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checklistEditModal', event => {
                $('#checklistEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checklistEditModal', event => {
                $('#checklistEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checklist_itemModal', event => {
                $('#checklist_itemModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checklist_itemModal', event => {
                $('#checklist_itemModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-checklist_itemEditModal', event => {
                $('#checklist_itemEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-checklist_itemEditModal', event => {
                $('#checklist_itemEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-locationsEditModal', event => {
                $('#locationsEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-locationsEditModal', event => {
                $('#locationsEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-groupModal', event => {
                $('#groupModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-nextServiceModal', event => {
                $('#nextServiceModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-nextServiceModal', event => {
                $('#nextServiceModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-groupModal', event => {
                $('#groupModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-groupEditModal', event => {
                $('#groupEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-groupEditModal', event => {
                $('#groupEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-ticket_inventoryModal', event => {
                $('#ticket_inventoryModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-ticket_inventoryModal', event => {
                $('#ticket_inventoryModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-ticket_inventoryEditModal', event => {
                $('#ticket_inventoryEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-ticket_inventoryEditModal', event => {
                $('#ticket_inventoryEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-attachmentModal', event => {
                $('#attachmentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-attachmentModal', event => {
                $('#attachmentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-asset_unassignmentModal', event => {
                $('#asset_unassignmentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-asset_unassignmentModal', event => {
                $('#asset_unassignmentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inventory_assignmentModal', event => {
                $('#inventory_assignmentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inventory_assignmentModal', event => {
                $('#inventory_assignmentModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-emailModal', event => {
                $('#emailModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-emailModal', event => {
                $('#emailModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-emailEditModal', event => {
                $('#emailEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-emailEditModal', event => {
                $('#emailEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-noticeModal', event => {
                $('#noticeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-noticeModal', event => {
                $('#noticeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-noticeEditModal', event => {
                $('#noticeEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-noticeEditModal', event => {
                $('#noticeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-trip_destinationModal', event => {
                $('#trip_destinationModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trip_destinationModal', event => {
                $('#trip_destinationModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-trip_destinationEditModal', event => {
                $('#trip_destinationEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trip_destinationEditModal', event => {
                $('#trip_destinationEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-fuelTankCapacityModal', event => {
                $('#fuelTankCapacityModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-fuelTankCapacityModal', event => {
                $('#fuelTankCapacityModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-odometerModal', event => {
                $('#odometerModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-odometerModal', event => {
                $('#odometerModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-breakdown_assignmentModal', event => {
                $('#breakdown_assignmentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-breakdown_assignmentModal', event => {
                $('#breakdown_assignmentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-breakdown_assignmentEditModal', event => {
                $('#breakdown_assignmentEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-breakdown_assignmentEditModal', event => {
                $('#breakdown_assignmentEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-breakdownModal', event => {
                $('#breakdownModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-breakdownModal', event => {
                $('#breakdownModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-breakdownEditModal', event => {
                $('#breakdownEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-breakdownEditModal', event => {
                $('#breakdownEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-trailer_linkModal', event => {
                $('#trailer_linkModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trailer_linkModal', event => {
                $('#trailer_linkModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-trailer_linkEditModal', event => {
                $('#trailer_linkEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trailer_linkEditModal', event => {
                $('#trailer_linkEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-fuelTankModal', event => {
                $('#fuelTankModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-fuelTankModal', event => {
                $('#fuelTankModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-logUpdateModal', event => {
                $('#logUpdateModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-logUpdateModal', event => {
                $('#logUpdateModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-logModal', event => {
                $('#logModal').modal('show');
            })
        </script>
       
       <script type="text/javascript">
            window.addEventListener('hide-logModal', event => {
                $('#logModal').modal('hide');
            })
        </script>
       <script type="text/javascript">
            window.addEventListener('show-logEditModal', event => {
                $('#logEditModal').modal('show');
            })
        </script>
       <script type="text/javascript">
            window.addEventListener('hide-logEditModal', event => {
                $('#logEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-cashflowDeleteModal', event => {
                $('#cashflowDeleteModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-cashflowDeleteModal', event => {
                $('#cashflowDeleteModal').modal('hide');
            })
        </script>
        
        <script type="text/javascript">
            window.addEventListener('show-withdrawalModal', event => {
                $('#withdrawalModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-withdrawalModal', event => {
                $('#withdrawalModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-expenseEditModal', event => {
                $('#expenseEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-expenseEditModal', event => {
                $('#expenseEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-depositModal', event => {
                $('#depositModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-depositModal', event => {
                $('#depositModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-incomeEditModal', event => {
                $('#incomeEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-incomeEditModal', event => {
                $('#incomeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-transactionModal', event => {
                $('#transactionModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-transactionModal', event => {
                $('#transactionModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-transactionEditModal', event => {
                $('#transactionEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-transactionEditModal', event => {
                $('#transactionEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-product_serviceModal', event => {
                $('#product_serviceModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-product_serviceModal', event => {
                $('#product_serviceModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-invoice_productEditModal', event => {
                $('#invoice_productEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-invoice_productEditModal', event => {
                $('#invoice_productEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-measurementModal', event => {
                $('#measurementModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-measurementModal', event => {
                $('#measurementModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-measurementEditModal', event => {
                $('#measurementEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-measurementEditModal', event => {
                $('#measurementEditModal').modal('hide');
            })
        </script>
        
        <script type="text/javascript">
            window.addEventListener('show-accountModal', event => {
                $('#accountModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-accountModal', event => {
                $('#accountModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-accountEditModal', event => {
                $('#accountEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-accountEditModal', event => {
                $('#accountEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-account_typeModal', event => {
                $('#account_typeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-account_typeModal', event => {
                $('#account_typeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-account_typeEditModal', event => {
                $('#account_typeEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-account_typeEditModal', event => {
                $('#account_typeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-allowanceModal', event => {
                $('#allowanceModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-allowanceEditModal', event => {
                $('#allowanceEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-allowanceEditModal', event => {
                $('#allowanceEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-loanModal', event => {
                $('#payrloanModalollModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-loanModal', event => {
                $('#loanModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-loanEditModal', event => {
                $('#loanEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-loanEditModal', event => {
                $('#loanEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-payrollModal', event => {
                $('#payrollModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-payrollModal', event => {
                $('#payrollModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-payrollEditModal', event => {
                $('#payrollEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-payrollEditModal', event => {
                $('#payrollEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-salary_itemModal', event => {
                $('#salary_itemModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-salary_itemModal', event => {
                $('#salary_itemModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-salary_itemEditModal', event => {
                $('#salary_itemEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-salary_itemEditModal', event => {
                $('#salary_itemEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-loan_typeModal', event => {
                $('#loan_typeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-loan_typeModal', event => {
                $('#loan_typeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-loan_typeEditModal', event => {
                $('#loan_typeEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-loan_typeEditModal', event => {
                $('#loan_typeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-deductionModal', event => {
                $('#deductionModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-deductionModal', event => {
                $('#deductionModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-deductionEditModal', event => {
                $('#deductionEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-deductionEditModal', event => {
                $('#deductionEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-editCredit_noteItemModal', event => {
                $('#editCredit_noteItemModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-editCredit_noteItemModal', event => {
                $('#editCredit_noteItemModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-bill_expenseModal', event => {
                $('#bill_expenseModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-bill_expenseModal', event => {
                $('#bill_expenseModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-bill_expenseEditModal', event => {
                $('#bill_expenseEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-bill_expenseEditModal', event => {
                $('#bill_expenseEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeDepartmentModal', event => {
                $('#removeDepartmentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeDepartmentModal', event => {
                $('#removeDepartmentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeProductAttributeValueModal', event => {
                $('#removeProductAttributeValueModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeProductAttributeValueModal', event => {
                $('#removeProductAttributeValueModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeAttributeValueModal', event => {
                $('#removeAttributeValueModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeAttributeValueModal', event => {
                $('#removeAttributeValueModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeAttributeModal', event => {
                $('#removeAttributeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeAttributeModal', event => {
                $('#removeAttributeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeProductAttributeModal', event => {
                $('#removeProductAttributeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeProductAttributeModal', event => {
                $('#removeProductAttributeModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-corridor_transporterModal', event => {
                $('#corridor_transporterModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-corridor_transporterModal', event => {
                $('#corridor_transporterModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-corridorDeleteModal', event => {
                $('#corridorDeleteModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-corridorDeleteModal', event => {
                $('#corridorDeleteModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-addCargoModal', event => {
                $('#addCargoModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addCargoModal', event => {
                $('#addCargoModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-editQuotationProductModal', event => {
                $('#editQuotationProductModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-credit_noteAuthorizationModal', event => {
                $('#credit_noteAuthorizationModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-credit_noteAuthorizationModal', event => {
                $('#credit_noteAuthorizationModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-editQuotationProductModal', event => {
                $('#editQuotationProductModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addQuotationProductModal', event => {
                $('#addQuotationProductModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addQuotationProductModal', event => {
                $('#addQuotationProductModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-editInvoiceItemModal', event => {
                $('#editInvoiceItemModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-bill_expenseEditModal', event => {
                $('#bill_expenseEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-bill_expenseEditModal', event => {
                $('#bill_expenseEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-editInvoiceItemModal', event => {
                $('#editInvoiceItemModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addInvoiceItemModal', event => {
                $('#addInvoiceItemModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addInvoiceItemModal', event => {
                $('#addInvoiceItemModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addTransporterModal', event => {
                $('#addTransporterModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addTransporterModal', event => {
                $('#addTransporterModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addBorderModal', event => {
                $('#addBorderModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addBorderModal', event => {
                $('#addBorderModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeQuotationProduct', event => {
                $('#removeQuotationProduct').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeQuotationProduct', event => {
                $('#removeQuotationProduct').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeBorderModal', event => {
                $('#removeBorderModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeBorderModal', event => {
                $('#removeBorderModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-addCorridorModal', event => {
                $('#addCorridorModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-addCorridorModal', event => {
                $('#addCorridorModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeCorridorModal', event => {
                $('#removeCorridorModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeCorridorModal', event => {
                $('#removeCorridorModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeModal', event => {
                $('#removeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeModal', event => {
                $('#removeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-removeRehandlingModal', event => {
                $('#removeRehandlingModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-removeRehandlingModal', event => {
                $('#removeRehandlingModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-trip_groupModal', event => {
                $('#trip_groupModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trip_groupModal', event => {
                $('#trip_groupModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-invoiceAuthorizationModal', event => {
                $('#invoiceAuthorizationModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-invoiceAuthorizationModal', event => {
                $('#invoiceAuthorizationModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-purchaseAuthorizationModal', event => {
                $('#purchaseAuthorizationModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-purchaseAuthorizationModal', event => {
                $('#purchaseAuthorizationModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-clearing_agentModal', event => {
                $('#clearing_agentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-clearing_agentModal', event => {
                $('#clearing_agentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-clearing_agentEditModal', event => {
                $('#clearing_agentEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-clearing_agentEditModal', event => {
                $('#clearing_agentEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-department_headModal', event => {
                $('#department_headModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-department_headModal', event => {
                $('#department_headModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-department_headEditModal', event => {
                $('#department_headEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-department_headEditModal', event => {
                $('#department_headEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-locationModal', event => {
                $('#locationModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-locationModal', event => {
                $('#locationModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-locationEditModal', event => {
                $('#locationEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-locationEditModal', event => {
                $('#locationEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-borderModal', event => {
                $('#borderModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-borderModal', event => {
                $('#borderModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-borderEditModal', event => {
                $('#borderEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-borderEditModal', event => {
                $('#borderEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-corridorModal', event => {
                $('#corridorModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-corridorModal', event => {
                $('#corridorModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-corridorEditModal', event => {
                $('#corridorEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-corridorEditModal', event => {
                $('#corridorEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-income_streamModal', event => {
                $('#income_streamModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-income_streamModal', event => {
                $('#income_streamModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-income_streamEditModal', event => {
                $('#income_streamEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-income_streamEditModal', event => {
                $('#income_streamEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-expense_categoryModal', event => {
                $('#expense_categoryModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-expense_categoryModal', event => {
                $('#expense_categoryModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-expense_categoryEditModal', event => {
                $('#expense_categoryEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-expense_categoryEditModal', event => {
                $('#expense_categoryEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-bank_accountModal', event => {
                $('#bank_accountModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-bank_accountModal', event => {
                $('#bank_accountModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-bank_accountEditModal', event => {
                $('#bank_accountEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-bank_accountEditModal', event => {
                $('#bank_accountEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-paymentModal', event => {
                $('#paymentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-paymentModal', event => {
                $('#paymentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-paymentEditModal', event => {
                $('#paymentEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-paymentEditModal', event => {
                $('#paymentEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-agentModal', event => {
                $('#agentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-agentModal', event => {
                $('#agentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-agentEditModal', event => {
                $('#agentEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-agentEditModal', event => {
                $('#agentEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-trip_groupModal', event => {
                $('#trip_groupModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trip_groupModal', event => {
                $('#trip_groupModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-trip_groupEditModal', event => {
                $('#trip_groupEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trip_groupEditModal', event => {
                $('#trip_groupEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-contactModal', event => {
                $('#contactModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-contactModal', event => {
                $('#contactModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-contactEditModal', event => {
                $('#contactEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-contactEditModal', event => {
                $('#contactEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-routeModal', event => {
                $('#routeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-routeModal', event => {
                $('#routeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-routeEditModal', event => {
                $('#routeEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-routeEditModal', event => {
                $('#routeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-truck_stopModal', event => {
                $('#truck_stopModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-truck_stopModal', event => {
                $('#truck_stopModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-truck_stopEditModal', event => {
                $('#truck_stopEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-truck_stopEditModal', event => {
                $('#truck_stopEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-transporterModal', event => {
                $('#transporterModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-transporterModal', event => {
                $('#transporterModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-transporterEditModal', event => {
                $('#transporterEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-transporterEditModal', event => {
                $('#transporterEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-tyre_assignmentModal', event => {
                $('#tyre_assignmentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-tyre_assignmentModal', event => {
                $('#tyre_assignmentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-tyre_assignmentEditModal', event => {
                $('#tyre_assignmentEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-tyre_assignmentEditModal', event => {
                $('#tyre_assignmentEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-tyre_detailEditModal', event => {
                $('#tyre_detailEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-tyre_detailEditModal', event => {
                $('#tyre_detailEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-asset_documentsModal', event => {
                $('#asset_documentsModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-asset_documentsModal', event => {
                $('#asset_documentsModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-asset_documentsEditModal', event => {
                $('#asset_documentsEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-asset_documentsEditModal', event => {
                $('#asset_documentsEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-asset_detailsEditModal', event => {
                $('#asset_detailsEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-asset_detailsEditModal', event => {
                $('#asset_detailsEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-purchaseQuotationsModal', event => {
                $('#purchaseQuotationsModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-purchaseQuotationsModal', event => {
                $('#purchaseQuotationsModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-purchaseQuotationsEditModal', event => {
                $('#purchaseQuotationsEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-purchaseQuotationsEditModal', event => {
                $('#purchaseQuotationsEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-purchaseModal', event => {
                $('#purchaseModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-purchaseModal', event => {
                $('#purchaseModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-purchaseEditModal', event => {
                $('#purchaseEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-purchaseEditModal', event => {
                $('#purchaseEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-ticket_expenseModal', event => {
                $('#ticket_expenseModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-ticket_expenseModal', event => {
                $('#ticket_expenseModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-ticket_expenseEditModal', event => {
                $('#ticket_expenseEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-ticket_expenseEditModal', event => {
                $('#ticket_expenseEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-stockModal', event => {
                $('#stockModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-stockModal', event => {
                $('#stockModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-stockEditModal', event => {
                $('#stockEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-stockEditModal', event => {
                $('#stockEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-jobModal', event => {
                $('#jobModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-jobModal', event => {
                $('#jobModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-jobEditModal', event => {
                $('#jobEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-jobEditModal', event => {
                $('#jobEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-job_typeModal', event => {
                $('#job_typeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-job_typeModal', event => {
                $('#job_typeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-job_typeEditModal', event => {
                $('#job_typeEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-job_typeEditModal', event => {
                $('#job_typeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inspectionModal', event => {
                $('#inspectionModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inspectionModal', event => {
                $('#inspectionModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inspectionEditModal', event => {
                $('#inspectionEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inspectionEditModal', event => {
                $('#inspectionEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-job_inventoryModal', event => {
                $('#job_inventoryModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-job_inventoryModal', event => {
                $('#job_inventoryModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-job_inventoryEditModal', event => {
                $('#job_inventoryEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-job_inventoryEditModal', event => {
                $('#job_inventoryEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inspection_groupModal', event => {
                $('#inspection_groupModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inspection_groupModal', event => {
                $('#inspection_groupModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inspection_groupEditModal', event => {
                $('#inspection_groupEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inspection_groupEditModal', event => {
                $('#inspection_groupEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inspection_typeModal', event => {
                $('#inspection_typeModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inspection_typeModal', event => {
                $('#inspection_typeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-inspection_typeEditModal', event => {
                $('#inspection_typeEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-inspection_typeEditModal', event => {
                $('#inspection_typeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-receiptModal', event => {
                $('#receiptModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-receiptModal', event => {
                $('#receiptModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-receiptEditModal', event => {
                $('#receiptEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-receiptEditModal', event => {
                $('#receiptEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-asset_assignmentModal', event => {
                $('#asset_assignmentModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-asset_assignmentModal', event => {
                $('#asset_assignmentModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-asset_assignmentEditModal', event => {
                $('#asset_assignmentEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-asset_assignmentEditModal', event => {
                $('#asset_assignmentEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-categoryValueModal', event => {
                $('#categoryValueModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-categoryValueModal', event => {
                $('#categoryValueModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-categoryValueEditModal', event => {
                $('#categoryValueEditModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-categoryValueEditModal', event => {
                $('#categoryValueEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-fuelTopupModal', event => {
                $('#fuelTopupModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-fuelTopupModal', event => {
                $('#fuelTopupModal').modal('hide');
            })
        </script>


        <script type="text/javascript">
            window.addEventListener('show-receiptUploadModal', event => {
                $('#receiptUploadModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-receiptUploadModal', event => {
                $('#receiptUploadModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-updateEmployeeLeaveDaysModal', event => {
                $('#updateEmployeeLeaveDaysModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-updateEmployeeLeaveDaysModal', event => {
                $('#updateEmployeeLeaveDaysModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-logoModal', event => {
                $('#logoModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-trailer_groupModal', event => {
                $('#trailer_groupModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-trailer_groupEditModal', event => {
                $('#trailer_groupEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-trailer_groupEditModal', event => {
                $('#trailer_groupEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-trailer_typeModal', event => {
                $('#trailer_typeModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-trailer_typeEditModal', event => {
                $('#trailer_typeEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-trailer_typeEditModal', event => {
                $('#trailer_typeEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-horse_modelModal', event => {
                $('#horse_modelModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-horse_modelEditModal', event => {
                $('#horse_modelEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-horse_modelEditModal', event => {
                $('#horse_modelEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-horse_makeModal', event => {
                $('#horse_makeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-horse_makeEditModal', event => {
                $('#horse_makeEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-horse_makeEditModal', event => {
                $('#horse_makeEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-vehicle_modelModal', event => {
                $('#vehicle_modelModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-vehicle_modelEditModal', event => {
                $('#vehicle_modelEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-vehicle_modelEditModal', event => {
                $('#vehicle_modelEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-vehicle_makeModal', event => {
                $('#vehicle_makeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-vehicle_makeEditModal', event => {
                $('#vehicle_makeEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-vehicle_makeEditModal', event => {
                $('#vehicle_makeEditModal').modal('hide');
            })
        </script>


        <script type="text/javascript">
            window.addEventListener('hide-horse_typeModal', event => {
                $('#horse_typeModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-horse_typeEditModal', event => {
                $('#horse_typeEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-horse_typeEditModal', event => {
                $('#horse_typeEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-horse_groupModal', event => {
                $('#horse_groupModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-horse_groupEditModal', event => {
                $('#horse_groupEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-horse_groupEditModal', event => {
                $('#horse_groupEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-horseModal', event => {
                $('#horseModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-horseEditModal', event => {
                $('#horseEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-horseEditModal', event => {
                $('#horseEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-companyModal', event => {
                $('#companyModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-companyEditModal', event => {
                $('#companyEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-companyEditModal', event => {
                $('#companyEditModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-branchModal', event => {
                $('#branchModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-branchEditModal', event => {
                $('#branchEditModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-branchEditModal', event => {
                $('#branchEditModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-departmentModal', event => {
                $('#departmentModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-departmentEditModal', event => {
                $('#departmentEditModal').modal('show');
            })
        </script>
         <script type="text/javascript">
            window.addEventListener('hide-departmentEditModal', event => {
                $('#departmentEditModal').modal('hide');
            })
            </script>


<script type="text/javascript">
    window.addEventListener('show-gateModal', event => {
        $('#gateModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-gateModal', event => {
        $('#gateModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-gateEditModal', event => {
        $('#gateEditModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-gateEditModal', event => {
        $('#gateEditModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-visitorModal', event => {
        $('#visitorModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-visitorModal', event => {
        $('#visitorModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-visitorEditModal', event => {
        $('#visitorEditModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-visitorEditModal', event => {
        $('#visitorEditModal').modal('hide');
    })
</script>

<script type="text/javascript">
    window.addEventListener('show-gate_passModal', event => {
        $('#gate_passModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-gate_passModal', event => {
        $('#gate_passModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-gate_passEditModal', event => {
        $('#gate_passEditModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-gate_passEditModal', event => {
        $('#gate_passEditModal').modal('hide');
    })
</script>


        <script type="text/javascript">
            window.addEventListener('hide-leaveTypeModal', event => {
                $('#leaveTypeModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-leaveTypeEditModal', event => {
                $('#leaveTypeEditModal').modal('show');
            })
            </script>

       <script type="text/javascript">
        window.addEventListener('hide-leaveTypeEditModal', event => {
            $('#leaveTypeEditModal').modal('hide');
        })
        </script>

        <script type="text/javascript">
            window.addEventListener('hide-employeeModal', event => {
                $('#employeeModal').modal('hide');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-employeeModal', event => {
                $('#employeeModal').modal('show');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-employeeEditModal', event => {
                $('#employeeEditModal').modal('show');
            })
            </script>

       <script type="text/javascript">
        window.addEventListener('hide-employeeEditModal', event => {
            $('#employeeEditModal').modal('hide');
        })
        </script>
        <script type="text/javascript">
            window.addEventListener('show-leaveModal', event => {
                $('#leaveModal').modal('show');
            })
        </script>
        <script type="text/javascript">
            window.addEventListener('hide-leaveModal', event => {
                $('#leaveModal').modal('hide');
            })
        </script>

        <script type="text/javascript">
            window.addEventListener('show-leaveEditModal', event => {
                $('#leaveEditModal').modal('show');
            })
            </script>

       <script type="text/javascript">
        window.addEventListener('hide-leaveEditModal', event => {
            $('#leaveEditModal').modal('hide');
        })
        </script>


    <script type="text/javascript">
        window.addEventListener('hide-cargoModal', event => {
            $('#cargoModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-cargoEditModal', event => {
            $('#cargoEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-cargoEditModal', event => {
        $('#cargoEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-service_typeModal', event => {
            $('#service_typeModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-service_typeEditModal', event => {
            $('#service_typeEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-service_typeEditModal', event => {
        $('#service_typeEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-destinationModal', event => {
            $('#destinationModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-destinationEditModal', event => {
            $('#destinationEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-destinationEditModal', event => {
        $('#destinationEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-countryModal', event => {
            $('#countryModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-countryEditModal', event => {
            $('#countryEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-countryEditModal', event => {
        $('#countryEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-vehicle_groupModal', event => {
            $('#vehicle_groupModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-vehicle_groupEditModal', event => {
            $('#vehicle_groupEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-vehicle_groupEditModal', event => {
        $('#vehicle_groupEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-vehicle_typeModal', event => {
            $('#vehicle_typeModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-vehicle_typeEditModal', event => {
            $('#vehicle_typeEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-vehicle_typeEditModal', event => {
        $('#vehicle_typeEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-vendor_typeModal', event => {
            $('#vendor_typeModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-vendor_typeEditModal', event => {
            $('#vendor_typeEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-vendor_typeEditModal', event => {
        $('#vendor_typeEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-vehicle_assignmentModal', event => {
            $('#vehicle_assignmentModal').modal('hide');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-vehicle_assignmentEditModal', event => {
            $('#vehicle_assignmentEditModal').modal('hide');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('show-vehicle_assignmentEditModal', event => {
            $('#vehicle_assignmentEditModal').modal('show');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-assignmentModal', event => {
            $('#assignmentModal').modal('hide');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('show-unAssignmentModal', event => {
            $('#unAssignmentModal').modal('show');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-unAssignmentModal', event => {
            $('#unAssignmentModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-assignmentEditModal', event => {
            $('#assignmentEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-assignmentEditModal', event => {
        $('#assignmentEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-customerModal', event => {
            $('#customerModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-customerEditModal', event => {
            $('#customerEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-customerEditModal', event => {
        $('#customerEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-vendorModal', event => {
            $('#vendorModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-vendorEditModal', event => {
            $('#vendorEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-vendorEditModal', event => {
        $('#vendorEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-fuelModal', event => {
            $('#fuelModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-fuelEditModal', event => {
            $('#fuelEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-fuelEditModal', event => {
        $('#fuelEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-trip_typeModal', event => {
            $('#trip_typeModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-trip_typeEditModal', event => {
            $('#trip_typeEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-trip_typeEditModal', event => {
        $('#trip_typeEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-brokerModal', event => {
            $('#brokerModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-brokerEditModal', event => {
            $('#brokerEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-brokerEditModal', event => {
        $('#brokerEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-currencyModal', event => {
            $('#currencyModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-currencyEditModal', event => {
            $('#currencyEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-currencyEditModal', event => {
        $('#currencyEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-fitnessModal', event => {
            $('#fitnessModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-fitnessEditModal', event => {
            $('#fitnessEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-fitnessEditModal', event => {
        $('#fitnessEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-fuel_requestModal', event => {
            $('#fuel_requestModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-fuel_requestEditModal', event => {
            $('#fuel_requestEditModal').modal('show');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('show-fuel_requestDecisionModal', event => {
            $('#fuel_requestDecisionModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-fuel_requestEditModal', event => {
        $('#fuel_requestEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
    window.addEventListener('hide-fuel_requestDecisionModal', event => {
        $('#fuel_requestDecisionModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-containerModal', event => {
            $('#containerModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-containerEditModal', event => {
            $('#containerEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-containerEditModal', event => {
        $('#containerEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-top_upModal', event => {
            $('#top_upModal').modal('hide');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('show-top_upModal', event => {
            $('#top_upModal').modal('show');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-top_upEditModal', event => {
            $('#top_upEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-top_upEditModal', event => {
        $('#top_upEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-allocationModal', event => {
            $('#allocationModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-allocationEditModal', event => {
            $('#allocationEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-allocationEditModal', event => {
        $('#allocationEditModal').modal('hide');
    })
    </script>


    <script type="text/javascript">
        window.addEventListener('hide-job_titleModal', event => {
            $('#job_titleModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-job_titleEditModal', event => {
            $('#job_titleEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-job_titleEditModal', event => {
        $('#job_titleEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-brandModal', event => {
            $('#brandModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-brandEditModal', event => {
            $('#brandEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-brandEditModal', event => {
        $('#brandEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-categoryModal', event => {
            $('#categoryModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-categoryEditModal', event => {
            $('#categoryEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-categoryEditModal', event => {
        $('#categoryEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-attributeValueEditModal', event => {
            $('#attributeValueEditModal').modal('show');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-attributeValueEditModal', event => {
            $('#attributeValueEditModal').modal('hide');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('show-attributeValueModal', event => {
            $('#attributeValueModal').modal('show');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-attributeValueModal', event => {
            $('#attributeValueModal').modal('hide');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-attributeModal', event => {
            $('#attributeModal').modal('hide');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('hide-attributeModal', event => {
            $('#attributeModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-attributeEditModal', event => {
            $('#attributeEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-attributeEditModal', event => {
        $('#attributeEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-storeModal', event => {
            $('#storeModal').modal('hide');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-storeEditModal', event => {
            $('#storeEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-storeEditModal', event => {
        $('#storeEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
        window.addEventListener('hide-valueModal', event => {
            $('#valueModal').modal('hide');
        })
    </script>
    <script type="text/javascript">
        window.addEventListener('show-valueModal', event => {
            $('#valueModal').modal('show');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-valueEditModal', event => {
            $('#valueEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-valueEditModal', event => {
        $('#valueEditModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('show-vehicleMakeModal', event => {
            $('#vehicleMakeModal').modal('show');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-vehicleMakeEditModal', event => {
            $('#vehicleMakeEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-vehicleMakeEditModal', event => {
        $('#vehicleMakeEditModal').modal('hide');
    })
    </script>

<script type="text/javascript">
    window.addEventListener('hide-vehicleModelModal', event => {
        $('#vehicleModelModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
        window.addEventListener('show-vehicleModelModal', event => {
            $('#vehicleModelModal').modal('show');
        })
    </script>

    <script type="text/javascript">
        window.addEventListener('show-vehicleModelEditModal', event => {
            $('#vehicleModelEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-vehicleModelEditModal', event => {
        $('#vehicleModelEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-vehicleModelEditModal', event => {
        $('#vehicleModelEditModal').modal('show');
    })
    </script>

   <script type="text/javascript">
        window.addEventListener('hide-productModal', event => {
            $('#productModal').modal('hide');
        })
    </script>
   <script type="text/javascript">
        window.addEventListener('show-productEditModal', event => {
            $('#productEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-productEditModal', event => {
        $('#productEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-loadingPointModal', event => {
        $('#loadingPointModal').modal('hide');
    })
    </script>
   <script type="text/javascript">
        window.addEventListener('show-loadingPointEditModal', event => {
            $('#loadingPointEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-loadingPointEditModal', event => {
        $('#loadingPointEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-offloadingPointModal', event => {
        $('#offloadingPointModal').modal('hide');
    })
    </script>
   <script type="text/javascript">
        window.addEventListener('show-offloadingPointEditModal', event => {
            $('#offloadingPointEditModal').modal('show');
        })
    </script>

    <script type="text/javascript">
    window.addEventListener('hide-offloadingPointEditModal', event => {
        $('#offloadingPointEditModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
    window.addEventListener('show-authorizationModal', event => {
        $('#authorizationModal').modal('show');
    })
    </script>
    <script type="text/javascript">
    window.addEventListener('hide-authorizationModal', event => {
        $('#authorizationModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
    window.addEventListener('show-fuelAuthorizationModal', event => {
        $('#fuelAuthorizationModal').modal('show');
    })
    </script>
    <script type="text/javascript">
    window.addEventListener('hide-fuelAuthorizationModal', event => {
        $('#fuelAuthorizationModal').modal('hide');
    })
    </script>
    <script type="text/javascript">
    window.addEventListener('show-statusModal', event => {
        $('#statusModal').modal('show');
    })
    </script>
    <script type="text/javascript">
    window.addEventListener('hide-statusModal', event => {
        $('#statusModal').modal('hide');
    })
    </script>

   <script type="text/javascript">
    window.addEventListener('show-paymentStatusModal', event => {
        $('#paymentStatusModal').modal('show');
    })
    </script>
    <script type="text/javascript">
    window.addEventListener('hide-paymentStatusModal', event => {
        $('#paymentStatusModal').modal('hide');
    })
    </script>

    <script type="text/javascript">
    window.addEventListener('show-tripDocumentsModal', event => {
        $('#tripDocumentsModal').modal('show');
    })
    </script>
    <script type="text/javascript">
    window.addEventListener('hide-tripDocumentsModal', event => {
        $('#tripDocumentsModal').modal('hide');
    })
    </script>



<script type="text/javascript">
    window.addEventListener('hide-trailerModal', event => {
        $('#trailerModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-trailerEditModal', event => {
        $('#trailerEditModal').modal('show');
    })
</script>

<script type="text/javascript">
window.addEventListener('hide-trailerEditModal', event => {
    $('#trailerEditModal').modal('hide');
})
</script>

<script type="text/javascript">
    window.addEventListener('show-fuelRestoreModal', event => {
        $('#fuelRestoreModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-bookingRestoreModal', event => {
        $('#bookingRestoreModal').modal('show');
    })
</script>

<script type="text/javascript">
    window.addEventListener('show-employeeRestoreModal', event => {
        $('#employeeRestoreModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-driverRestoreModal', event => {
        $('#driverRestoreModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-tripRestoreModal', event => {
        $('#tripRestoreModal').modal('show');
    })
</script>

<script type="text/javascript">
window.addEventListener('hide-fuelRestoreModal', event => {
    $('#fuelRestoreModal').modal('hide');
})
</script>

<script type="text/javascript">
window.addEventListener('show-expenseModal', event => {
    $('#expenseModal').modal('show');
})
</script>

<script type="text/javascript">
window.addEventListener('hide-expenseModal', event => {
    $('#expenseModal').modal('hide');
})
</script>

<script type="text/javascript">
window.addEventListener('hide-expenseEditModal', event => {
    $('#expenseEditModal').modal('hide');
})
</script>

<script type="text/javascript">
window.addEventListener('show-expenseEditModal', event => {
    $('#expenseEditModal').modal('show');
})
</script>

<script type="text/javascript">
window.addEventListener('show-quotationModal', event => {
    $('#quotationModal').modal('show');
})
</script>
<script type="text/javascript">
window.addEventListener('hide-quotationModal', event => {
    $('#quotationModal').modal('hide');
})
</script>
<script type="text/javascript">
window.addEventListener('show-quotationEditModal', event => {
    $('#quotationEditModal').modal('show');
})
</script>
<script type="text/javascript">
window.addEventListener('hide-quotationEditModal', event => {
    $('#quotationEditModal').modal('hide');
})
</script>

<script type="text/javascript">
window.addEventListener('hide-fuelRequestAuthorizationModal', event => {
    $('#fuelRequestAuthorizationModal').modal('hide');
})
</script>
<script type="text/javascript">
window.addEventListener('show-fuelRequestAuthorizationModal', event => {
    $('#fuelRequestAuthorizationModal').modal('show');
})
</script>

<script type="text/javascript">
window.addEventListener('hide-documentModal', event => {
    $('#documentModal').modal('hide');
})
</script>
<script type="text/javascript">
    window.addEventListener('hide-documentEditModal', event => {
        $('#documentEditModal').modal('hide');
    })
    </script>

<script type="text/javascript">
window.addEventListener('show-documentEditModal', event => {
    $('#documentEditModal').modal('show');
})
</script>
<script type="text/javascript">
window.addEventListener('hide-vehicleDocumentEditModal', event => {
    $('#vehicleDocumentEditModal').modal('hide');
})
</script>
<script type="text/javascript">
window.addEventListener('show-vehicleDocumentEditModal', event => {
    $('#vehicleDocumentEditModal').modal('show');
})
</script>
<script type="text/javascript">
window.addEventListener('hide-trailerDocumentEditModal', event => {
    $('#trailerDocumentEditModal').modal('hide');
})
</script>
<script type="text/javascript">
window.addEventListener('hide-tripDocumentEditModal', event => {
    $('#tripDocumentEditModal').modal('hide');
})
</script>
<script type="text/javascript">
window.addEventListener('show-tripDocumentEditModal', event => {
    $('#tripDocumentEditModal').modal('show');
})
</script>
<script type="text/javascript">
window.addEventListener('hide-tripExpenseEditModal', event => {
    $('#tripExpenseEditModal').modal('hide');
})
</script>
<script type="text/javascript">
window.addEventListener('hide-tripExpenseModal', event => {
    $('#tripExpenseModal').modal('hide');
})
</script>
<script type="text/javascript">
window.addEventListener('show-tripExpenseEditModal', event => {
    $('#tripExpenseEditModal').modal('show');
})
</script>
<script type="text/javascript">
window.addEventListener('hide-trailerDocumentEditModal', event => {
    $('#trailerDocumentEditModal').modal('hide');
})
</script>

<script type="text/javascript">
window.addEventListener('show-trailerDocumentEditModal', event => {
    $('#trailerDocumentEditModal').modal('show');
})
</script>

<script type="text/javascript">
window.addEventListener('hide-imageModal', event => {
    $('#imageModal').modal('hide');
})
</script>

<script type="text/javascript">
    window.addEventListener('show-decisionModal', event => {
        $('#decisionModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-decisionModal', event => {
        $('#decisionModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-closeTicketModal', event => {
        $('#closeTicketModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-closeTicketModal', event => {
        $('#closeTicketModal').modal('hide');
    })
</script>

<script type="text/javascript">
    window.addEventListener('show-saveModal', event => {
        $('#saveModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-saveModal', event => {
        $('#saveModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-updateModal', event => {
        $('#updateModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-updateModal', event => {
        $('#updateModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-journalModal', event => {
        $('#journalModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-journalModal', event => {
        $('#journalModal').modal('hide');
    })
</script>
<script type="text/javascript">
    window.addEventListener('show-journalEditModal', event => {
        $('#journalEditModal').modal('show');
    })
</script>
<script type="text/javascript">
    window.addEventListener('hide-journalEditModal', event => {
        $('#journalEditModal').modal('hide');
    })
</script>

<script>
    ClassicEditor
        .create( document.querySelector( '#editor' ) )
        .catch( error => {
            console.error( error );
        } );
</script>
@stack('custom-scripts')
       <script>
        function goBack() {
          window.history.back();
        }
        </script>

    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> --}}

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

   <script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById('sidebar');
        // console.log("Script loaded, sidebar:", sidebar); 

        if (sidebar) {
            // Restore previous scroll position
            const savedScroll = localStorage.getItem('sidebar-scroll');
            if (savedScroll !== null) {
                sidebar.scrollTop = parseInt(savedScroll, 10);
                console.log("Restored sidebar scroll to:", savedScroll); // You should see this
            }

            // Save new scroll position on scroll
            sidebar.addEventListener('scroll', function() {
                localStorage.setItem('sidebar-scroll', sidebar.scrollTop);
                console.log("Saved sidebar scroll:", sidebar.scrollTop);
            });

            // Livewire safety restore
            if (window.Livewire) {
                window.Livewire.hook('message.processed', () => {
                    const savedScroll = localStorage.getItem('sidebar-scroll');
                    if (savedScroll !== null) {
                        sidebar.scrollTop = parseInt(savedScroll, 10);
                        console.log("Livewire restored sidebar scroll to:", savedScroll);
                    }
                });
            }
        } else {
            console.warn("Sidebar element not found.");
        }
    });
</script>
    <script>
(function () {
  const sidebar = document.getElementById('sidebar');
  if (!sidebar) return;

  const key = 'sidebar.scrollTop';

  // Restore scroll
  const saved = localStorage.getItem(key);
  if (saved !== null) sidebar.scrollTop = parseInt(saved, 10) || 0;

  // Save scroll (throttled)
  let t = null;
  sidebar.addEventListener('scroll', function () {
    if (t) return;
    t = setTimeout(function () {
      localStorage.setItem(key, String(sidebar.scrollTop));
      t = null;
    }, 150);
  });
})();

</script>
<script>
    document.querySelectorAll('#sidebar li.active').forEach(activeLi => {
  const parentGroup = activeLi.closest('li.has-children[data-menu-key]');
  if (parentGroup) {
    parentGroup.classList.add('open');
    const child = parentGroup.querySelector('.child-nav');
    if (child) child.style.display = 'block';
  }
});
</script>

@auth
<script>
(function () {
    if (! navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            fetch('/login/location', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    lat:      pos.coords.latitude,
                    lng:      pos.coords.longitude,
                    accuracy: Math.round(pos.coords.accuracy)
                })
            });
        },
        function () {},
        { enableHighAccuracy: true, timeout: 10000 }
    );
})();
</script>
@endauth


    
</body>
</html>
