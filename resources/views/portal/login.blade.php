@extends('layouts.customer_portal')

@section('title', 'Freight Portal Login')

@section('content')
    <div class="row">
        <div class="col-md-4 col-md-offset-4" style="margin-top: 60px;">
            <div class="panel">
                <div class="panel-heading">
                    <div class="panel-title"><h5>Freight Portal Login</h5></div>
                </div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('customer.postLogin') }}">
                        @csrf
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="form-group">
                            <label>PIN</label>
                            <input type="password" name="password" class="form-control" maxlength="4" inputmode="numeric" pattern="[0-9]*" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
