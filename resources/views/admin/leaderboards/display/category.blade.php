@include('admin.layouts.header', ['title' => $title])

<div class="container mt-4">
    <div class="row">
        <div class="col-md-5 mx-auto">
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Categories</h2>

                <ul>
                    @foreach($categories as $cate)
                    <li>
                        <a class="btn btn-link text-dark text-decoration-none" href="{{ route('leaderboard.list.one', [$cate->id]) }}">{{ $cate->name }}</a>
                    </li>
                    @endforeach
                </ul>

            </div>
        </div>
    </div>
</div>


@include('admin.layouts.footer')
