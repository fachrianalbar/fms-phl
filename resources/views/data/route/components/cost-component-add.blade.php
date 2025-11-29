<div class="tab-pane fade " id="profile-icon" role="tabpanel" aria-labelledby="profile-icon-tabs">
    <div class="card-body col-md-6 mt-3">
        <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}?page=cost-component" onsubmit="return submitFormCostComponent()">
            @csrf
            @method('PUT')
            <div class="col-md-12 position-relative">
                <label class="form-label" for="costComponentSelect">{{ __('menu_route.cost_component_name') }}</label>
                <select class="js-example-basic-single" name="name" id="costComponentSelect" required="" onchange="getCostComponentPriceForAdd(this)">
                    <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                    @foreach ($component as $item)
                        <option value="{{ $item->name }}" data-price="{{ $item->price }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label" for="costComponentPrice">{{ __('menu_route.price') }}</label>
                <input class="form-control" name="price" id="costComponentPrice" type="text"
                    placeholder="{{ __('menu_route.price') }}" readonly style="background-color: #e9ecef;">
            </div>

            {{-- <div class="col-md-12 position-relative">
                <label class="form-label" for="type">Type</label>
                <select class="js-example-basic-single" name="type" id="type" required="">
                    <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                    @foreach ($componentType as $item)
                        <option value="{{ $item->value }}\">{{ $item->value }}</option>
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

<script>
    function getCostComponentPriceForAdd(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        const priceInput = document.getElementById('costComponentPrice');
        
        if (price) {
            // Format price dengan format xxx.xxx.xxx
            const formattedPrice = formatNumber(Math.round(parseFloat(price)));
            priceInput.value = formattedPrice;
        } else {
            priceInput.value = '';
        }
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function submitFormCostComponent() {
        const priceInput = document.getElementById('costComponentPrice');
        // Hapus titik pemisah ribuan sebelum submit
        priceInput.value = priceInput.value.replace(/\./g, '');
        return true;
    }
</script>
