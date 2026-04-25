@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => $title,
'secondSegment' => __('general.add'),
])

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>
                @if($isSubMenu)
                Add Sub Menu for: {{ $parentName }}
                @else
                {{ $title }} {{ __('general.add_data') }}
                @endif
            </h4>

            @if($isSubMenu)
            <a href="{{ route('master.menu.sub-menu', $parentCode) }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
            @else
            <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
            @endif
        </div>
        <div class="card-body col-md-6">
            <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
                @csrf
                <input type="hidden" name="parentCode" value="{{ $parentCode }}">

                <div class="col-md-12">
                    <label class="form-label" for="name">Name (English) <span class="text-danger">*</span></label>
                    <input class="form-control" name="name" id="name" type="text" required
                        placeholder="Menu Name (English)" value="{{ old('name') }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="nama">Nama (Indonesia) <span class="text-danger">*</span></label>
                    <input class="form-control" name="nama" id="nama" type="text" required
                        placeholder="Nama Menu (Indonesia)" value="{{ old('nama') }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="icon">Icon</label>
                    <input class="form-control" name="icon" id="icon" type="text"
                        placeholder="e.g., mdi mdi-home or fa fa-home" value="{{ old('icon') }}">
                    <small class="text-muted">Enter icon class name (Material Design Icons or Font Awesome)</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="url">URL</label>
                    <input class="form-control" name="url" id="url" type="text"
                        placeholder="e.g., /dashboard or #" value="{{ old('url', $isSubMenu ? '' : '#') }}">
                    <small class="text-muted">
                        @if($isSubMenu)
                        Enter the route path for this sub menu
                        @else
                        Enter # if this menu will have sub menus, or enter the route path for single menu
                        @endif
                    </small>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="sort">Sort Order</label>
                    <input class="form-control" name="sort" id="sort" type="number"
                        placeholder="Sort Order (e.g., 1, 2, 3)" value="{{ old('sort', 0) }}">
                    <small class="text-muted">Lower number will be displayed first</small>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary" type="submit">
                        <i class="mdi mdi-content-save me-1"></i>Save
                    </button>
                    @if($isSubMenu)
                    <a href="{{ route('master.menu.sub-menu', $parentCode) }}" class="btn btn-secondary">
                        <i class="mdi mdi-close me-1"></i>Cancel
                    </a>
                    @else
                    <a href="{{ route($view . 'index') }}" class="btn btn-secondary">
                        <i class="mdi mdi-close me-1"></i>Cancel
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection