@include('admin.layouts.header')


<div class="container mt-4">
    <div class="row">
        <div class="col-md-5 mx-auto">
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Leaderboards</h2>
                <ul>
                    @foreach($data as $d)
                    <li>
                        <a style="text-align: left;" target="_blank" class="btn btn-link text-dark text-decoration-none" href="https://www.goodyellowco.com/leaderboard/brands/list/{{ $d->slug }}">{{ $d->title }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>


@include('admin.layouts.footer')







<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
