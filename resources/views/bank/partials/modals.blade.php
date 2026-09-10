{{-- Modal CRUD User Bank. Create dan edit memakai satu modal agar alur kerja tetap ringkas. --}}

<style>
    /* Modal User Bank: satu bahasa visual dengan kartu dan tabel halaman Bank. */
    .user-bank-modal .modal-dialog {
        max-width: 780px;
        padding: 12px;
    }

    .user-bank-modal .modal-content {
        overflow: hidden;
        border: 1px solid #dbe4ef;
        border-radius: 18px;
        box-shadow: 0 24px 64px rgba(15, 23, 42, 0.22);
    }

    .user-bank-modal-header {
        position: relative;
        overflow: hidden;
        padding: 22px 26px;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 58%, #1e40af 100%);
        border-bottom: 0;
    }

    .user-bank-modal-header::after {
        position: absolute;
        top: -72px;
        right: -36px;
        width: 210px;
        height: 210px;
        content: '';
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 50%;
        box-shadow: 0 0 0 22px rgba(255, 255, 255, 0.035), 0 0 0 44px rgba(255, 255, 255, 0.025);
        pointer-events: none;
    }

    .user-bank-modal-header .btn-close {
        position: relative;
        z-index: 1;
        opacity: 0.85;
        filter: brightness(0) invert(1);
    }

    .user-bank-modal-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        color: #ffffff;
        font-size: 27px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 14px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }

    .user-bank-modal-eyebrow {
        margin-bottom: 4px;
        color: rgba(255, 255, 255, 0.72);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .user-bank-modal .modal-title {
        color: #ffffff;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .user-bank-modal-description {
        max-width: 470px;
        margin: 3px 0 0;
        color: rgba(255, 255, 255, 0.82);
        font-size: 12px;
        line-height: 1.5;
    }

    .user-bank-modal-mode {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 3px 10px;
        color: #dbeafe;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        background: rgba(15, 23, 42, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 999px;
    }

    .user-bank-modal .modal-body {
        padding: 22px 26px;
        background: #f8fafc;
    }

    .user-bank-section {
        padding: 17px 18px 18px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }

    .user-bank-section + .user-bank-section {
        margin-top: 14px;
    }

    .user-bank-section-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eef2f7;
    }

    .user-bank-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        color: #2563eb;
        font-size: 18px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 10px;
    }

    .user-bank-section-title {
        margin: 0;
        color: #1e293b;
        font-size: 13px;
        font-weight: 700;
    }

    .user-bank-section-subtitle {
        margin: 2px 0 0;
        color: #64748b;
        font-size: 11px;
        line-height: 1.4;
    }

    .user-bank-field label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .user-bank-required {
        color: #dc2626;
        font-size: 10px;
        font-weight: 600;
    }

    .user-bank-control-shell {
        position: relative;
    }

    .user-bank-control-shell > .mdi {
        position: absolute;
        z-index: 5;
        top: 50%;
        left: 14px;
        color: #64748b;
        font-size: 18px;
        line-height: 1;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .user-bank-control-shell .form-control,
    .user-bank-control-shell .select2-container .select2-selection--single {
        min-height: 42px;
        color: #1e293b;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .user-bank-control-shell .form-control {
        padding: 9px 13px 9px 42px;
        font-size: 12.5px;
    }

    .user-bank-control-shell .form-control::placeholder {
        color: #94a3b8;
    }

    .user-bank-control-shell .form-control:focus,
    .user-bank-control-shell:focus-within .select2-selection--single {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14);
        outline: 0;
    }

    .user-bank-control-shell .select2-container {
        width: 100% !important;
    }

    .user-bank-control-shell .select2-container .select2-selection--single {
        display: flex;
        align-items: center;
        height: 42px;
        padding: 0 34px 0 42px;
    }

    .user-bank-control-shell .select2-container .select2-selection__rendered {
        width: 100%;
        padding: 0;
        overflow: hidden;
        color: #1e293b;
        font-size: 12.5px;
        line-height: 40px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .user-bank-control-shell .select2-container .select2-selection__placeholder {
        color: #94a3b8;
    }

    .user-bank-control-shell .select2-container .select2-selection__arrow {
        top: 7px;
        right: 10px;
        height: 26px;
    }

    .user-bank-helper {
        display: block;
        margin-top: 6px;
        color: #64748b;
        font-size: 10.5px;
        line-height: 1.45;
    }

    .user-bank-balance-field {
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .user-bank-balance-field .form-control {
        font-weight: 600;
    }

    .user-bank-currency {
        position: absolute;
        z-index: 6;
        top: 50%;
        left: 13px;
        color: #2563eb;
        font-size: 11px;
        font-weight: 800;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .user-bank-modal .modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 26px 19px;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
    }

    .user-bank-footer-note {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        line-height: 1.45;
    }

    .user-bank-footer-actions {
        display: flex;
        flex: 0 0 auto;
        gap: 8px;
    }

    .user-bank-footer-actions .btn {
        min-width: 96px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .user-bank-footer-actions .btn-primary {
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    @media (max-width: 767.98px) {
        .user-bank-modal .modal-dialog {
            min-height: calc(100% - 1rem);
            margin: 0.5rem;
            padding: 0;
        }

        .user-bank-modal-header,
        .user-bank-modal .modal-footer {
            padding-right: 18px;
            padding-left: 18px;
        }

        .user-bank-modal .modal-body {
            padding: 14px;
        }

        .user-bank-modal-icon {
            width: 44px;
            height: 44px;
            flex-basis: 44px;
            font-size: 23px;
        }

        .user-bank-modal .modal-title {
            font-size: 18px;
        }

        .user-bank-modal .modal-footer {
            align-items: stretch;
            flex-direction: column;
            gap: 12px;
        }

        .user-bank-footer-actions {
            width: 100%;
        }

        .user-bank-footer-actions .btn {
            flex: 1 1 0;
        }
    }
</style>

<!-- Modal CRUD User Bank -->
<div class="modal fade user-bank-modal" id="userBankModal" tabindex="-1" aria-labelledby="userBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="userBankForm" method="POST">
                @csrf

                <div class="modal-header user-bank-modal-header">
                    <div class="d-flex align-items-center gap-3 position-relative z-1">
                        <div class="user-bank-modal-icon" aria-hidden="true">
                            <i class="mdi mdi-bank-plus"></i>
                        </div>
                        <div>
                            <div class="user-bank-modal-eyebrow">Master rekening</div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h5 class="modal-title mb-0" id="userBankModalLabel">Tambah User Bank</h5>
                                <span class="user-bank-modal-mode" id="userBankModalMode">Tambah data</span>
                            </div>
                            <p class="user-bank-modal-description" id="userBankModalDescription">
                                Tambahkan rekening baru untuk digunakan dalam transaksi perusahaan.
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
                </div>

                <div class="modal-body">
                    <div class="user-bank-section">
                        <div class="user-bank-section-heading">
                            <span class="user-bank-section-icon" aria-hidden="true"><i class="mdi mdi-card-account-details-outline"></i></span>
                            <div>
                                <h6 class="user-bank-section-title">Informasi Rekening</h6>
                                <p class="user-bank-section-subtitle">Masukkan bank dan identitas pemilik rekening.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 user-bank-field">
                                <label for="bankCode">
                                    <span>Bank Name</span>
                                    <span class="user-bank-required">Wajib</span>
                                </label>
                                <div class="user-bank-control-shell">
                                    <i class="mdi mdi-bank-outline" aria-hidden="true"></i>
                                    <select class="js-example-basic-single form-select" name="bankCode" id="bankCode" required>
                                        <option value="">{{ __('general.choose') }}...</option>
                                        @foreach ($bank as $item)
                                            <option value="{{ $item->code }}">{{ $item->bankCode.' - '.$item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="user-bank-helper">Pilih bank sesuai rekening yang akan dicatat.</small>
                            </div>

                            <div class="col-md-6 user-bank-field">
                                <label for="accountNumber">
                                    <span>Account Number</span>
                                    <span class="user-bank-required">Wajib</span>
                                </label>
                                <div class="user-bank-control-shell">
                                    <i class="mdi mdi-numeric" aria-hidden="true"></i>
                                    <input class="form-control" name="accountNumber" id="accountNumber" type="text" required
                                        placeholder="Contoh: 1234567890" autocomplete="off">
                                </div>
                                <small class="user-bank-helper">Gunakan nomor rekening tanpa spasi atau tanda baca.</small>
                            </div>

                            <div class="col-12 user-bank-field">
                                <label for="accountName">
                                    <span>Account Name</span>
                                    <span class="user-bank-required">Wajib</span>
                                </label>
                                <div class="user-bank-control-shell">
                                    <i class="mdi mdi-account-outline" aria-hidden="true"></i>
                                    <input class="form-control" name="accountName" id="accountName" type="text" required
                                        placeholder="Nama pemilik rekening" autocomplete="off">
                                </div>
                                <small class="user-bank-helper">Nama harus sesuai dengan pemilik rekening pada bank.</small>
                            </div>
                        </div>
                    </div>

                    <div class="user-bank-section">
                        <div class="user-bank-section-heading">
                            <span class="user-bank-section-icon" aria-hidden="true"><i class="mdi mdi-shape-outline"></i></span>
                            <div>
                                <h6 class="user-bank-section-title">Klasifikasi Rekening</h6>
                                <p class="user-bank-section-subtitle">Tentukan pemilik dan penggunaan rekening.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 user-bank-field">
                                <label for="type">
                                    <span>Type</span>
                                    <span class="user-bank-required">Wajib</span>
                                </label>
                                <div class="user-bank-control-shell">
                                    <i class="mdi mdi-account-switch-outline" aria-hidden="true"></i>
                                    <select class="js-example-basic-single form-select" name="type" id="type" required>
                                        <option value="">{{ __('general.choose') }}...</option>
                                        <option value="1">Person</option>
                                        <option value="2">Company</option>
                                    </select>
                                </div>
                                <small class="user-bank-helper">Pilih apakah rekening milik person atau company.</small>
                            </div>

                            <div class="col-md-6 user-bank-field">
                                <label for="rekening_type">
                                    <span>Rekening Type</span>
                                    <span class="user-bank-required">Wajib</span>
                                </label>
                                <div class="user-bank-control-shell">
                                    <i class="mdi mdi-shield-check-outline" aria-hidden="true"></i>
                                    <select class="js-example-basic-single form-select" name="rekening_type" id="rekening_type" required>
                                        <option value="">{{ __('general.choose') }}...</option>
                                        <option value="internal">Internal</option>
                                        <option value="external">External</option>
                                    </select>
                                </div>
                                <small class="user-bank-helper">Internal untuk rekening entitas sendiri, external untuk pihak lain.</small>
                            </div>
                        </div>
                    </div>

                    <div class="user-bank-section" id="balanceField">
                        <div class="user-bank-section-heading">
                            <span class="user-bank-section-icon" aria-hidden="true"><i class="mdi mdi-wallet-outline"></i></span>
                            <div>
                                <h6 class="user-bank-section-title">Saldo Awal</h6>
                                <p class="user-bank-section-subtitle">Isi saldo pembuka saat membuat rekening baru.</p>
                            </div>
                        </div>

                        <div class="user-bank-balance-field user-bank-field">
                            <label for="balance">
                                <span>Balance</span>
                                <span class="user-bank-required">Wajib saat tambah</span>
                            </label>
                            <div class="user-bank-control-shell">
                                <span class="user-bank-currency">Rp</span>
                                <input class="form-control text-end" name="balance" id="balance" type="text"
                                    oninput="formatAngka(this)" placeholder="0" inputmode="numeric" autocomplete="off">
                            </div>
                            <small class="user-bank-helper">Gunakan angka positif. Saldo ini menjadi saldo pembuka rekening.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer user-bank-modal-footer">
                    <p class="user-bank-footer-note"><i class="mdi mdi-information-outline me-1"></i>Pastikan data rekening sudah benar sebelum disimpan.</p>
                    <div class="user-bank-footer-actions">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                            <i class="mdi mdi-close me-1"></i>{{ __('general.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" id="userBankSubmitBtn">
                            <i class="mdi mdi-content-save-outline me-1"></i>{{ __('general.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
