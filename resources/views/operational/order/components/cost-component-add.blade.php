<div class="tab-pane fade " id="profile-icon" role="tabpanel" aria-labelledby="profile-icon-tabs">
    <div class="card-body col-md-6 mt-3">
        <div id="ajax-cost-component-form" class="row g-3" data-action="{{ route($view . 'store') }}?page=cost-component">
            @csrf
            <div class="col-md-12">
                <label class="form-label" for="name">{{ __('menu_order.name') }}</label>
                <input class="form-control" name="name" id="name" type="text"
                    placeholder="{{ __('menu_order.name') }}">
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

            <div class="col-12 mt-4">
                <button class="btn btn-primary" type="button"
                    id="submit-cost-component">{{ __('general.add') }}</button>
            </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('submit-cost-component');

            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    e.preventDefault(); // Hindari submit form

                    const container = document.getElementById('ajax-cost-component-form');
                    const actionUrl = container.getAttribute('data-action');
                    const nameInput = document.getElementById('name');
                    const name = nameInput.value.trim();

                    fetch(actionUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                name: name
                            })
                        })
                        .then(response => {
                            if (!response.ok) throw response;
                            return response.json();
                        })
                        .then(data => {
                            swal({
                                title: "Success",
                                text: "Component added successfully!",
                                icon: "success",
                                timer: 1000,
                                buttons: false
                            });
                            nameInput.value = ''; // Kosongkan input

                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        })
                        .catch(async (error) => {
                            let errMsg = 'Failed to add component.';
                            try {
                                const errData = await error.json();
                                errMsg = errData.message || errMsg;
                            } catch (e) {
                                // fallback kalau error response bukan JSON
                                errMsg = 'Unexpected error occurred.';
                            }
                            alert(errMsg);
                        });
                });
            }
        });
    </script>
