@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <p>{{ $msg ?? '' }}</p>
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Choose Leaderboards</h2>
                <h5 class="mb-3 text-primary">{{ $levelthreeName->lb_three_name }}</h5>
                <form action="{{ route('lb.level.four.post') }}" method="POST">
                    @csrf

                    <input type="hidden" name="category" value="{{ $levelthree }}">

                    <label>New Leaderboard Name*</label>
                    <input type="text" name="lb_name_new" class="form-control">

                    <p class="fs-6 fw-bold mt-4 mb-4">Or,</p>

                    @foreach($leaderboards as $lb)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $lb->slug }}" id="defaultCheck_{{ $lb->id }}" name="slug[]">
                        <label class="form-check-label" for="defaultCheck_{{ $lb->id }}">
                           <span class="fw-bold">{{ $lb->category }}</span> - {{ $lb->title }}
                        </label>
                    </div>
                    @endforeach







                    <div class="d-grid gap-2 mx-auto col-5">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold mt-3">Save & Finish</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- <div class="col-md-6">
            <table class="table table-bordered table striped">
                <thead>
                    <tr>

                        <th>Level Two Leaderboard List</th>
                        <th>Order</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($level_three as $three)
                    <tr>

                        <td>{{ $three->lb_three_name }}</td>
                        <td>{{ $three->lb_three_order_no }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div> --}}
    </div>
</div>





@include('admin.layouts.footer')
