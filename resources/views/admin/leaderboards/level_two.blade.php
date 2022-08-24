@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <p>{{ $msg ?? '' }}</p>
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Leaderboard Level Two</h2>
                <h5 class="mb-3 text-primary">{{ $leveloneName->lb_name }}</h5>
                <form action="{{ route('lb.level.two.post') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="mb-2 fw-bold">Select Existing Leaderboard List*</label>
                        <select name="lb_name_exist" class="form-control @error('lb_name_exist') is-invalid @enderror">
                            <option value="">--Select--</option>
                            @foreach ($level_two as $lvo)
                                <option value="{{ $lvo->id }}">{{ $lvo->lb_two_name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('lb_name_exist'))
                            <span class="text-danger">{{ $errors->first('lb_name_exist') }}</span>
                        @endif
                    </div>



                    <input type="hidden" name="category" value="{{ $leveltwo }}">

                    <p class="fw-bold mt-3">Or,</p>

                    <div class="form-group mt-3">
                        <label class="fw-bold mb-2">New Leaderboard List*</label>
                        <input type="text" name="lb_name" class="form-control @error('lb_name') is-invalid @enderror" placeholder="">
                         @if ($errors->has('lb_name'))
                            <span class="text-danger">{{ $errors->first('lb_name') }}</span>
                        @endif
                    </div>

                     <label class="mt-4">Highest Discount</label>
                    <input type="text" required class="form-control" value="0" name="discount">

                    <div class="form-group mt-3">
                        <label class="fw-bold mb-2">Order*</label>
                        <input type="text" name="lb_order_no" class="form-control @error('lb_order_no') is-invalid @enderror" placeholder="">
                         @if ($errors->has('lb_order_no'))
                            <span class="text-danger">{{ $errors->first('lb_order_no') }}</span>
                        @endif
                    </div>



                    <div class="d-grid gap-2 mx-auto col-5">
                        <button type="submit" class="btn btn-warning fw-bold mt-3">Next</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <table class="table table-bordered table striped">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Level Two Leaderboard List</th>
                        <th>Order</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($level_two as $two)
                    <tr>
                       <td>
                            <a href="{{ route('edit.level', [$two->id, $leveltwo, 'two']) }}">Edit</a>
                            &nbsp;
                            <a onclick="return confirm('Are you sure?')" href="{{ route('delete.level', [$two->id, $leveltwo, 'two']) }}" class="text-danger">Delete</a>
                        </td>
                        <td>{{ $two->lb_two_name }}</td>
                        <td>{{ $two->lb_two_order_no }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>




@include('admin.layouts.footer')
