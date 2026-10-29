{{-- Handler tombol "Sinkronkan nominal" per baris nota (unpaid, partial, paid).
     Dipasangkan dengan vendor/invoice/partials/nota-sync-modal.blade.php.
     Wajib di-include di dalam @push('script') agar jQuery & Bootstrap sudah tersedia. --}}
<script>
    (function () {
        const previewUrlTemplate = "{{ route('ajax.vendor-invoice-sync-preview', ['orderCode' => '__ORDER__']) }}";
        const syncUrlTemplate = "{{ route('vendor.invoice.sync-nota', ['orderCode' => '__ORDER__']) }}";
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        let activeOrderCode = null;
        let previewData = null;
        let requestSequence = 0;
        let submissionInFlight = false;

        const modalElement = document.getElementById('nota-sync-modal');
        const modalInstance = modalElement && window.bootstrap
            ? window.bootstrap.Modal.getOrCreateInstance(modalElement)
            : null;

        function money(value) {
            const num = Math.round(Number(value) || 0);
            const sign = num < 0 ? '-' : '';

            return sign + 'Rp ' + Math.abs(num).toLocaleString('id-ID', {
                maximumFractionDigits: 0
            });
        }

        function rateText(value) {
            return Number(value || 0).toLocaleString('id-ID', {
                maximumFractionDigits: 4
            }) + '%';
        }

        function escapeHtml(value) {
            return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[character];
            });
        }

        function notify(type, title, message) {
            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    icon: type,
                    title: title,
                    text: message,
                    confirmButtonText: 'Tutup'
                });

                return;
            }

            if (typeof window.swal === 'function') {
                window.swal(title, message, type);

                return;
            }

            window.alert(title + '\n' + message);
        }

        function reloadTables() {
            if (!window.jQuery || !$.fn.dataTable || typeof $.fn.dataTable.tables !== 'function') {
                window.location.reload();

                return;
            }

            const tables = $.fn.dataTable.tables({ visible: true, api: true });

            if (!tables.length) {
                window.location.reload();

                return;
            }

            $(tables).each(function () {
                $(this).DataTable().ajax.reload(null, false);
            });
        }

        function setConfirmEnabled(enabled, label) {
            const button = $('#nota-sync-confirm');

            button.prop('disabled', !enabled);
            button.html('<i class="mdi mdi-sync"></i> ' + (label || 'Ya, Sinkronkan'));
        }

        function setAlert(type, message) {
            const icons = {
                info: 'mdi-information-outline',
                success: 'mdi-check-circle-outline',
                warning: 'mdi-alert-outline',
                danger: 'mdi-alert-circle-outline'
            };

            $('#nota-sync-alert')
                .removeClass('is-info is-success is-warning is-danger')
                .addClass('is-' + type);
            $('#nota-sync-alert i').attr('class', 'mdi ' + (icons[type] || icons.info));
            $('#nota-sync-alert-text').text(message);
        }

        function resetModalState() {
            activeOrderCode = null;
            previewData = null;
            requestSequence += 1;

            $('#nota-sync-number, #nota-sync-vendor').text('-');
            $('#nota-sync-total-old, #nota-sync-total-new, #nota-sync-diff')
                .text(money(0))
                .removeClass('nota-sync-diff-up nota-sync-diff-down');
            $('#nota-sync-foot').addClass('d-none');
            $('#nota-sync-note').text('');
            $('#nota-sync-body').html('<tr><td colspan="7" class="nota-sync-empty">Memuat data order...</td></tr>');
            setAlert('info', 'Memuat pratinjau sinkronisasi...');
            setConfirmEnabled(false);
        }

        function renderRows(items) {
            if (!items.length) {
                $('#nota-sync-body').html('<tr><td colspan="7" class="nota-sync-empty">Nota ini tidak memiliki baris order.</td></tr>');

                return;
            }

            const rows = items.map(function (item, index) {
                const difference = Math.round(Number(item.difference) || 0);
                const deltaClass = difference > 0 ? 'is-up' : (difference < 0 ? 'is-down' : '');
                const deltaText = difference === 0 ? '-' : (difference > 0 ? '+ ' : '- ') + money(Math.abs(difference));
                const overpaidBadge = item.overpaid
                    ? '<span class="badge rounded-pill text-bg-danger ms-2" style="font-size: 10px; font-weight: 600;">Kelebihan bayar</span>'
                    : '';

                return '<tr class="' + (item.changed ? 'is-changed' : '') + '">'
                    + '<td class="text-center">' + (index + 1) + '</td>'
                    + '<td class="nota-sync-code">' + escapeHtml(item.order_code) + overpaidBadge + '</td>'
                    + '<td class="nota-sync-money">' + money(item.current_amount) + '</td>'
                    + '<td class="nota-sync-money">' + money(item.new_amount) + '</td>'
                    + '<td class="nota-sync-delta ' + deltaClass + '">' + deltaText + '</td>'
                    + '<td class="nota-sync-money">' + money(item.paid_amount) + '</td>'
                    + '<td class="nota-sync-money">' + money(item.new_remaining) + '</td>'
                    + '</tr>';
            });

            $('#nota-sync-body').html(rows.join(''));
        }

        function renderPreview(data) {
            const totalOld = Math.round(Number(data.total_current) || 0);
            const totalNew = Math.round(Number(data.total_new) || 0);
            const totalDiff = totalNew - totalOld;
            const totalPaid = Math.round(Number(data.total_paid) || 0);
            const totalRemaining = (data.items || []).reduce(function (carry, item) {
                return carry + Math.round(Number(item.new_remaining) || 0);
            }, 0);

            $('#nota-sync-number').text(data.nota_number || '-');
            $('#nota-sync-vendor').text(data.fleet_company_name || '-');
            $('#nota-sync-total-old').text(money(totalOld));
            $('#nota-sync-total-new').text(money(totalNew));

            const diffLabel = $('#nota-sync-diff')
                .text(totalDiff === 0 ? money(0) : (totalDiff > 0 ? '+ ' : '- ') + money(Math.abs(totalDiff)))
                .removeClass('nota-sync-diff-up nota-sync-diff-down');

            if (totalDiff > 0) {
                diffLabel.addClass('nota-sync-diff-up');
            } else if (totalDiff < 0) {
                diffLabel.addClass('nota-sync-diff-down');
            }

            renderRows(data.items || []);

            $('#nota-sync-foot').removeClass('d-none');
            $('#nota-sync-foot-old').text(money(totalOld));
            $('#nota-sync-foot-new').text(money(totalNew));

            const footDiff = $('#nota-sync-foot-diff')
                .text(totalDiff === 0 ? '-' : (totalDiff > 0 ? '+ ' : '- ') + money(Math.abs(totalDiff)))
                .removeClass('is-up is-down');

            if (totalDiff > 0) {
                footDiff.addClass('is-up');
            } else if (totalDiff < 0) {
                footDiff.addClass('is-down');
            }

            $('#nota-sync-foot-paid').text(money(totalPaid));
            $('#nota-sync-foot-remaining').text(money(totalRemaining));

            $('#nota-sync-note').text(
                'DPP ' + money(data.total_dpp)
                + ' · PPN ' + rateText(data.ppn_rate) + ' = ' + money(data.ppn_amount)
                + ' · PPh ' + rateText(data.pph_rate) + ' = ' + money(data.pph_amount)
                + ' · Claim ' + money(data.claim_amount)
                + '. Pajak & claim didistribusikan proporsional ke setiap order.'
            );

            if (data.invalid) {
                setAlert('danger', (data.warnings || []).join(' ') || 'Nominal nota tidak valid untuk disinkronkan.');
                setConfirmEnabled(false);

                return;
            }

            if (!data.has_change) {
                setAlert('info', 'Nominal nota sudah sesuai dengan harga order terkini. Tidak ada yang perlu disinkronkan.');
                setConfirmEnabled(false, 'Sudah Sinkron');

                return;
            }

            if (data.has_overpaid) {
                setAlert('warning', 'Ada order yang nominal barunya lebih kecil dari pembayaran yang sudah tercatat. Sisa tagihan akan menjadi Rp 0 dan nota dianggap lunas — kelebihan bayar tetap tersimpan pada nilai terbayar.');
                setConfirmEnabled(true);

                return;
            }

            if (data.has_payment) {
                setAlert('warning', 'Nota ini sudah memiliki pembayaran. Sinkronisasi hanya mengubah tagihan dan sisa; riwayat pembayaran tidak berubah.');
                setConfirmEnabled(true);

                return;
            }

            setAlert('success', 'Nominal ' + (data.items || []).length + ' order pada nota ini akan disesuaikan dengan harga order terkini.');
            setConfirmEnabled(true);
        }

        function openPreview(orderCode) {
            resetModalState();
            activeOrderCode = orderCode;

            if (modalInstance) {
                modalInstance.show();
            }

            const sequence = requestSequence;

            $.ajax({
                url: previewUrlTemplate.replace('__ORDER__', encodeURIComponent(orderCode)),
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (sequence !== requestSequence) {
                        return;
                    }

                    if (!response || response.success !== true || !response.data) {
                        setAlert('danger', (response && response.message) || 'Pratinjau sinkronisasi tidak tersedia.');
                        $('#nota-sync-body').html('<tr><td colspan="7" class="nota-sync-empty">Data nota tidak ditemukan.</td></tr>');
                        setConfirmEnabled(false);

                        return;
                    }

                    previewData = response.data;
                    renderPreview(previewData);
                },
                error: function (xhr) {
                    if (sequence !== requestSequence) {
                        return;
                    }

                    const message = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat pratinjau sinkronisasi.';
                    setAlert('danger', message);
                    $('#nota-sync-body').html('<tr><td colspan="7" class="nota-sync-empty">' + escapeHtml(message) + '</td></tr>');
                    setConfirmEnabled(false);
                }
            });
        }

        function submitSync() {
            if (submissionInFlight || !activeOrderCode || !previewData) {
                return;
            }

            if (previewData.invalid || !previewData.has_change) {
                return;
            }

            submissionInFlight = true;
            setConfirmEnabled(false, 'Menyinkronkan...');

            $.ajax({
                url: syncUrlTemplate.replace('__ORDER__', encodeURIComponent(activeOrderCode)),
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                data: {
                    _token: csrfToken
                },
                success: function (response) {
                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    notify('success', 'Berhasil', (response && response.message) || 'Nominal nota berhasil disinkronkan.');
                    reloadTables();
                },
                error: function (xhr) {
                    const message = (xhr.responseJSON && xhr.responseJSON.message) || 'Sinkronisasi nominal nota gagal diproses.';
                    setAlert('danger', message);
                    setConfirmEnabled(true);
                    notify('error', 'Gagal', message);
                },
                complete: function () {
                    submissionInFlight = false;
                }
            });
        }

        $(document).on('click', '.js-nota-sync', function (event) {
            event.preventDefault();

            const orderCode = String($(this).attr('data-order-code') || '').trim();

            if (orderCode === '') {
                return;
            }

            openPreview(orderCode);
        });

        $(document).on('click', '#nota-sync-confirm', function (event) {
            event.preventDefault();
            submitSync();
        });

        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function () {
                submissionInFlight = false;
                resetModalState();
            });
        }
    })();
</script>
