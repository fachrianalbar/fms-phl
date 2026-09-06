<script>
    let vendorPaymentTable;
    let paymentBanksLoaded = false;
    let paymentSubmissionInFlight = false;
    let paymentRequestKey = null;
    let paymentBankRequestSequence = 0;
    const selectedOrders = {};

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(Math.round(Number(value) || 0));
    }

    function formatCurrency(value) {
        return 'Rp ' + formatNumber(value);
    }

    function formatRate(value) {
        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 4
        }).format(Number(value) || 0);
    }

    /** Escape nilai dinamis sebelum dimasukkan ke opsi `html` SweetAlert2. */
    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            }[character];
        });
    }

    /** Loader SweetAlert2 untuk setiap proses submit (non-blocking, tanpa tombol). */
    function swalLoader(title, html) {
        return Swal.fire({
            title: title,
            html: html,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function() {
                Swal.showLoading();
            },
        });
    }

    /** Tutup loader SweetAlert2 bila sedang tampil. */
    function closeSwalLoader() {
        if (Swal.isVisible()) {
            Swal.close();
        }
    }

    function formatNotaDate(value) {
        if (!value) {
            return '-';
        }

        const date = new Date(value);
        if (isNaN(date.getTime())) {
            return String(value);
        }

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');

        return day + '-' + month + '-' + date.getFullYear();
    }

    function localDateValue() {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return year + '-' + month + '-' + day;
    }

    function generateRequestKey() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(character) {
            const random = Math.floor(Math.random() * 16);
            const value = character === 'x' ? random : (random & 0x3) | 0x8;

            return value.toString(16);
        });
    }

    function paymentStatusLabel(status) {
        if (status === 'partial') {
            return 'Dibayar sebagian';
        }
        if (status === 'paid') {
            return 'Lunas';
        }

        return 'Belum dibayar';
    }

    function readPaymentCheckbox(checkbox) {
        return {
            notaNumber: String(checkbox.attr('data-nota-number') || ''),
            notaDate: String(checkbox.attr('data-nota-date') || ''),
            vendorName: String(checkbox.attr('data-vendor-name') || '-'),
            orderCodes: String(checkbox.attr('data-order-codes') || '').split(',').map(function(code) {
                return code.trim();
            }).filter(function(code) {
                return code !== '';
            }),
            orderCount: Number(checkbox.attr('data-order-count') || 0),
            orderFormat: String(checkbox.attr('data-order-format') || '').toUpperCase().trim(),
            fleetCompanyCode: String(checkbox.attr('data-fleet-company-code') || ''),
            paymentStatus: String(checkbox.attr('data-payment-status') || 'pending'),
            checkboxType: 'payment',
            billingAmount: Math.round(Number(checkbox.attr('data-billing-amount') || 0)),
            paidAmount: Math.round(Number(checkbox.attr('data-paid-amount') || 0)),
            remainingAmount: Math.round(Number(checkbox.attr('data-remaining-amount') || 0)),
            ppnAmount: Math.round(Number(checkbox.attr('data-ppn-amount') || 0)),
            pphAmount: Math.round(Number(checkbox.attr('data-pph-amount') || 0)),
        };
    }

    function selectedPaymentItems() {
        return Object.values(selectedOrders).filter(function(item) {
            return item.checkboxType === 'payment';
        });
    }

    function calculateSelectedTotals() {
        return selectedPaymentItems().reduce(function(totals, item) {
            totals.billing += item.billingAmount;
            totals.paid += item.paidAmount;
            totals.remaining += item.remainingAmount;
            totals.ppn += item.ppnAmount;
            totals.pph += item.pphAmount;
            totals.orderCount += item.orderCount;

            return totals;
        }, {
            billing: 0,
            paid: 0,
            remaining: 0,
            ppn: 0,
            pph: 0,
            orderCount: 0,
        });
    }

    function syncSelectAllState() {
        const checkboxes = $('.row-payment-checkbox[data-checkbox-type="payment"]:visible:not(:disabled)');
        const checkedCount = checkboxes.filter(':checked').length;
        const selectAll = $('#selectAllNotas');

        selectAll.prop('checked', checkboxes.length > 0 && checkedCount === checkboxes.length);
        selectAll.prop('indeterminate', checkedCount > 0 && checkedCount < checkboxes.length);
    }

    function restoreSelectedCheckboxes() {
        $('.row-payment-checkbox[data-checkbox-type="payment"]').each(function() {
            const checkbox = $(this);
            const notaNumber = String(checkbox.attr('data-nota-number') || '');
            const selected = !!selectedOrders[notaNumber];

            checkbox.prop('checked', selected);
            checkbox.closest('tr').toggleClass('table-active', selected);

            if (selected) {
                const refreshed = readPaymentCheckbox(checkbox);
                refreshed.allocationAmount = selectedOrders[notaNumber].allocationAmount;
                selectedOrders[notaNumber] = refreshed;
            }
        });

        syncSelectAllState();
    }

    function updateSelectionSummary() {
        const items = selectedPaymentItems();
        const count = items.length;
        const totals = calculateSelectedTotals();
        const vendorCount = new Set(items.map(function(item) {
            return item.fleetCompanyCode || item.vendorName;
        })).size;
        $('#selectionEmptyHelp').toggleClass('d-none', count > 0);
        $('#selectionCommandBar').toggleClass('d-none', count === 0);
        $('#selectionHeadline').text(count + ' nota dipilih');
        $('#selectionVendorFact').text(vendorCount + ' vendor');
        $('#selectionOrderFact').text(totals.orderCount + ' order');
        $('#selectionRemainingFact').text('Sisa ' + formatCurrency(totals.remaining));
        $('#openPaymentModalBtn').prop('disabled', count === 0 || totals.remaining < 1);

        syncSelectAllState();
    }

    function clearPaymentSelection() {
        Object.keys(selectedOrders).forEach(function(key) {
            delete selectedOrders[key];
        });
        $('.row-payment-checkbox').prop('checked', false).closest('tr').removeClass('table-active');
        $('#selectAllNotas').prop('checked', false).prop('indeterminate', false);
        updateSelectionSummary();
    }

    function setBankLoadingState(message, isError) {
        $('#paymentBankStatus')
            .text(message)
            .toggleClass('text-danger', !!isError)
            .toggleClass('text-success', false);
        $('#reloadPaymentBanksBtn').toggleClass('d-none', !isError);
    }

    function loadBankData(forceReload) {
        if (paymentBanksLoaded && !forceReload) {
            return;
        }

        const bankSelect = $('#userBankCode');
        const requestSequence = ++paymentBankRequestSequence;
        paymentBanksLoaded = false;
        bankSelect.prop('disabled', true).empty().append(new Option('Memuat rekening...', ''));
        bankSelect.trigger('change');
        setBankLoadingState('Memuat rekening perusahaan...', false);
        updatePaymentSummary();

        $.ajax({
            url: "{{ route('api.user-bank.company') }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (requestSequence !== paymentBankRequestSequence) {
                    return;
                }

                bankSelect.empty().append(new Option('Pilih rekening sumber dana', ''));

                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(function(bank) {
                        const label = (bank.bank_name || 'Bank') + ' · ' + (bank.account_number || '-') + ' · ' + (bank.account_name || '-');
                        const option = new Option(label, bank.code || '', false, false);
                        bankSelect.append(option);
                    });
                    paymentBanksLoaded = true;
                    bankSelect.prop('disabled', false);
                    setBankLoadingState(response.length + ' rekening tersedia.', false);
                } else {
                    bankSelect.append(new Option('Tidak ada rekening perusahaan', '', false, false));
                    setBankLoadingState('Tidak ada rekening perusahaan yang dapat digunakan.', true);
                }

                bankSelect.trigger('change');
                updatePaymentSummary();
            },
            error: function() {
                if (requestSequence !== paymentBankRequestSequence) {
                    return;
                }

                bankSelect.empty().append(new Option('Gagal memuat rekening', ''));
                bankSelect.trigger('change');
                setBankLoadingState('Rekening gagal dimuat. Periksa koneksi lalu muat ulang.', true);
                updatePaymentSummary();
            }
        });
    }

    function renderPaymentAllocations() {
        const body = $('#paymentAllocationBody').empty();
        const fullPayment = $('#paymentModeFull').is(':checked');
        const items = selectedPaymentItems().sort(function(first, second) {
            return String(first.notaDate).localeCompare(String(second.notaDate));
        });

        items.forEach(function(item) {
            if (fullPayment || item.allocationAmount === undefined) {
                item.allocationAmount = item.remainingAmount;
            }

            const row = $('<tr>').attr('data-nota-number', item.notaNumber);
            const identity = $('<td>')
                .append($('<strong>').text(item.notaNumber))
                .append($('<span>', {
                    class: 'allocation-vendor'
                }).text(item.vendorName + ' · ' + item.orderCount + ' order · ' + formatNotaDate(item.notaDate)));
            const status = $('<td>', {
                class: 'text-center'
            }).append($('<span>', {
                class: 'badge ' + (item.paymentStatus === 'partial' ? 'text-bg-info' : 'text-bg-warning')
            }).text(paymentStatusLabel(item.paymentStatus)));
            const before = $('<td>', {
                class: 'text-end payment-money'
            }).text(formatCurrency(item.remainingAmount));
            const input = $('<input>', {
                type: 'text',
                inputmode: 'numeric',
                autocomplete: 'off',
                class: 'form-control form-control-sm allocation-amount-input',
                'aria-label': 'Nominal pembayaran nota ' + item.notaNumber,
            })
                .attr('data-nota-number', item.notaNumber)
                .prop('readonly', fullPayment)
                .val(formatNumber(item.allocationAmount));
            const inputGroup = $('<div>', {
                class: 'input-group input-group-sm'
            }).append($('<span>', {
                class: 'input-group-text'
            }).text('Rp')).append(input);
            const amountCell = $('<td>').append(inputGroup).append($('<span>', {
                class: 'allocation-message text-muted'
            }));
            const after = $('<td>', {
                class: 'text-end payment-money allocation-after'
            });

            row.append(identity, status, before, amountCell, after);
            body.append(row);
        });

        $('#paymentAllocationCount').text(items.length + ' nota');
        updatePaymentSummary();
    }

    function allocationValidation(item) {
        const amount = Math.round(Number(item.allocationAmount) || 0);

        if (amount < 1) {
            return 'Nominal minimal Rp 1.';
        }
        if (amount > item.remainingAmount) {
            return 'Melebihi sisa ' + formatCurrency(item.remainingAmount) + '.';
        }

        return '';
    }

    function updatePaymentSummary() {
        const items = selectedPaymentItems();
        let totalPayment = 0;
        let totalAfter = 0;
        let firstError = '';

        items.forEach(function(item) {
            item.allocationAmount = Math.round(Number(item.allocationAmount) || 0);
            totalPayment += item.allocationAmount;
            totalAfter += Math.max(0, item.remainingAmount - item.allocationAmount);

            const row = $('#paymentAllocationBody tr').filter(function() {
                return $(this).attr('data-nota-number') === item.notaNumber;
            });
            const error = allocationValidation(item);
            const input = row.find('.allocation-amount-input');
            const message = row.find('.allocation-message');

            input.toggleClass('is-invalid', error !== '').attr('aria-invalid', error !== '' ? 'true' : 'false');
            message.text(error || 'Maks. ' + formatCurrency(item.remainingAmount))
                .toggleClass('text-danger', error !== '')
                .toggleClass('text-muted', error === '');
            row.find('.allocation-after').text(formatCurrency(Math.max(0, item.remainingAmount - item.allocationAmount)));

            if (!firstError && error) {
                firstError = item.notaNumber + ': ' + error;
            }
        });

        const totals = calculateSelectedTotals();
        const vendorCount = new Set(items.map(function(item) {
            return item.fleetCompanyCode || item.vendorName;
        })).size;
        const bankSelected = $('#userBankCode').val() !== '';
        const dateValid = $('#date').val() !== '';
        const ready = items.length > 0 && !firstError && totalPayment > 0 && paymentBanksLoaded && bankSelected && dateValid && !paymentSubmissionInFlight;

        $('#paymentGrandTotal').text(formatCurrency(totalPayment));
        $('#paymentAfterSummary').text('Sisa setelah pembayaran: ' + formatCurrency(totalAfter));
        $('#paymentFactNotas').text(items.length);
        $('#paymentFactVendors').text(vendorCount);
        $('#paymentFactOrders').text(totals.orderCount);
        $('#paymentFactRemaining').text(formatCurrency(totals.remaining));
        $('#paymentSubmitLabel').text(totalPayment > 0 ? 'Bayar ' + formatCurrency(totalPayment) : 'Proses Pembayaran');
        $('#paymentAllocationError').toggleClass('d-none', firstError === '').text(firstError);
        $('#date').toggleClass('is-invalid', !dateValid).attr('aria-invalid', dateValid ? 'false' : 'true');
        $('#userBankCode').toggleClass('is-invalid', paymentBanksLoaded && !bankSelected).attr('aria-invalid', bankSelected ? 'false' : 'true');
        $('#submitPaymentBtn').prop('disabled', !ready);

        if (bankSelected) {
            $('#paymentBankStatus')
                .text('Rekening siap digunakan.')
                .toggleClass('text-danger', false)
                .toggleClass('text-success', true);
        } else {
            $('#paymentBankStatus')
                .text('Pilih rekening sumber dana.')
                .toggleClass('text-danger', false)
                .toggleClass('text-success', false);
        }

        let hint = 'Siap diproses sebagai satu batch pembayaran.';
        if (firstError) {
            hint = 'Perbaiki nominal pembayaran yang ditandai.';
        } else if (!dateValid) {
            hint = 'Isi tanggal pembayaran.';
        } else if (!paymentBanksLoaded || !bankSelected) {
            hint = 'Pilih rekening sumber dana.';
        }
        $('#paymentSubmitHint').text(hint);
    }

    function applyPaymentMode() {
        const fullPayment = $('#paymentModeFull').is(':checked');

        selectedPaymentItems().forEach(function(item) {
            if (fullPayment) {
                item.allocationAmount = item.remainingAmount;
            }
        });

        $('.allocation-amount-input').each(function() {
            const input = $(this);
            const item = selectedOrders[String(input.attr('data-nota-number') || '')];
            input.prop('readonly', fullPayment);
            if (item) {
                input.val(formatNumber(item.allocationAmount));
            }
        });

        updatePaymentSummary();
    }

    function setPaymentSubmitting(isSubmitting) {
        paymentSubmissionInFlight = isSubmitting;
        $('#paymentSubmitSpinner').toggleClass('d-none', !isSubmitting);
        $('#paymentSubmitIcon').toggleClass('d-none', isSubmitting);
        $('#payment-modal [data-bs-dismiss]').prop('disabled', isSubmitting);
        $('#paymentModeFull, #paymentModeCustom, #date, #description, .allocation-amount-input').prop('disabled', isSubmitting);
        $('#userBankCode').prop('disabled', isSubmitting || !paymentBanksLoaded).trigger('change.select2');
        updatePaymentSummary();
    }

    function paymentPayload() {
        return {
            requestKey: paymentRequestKey,
            payments: selectedPaymentItems().map(function(item) {
                return {
                    nota_number: item.notaNumber,
                    amount: Math.round(Number(item.allocationAmount) || 0),
                    expected_remaining: item.remainingAmount,
                };
            }),
            date: $('#date').val(),
            userBankCode: $('#userBankCode').val(),
            description: $('#description').val().trim(),
        };
    }

    function showDetailModal(orderCode) {
        const detailFields = [
            '#detail-code', '#detail-nota-number', '#detail-order-code', '#detail-shipment-number',
            '#detail-plate-number', '#detail-fleet-company', '#detail-driver', '#detail-customer',
            '#detail-billing-amount', '#detail-ppn-amount', '#detail-pph-amount', '#detail-paid-amount',
            '#detail-remaining-amount', '#detail-payment-status', '#detail-bank', '#detail-description'
        ];
        detailFields.forEach(function(selector) {
            $(selector).val('-');
        });
        paintDetailTones();
        $('#payment-history-body').html('<tr><td colspan="4" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Memuat riwayat pembayaran...</td></tr>');
        $('#detail-modal').modal('show');

        $.ajax({
            url: "{{ route('ajax.vendor-invoice-detail', ':orderCode') }}".replace(':orderCode', encodeURIComponent(orderCode)),
            type: 'GET',
            success: function(data) {
                if (!data) {
                    $('#detail-modal').modal('hide');
                    Swal.fire({
                        title: 'Gagal',
                        text: "{{ __('menu_vendor_payment.payment_not_found') }}",
                        icon: 'error',
                    });
                    return;
                }

                $('#detail-code').val(data.batch_code || data.code || '');
                $('#detail-nota-number').val(data.nota_number || '-');

                const associated = Array.isArray(data.associated_payments) ? data.associated_payments : [];
                $('#detail-order-code').val(associated.map(function(payment) { return payment.order ? payment.order.code : ''; }).filter(Boolean).join(', ') || '-');
                $('#detail-shipment-number').val(associated.map(function(payment) { return payment.order ? (payment.order.shipmentNumber || '') : ''; }).filter(Boolean).join(', ') || '-');
                $('#detail-plate-number').val(associated.map(function(payment) { return payment.order && payment.order.fleet ? payment.order.fleet.plateNumber : ''; }).filter(Boolean).join(', ') || '-');
                $('#detail-fleet-company').val([...new Set(associated.map(function(payment) { return payment.order && payment.order.fleet && payment.order.fleet.company ? payment.order.fleet.company.name : ''; }).filter(Boolean))].join(', ') || '-');
                $('#detail-driver').val([...new Set(associated.map(function(payment) { return payment.order && payment.order.driver ? payment.order.driver.name : ''; }).filter(Boolean))].join(', ') || '-');
                $('#detail-customer').val([...new Set(associated.map(function(payment) { return payment.order && payment.order.customer ? payment.order.customer.name : ''; }).filter(Boolean))].join(', ') || '-');

                const billingAmount = data.total_billing || data.amount || 0;
                const paidAmount = data.total_paid || data.paid_amount || 0;
                const remainingAmount = data.total_remaining || data.remaining_amount || 0;
                const ppnAmount = data.nota_ppn || 0;
                const pphAmount = data.nota_pph || 0;
                const claimAmount = data.nota_claim || 0;
                $('#detail-billing-amount').val(formatCurrency(billingAmount));
                $('#detail-ppn-amount').val(formatRate(data.nota_ppn_rate || 0) + '% → ' + formatCurrency(ppnAmount));
                $('#detail-pph-amount').val(formatRate(data.nota_pph_rate || 0) + '% → ' + formatCurrency(pphAmount));
                $('#detail-claim-amount').val(formatCurrency(claimAmount));
                $('#detail-paid-amount').val(formatCurrency(paidAmount));
                $('#detail-remaining-amount').val(formatCurrency(remainingAmount));
                $('#detail-payment-status').val(paymentStatusLabel(data.payment_status));

                let bankInfo = '-';
                if (data.bankInfo) {
                    const bankName = data.bankInfo.bank_name || 'Bank';
                    const accountNumber = data.bankInfo.account_number || '-';
                    const accountName = data.bankInfo.account_name || '-';
                    bankInfo = bankName + ' - ' + accountNumber + ' (' + accountName + ')';
                }
                $('#detail-bank').val(bankInfo);
                $('#detail-description').val(data.description || '');
                paintDetailTones();

                const historyBody = $('#payment-history-body').empty();
                if (Array.isArray(data.payment_histories) && data.payment_histories.length > 0) {
                    data.payment_histories.forEach(function(history) {
                        const paymentDate = history.payment_date ? new Date(history.payment_date + 'T00:00:00').toLocaleDateString('id-ID') : '-';
                        const row = $('<tr>')
                            .append($('<td>').text(paymentDate))
                            .append($('<td>', { class: 'text-end payment-money' }).text(formatCurrency(history.amount)))
                            .append($('<td>').text(history.bank_info || history.user_bank_code || '-'))
                            .append($('<td>').text(history.description || '-'));
                        historyBody.append(row);
                    });
                } else {
                    historyBody.html('<tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat pembayaran.</td></tr>');
                }
            },
            error: function() {
                $('#detail-modal').modal('hide');
                Swal.fire({
                    title: 'Gagal',
                    text: "{{ __('menu_vendor_payment.failed_load_payment') }}",
                    icon: 'error',
                });
            }
        });
    }

    function confirmCancelPayment(orderCode, batchCode) {
        if (!batchCode) {
            Swal.fire({
                title: 'Tidak dapat dibatalkan',
                text: 'Kode batch pembayaran tidak tersedia. Data lama tidak dibatalkan otomatis demi keamanan.',
                icon: 'warning',
            });
            return;
        }

        $('#delete-form').attr('action', "{{ url('vendor/invoice/payment') }}/" + encodeURIComponent(orderCode));
        $('#cancel-payment-batch-code').val(batchCode);
        Swal.fire({
            title: 'Batalkan batch pembayaran terakhir?',
            text: 'Batch dapat mencakup beberapa nota yang dibayar bersamaan. Saldo akan dikembalikan sesuai transaksi batch tersebut.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan Batch',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#dc3545',
        }).then(function(result) {
            if (result.isConfirmed) {
                // Loader selama proses submit pembatalan pembayaran.
                swalLoader('Membatalkan pembayaran', 'Sedang mengembalikan saldo dan memperbarui nota...');
                $('#delete-form').submit();
            }
        });
    }

    function confirmCancelNota(orderCode) {
        $('#cancel-nota-form').attr('action', "{{ url('vendor/invoice/cancel-nota') }}/" + encodeURIComponent(orderCode));
        Swal.fire({
            title: 'Batalkan nota?',
            text: 'Seluruh order di dalam nota akan kembali ke menu Order Menunggu Nota.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan Nota',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#dc3545',
        }).then(function(result) {
            if (result.isConfirmed) {
                // Loader selama proses submit pembatalan nota.
                swalLoader('Membatalkan nota', 'Sedang mengembalikan order ke daftar Order Menunggu Nota...');
                $('#cancel-nota-form').submit();
            }
        });
    }

    $(document).ready(function() {
        vendorPaymentTable = $('#dtUnpaid').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            scrollX: true,
            autoWidth: false,
            pageLength: 25,
            ajax: {
                url: "{{ route('dt.vendor-invoice.unpaid') }}",
            },
            columns: [
                { data: 'select', className: 'text-center', responsivePriority: 1 },
                { data: 'action', className: 'text-center', responsivePriority: 5 },
                { data: 'DT_RowIndex', className: 'text-center', responsivePriority: 13 },
                { data: 'nota_number', className: 'dtr-control', responsivePriority: 1 },
                { data: 'nota_date', render: function(data) { return formatNotaDate(data); }, responsivePriority: 6 },
                { data: 'fleet_company_name', responsivePriority: 2 },
                { data: 'order_count', className: 'text-center', responsivePriority: 9 },
                { data: 'plate_numbers', responsivePriority: 10 },
                { data: 'amount', className: 'text-end', responsivePriority: 8 },
                { data: 'ppn_amount', className: 'text-end', responsivePriority: 12 },
                { data: 'pph_amount', className: 'text-end', responsivePriority: 12 },
                { data: 'claim_amount', className: 'text-end', responsivePriority: 12 },
                { data: 'paid_amount', className: 'text-end', responsivePriority: 7 },
                { data: 'remaining_amount', className: 'text-end fw-semibold', responsivePriority: 2 },
                { data: 'payment_status', className: 'text-center', responsivePriority: 3 },
            ],
            columnDefs: [
                { searchable: false, targets: [0, 1, 2] },
                { orderable: false, targets: [0, 1, 2] },
            ],
            order: [[4, 'asc']],
            drawCallback: function() {
                restoreSelectedCheckboxes();
                updateSelectionSummary();
            },
            language: {
                processing: 'Memuat nota...',
                search: '',
                searchPlaceholder: 'Cari no nota, vendor...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ nota',
                infoEmpty: 'Tidak ada nota belum lunas',
                zeroRecords: 'Tidak ditemukan data yang sesuai',
                paginate: {
                    next: "<i class='mdi mdi-chevron-right'></i>",
                    previous: "<i class='mdi mdi-chevron-left'></i>",
                }
            }
        });

        $.fn.dataTable.ext.errMode = 'none';
        $('#dtUnpaid').on('error.dt', function() {
            Swal.fire({
                title: 'Gagal memuat data',
                text: 'Daftar nota tidak dapat dimuat. Silakan periksa koneksi lalu coba lagi.',
                icon: 'error',
            });
        });

        // Select2 untuk select sumber dana pada modal review pembayaran.
        $('#userBankCode').select2({
            dropdownParent: $('#payment-modal'),
            width: '100%',
        });

        // Select2 untuk select bank pada modal generate nota (modal dibagikan
        // dengan halaman Order Menunggu Nota) agar semua select seragam.
        $('#notaUserBankCode').select2({
            dropdownParent: $('#nota-modal'),
            width: '100%',
        });

        // Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables.
        $('#dtUnpaid_wrapper .dataTables_length select').select2({
            minimumResultsForSearch: Infinity,
            width: '88px',
            dropdownAutoWidth: true,
        });

        $(document).on('click', '.js-vendor-payment-detail', function() {
            showDetailModal(String($(this).attr('data-order-code') || ''));
        });

        $(document).on('click', '.js-vendor-payment-cancel', function() {
            confirmCancelPayment(
                String($(this).attr('data-order-code') || ''),
                String($(this).attr('data-batch-code') || '')
            );
        });

        $(document).on('click', '.js-vendor-nota-cancel', function() {
            confirmCancelNota(String($(this).attr('data-order-code') || ''));
        });

        $(document).on('change', '.row-payment-checkbox', function() {
            const checkbox = $(this);
            const item = readPaymentCheckbox(checkbox);

            if (!item.notaNumber) {
                return;
            }

            if (checkbox.is(':checked')) {
                selectedOrders[item.notaNumber] = item;
            } else {
                delete selectedOrders[item.notaNumber];
            }

            checkbox.closest('tr').toggleClass('table-active', checkbox.is(':checked'));
            updateSelectionSummary();
        });

        $('#selectAllNotas').on('change', function() {
            const checked = $(this).is(':checked');
            $('.row-payment-checkbox[data-checkbox-type="payment"]:visible:not(:disabled)').each(function() {
                if ($(this).is(':checked') !== checked) {
                    $(this).prop('checked', checked).trigger('change');
                }
            });
        });

        $('#clearSelectionBtn').on('click', clearPaymentSelection);

        $('#openPaymentModalBtn').on('click', function() {
            const items = selectedPaymentItems();
            const totals = calculateSelectedTotals();

            if (items.length === 0 || totals.remaining < 1) {
                Swal.fire({
                    title: 'Pilih nota',
                    text: 'Pilih minimal satu nota yang masih memiliki sisa tagihan.',
                    icon: 'warning',
                });
                return;
            }

            paymentRequestKey = generateRequestKey();
            items.forEach(function(item) {
                item.allocationAmount = item.remainingAmount;
            });
            $('#paymentModeFull').prop('checked', true);
            $('#date').val(localDateValue());
            $('#description').val('');
            $('#paymentDescriptionCount').text('0/255');
            $('#userBankCode').val('').trigger('change');
            renderPaymentAllocations();
            setPaymentSubmitting(false);
            $('#payment-modal').modal('show');
            loadBankData(false);
        });

        $('input[name="paymentMode"]').on('change', applyPaymentMode);

        $(document).on('input', '.allocation-amount-input', function() {
            const input = $(this);
            const notaNumber = String(input.attr('data-nota-number') || '');
            const item = selectedOrders[notaNumber];
            const numericValue = String(input.val() || '').replace(/\D/g, '');

            if (!item) {
                return;
            }

            item.allocationAmount = numericValue === '' ? 0 : Number(numericValue);
            input.val(numericValue === '' ? '' : formatNumber(item.allocationAmount));
            updatePaymentSummary();
        });

        $('#date, #userBankCode').on('change', updatePaymentSummary);
        $('#description').on('input', function() {
            $('#paymentDescriptionCount').text(String($(this).val()).length + '/255');
        });
        $('#reloadPaymentBanksBtn').on('click', function() {
            loadBankData(true);
        });

        $('#batch-payment-form').on('submit', function(event) {
            event.preventDefault();
            updatePaymentSummary();

            if ($('#submitPaymentBtn').prop('disabled') || paymentSubmissionInFlight) {
                return;
            }

            const payload = paymentPayload();
            const selectedBankText = $('#userBankCode option:selected').text();
            const totalPayment = payload.payments.reduce(function(total, payment) {
                return total + payment.amount;
            }, 0);
            const confirmationHtml = '<strong>' + escapeHtml(formatCurrency(totalPayment)) + '</strong> untuk '
                + payload.payments.length + ' nota.<br>Sumber dana: ' + escapeHtml(selectedBankText)
                + '<br>Tanggal: ' + escapeHtml(formatNotaDate(payload.date + 'T00:00:00'));

            Swal.fire({
                title: 'Konfirmasi pembayaran vendor',
                html: confirmationHtml,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses Pembayaran',
                cancelButtonText: 'Periksa Lagi',
                confirmButtonColor: '#198754',
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                setPaymentSubmitting(true);
                // Loader selama proses submit pembayaran berjalan.
                swalLoader('Memproses pembayaran', 'Jangan menutup halaman. Sistem sedang mengunci nota dan mencatat mutasi bank.');

                $.ajax({
                    url: $('#batch-payment-form').attr('action'),
                    type: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('#batch-payment-form input[name="_token"]').val(),
                    },
                    success: function(response) {
                        Swal.close();
                        setPaymentSubmitting(false);
                        $('#payment-modal').modal('hide');

                        const result = response.result || {};
                        const outcome = (result.fully_paid_count || 0) + ' nota lunas' + ((result.partial_count || 0) > 0 ? ' · ' + result.partial_count + ' nota masih sebagian' : '');
                        const successHtml = '<strong>' + escapeHtml(formatCurrency(result.payment_amount || 0)) + '</strong> berhasil dicatat.<br>'
                            + escapeHtml(outcome) + '<br>Kode: <strong>' + escapeHtml(result.batch_code || '-') + '</strong>';

                        Swal.fire({
                            title: 'Pembayaran berhasil',
                            html: successHtml,
                            icon: 'success',
                            confirmButtonText: 'Lihat Daftar Terbaru',
                            confirmButtonColor: '#198754',
                        }).then(function() {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.close();
                        setPaymentSubmitting(false);

                        const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Pembayaran gagal diproses. Data tetap tersimpan di form dan dapat dicoba kembali.';
                        const isConflict = xhr.status === 409;

                        if (isConflict) {
                            $('#payment-modal').modal('hide');
                            clearPaymentSelection();
                            vendorPaymentTable.ajax.reload(null, false);
                        }

                        Swal.fire({
                            title: isConflict ? 'Data nota berubah' : 'Pembayaran gagal',
                            text: message,
                            icon: isConflict ? 'warning' : 'error',
                            confirmButtonText: 'Tutup',
                        });
                    }
                });
            });
        });

        updateSelectionSummary();
    });
</script>
