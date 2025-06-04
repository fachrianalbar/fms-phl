<div class="tab-pane fade show active" id="icon-home" role="tabpanel" aria-labelledby="icon-home-tab">

    <div class="card-body col-md-12 mt-3">
        {{-- @include('partials.alert') --}}

        <div class="">
            <table class="table table-striped w-100 nowrap" id="dt">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No</th>
                        <th>{{ __('menu_route.cost_component') }}</th>
                        {{-- <th>Component Type</th> --}}
                        <th>{{ __('menu_route.price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalPrice = 0;
                    @endphp
                    @foreach ($data->routeDetail as $item)
                        @if ($item->costComponent->type == 'Allowance Office' && auth()->user()->roleCode != 'SPRADMIN')
                            @continue;
                        @endif
                        <tr>
                            <td>
                                <a href="javascript:deleteCostComponent('{{ $item->id }}')"
                                    class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Delete">
                                    <i class="mdi mdi-delete fs-14 text-danger"></i>


                                    {{-- <ul class="action">
                                        <li class="delete">
                                            <a href="javascript:deleteCostComponent('{{ $item->id }}')"><i
                                                    class="icon-trash"></i></a>
                                        </li>
                                    </ul> --}}
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->costComponent->name }}</td>
                            {{-- <td>{{ $item->type }} =
                                {{ $item->type == 'Amount' ? number_format($item->amount, 0, ',', '.') : $item->percentage . '%' }}
                            </td> --}}
                            <td>
                                @php
                                    $price =
                                        $item->type == 'Percentage'
                                            ? $data->price * ($item->percentage / 100)
                                            : $item->amount;
                                    $totalPrice += $price;
                                @endphp
                                Rp {{ number_format($price, 0, ',', '.') }}
                            </td>

                        </tr>
                    @endforeach
                    {{-- <tr>
                        <td colspan="3" class="fw-bold text-start h5">Total:</td>
                        <td>Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
                        <td></td>
                    </tr> --}}
                </tbody>
            </table>
            <h4>Total: Rp {{ number_format($totalPrice, 0, ',', '.') }} </h4>
        </div>

        @include('data.route.components.route-detail-add')
    </div>
</div>

<script>
    const componentSelect = document.getElementById('componentType');
    const amountDiv = document.getElementById('amount');
    const percentageDiv = document.getElementById('percentage');

    // function toggleFields(id) {
    //     if (componentSelect.value === 'Amount') {
    //         amountDiv.classList.remove('d-none');
    //         percentageDiv.classList.add('d-none');
    //         document.getElementById('amount').setAttribute('required', 'required');
    //         document.getElementById('percentage').removeAttribute('required');
    //     } else if (componentSelect.value === 'Percentage') {
    //         percentageDiv.classList.remove('d-none');
    //         amountDiv.classList.add('d-none');
    //         document.getElementById('percentage').setAttribute('required', 'required');
    //         document.getElementById('amount').removeAttribute('required');
    //     } else {
    //         amountDiv.classList.add('d-none');
    //         percentageDiv.classList.add('d-none');
    //         document.getElementById('amount').removeAttribute('required');
    //         document.getElementById('percentage').removeAttribute('required');
    //     }
    // }
</script>
