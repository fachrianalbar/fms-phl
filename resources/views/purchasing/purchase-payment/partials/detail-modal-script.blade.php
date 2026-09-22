{{-- Skrip modal detail pembelian. Include di dalam @push('script') SETELAH
    jQuery + SweetAlert2 dimuat. Mendefinisikan window.openPurchaseDetail(code)
    dan delegasi tombol .btn-detail-row. --}}

<script>
    (function() {
        const pdDetailUrlTemplate = @json(route('purchasing.purchase-payment.detail', ['purchaseCode' => '__CODE__']));

        function pdFormatCurrency(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 0
            }).format(Math.round(Number(value) || 0));
        }

        function pdStatusBadge(status) {
            if (status === 'Paid') return '<span class="badge bg-success">Paid / Lunas</span>';
            if (status === 'Partial') return '<span class="badge bg-warning text-dark">Partial / Sebagian</span>';
            return '<span class="badge bg-secondary">Unpaid / Belum Bayar</span>';
        }

        window.openPurchaseDetail = function(code) {
            if (!code) return;

            $('#pd-code').text(code);
            $('#pd-supplier').text('-');
            $('#pd-status').html('-');
            $('#pd-date').text('-');
            $('#pd-due-date').text('-');
            $('#pd-billing, #pd-paid, #pd-remaining').text('Rp 0');
            $('#pd-history-body').empty();
            $('#pd-history-empty').addClass('d-none');

            $('#purchase-detail-modal').modal('show');

            $.ajax({
                url: pdDetailUrlTemplate.replace('__CODE__', encodeURIComponent(code)),
                type: 'GET',
                dataType: 'json',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(data) {
                    $('#pd-code').text(data.code || code);
                    $('#pd-supplier').text(data.supplier || '-');
                    $('#pd-status').html(pdStatusBadge(data.payment_status));
                    $('#pd-date').text(data.date || '-');
                    $('#pd-due-date').text(data.due_date || '-');
                    $('#pd-billing').text(pdFormatCurrency(data.billing));
                    $('#pd-paid').text(pdFormatCurrency(data.paid));
                    $('#pd-remaining').text(pdFormatCurrency(data.remaining));

                    const histories = Array.isArray(data.histories) ? data.histories : [];
                    const body = $('#pd-history-body').empty();

                    if (histories.length === 0) {
                        $('#pd-history-empty').removeClass('d-none');
                        return;
                    }

                    histories.forEach(function(history, index) {
                        const row = $('<tr>')
                            .append($('<td>', {
                                class: 'text-center'
                            }).text(index + 1))
                            .append($('<td>').text(history.payment_date || '-'))
                            .append($('<td>').append($('<code>').text(history.batch_code || '-')))
                            .append($('<td>').text(history.bank || '-'))
                            .append($('<td>', {
                                class: 'text-end fw-semibold'
                            }).text(pdFormatCurrency(history.amount)))
                            .append($('<td>').text(history.description || '-'));

                        body.append(row);
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Gagal memuat detail',
                        text: 'Data detail pembelian tidak dapat dimuat. Silakan coba lagi.',
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                }
            });
        };

        // Delegasi klik tombol detail (dipakai index & paid).
        $(document).on('click', '.btn-detail-row', function() {
            openPurchaseDetail(String($(this).attr('data-code') || ''));
        });
    })();
</script>
