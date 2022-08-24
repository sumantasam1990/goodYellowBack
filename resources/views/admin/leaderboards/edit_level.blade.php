@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <p>{{ $msg ?? '' }}</p>
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Edit</h2>
                <form action="{{ route('edit.level.post') }}" method="POST">
                    @csrf

                    <input type="hidden" value="{{ $id }}" name="hd_id">
                    <input type="hidden" value="{{ $status }}" name="hd_status">

                    @if($status == 'two')
                    <div class="form-group mt-3">
                        <label class="fw-bold mb-2">Name</label>
                        <input type="text" name="lb_name" class="form-control" value="{{ $data->lb_two_name }}" placeholder="">

                    </div>
                    @endif

                    @if($status == 'one')
                    <div class="form-group mt-3">
                        <label class="fw-bold mb-2">Name</label>
                        <input type="text" name="lb_name" class="form-control" value="{{ $data->lb_name }}" placeholder="">

                    </div>
                    @endif

                    @if($status == 'three')
                    <div class="form-group mt-3">
                        <label class="fw-bold mb-2">Name</label>
                        <input type="text" name="lb_name" class="form-control" value="{{ $data->lb_three_name }}" placeholder="">

                    </div>
                    @endif

                    @if($status != 'three')
                        <label class="mt-4">Highest Discount</label>
                        <input type="text" required class="form-control" value="{{ $data->discount }}" name="discount">
                    @endif


                    @if($status == 'one')
                    <div class="form-group mt-3">
                        <label class="fw-bold mb-2">Order</label>
                        <input type="text" name="lb_order_no" class="form-control" value="{{ $data->lb_order_no }}" placeholder="">

                    </div>
                    @endif

                    @if($status == 'two')
                    <div class="form-group mt-3">
                        <label class="fw-bold mb-2">Order</label>
                        <input type="text" name="lb_order_no" class="form-control" value="{{ $data->lb_two_order_no }}" placeholder="">

                    </div>
                    @endif

                    @if($status == 'three')
                    <div class="form-group mt-3">
                        <label class="fw-bold mb-2">Order</label>
                        <input type="text" name="lb_order_no" class="form-control" value="{{ $data->lb_three_order_no }}" placeholder="">

                    </div>
                    @endif



                    <div class="d-grid gap-2 mx-auto col-5">
                        <button type="submit" class="btn btn-warning fw-bold mt-3">Update</button>
                    </div>
                </form>
            </div>
        </div>


    </div>
</div>



@include('admin.layouts.footer')
