@include('admin.layouts.header', ['title' => $title])

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4 mx-auto">
            @if($errors->any())
                {!! implode('', $errors->all('<div>:message</div>')) !!}
            @endif
            <form action="{{ route('dummy.brands.leaderboard.post') }}" method="POST" class="box">
                @csrf

                <input type="hidden" value="{{ $user_id }}" name="user_id">

                <label>Select Existing Leaderboard</label>
                <select class="form-control" name="lb_exist">
                    <option value="">--Select--</option>
                    @foreach ($leaderboards as $lb)
                        <option value="{{ $lb->slug }}">{{ $lb->title }}</option>
                    @endforeach
                </select>

                {{-- <p class="mt-3 fw-bold text-center fs-4">Or,</p>

                <label>Select A Category</label>
                <select class="form-control" name="category">
                    <option value="">--Select--</option>
                    <option>Brand</option>
                    <option>Causes</option>
                    <option>Good for planet</option>
                    <option>Good for people</option>
                </select>

                <label class="mt-2">Create New Dummy Leaderboard</label>
                <input type="text" name="lb_new" class="form-control"> --}}



                <div class="d-grid gap-2 mx-auto col-4 mt-4">
                    <button type="submit" class="btn btn-warning">Save</button>
                </div>
            </form>
        </div>

        <div class="col-md-8 mx-auto">
            <h4 class="mb-3">Leaderboards for this user</h4>
            <div class="row">
                @foreach ($dummylb as $lb)
                <div class="col-md-4">

                    <div class="list">

                        <h4 class="w-bold mt-2">{{ $lb->category }}</h4>
                        <p class="mb-1">{{ $lb->title }} </p>
                        <p>
                            <span class="badge bg-danger">{{ $lb->dummy_lb == '1' ? 'dummy' : 'original' }}</span>
                        </p>


                        {{-- <p>
                            <a class="btn btn-dark" href="{{ route('dummy.brands.leaderboard', [$d['id']]) }}">Add Leaderboard</a>
                        </p> --}}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
