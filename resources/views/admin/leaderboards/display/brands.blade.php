@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mx-auto">
                <h2 class="fs-2 fw-bold mb-3">Brands</h2>

                <div class="row">
                    @foreach($data as $d)
                    <div class="col-md-4">

                        <h4>{{ $d->company }}</h4>

                    </div>
                    @endforeach
                </div>
        </div>
    </div>
</div>



@include('admin.layouts.footer')
