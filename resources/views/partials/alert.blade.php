@if (Session::has('fail'))
    <div class="alert alert-danger alert-dismissible fade show" border-left-wrapper role="alert">
        <p>{!! Session::get('fail') !!}</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show" border-left-wrapper role="alert">
        <p>{!! Session::get('success') !!}</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())

    <div class="alert alert-danger alert-dismissible fade show" border-left-wrapper role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
