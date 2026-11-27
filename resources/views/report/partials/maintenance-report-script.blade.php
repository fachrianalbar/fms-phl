<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
<script>
    /**
     * Inisialisasi filter standar laporan perawatan: Select2, Flatpickr dua arah, reset.
     * Mengembalikan objek { getFilters, reset }.
     */
    window.initMaintenanceReportFilter = function(fieldIds) {
        $('.select2-filter').each(function() {
            $(this).select2({
                placeholder: $(this).data('placeholder') || 'Semua',
                allowClear: true,
                width: '100%'
            });
        });

        let startPicker;
        let endPicker;

        startPicker = flatpickr('#startDate', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            onChange: function(selectedDates, dateStr) {
                if (endPicker) endPicker.set('minDate', dateStr || null);
            }
        });

        endPicker = flatpickr('#endDate', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            onChange: function(selectedDates, dateStr) {
                if (startPicker) startPicker.set('maxDate', dateStr || null);
            }
        });

        if ($('#startDate').val()) endPicker.set('minDate', $('#startDate').val());
        if ($('#endDate').val()) startPicker.set('maxDate', $('#endDate').val());

        function getFilters() {
            const filters = {};
            fieldIds.forEach(function(id) {
                filters[id] = $('#' + id).val() || '';
            });
            return filters;
        }

        function reset() {
            $('#filterForm').find('input').val('');
            $('.select2-filter').val('').trigger('change');
            startPicker.clear();
            endPicker.clear();
            startPicker.set('maxDate', null);
            endPicker.set('minDate', null);
        }

        return { getFilters: getFilters, reset: reset };
    };

    window.buildMaintenanceReportQuery = function(filters) {
        const params = new URLSearchParams();
        Object.entries(filters).forEach(function(entry) {
            if (entry[1]) params.set(entry[0], entry[1]);
        });
        return params.toString() ? '?' + params.toString() : '';
    };

    window.maintenanceReportLanguage = function(entity) {
        return {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ ' + entity,
            infoEmpty: 'Tidak ada data ' + entity,
            zeroRecords: 'Data ' + entity + ' tidak ditemukan',
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
        };
    };
</script>
