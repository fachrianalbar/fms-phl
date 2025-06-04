<form class="row g-3 mt-5" method="post" onsubmit="return submitForm('amounts')"
    action="{{ route('data.route-detail.store') }}">
    @csrf
    <input type="hidden" value="{{ $data }}" name="routeData">
    {{-- <input type="hidden" value="{{ $totalPrice }}" name="totalPrice"> --}}
    <div class="row">
        <div class="col-md-6 position-relative">
            <label class="form-label" for="componentCode">{{ __('menu_route.cost_component_name') }}</label>
            <select class="js-example-basic-single" name="componentCode" id="componentCode" required="">
                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                @foreach ($component as $item)
                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6" id="amount">
            <label class="form-label" for="amount">{{ __('menu_route.price') }}</label>
            <input class="form-control" name="amount" id="amounts" oninput="formatAngka(this)" type="text"
                placeholder="{{ __('menu_route.price') }}" max="{{ $data->price }}">
            {{-- <label class="form-label" for="componentType">Cost Component Type</label> --}}
            <input type="hidden" name="componentType" value="Amount">
            {{-- <select class="js-example-basic-single" name="`componentType" id="componentType"
                onchange="toggleFields(this.value)" required="">
                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                <option value="Amount">Amount</option>
                <option value="Percentage">Percentage</option>
            </select> --}}
        </div>
    </div>

    <div class="row mt-4">


        {{-- <div class="col-md-6 d-none" id="percentage">
            <label class="form-label" for="percentage">Percentage</label>
            <input class="form-control" name="percentage" type="number" max="100" placeholder="Percentage">
        </div> --}}
    </div>

    <div class="col-12">
        <button class="btn btn-primary" type="submit">{{ __('general.add') }}</button>
    </div>
</form>
