<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? '' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">


<style>
.box {
    padding: 8px;
    border: 1px solid #000000;
    min-height: 300px;
    border-radius: 12px;
}

label {
    font-weight: bold;
    margin-bottom: 6px;
}

.list {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px ;
}

.list img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
</style>

  </head>
  <body>

    <nav class="navbar navbar-expand-lg bg-warning">
  <div class="container-fluid">
    <a class="navbar-brand text-dark" href="#">Administrator</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('users.list') }}">All Brands</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="{{ route('buyers.list') }}">All Buyers</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Leaderboard Lists
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('lb.category') }}">Add Leaderboard List</a></li>
            {{-- <li><a class="dropdown-item" href="{{ route('leaderboard.list') }}">View Leaderboard Lists</a></li> --}}
            {{-- <li><hr class="dropdown-divider"></li> --}}
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Dummy Brands & Leaderboards
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('dummy.brands') }}">Add dummy brand</a></li>
            {{-- <li><a class="dropdown-item" href="{{ route('leaderboard.list') }}"></a></li> --}}
            {{-- <li><hr class="dropdown-divider"></li> --}}
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="{{ route('discount.list.add') }}">Add Discount List</a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-danger fw-bold" href="{{ route('admin.logout') }}">Logout</a>
        </li>

      </ul>

    </div>
  </div>
</nav>
