@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-5 mx-auto">
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Lavel Two</h2>

                <ul class="list-group">
                    @foreach($data as $d)
                    <li class="list-group-item">
                        <a class="btn btn-link text-dark text-decoration-none" href="{{ route('leaderboard.list.three', [$d->id]) }}">{{ $d->lb_two_name }}</a>
                        <a onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm" style="float: right;" href="{{ route('level.delete', [$d->id, 'two']) }}">Delete</a>
                    </li>
                    @endforeach
                </ul>

            </div>
        </div>
    </div>
</div>


@include('admin.layouts.footer')
