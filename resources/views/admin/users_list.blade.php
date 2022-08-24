@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mx-auto">
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold">All Users</h2>
                @if (count($users) > 0)
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Brand Name</th>
                            <th>Founder's Name</th>
                            <th>Founder's Email</th>
                            <th>Email Verified</th>
                            <th>Highest Discount</th>
                            <th>Discount Code</th>
                            <th>Status</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($users as $user)
                        <tr>
                            <td><a target="_blank" href="https://www.goodyellowco.com/storefront/{{ $user['company_slug'] }}">{{ $user['company'] }} </a></td>
                           <td>{{ $user['name'] }}</td>
                           <td>{{ $user['email'] }}</td>
                           <td>{{ ($user['email_verified_at'] != '' ? 'Verified' : 'Not verified') }}</td>
                            <td>{{ $user['discount'] }}%</td>
                           <td>{{ $user['private'] === 1 ? 'Private' : 'Public' }}</td>
                           <td>{{ $user['discount_code'] }}</td>

                           <td><a href="{{ route('users.list.level', [$user['uid']]) }}">Go To Levels</a></td>
                           <td><a class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');" href="{{ route('user.delete', [$user['uid']]) }}">Delete</a></td>

                        </tr>
                        @endforeach

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
