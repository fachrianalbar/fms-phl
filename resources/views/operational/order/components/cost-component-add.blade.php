<div class="tab-pane fade " id="profile-icon" role="tabpanel" aria-labelledby="profile-icon-tabs">
    <div class="card-body col-md-6 mt-3">
        <div id="ajax-cost-component-form" class="row g-3" data-action="{{ route($view . 'store') }}?page=cost-component">
            @csrf
            <div class="col-md-12">
                <label class="form-label" for="name">Name</label>
                <input class="form-control" name="name" id="name" type="text" required placeholder="Name">
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
                <button class="btn btn-primary" id="submit-cost-component">{{ __('general.add') }}</button>
            </div>
            </form>
        </div>
    </div>

    <script>
        $('#submit-cost-component').on('click', function(e) {
            e.preventDefault(); // Hindari submit biasa

            const container = $('#ajax-cost-component-form');
            const actionUrl = container.data('action');
            const name = $('#cost-name').val();

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: name,
                },
                success: function(response) {
                    swal({
                        title: 'Success',
                        text: 'Component added successfully!',
                        icon: 'success',
                    });

                    // Reset field (optional)
                    $('#cost-name').val('');
                },
                error: function(xhr) {
                    swal({
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Failed to add component.',
                        icon: 'error',
                    });
                }
            });
        });
    </script>
