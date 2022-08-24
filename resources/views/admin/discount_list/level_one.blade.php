@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <p>{{ $msg ?? '' }}</p>
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Discount Leaderboard Levels</h2>
                <h5 class="mb-3 text-primary">{{ $category_name->name }}</h5>
                @if($errors->any())
                    <div class="alert bg-danger">
                        <span class="text-light fw-bold fs-5">**{{$errors->first()}}</span>
                    </div>
                @endif
                <form action="{{ route('discount.list.level.one.post') }}" method="POST">
                    @csrf

                    <input type="hidden" name="did" value="{{ $did }}">
                    <div class="form-group">
                        <label class="mb-2 fw-bold">Level One</label>
                        <select onchange="level_two_select(this.value)" name="one" class="form-control @error('lb_name_exist') is-invalid @enderror">
                            <option value="">--Select--</option>
                            @foreach ($level_one as $lvo)
                                <option <?php echo (isset($_GET['two']) && $_GET['two'] == $lvo->id ? 'selected' : ''); ?> value="{{ $lvo->id }}">{{ $lvo->lb_name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('lb_name_exist'))
                            <span class="text-danger">{{ $errors->first('lb_name_exist') }}</span>
                        @endif
                    </div>

                    @if (isset($_GET['two']))
                    <div class="form-group">
                        <label class="mb-2 fw-bold">Level Two</label>
                        <select onchange="level_three_select(this.value)" name="two" class="form-control @error('lb_name_exist') is-invalid @enderror">
                            <option value="">--Select--</option>
                            @foreach ($level_two as $lvo)
                                <option <?php echo (isset($_GET['three']) && $_GET['three'] == $lvo->id ? 'selected' : ''); ?> value="{{ $lvo->id }}">{{ $lvo->lb_two_name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('lb_name_exist'))
                            <span class="text-danger">{{ $errors->first('lb_name_exist') }}</span>
                        @endif
                    </div>
                    @endif

                    @if (isset($_GET['three']))
                    <div class="form-group">
                        <label class="mb-2 fw-bold">Level Three</label>
                        <select name="three" class="form-control @error('lb_name_exist') is-invalid @enderror">
                            <option value="">--Select--</option>
                            @foreach ($level_three as $lvo)
                                <option value="{{ $lvo->id }}">{{ $lvo->lb_three_name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('lb_name_exist'))
                            <span class="text-danger">{{ $errors->first('lb_name_exist') }}</span>
                        @endif
                    </div>
                    @endif


                    <input type="hidden" name="category" value="{{ $category }}">


                    <div class="d-grid gap-2 mx-auto col-6">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold mt-3">Save </button>


                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            {{-- <table class="table table-bordered table striped">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Level One Leaderboard List</th>
                        <th>Order</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($level_one as $one)
                    <tr>
                        <td>
                            <a href="{{ route('edit.level', [$one->id, $category, 'one']) }}">Edit</a>
                            &nbsp;
                            <a onclick="return confirm('Are you sure?')" href="{{ route('delete.level', [$one->id, $category, 'one']) }}" class="text-danger">Delete</a>
                        </td>
                        <td>{{ $one->lb_name }}</td>
                        <td>{{ $one->lb_order_no }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table> --}}
        </div>
    </div>
</div>



@include('admin.layouts.footer')


<script>
function level_two_select(str) {
    window.location.href = '?two=' + str;
}

function level_three_select(str) {
    window.location.href = '<?php echo Request::fullUrl(); ?>' + '&three=' + str;
}
</script>
