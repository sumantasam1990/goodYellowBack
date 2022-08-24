@include('admin.layouts.header', ['title' => $title])

<div class="container mt-4">
    <div class="row">
        <div class="col-md-5 mx-auto">
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold mb-3">Discount Leaderboard Category</h2>
                <form action="{{ route('discount.list.category.post') }}" method="POST">
                    @csrf

                    <input type="hidden" value="{{ $did }}" name="did">
                    <div class="form-group">
                        <label class="mb-2 fw-bold">Select Category</label>
                        <select name="category" class="form-control @error('category') is-invalid @enderror">
                            <option value="">--Select--</option>
                            @foreach ($categories as $cate)
                                <option value="{{ $cate->id }}">{{ $cate->name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('category'))
                            <span class="text-danger">{{ $errors->first('category') }}</span>
                        @endif
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
