{{-- Flash message server (success/fail/error) & validation errors → SweetAlert2.
    Include-kan di dalam @push('script') SETELAH assets/js/sweet-alert/sweetalert2.min.js. --}}

@php
    $flashLines = [];
    $flashIcon = 'success';
    $flashTitle = 'Berhasil';

    if (Session::has('success')) {
        $flashLines[] = (string) Session::get('success');
    } elseif (Session::has('fail')) {
        $flashIcon = 'error';
        $flashTitle = 'Gagal';
        $flashLines[] = (string) Session::get('fail');
    } elseif (Session::has('error')) {
        $flashIcon = 'error';
        $flashTitle = 'Gagal';
        $flashLines[] = (string) Session::get('error');
    }

    $flashErrorBag = (isset($errors) && $errors->any()) ? $errors->all() : [];

    if (! empty($flashErrorBag)) {
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
