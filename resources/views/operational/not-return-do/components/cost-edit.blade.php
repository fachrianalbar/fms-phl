
<div class="row g-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-end align-items-center mb-3">
            @php
                $allCosts = $cost;
                $totalCost = $allCosts->sum('nominal');
            @endphp
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill" id="internalCostBadge">
                    {{ $allCosts->count() }} item
                </span>
                <button class="btn btn-primary btn-sm" type="button" id="add-internal-cost">
                    <i class="mdi mdi-plus-circle me-1"></i>{{ __('general.add_data') }}
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover w-100 nowrap" id="dt-internal-cost">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 5%">#</th>
                        <th style="width: 25%">{{ __('menu_order.component_name') }}</th>
                        <th style="width: 18%">Tipe</th>
                        <th style="width: 20%">{{ __('menu_order.nominal') ?? 'Nominal' }}</th>
                        <th>{{ __('menu_order.description') }}</th>
                    </tr>
                </thead>
                <tbody id="internalCostForm">
                    @if ($allCosts->count() > 0)
                        @foreach ($allCosts as $item)
                        <tr class="cost-row" style="transition: all 0.3s ease;">
                            <td class="text-center align-middle">
                                <a href="#" class="btn btn-icon btn-sm bg-danger-subtle remove-internal-cost-btn" 
                                    data-bs-toggle="tooltip" title="{{ __('general.delete_data') ?? 'Hapus' }}">
                                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                                </a>
                            </td>
                            <td class="align-middle">
                                <select class="form-control js-example-basic-single internal-cost-component-select w-100" style="width:100%" 
                                    name="internalCostComponent[]" id="internalCostComponent_edit_{{ $loop->iteration }}" required>
                                    <option selected disabled value="">{{ __('general.choose') }}...</option>
                                    @foreach ($component as $comp)
                                        <option value="{{ $comp->code }}" 
                                            {{ $item->componentType == $comp->code ? 'selected' : '' }}>
                                            {{ $comp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="align-middle">
                                <select class="form-control js-example-basic-single internal-cost-type-select w-100" style="width:100%" 
                                    name="internalCostType[]" id="internalCostType_edit_{{ $loop->iteration }}" required>
                                    <option value="On Charge" {{ $item->type == 'On Charge' ? 'selected' : '' }}>On Charge</option>
                                    <option value="Off Charge" {{ ($item->type == 'Off Charge' || !$item->type) ? 'selected' : '' }}>Off Charge</option>
                                </select>
                            </td>
                            <td class="align-middle">
                                <input class="form-control nominal-input text-end" name="internalCostNominal[]" 
                                    type="text" oninput="formatAngka(this)" placeholder="{{ __('menu_order.nominal') ?? 'Nominal' }}" 
                                    value="{{ number_format($item->nominal, 0, ',', '.') }}" required>
                            </td>
                            <td class="align-middle">
                                <input class="form-control" name="internalCostDescription[]" 
                                    type="text" placeholder="{{ __('menu_order.description') }}" 
                                    value="{{ $item->description ?? '' }}">
                                <input type="hidden" name="internalCostId[]" value="{{ $item->id }}">
                                <input type="hidden" name="internalCostIsRoute[]" value="{{ $item->is_route }}">
                                <input type="hidden" name="internalCostDelete[]" value="0" class="internal-delete-flag">
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr id="internal-empty-row">
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="mdi mdi-information-outline me-1"></i>
                                Belum ada biaya komponen. Klik tombol <strong>"{{ __('general.add_data') }}"</strong> untuk menambah.
                            </td>
                        </tr>
                    @endif
                </tbody>
                @if ($allCosts->count() > 0)
                <tfoot>
                    <tr class="table-light">
                        <td colspan="3" class="text-end fw-bold">Total:</td>
                        <td class="text-end fw-bold" id="internalCostTotal">
                            Rp {{ number_format($totalCost, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize existing Select2 elements
    initializeInternalCostSelect2();
    
    // Add internal cost row
    const addInternalCostBtn = document.getElementById('add-internal-cost');
    if (addInternalCostBtn) {
        addInternalCostBtn.addEventListener('click', function() {
            addInternalCostRow();
        });
    }

    // Handle remove internal cost with event delegation
    const internalCostForm = document.getElementById('internalCostForm');
    if (internalCostForm) {
        internalCostForm.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('a.remove-internal-cost-btn');
            if (removeBtn) {
                e.preventDefault();
                removeInternalCost(removeBtn);
            }
        });

        // Calculate total on nominal input change
        internalCostForm.addEventListener('input', function(e) {
            if (e.target.classList.contains('nominal-input')) {
                calculateInternalCostTotal();
            }
        });
    }

    // Initial total calculation
    calculateInternalCostTotal();
});

function initializeInternalCostSelect2() {
    const selects = document.querySelectorAll('.internal-cost-component-select, .internal-cost-type-select');
    if (typeof $ !== 'undefined' && $.fn.select2) {
        selects.forEach(select => {
            if (!$(select).hasClass('select2-hidden-accessible')) {
                $(select).select2({
                    placeholder: $(select).hasClass('internal-cost-component-select') ? "{{ __('general.choose') }}..." : undefined,
                    allowClear: $(select).hasClass('internal-cost-component-select'),
                    width: '100%'
                });
            }
        });
    }
}

function addInternalCostRow() {
    const tbody = document.getElementById('internalCostForm');
    const emptyRow = document.getElementById('internal-empty-row');
    
    // Remove empty row if exists
    if (emptyRow) {
        emptyRow.remove();
    }

    // Ensure tfoot exists
    ensureInternalCostTfoot();

    const rowCount = tbody.querySelectorAll('tr.cost-row').length + 1;
    const uniqueId = Date.now(); // Use timestamp for unique IDs
    
    const newRow = document.createElement('tr');
    newRow.className = 'cost-row';
    newRow.style.transition = 'all 0.3s ease';
    newRow.style.opacity = '0';
    newRow.style.transform = 'translateY(-10px)';
    
    newRow.innerHTML = `
        <td class="text-center align-middle">
            <a href="#" class="btn btn-icon btn-sm bg-danger-subtle remove-internal-cost-btn" 
                data-bs-toggle="tooltip" title="{{ __('general.delete_data') ?? 'Hapus' }}">
                <i class="mdi mdi-delete fs-14 text-danger"></i>
            </a>
        </td>
        <td class="align-middle">
            <select class="form-control js-example-basic-single internal-cost-component-select w-100" style="width:100%" 
                name="internalCostComponent[]" id="internalCostComponent_${uniqueId}" required>
                <option selected disabled value="">{{ __('general.choose') }}...</option>
                @foreach ($component as $item)
                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </td>
        <td class="align-middle">
            <select class="form-control js-example-basic-single internal-cost-type-select w-100" style="width:100%" 
                name="internalCostType[]" id="internalCostType_${uniqueId}" required>
                <option value="On Charge">On Charge</option>
                <option value="Off Charge" selected>Off Charge</option>
            </select>
        </td>
        <td class="align-middle">
            <input class="form-control nominal-input text-end" name="internalCostNominal[]" 
                type="text" oninput="formatAngka(this)" placeholder="{{ __('menu_order.nominal') ?? 'Nominal' }}" required>
        </td>
        <td class="align-middle">
            <input class="form-control" name="internalCostDescription[]" 
                type="text" placeholder="{{ __('menu_order.description') }}">
            <input type="hidden" name="internalCostId[]" value="">
            <input type="hidden" name="internalCostIsRoute[]" value="0">
            <input type="hidden" name="internalCostDelete[]" value="0" class="internal-delete-flag">
        </td>
    `;
    
    tbody.appendChild(newRow);

    // Animate in
    requestAnimationFrame(() => {
        newRow.style.opacity = '1';
        newRow.style.transform = 'translateY(0)';
    });
    
    // Initialize Select2 for the new select elements
    const newComponentSelect = newRow.querySelector(`#internalCostComponent_${uniqueId}`);
    const newTypeSelect = newRow.querySelector(`#internalCostType_${uniqueId}`);
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $(newComponentSelect).select2({
            placeholder: "{{ __('general.choose') }}...",
            allowClear: true,
            width: '100%'
        });
        $(newTypeSelect).select2({
            width: '100%'
        });
    }

    // Update badge count
    updateInternalCostBadge();
}

function removeInternalCost(button) {
    if (!button || typeof button.closest !== 'function') {
        console.error('Invalid button element passed to removeInternalCost');
        return;
    }
    
    const row = button.closest('tr');
    if (!row) {
        console.error('Could not find parent row element');
        return;
    }
    
    // Animate out
    row.style.opacity = '0';
    row.style.transform = 'translateX(20px)';
    
    setTimeout(() => {
        // Check if this row has an existing cost ID (not a newly added row)
        const deleteFlag = row.querySelector('.internal-delete-flag');
        const costIdInput = row.querySelector('input[name="internalCostId[]"]');
        
        if (deleteFlag && costIdInput && costIdInput.value) {
            // This is an existing cost - mark for deletion
            deleteFlag.value = '1';
            row.style.display = 'none';
        } else {
            // This is a newly added row - remove it completely
            row.remove();
        }
        
        const tbody = document.getElementById('internalCostForm');
        
        // Count visible rows
        const visibleRows = Array.from(tbody.querySelectorAll('tr.cost-row')).filter(tr => 
            tr.style.display !== 'none'
        );
        
        // Show empty row if no visible data left
        if (visibleRows.length === 0) {
            const emptyRow = document.getElementById('internal-empty-row');
            if (!emptyRow) {
                const newEmptyRow = document.createElement('tr');
                newEmptyRow.id = 'internal-empty-row';
                newEmptyRow.innerHTML = `
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="mdi mdi-information-outline me-1"></i>
                        Belum ada biaya komponen. Klik tombol <strong>"{{ __('general.add_data') }}"</strong> untuk menambah.
                    </td>
                `;
                tbody.appendChild(newEmptyRow);
            } else {
                emptyRow.style.display = '';
            }
        }

        // Update badge and total
        updateInternalCostBadge();
        calculateInternalCostTotal();
    }, 300);
}

function updateInternalCostBadge() {
    const badge = document.getElementById('internalCostBadge');
    if (badge) {
        const tbody = document.getElementById('internalCostForm');
        const visibleRows = Array.from(tbody.querySelectorAll('tr.cost-row')).filter(tr => 
            tr.style.display !== 'none'
        );
        badge.textContent = visibleRows.length + ' item';
    }
}

function calculateInternalCostTotal() {
    const tbody = document.getElementById('internalCostForm');
    const totalEl = document.getElementById('internalCostTotal');
    
    if (!tbody || !totalEl) return;

    let total = 0;
    const visibleRows = Array.from(tbody.querySelectorAll('tr.cost-row')).filter(tr => 
        tr.style.display !== 'none'
    );

    visibleRows.forEach(row => {
        const nominalInput = row.querySelector('input[name="internalCostNominal[]"]');
        if (nominalInput && nominalInput.value) {
            const val = parseInt(nominalInput.value.replace(/\./g, ''), 10);
            if (!isNaN(val)) {
                total += val;
            }
        }
    });

    totalEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
}

function ensureInternalCostTfoot() {
    const table = document.getElementById('dt-internal-cost');
    if (!table) return;
    
    let tfoot = table.querySelector('tfoot');
    if (!tfoot) {
        tfoot = document.createElement('tfoot');
        tfoot.innerHTML = `
            <tr class="table-light">
                <td colspan="3" class="text-end fw-bold">Total:</td>
                <td class="text-end fw-bold" id="internalCostTotal">Rp 0</td>
                <td></td>
            </tr>
        `;
        table.appendChild(tfoot);
    }
}
</script>
