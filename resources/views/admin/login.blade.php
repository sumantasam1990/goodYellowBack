<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">


<div class="container mt-4">
    <div class="row">
        <div class="col-md-5 mx-auto">
            <h2 class="fs-2 fw-bold">Administrator Login</h2>

            <div style="padding: 12px;
    border: 1px solid #ccc;
    border-radius: 2px;">
                <form action="{{ route('admin.login.post') }}" method="POST">
                    @csrf
                    <div class="form-group mt-2">
                        <label class="fw-bold">Username</label>
                        <input type="text" name="username" class="form-control">
                    </div>

                    <div class="form-group mt-2">
                        <label class="fw-bold">Password</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <div class="d-grid gap-2 col-4 mt-3 mx-auto">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>




        </div>
    </div>
</div>




@include('admin.layouts.footer')
