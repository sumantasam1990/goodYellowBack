@include('admin.layouts.header')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mx-auto">
            <div class="table-responsive">
                <h2 class="fs-2 fw-bold">All Buyers ({{ $users->total() }})</h2>
                @if (count($users) > 0)
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Street Address</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Zip</th>
                            <th>Phone</th>
                            <th>Email Verification</th>
                            <th>Promo code used</th>

                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach ($users as $user)
                        <tr>
                            <td class="fw-bold">{{ $i }}.</td>
                           <td>{{ $user['fname'] }} {{ $user['lname'] }}</td>
                           <td>{{ $user['email'] }}</td>
                            <td>{{ $user['street_addr'] }}</td>
                           <td>{{ $user['city'] }}</td>
                           <td>{{ $user['state'] }}</td>
                           <td>{{ $user['zip'] }}</td>
                           <td>{{ $user['phone'] }}</td>
                           <td>{{ ($user['verification_email'] != '' ? 'Verified' : 'Not verified') }}</td>
                           <td>{{ $user['buyer_promo'] }}</td>

                        </tr>
                        @php $i++; @endphp
                        @endforeach

                    </tbody>
                </table>
                {{ $users->links() }}
                @else
                <h4 class="fw-bold text-secondary">No buyers found.</h4>
                @endif
            </div>


        </div>
    </div>
</div>




@include('admin.layouts.footer')
