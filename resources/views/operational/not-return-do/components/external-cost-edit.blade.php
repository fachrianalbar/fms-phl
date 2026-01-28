<div class="row g-3 mt-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">{{ __('menu_order.cost_component') }}</h5>
            <button class="btn btn-primary btn-sm" type="button" id="add-external-cost">
                {{ __('general.add_data') }}
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped w-100 nowrap" id="dt-external-cost">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%">#</th>
                        <th>{{ __('menu_order.component_name') }}</th>
                        <th>{{ __('menu_order.nominal') }}</th>
                        <th>{{ __('menu_order.description') }}</th>
                    </tr>
                </thead>
                <tbody id="externalCostForm">
                    @php
                        // Filter only "On Charge" costs with is_route = 0 (external fleet costs)
                        $externalCosts = $cost->where('type', 'On Charge')->where('is_route', 0);
                    @endphp

                    @if ($externalCosts->count() > 0)
                        @foreach ($externalCosts as $item)
                        <tr>
                            <td class="text-center">
                                <a href="#" class="btn btn-icon btn-sm bg-danger-subtle" 
                                    data-bs-toggle="tooltip" title="{{ __('general.delete_data') }}">
                                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                                </a>
                            </td>
                            <td>
                                <select class="form-control js-example-basic-single cost-component-select w-100" style="width:100%" 
                                    name="externalCostComponent[]" id="costComponent_edit_{{ $loop->iteration }}" required>
                                    <option selected disabled value="">{{ __('general.choose') }}...</option>
                                    @foreach ($component as $comp)
                                        <option value="{{ $comp->code }}" 
                                            {{ $item->componentType == $comp->code ? 'selected' : '' }}>
                                            {{ $comp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input class="form-control nominal-input text-end" name="externalCostNominal[]" 
                                    type="text" oninput="formatAngka(this)" placeholder="{{ __('menu_order.nominal') }}" 
                                    value="{{ number_format($item->nominal, 0, ',', '.') }}" required>
                            </td>
                            <td>
                                <input class="form-control" name="externalCostDescription[]" 
                                    type="text" placeholder="{{ __('menu_order.description') }}" 
                                    value="{{ $item->description ?? '' }}">
                                <input type="hidden" name="externalCostId[]" value="{{ $item->id }}">
                                <input type="hidden" name="externalCostDelete[]" value="0" class="delete-flag">
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr id="empty-row">
                            <td colspan="4" class="text-center text-muted py-3">
                                {{ __('general.no_data') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize existing Select2 elements
    initializeExternalCostSelect2();
    
    // Add external cost row
    const addExternalCostBtn = document.getElementById('add-external-cost');
    if (addExternalCostBtn) {
        addExternalCostBtn.addEventListener('click', function() {
            addExternalCostRow();
        });
    }

    // Handle remove external cost with event delegation
    const externalCostForm = document.getElementById('externalCostForm');
    if (externalCostForm) {
        externalCostForm.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('a.btn-icon.bg-danger-subtle');
            if (removeBtn) {
                e.preventDefault();
                removeExternalCost(removeBtn);
            }
        });
    }
});

function initializeExternalCostSelect2() {
    const selects = document.querySelectorAll('.cost-component-select');
    if (typeof $ !== 'undefined' && $.fn.select2) {
        selects.forEach(select => {
            if (!$(select).hasClass('select2-hidden-accessible')) {
                $(select).select2({
                    placeholder: "{{ __('general.choose') }}...",
                    allowClear: true,
                    width: '100%'
                });
            }
        });
    }
}

function addExternalCostRow() {
    const tbody = document.getElementById('externalCostForm');
    const emptyRow = document.getElementById('empty-row');
    
    // Remove empty row if exists
    if (emptyRow) {
        emptyRow.remove();
    }

    const rowCount = tbody.querySelectorAll('tr').length + 1;
    
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td class="text-center">
            <a href="#" class="btn btn-icon btn-sm bg-danger-subtle" 
                data-bs-toggle="tooltip" title="{{ __('general.delete_data') }}">
                <i class="mdi mdi-delete fs-14 text-danger"></i>
            </a>
        </td>
        <td>
            <select class="form-control js-example-basic-single cost-component-select w-100" style="width:100%" 
                name="externalCostComponent[]" id="costComponent_${rowCount}" required>
                <option selected disabled value="">{{ __('general.choose') }}...</option>
                @foreach ($component as $item)
                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input class="form-control nominal-input text-end" name="externalCostNominal[]" 
                type="text" oninput="formatAngka(this)" placeholder="{{ __('menu_order.nominal') }}" required>
        </td>
        <td>
            <input class="form-control" name="externalCostDescription[]" 
                type="text" placeholder="{{ __('menu_order.description') }}">
            <input type="hidden" name="externalCostId[]" value="">
            <input type="hidden" name="externalCostDelete[]" value="0">
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    // Initialize Select2 for the new select element
    const newSelect = newRow.querySelector(`#costComponent_${rowCount}`);
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $(newSelect).select2({
            placeholder: "{{ __('general.choose') }}...",
            allowClear: true,
            width: '100%'
        });
    }
}

function removeExternalCost(button) {
    if (!button || typeof button.closest !== 'function') {
        console.error('Invalid button element passed to removeExternalCost');
        return;
    }
    
    const row = button.closest('tr');
    if (!row) {
        console.error('Could not find parent row element');
        return;
    }
    
    // Check if this row has an existing cost ID (not a newly added row)
    const deleteFlag = row.querySelector('.delete-flag');
    if (deleteFlag) {
        // This is an existing cost - mark for deletion
        deleteFlag.value = '1';
        row.style.display = 'none'; // Hide the row instead of removing it
    } else {
        // This is a newly added row - remove it completely
        row.remove();
    }
    
    const tbody = document.getElementById('externalCostForm');
    
    // Count visible rows (excluding hidden deleted rows)
    const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(tr => 
        tr.style.display !== 'none' && !tr.id.includes('empty-row')
    );
    
    // Show empty row if no visible data left
    if (visibleRows.length === 0) {
        const emptyRow = document.getElementById('empty-row');
        if (!emptyRow) {
            const newEmptyRow = document.createElement('tr');
            newEmptyRow.id = 'empty-row';
            newEmptyRow.innerHTML = `
                <td colspan="4" class="text-center text-muted py-3">
                    {{ __('general.no_data') }}
                </td>
            `;
            tbody.appendChild(newEmptyRow);
        } else {
            emptyRow.style.display = '';
        }
    }
}
</script>
