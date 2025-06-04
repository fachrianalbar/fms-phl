<div class="tab-pane fade " id="profile-icon" role="tabpanel" aria-labelledby="profile-icon-tabs">
    <div class="card-body col-md-6 mt-3">
        <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}?page=cost-component">
            @csrf
            @method('PUT')
            <div class="col-md-12">
                <label class="form-label" for="name">{{ __('menu_route.name') }}</label>
                <input class="form-control" name="name" id="name" type="text" required
                    placeholder="{{ __('menu_route.name') }}">
            </div>

            {{-- <div class="col-md-12 position-relative">
                <label class="form-label" for="type">Type</label>
                <select class="js-example-basic-single" name="type" id="type" required="">
                    <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                    @foreach ($componentType as $item)
                        <option value="{{ $item->value }}">{{ $item->value }}</option>
                    @endforeach
                </select>
                <div class="invalid-tooltip">Please select a valid state.</div>
            </div> --}}


            <div class="col-12">
                <button class="btn btn-primary" type="submit">{{ __('general.add') }}</button>
            </div>
        </form>
    </div>
</div>
