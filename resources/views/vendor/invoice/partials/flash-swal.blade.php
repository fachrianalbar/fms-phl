{{-- Flash message server (success/fail/error) & validation errors → SweetAlert2.
    Partial ini menggantikan tampilan alert Bootstrap (partials/alert.blade.php)
    pada halaman yang menggunakannya.

    Catatan penyertaan: include-kan di dalam @push('script') SETELAH
    assets/js/sweet-alert/sweetalert2.min.js dimuat, mis.:

        <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
        @include('vendor.invoice.partials.flash-swal')
--}}

@php
    $flashLines = [];
    $flashIcon = 'success';
    $flashTitle = 'Berhasil';

    if (Session::has('success')) {
        $flashLines[] = (string) Session::get('success');

        if (Session::has('code_replaced')) {
            $codeReplacement = Session::get('code_replaced');
            $flashLines[] = 'Kode ' . ($codeReplacement['requested'] ?? '-')
                . ' sudah pernah digunakan. Sistem otomatis menggunakan '
                . ($codeReplacement['resolved'] ?? '-') . '.';
        }
    } elseif (Session::has('fail')) {
        $flashIcon = 'error';
        $flashTitle = 'Gagal';
        $flashLines[] = (string) Session::get('fail');
    } elseif (Session::has('error')) {
        $flashIcon = 'error';
        $flashTitle = 'Gagal';
        $flashLines[] = (string) Session::get('error');
    }

    // $errors hanya dibagikan middleware web; beri guard agar partial aman
    // dipakai pada konteks render apa pun.
    $flashErrorBag = (isset($errors) && $errors->any()) ? $errors->all() : [];

    if (!empty($flashErrorBag)) {
        if (empty($flashLines)) {
            $flashIcon = 'error';
            $flashTitle = 'Periksa kembali data';
        }

        foreach ($flashErrorBag as $flashError) {
            $flashLines[] = $flashError;
        }
    }

    $flashHtml = implode('<br>', array_map(fn ($line) => e($line), $flashLines));
@endphp

@if ($flashLines)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: @json($flashTitle),
                html: @json($flashHtml),
                icon: @json($flashIcon),
                confirmButtonText: 'Mengerti',
                confirmButtonColor: @json($flashIcon === 'error' ? '#dc3545' : '#198754'),
            });
        });
    </script>
@endif
