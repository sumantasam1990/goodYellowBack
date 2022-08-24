@include('admin.layouts.header', ['title' => $title])

<div class="container mt-4">
    <div class="row">
        <div class="col-md-5 mx-auto">
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Discount Leaderboards</h2>
                <form action="{{ route('discount.list.add.post') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Select Existing Discount Title</label>
                        <select class="form-control" name="title_select">
                            <option value="">Select*</option>
                            @foreach ($data as $d)
                                <option value="{{ $d->id }}">{{ $d->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <p class="fw-bold fs-5 mt-4 mb-4 text-center">Or,</p>

                    <div class="form-group">
                        <label>Add Discount Title</label>
                        <input type="text" name="title" class="form-control" placeholder="5%-10% Discount">
                    </div>

                    <div class="d-grid gap-2 mx-auto col-5">
                        <button type="submit" class="btn btn-warning fw-bold mt-3">Next</button>

                    </div>


                </form>
            </div>
        </div>
    </div>
</div>




@include('admin.layouts.footer')
