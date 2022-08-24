@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mx-auto">
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold">User Selected Levels</h2>
                @if (count($data) > 0)
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Level One</th>
                            <th>Level Two</th>
                            <th>Level Three</th>
                        </tr>
                    </thead>
                    <tbody>


                        <tr>

                            <td>
                                @foreach ($data['one'] as $levelOne)
                                    <p>{{ $levelOne['data'] }}</p>
                                @endforeach
                            </td>

                            <td>
                                @foreach ($data['two'] as $levelOne)
                                    <p>{{ $levelOne['data'] }}</p>
                                @endforeach
                            </td>

                            <td>
                                @foreach ($data['three'] as $levelOne)
                                    <p>{{ $levelOne['data'] }}</p>
                                @endforeach
                            </td>


                        </tr>


                    </tbody>
                </table>
                @else
                <h4 class="fw-bold text-secondary">No user found.</h4>
                @endif
            </div>
        </div>
    </div>
</div>




@include('admin.layouts.footer')
