@if (Session::has('fail'))
    <div class="alert alert-danger alert-dismissible fade show" border-left-wrapper role="alert">
        <p>{!! Session::get('fail') !!}</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (Session::has('error'))
    <div class="alert alert-danger alert-dismissible fade show" border-left-wrapper role="alert">
        <p>{!! Session::get('error') !!}</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show" border-left-wrapper role="alert">
        <p>{!! Session::get('success') !!}</p>
        @if (Session::has('code_replaced'))
            @php($codeReplacement = Session::get('code_replaced'))
            <p class="mb-0">
                Kode {{ $codeReplacement['requested'] ?? '-' }} sudah pernah digunakan. Sistem otomatis menggunakan
                {{ $codeReplacement['resolved'] ?? '-' }}.
            </p>
        @endif
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
