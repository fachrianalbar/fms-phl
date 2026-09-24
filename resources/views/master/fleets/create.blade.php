@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@push('style')
    {{-- Flatpickr & Select2 CSS --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    <style>
        /* ── Section Title Card Header ── */
        .form-section-title {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 8px;
            margin-bottom: 16px;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            color: #334155;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }

        /* ── Input Tanggal Flatpickr ── */
        .flatpickr-input-custom {
            height: 38px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            color: #334155 !important;
            background-color: #ffffff !important;
            padding: 0 12px 0 36px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: 12px center !important;
            background-size: 15px 15px !important;
            transition: all 0.2s ease !important;
        }

        .flatpickr-input-custom:focus {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
            background-color: #ffffff !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234f46e5' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E") !important;
        }

        /* ── Select2 Terstandarisasi 38px ── */
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease !important;
        }

        .select2-container--default .select2-selection--single:hover {
            border-color: #94a3b8 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            font-size: 13px !important;
            line-height: 36px !important;
            padding-left: 12px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
            font-size: 13px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 10px !important;
            top: 1px !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
        }

        .select2-dropdown {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }

        /* ── Tombol Primer Terstandarisasi ── */
        .btn-submit-primary {
            height: 38px !important;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            padding: 0 20px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25) !important;
            transition: all 0.2s ease !important;
        }

        .btn-submit-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35) !important;
            transform: translateY(-1px) !important;
        }

        /* ── Dark mode ─────────────────────────────── */
        html[data-bs-theme="dark"] .form-section-title {
            color: var(--bs-heading-color) !important;
            border-bottom-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .form-label {
            color: var(--bs-body-color) !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            {{-- Card Header --}}
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-truck-plus text-primary fs-20"></i>
                        {{ $title }} {{ __('general.add_data') }}
                    </h4>
                    <small class="text-muted">Lengkapi data armada, kepemilikan, spesifikasi teknis, dan masa berlaku dokumen</small>
                </div>

                <a href="{{ route($view . 'index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                    style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                    <i class="mdi mdi-arrow-left"></i> {{ __('general.back_to_list') }}
                </a>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                <form method="post" action="{{ route($view . 'store') }}" enctype="multipart/form-data" id="fleetForm">
                    @csrf

                    {{-- Bagian 1: Informasi Armada & Kepemilikan --}}
                    <div class="form-section-title">
                        <i class="mdi mdi-card-account-details-outline text-primary fs-16"></i>
                        <span>1. Informasi Armada & Kepemilikan</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="plateNumber">
                                {{ __('menu_fleet.plate_number') }} <span class="text-danger">*</span>
                            </label>
                            <input class="form-control font-monospace text-uppercase" name="plateNumber" id="plateNumber"
                                type="text" placeholder="Contoh: B 1234 CD" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetCompanyCode">
                                {{ __('menu_fleet.company') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2-form" name="fleetCompanyCode" id="fleetCompanyCode" required style="width: 100%;">
                                <option value="">{{ __('general.choose') }} Perusahaan...</option>
                                @foreach ($company as $item)
                                    <option value="{{ $item->code }}" data-type="{{ $item->type }}">
                                        {{ $item->name }}{{ $item->type ? ' (' . $item->type . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="fleetBrandCode">{{ __('menu_fleet.brand') }}</label>
                            <select class="form-select select2-form" name="fleetBrandCode" id="fleetBrandCode" style="width: 100%;">
                                <option value="">{{ __('general.choose') }} Merek...</option>
                                @foreach ($brand as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="fleetTypeCode">{{ __('menu_fleet.type') }}</label>
                            <select class="form-select select2-form" name="fleetTypeCode" id="fleetTypeCode" style="width: 100%;">
                                <option value="">{{ __('general.choose') }} Tipe...</option>
                                @foreach ($type as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="year">{{ __('menu_fleet.year') }}</label>
                            <input class="form-control" name="year" id="year" type="number" min="1990" max="{{ date('Y') + 1 }}"
                                placeholder="Contoh: {{ date('Y') }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label" for="driverCode">
                                {{ __('menu_fleet.driver') }} <span class="text-danger" id="driver-required-icon" style="display: none;">*</span>
                            </label>
                            <select class="form-select select2-form" name="driverCode" id="driverCode" style="width: 100%;">
                                <option value="">{{ __('general.choose') }} Pengemudi...</option>
                                @foreach ($driver as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->name }}{{ $item->position?->name ? ' (' . $item->position->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="driver-help-text">Wajib diisi jika jenis kepemilikan armada adalah Internal</small>
                        </div>
                    </div>

                    {{-- Bagian 2: Spesifikasi Nomor Rangka & Mesin --}}
                    <div class="form-section-title">
                        <i class="mdi mdi-engine-outline text-primary fs-16"></i>
                        <span>2. Identifikasi Nomor Rangka & Mesin</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="frameNumber">{{ __('menu_fleet.frame_number') }}</label>
                            <input class="form-control font-monospace" name="frameNumber" id="frameNumber" type="text"
                                placeholder="Masukkan nomor rangka kendaraan">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="engineNumber">{{ __('menu_fleet.engine_number') }}</label>
                            <input class="form-control font-monospace" name="engineNumber" id="engineNumber" type="text"
                                placeholder="Masukkan nomor mesin kendaraan">
                        </div>
                    </div>

                    {{-- Bagian 3: Masa Berlaku Dokumen & Perizinan (Flatpickr) --}}
                    <div class="form-section-title">
                        <i class="mdi mdi-calendar-clock text-primary fs-16"></i>
                        <span>3. Masa Berlaku Dokumen & Perizinan (Flatpickr)</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label" for="vehicleRegistrationDueDate">
                                {{ __('menu_fleet.vehicle_registration_due_date') }} (STNK)
                            </label>
                            <input class="form-control flatpickr-input-custom" name="vehicleRegistrationDueDate"
                                id="vehicleRegistrationDueDate" type="text" placeholder="Pilih Jatuh Tempo STNK">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="vehicleTax">{{ __('menu_fleet.vehicle_tax') }} (Pajak)</label>
                            <input class="form-control flatpickr-input-custom" name="vehicleTax" id="vehicleTax"
                                type="text" placeholder="Pilih Tanggal Pajak">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="vehicleKir">{{ __('menu_fleet.vehicle_kir') }} (KIR)</label>
                            <input class="form-control flatpickr-input-custom" name="vehicleKir" id="vehicleKir"
                                type="text" placeholder="Pilih Tanggal KIR">
                        </div>
                    </div>

                    {{-- Bagian 4: Lampiran Dokumen & Foto Fisik Armada --}}
                    <div class="form-section-title">
                        <i class="mdi mdi-camera-plus-outline text-primary fs-16"></i>
                        <span>4. Lampiran Dokumen & Foto Fisik Armada</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="barcode">Barcode (File Gambar)</label>
                            <input class="form-control file" id="barcode" name="barcode" type="file"
                                accept=".jpg, .jpeg, .png">
                            <small class="text-muted">Format: JPG, JPEG, PNG (Maksimal 2MB)</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="vehicleRegistrationNumber">
                                {{ __('menu_fleet.vehicle_registration_number') }} (Foto STNK)
                            </label>
                            <input class="form-control" name="vehicleRegistrationNumber" id="vehicleRegistrationNumber"
                                type="file" accept=".jpg, .jpeg, .png">
                            <small class="text-muted">Format: JPG, JPEG, PNG (Maksimal 2MB)</small>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0" for="fleetPicture">
                                    {{ __('menu_fleet.vehicle') }} (Foto Fisik Armada)
                                </label>
                                <button id="addInputFile" type="button"
                                    class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                    style="border-radius: 6px; font-weight: 600; font-size: 12px;">
                                    <i class="mdi mdi-plus"></i> Tambah Foto Lagi
                                </button>
                            </div>

                            <input class="form-control mb-2" name="fleetPicture[0]" id="fleetPicture" type="file"
                                accept=".jpg, .jpeg, .png">

                            <div id="listInputFile"></div>
                            <small class="text-muted">Anda dapat menambahkan lebih dari satu foto fisik kendaraan armada</small>
                        </div>
                    </div>

                    {{-- Tombol Submit & Batal --}}
                    <div class="d-flex align-items-center gap-2 pt-3 border-top" style="border-color: #e2e8f0;">
                        <button class="btn btn-submit-primary" type="submit">
                            <i class="mdi mdi-content-save-outline fs-16"></i> Simpan Armada
                        </button>
                        <a href="{{ route($view . 'index') }}" class="btn btn-sm btn-outline-secondary"
                            style="border-radius: 8px; font-weight: 600; padding: 8px 18px; height: 38px; display: inline-flex; align-items: center;">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>

    <script>
        $(document).ready(function() {
            // 1. Inisialisasi Select2
            $('.select2-form').select2({
                placeholder: 'Pilih Opsi...',
                allowClear: true
            });

            // 2. Inisialisasi Flatpickr Tanggal
            flatpickr('#vehicleRegistrationDueDate', {
                dateFormat: 'Y-m-d',
                allowInput: true
            });

            flatpickr('#vehicleTax', {
                dateFormat: 'Y-m-d',
                allowInput: true
            });

            flatpickr('#vehicleKir', {
                dateFormat: 'Y-m-d',
                allowInput: true
            });

            // 3. Logika Driver Wajib jika Perusahaan Internal
            $('#fleetCompanyCode').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var companyType = selectedOption.data('type');

                if (companyType === 'External') {
                    $('#driverCode').removeAttr('required');
                    $('#driver-required-icon').hide();
                    $('#driver-help-text').text('Opsional untuk armada External');
                } else if (companyType === 'Internal') {
                    $('#driverCode').attr('required', true);
                    $('#driver-required-icon').show();
                    $('#driver-help-text').text('Wajib diisi untuk armada Internal');
                } else {
                    $('#driverCode').removeAttr('required');
                    $('#driver-required-icon').hide();
                    $('#driver-help-text').text('Wajib diisi jika armada berjenis Internal');
                }
            });

            // Trigger saat load jika sudah ada data terpilih
            $('#fleetCompanyCode').trigger('change');
        });

        // 4. Dynamic Multiple Input File Tambahan
        const addInputFile = document.getElementById("addInputFile");
        const listInputFile = document.getElementById("listInputFile");

        let inputFileCount = 1;
        if (addInputFile) {
            addInputFile.addEventListener('click', () => {
                listInputFile.appendChild(addFile(inputFileCount));
                inputFileCount++;
            });
        }

        function addFile(num) {
            const div = document.createElement('div');
            div.classList.add('d-flex', 'align-items-center', 'gap-2', 'mb-2', `file${num}`);
            div.innerHTML = `
                <input type="file" class="form-control" name="fleetPicture[${num}]" accept=".jpg, .jpeg, .png">
                <button type="button" onclick="removeFile(${num})" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center"
                    style="height: 38px; width: 38px; min-width: 38px; border-radius: 8px;" title="Hapus Input">
                    <i class="mdi mdi-delete-outline fs-16"></i>
                </button>
            `;
            return div;
        }

        function removeFile(num) {
            let content = document.querySelector(`.file${num}`);
            if (content) {
                content.remove();
            }
        }
    </script>
@endpush
