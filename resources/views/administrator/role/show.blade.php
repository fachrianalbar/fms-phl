@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Menu Access',
])

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Menu Access Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" novalidate=""
                    action="{{ route('administrator.role-access', $data->id) }}">
                    @csrf
                    @method('PUT')
                    <ul>
                        @foreach ($menus as $menu)
                            @if ($menu->parentCode == '0')
                                @php
                                    $sub = $menus->where('parentCode', $menu->code);
                                @endphp

                                <div class="d-flex justify-content-between align-items-center my-2">
                                    <h5>{{ $menu->name }}</h5>


                                </div>

                                @foreach ($sub as $item)
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <li class="mx-3 my-1">{{ $item->name }}</li>

                                        <input type="checkbox" name="menu[{{ $item->code }}]" value="{{ $item->code }}"
                                            {{ in_array($item->code, $menuArr) ? 'checked' : '' }}>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </ul>



                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
