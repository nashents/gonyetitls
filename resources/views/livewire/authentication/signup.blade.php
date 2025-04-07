<div>
    <x-loading/>
    <form wire:submit.prevent="store()" >
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" wire:model.debounce.300ms="name" class="form-control input-lg"  placeholder="Enter Your Name" autocomplete="off" required>
            @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="surname">Surname</label>
            <input type="text" wire:model.debounce.300ms="surname" class="form-control input-lg"  placeholder="Enter Your Surname" autocomplete="off" required>
            @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
            <div class="form-group">
               <label for="gender">Gender</label>
                    <div class="col-sm-10">
                       <div class="radio">
                           <label>
                           <input type="radio" wire:model.debounce.300ms="gender" id="optionsRadios1" value="male" required>
                           Male
                           </label>
                       </div>
                       <div class="radio">
                           <label>
                           <input type="radio"  wire:model.debounce.300ms="gender"  id="optionsRadios2" value="female" required>
                           Female
                           </label>
                       </div>
                   </div>
                   @error('gender') <span class="text-danger error">{{ $message }}</span>@enderror
               </div>
    <br><br>
    <br>
        <div class="form-group">
            <label for="dob">DOB</label>
            <input type="date" wire:model.debounce.300ms="dob" class="form-control input-lg"  placeholder="Enter Date of Birth" required>
            @error('dob') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Email Address</label>
            <input type="email" wire:model.debounce.300ms="email" class="form-control input-lg"  placeholder="Enter Your Email" autocomplete="off" required>
            @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Phonenumber</label>
            <input type="number" wire:model.debounce.300ms="phonenumber" class="form-control input-lg"  placeholder="Enter Your Phonenumber" required>
            @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="exampleInputEmail1">IDNumber</label>
            <input type="text" wire:model.debounce.300ms="idnumber" class="form-control input-lg"  placeholder="Enter Your IDNumber" required>
            @error('idnumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Post</label>
            <input type="text" wire:model.debounce.300ms="post" class="form-control input-lg"  placeholder="Enter Your Post" required>
            @error('post') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Start Date</label>
            <input type="date" wire:model.debounce.300ms="start_date" class="form-control input-lg"  placeholder="Enter Your Start Date" >
            @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Country</label>
            <input type="text" wire:model.debounce.300ms="country" class="form-control input-lg"  placeholder="Enter Your Country" required>
            @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Province</label>
            <select wire:model.debounce.300ms="province" class="form-control" required>
                <option value="" selected>Select Province</option>
                <option value="Harare">Harare</option>
                <option value="Bulawayo">Bulawayo</option>
                <option value="Manicaland">Manicaland</option>
                <option value="Mashonaland Central">Mashonaland Central</option>
                <option value="Mashonaland East">Mashonaland East</option>
                <option value="Mashonaland West">Mashonaland West</option>
                <option value="Midlands">Midlands</option>
                <option value="Masvingo">Masvingo</option>
                <option value="Matebeleland North">Matebeleland North</option>
                <option value="Matebeleland South">Matebeleland South</option>
            </select>
            @error('province') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">City</label>
            <input type="text" wire:model.debounce.300ms="city" class="form-control input-lg"  placeholder="Enter Your City" required>
            @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Suburb</label>
            <input type="text" wire:model.debounce.300ms="suburb" class="form-control input-lg"  placeholder="Enter Your Suburb" required>
            @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Street Address</label>
            <input type="text" wire:model.debounce.300ms="street_address" class="form-control input-lg"  placeholder="Enter Your Street Address" required>
            @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="exampleInputEmail1">Departments</label>
            <select wire:model.debounce.300ms="department_id" class="form-control" required>
                <option value="" selected>Select Department</option>
                @foreach ($departments as $department)
                    <option value="{{$department->id}}">{{$department->name}}</option>
                @endforeach
            </select>
            @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Branches</label>
            <select wire:model.lazy="branch_id" class="form-control" required>
                <option value="" selected>Select Branch</option>
                @foreach ($branches as $branch)
                    <option value="{{$branch->id}}">{{$branch->name}}</option>
                @endforeach
            </select>
            @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Roles</label>
            <select wire:model.lazy="role_id" class="form-control" multiple="multiple" required>
                <option value="" selected>Select Role</option>
                @foreach ($roles as $role)
                    <option value="{{$role->id}}">{{$role->name}}</option>
                @endforeach
            </select>
            @error('role_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Ranks</label>
            <select wire:model.lazy="rank_id" class="form-control" required>
                <option value="" selected>Select Rank</option>
                @foreach ($ranks as $rank)
                    <option value="{{$rank->id}}">{{$rank->name}}</option>
                @endforeach
            </select>
            @error('rank_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Companies</label>
            <select wire:model.debounce.300ms="company_id" class="form-control" required>
                <option value="" selected>Select Company</option>
                @foreach ($companies as $company)
                    <option value="{{$company->id}}">{{$company->name}}</option>
                @endforeach
            </select>
            @error('company_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" wire:model.debounce.300ms="password" class="form-control input-lg" id="exampleInputPassword1" placeholder="Password" required>
            @error('password') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Password Confirmation</label>
            <input type="password" wire:model.debounce.300ms="password_confirmation" class="form-control input-lg" id="exampleInputPassword1" placeholder=" Confirm Password" required>
            @error('password_confirmation') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>

        <div class="form-group mt-20">
            <div class="">
                <a href="{{route('login')}}" class="form-link"><small class="muted-text">Already a member?</small></a>
                <button type="submit" class="btn btn-success btn-labeled pull-right">Signup<span class="btn-label btn-label-right"><i class="fa fa-check"></i></span></button>
                <div class="clearfix"></div>
            </div>
        </div>
    </form>
</div>
