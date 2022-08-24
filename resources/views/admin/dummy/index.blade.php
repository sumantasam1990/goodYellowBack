@include('admin.layouts.header', ['title' => $title])

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2>Dummy Brands</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mx-auto">

            @if($errors->any())
                {!! implode('', $errors->all('<div>:message</div>')) !!}
            @endif
            <form action="{{ route('dummy.brands.post') }}" method="POST" class="box" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="brand_name" class="form-control" value="{{ uniqid() }}">

                <label class="mt-3">Total Customers*</label>
                <input type="text" name="customers" class="form-control">

                <label class="mt-3">Total Sales*</label>
                <input type="text" name="sales" class="form-control">

                <label class="mt-3">Highest Discount*</label>
                <input type="text" name="discount" class="form-control">

                <label class="mt-3">Brand Photo*</label>
                <input type="file" class="form-control" name="image">

                <div class="d-grid gap-2 mx-auto col-4 mt-4">
                    <button type="submit" class="btn btn-warning">Save</button>
                </div>
            </form>
        </div>

        <div class="col-md-8 mx-auto">
            <div class="row">
                @foreach ($users as $d)
                <div class="col-md-4">
                    <div class="list">
                        <img src="{{ asset('uploads/' . $d['url']) }}" class="img-fluid">
                        {{-- <h4 class="w-bold mt-2">{{ $d['company'] }}</h4> --}}
                        <p class="mb-1">{{ $d['dummy_customers'] }} Customers</p>
                        <p class="mb-1">{{ $d['dummy_sales'] }} Sales</p>
                        <p>Highest Discount {{ $d['dummy_discount'] }}%</p>

                        <p>
                            <a class="btn btn-dark" href="{{ route('dummy.brands.leaderboard', [$d['id']]) }}">Add Leaderboard</a>
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
