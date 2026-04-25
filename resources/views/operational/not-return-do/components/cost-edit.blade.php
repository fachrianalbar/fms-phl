
<div class="row g-3 mt-3">
    <table class="table table-striped w-100 nowrap" id="dt-cost-component">
        <thead>
            <tr>
                {{-- tambah No --}}
                <th class="text-center">No</th>
                <th>{{ __('menu_order.component_name') }}</th>
                <th>{{ __('menu_order.description') }}</th>
                <th>Nominal</th>
                <th>Tipe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cost as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    @if ($item->costComponent)
                    {{ $item->costComponent->name }}
                    @else
                    <span class="text-danger">Component not found</span>
                    @endif
                </td>
                <td>
                    {{ $item->description ?? '-' }}
                </td>
                <td>
                    {{ number_format($item->nominal, 0, ',', '.') }}
                </td>
                <td>
                    {{ $item->type == 'On Charge' ? 'Ditagihkan' : 'Tidak Ditagihkan' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if (count($cost) == 0)
    <div class="alert alert-info mt-3" role="alert">
        <strong>Info:</strong> Belum ada cost component.
    </div>
    @endif
</div>
