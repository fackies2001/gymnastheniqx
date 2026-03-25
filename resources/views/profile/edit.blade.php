@extends('layouts.adminlte')

@section('content_body')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap');

        .profile-page * {
            font-family: 'DM Sans', sans-serif;
        }

        /* ── Page background ── */
        .profile-page {
            background: #f0f2f7;
            min-height: 100vh;
            padding: 2rem;
        }

        /* ── Hero banner ── */
        .profile-hero {
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 60%, #1e4fad 100%);
            border-radius: 20px;
            padding: 2.5rem 2.5rem 4.5rem;
            position: relative;
            overflow: hidden;
            margin-bottom: -3.5rem;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: 30%;
            width: 340px;
            height: 340px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .profile-hero h2 {
            font-family: 'DM Serif Display', serif;
            color: #fff;
            font-size: 1.6rem;
            margin: 0;
            letter-spacing: 0.3px;
        }

        .profile-hero p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            margin: 0.25rem 0 0;
        }

        /* ── Main card ── */
        .profile-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* ── Avatar section ── */
        .avatar-section {
            padding: 2rem 2.5rem 1.5rem;
            border-bottom: 1px solid #f0f2f7;
            display: flex;
            align-items: flex-end;
            gap: 1.5rem;
        }

        .avatar-wrapper {
            position: relative;
            flex-shrink: 0;
        }

        .avatar-wrapper img,
        .avatar-initials {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            object-fit: cover;
        }

        .avatar-initials {
            background: linear-gradient(135deg, #1e4fad, #2d3561);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
        }

        .avatar-edit-btn {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 28px;
            height: 28px;
            background: #1e4fad;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid #fff;
            transition: background 0.2s;
        }

        .avatar-edit-btn:hover {
            background: #1638a0;
        }

        .avatar-edit-btn i {
            color: #fff;
            font-size: 11px;
        }

        .avatar-info h4 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1f36;
            margin: 0 0 0.2rem;
        }

        .avatar-info p {
            color: #7a8099;
            font-size: 0.85rem;
            margin: 0 0 0.5rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: #e8f8f0;
            color: #1a8a4a;
        }

        .status-badge.active::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #1a8a4a;
            border-radius: 50%;
        }

        .status-badge.inactive {
            background: #fdecea;
            color: #c0392b;
        }

        /* ── Form body ── */
        .profile-form-body {
            padding: 2rem 2.5rem;
        }

        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #aab0c6;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f0f2f7;
        }

        .form-row-custom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            margin-bottom: 1.2rem;
        }

        .form-row-custom.single {
            grid-template-columns: 1fr;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-group label {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #7a8099;
            margin: 0;
        }

        .field-input-wrap {
            position: relative;
        }

        .field-input-wrap .field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab0c6;
            font-size: 13px;
            pointer-events: none;
        }

        .field-input-wrap input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1.5px solid #e8eaf2;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #1a1f36;
            background: #fafbfe;
            transition: all 0.2s;
            font-family: 'DM Sans', sans-serif;
        }

        .field-input-wrap input:focus {
            outline: none;
            border-color: #1e4fad;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(30, 79, 173, 0.08);
        }

        .field-input-wrap input:read-only,
        .field-input-wrap input[readonly] {
            background: #f4f5f8;
            color: #9aa0b9;
            cursor: not-allowed;
        }

        .field-hint {
            font-size: 0.72rem;
            color: #aab0c6;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Divider ── */
        .form-divider {
            border: none;
            border-top: 1px solid #f0f2f7;
            margin: 1.8rem 0;
        }

        /* ── Save button ── */
        .btn-save-profile {
            background: linear-gradient(135deg, #1e4fad, #2d3561);
            color: #fff;
            border: none;
            padding: 11px 28px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(30, 79, 173, 0.25);
        }

        .btn-save-profile:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(30, 79, 173, 0.35);
        }

        .btn-save-profile:active {
            transform: translateY(0);
        }

        .save-success {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #1a8a4a;
            background: #e8f8f0;
            padding: 8px 14px;
            border-radius: 8px;
        }

        /* ── Security card ── */
        .security-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .security-card-header {
            background: linear-gradient(135deg, #1a1f36, #2d3561);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .security-card-header i {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }

        .security-card-header h6 {
            color: #fff;
            font-size: 0.9rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.3px;
        }

        .security-card-body {
            padding: 1.8rem 2rem;
        }

        .security-field {
            margin-bottom: 1.2rem;
        }

        .security-field label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #7a8099;
            display: block;
            margin-bottom: 6px;
        }

        .security-input-wrap {
            position: relative;
        }

        .security-input-wrap .s-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab0c6;
            font-size: 12px;
            pointer-events: none;
        }

        .security-input-wrap input {
            width: 100%;
            padding: 10px 14px 10px 36px;
            border: 1.5px solid #e8eaf2;
            border-radius: 10px;
            font-size: 0.875rem;
            color: #1a1f36;
            background: #fafbfe;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.2s;
        }

        .security-input-wrap input:focus {
            outline: none;
            border-color: #1e4fad;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(30, 79, 173, 0.08);
        }

        .btn-update-security {
            width: 100%;
            background: #1a1f36;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 1.5rem;
            transition: all 0.2s;
        }

        .btn-update-security:hover {
            background: #2d3561;
            transform: translateY(-1px);
        }

        /* ── Admin Help card ── */
        .help-card {
            background: #fff;
            border-radius: 16px;
            border-left: 4px solid #1e4fad;
            padding: 1.2rem 1.5rem;
            margin-top: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .help-card h6 {
            font-size: 0.8rem;
            font-weight: 700;
            color: #1e4fad;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .help-card p {
            font-size: 0.8rem;
            color: #7a8099;
            margin: 0;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .profile-page {
                padding: 1rem;
            }

            .form-row-custom {
                grid-template-columns: 1fr;
            }

            .avatar-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-hero {
                padding: 1.5rem 1.5rem 4rem;
            }

            .profile-form-body {
                padding: 1.5rem;
            }
        }
    </style>

    <div class="profile-page">
        <div class="row">
            {{-- LEFT: Profile form --}}
            <div class="col-lg-8 mb-4">

                {{-- Hero Banner --}}
                <div class="profile-hero">
                    <h2>My Profile</h2>
                    <p>Manage your personal information and account settings</p>
                </div>

                {{-- Main Profile Card --}}
                <div class="profile-card">

                    {{-- Avatar section --}}
                    <div class="avatar-section">
                        <div class="avatar-wrapper">
                            @if ($user->profile_photo)
                                <img id="profilePreview" src="{{ $user->profile_photo }}" alt="{{ $user->name }}">
                            @else
                                @php
                                    $nameParts = explode(' ', $user->name);
                                    $initials =
                                        count($nameParts) >= 2
                                            ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
                                            : strtoupper(substr($user->name, 0, 2));
                                @endphp
                                <div class="avatar-initials" id="profilePreviewPlaceholder">
                                    {{ $initials }}
                                </div>
                            @endif
                            <label for="profile_photo" class="avatar-edit-btn" title="Change photo">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>

                        <div class="avatar-info">
                            <h4>{{ $user->name }}</h4>
                            <p>{{ $user->email }}</p>
                            @if (strtolower($user->status) === 'active')
                                <span class="status-badge active">Active Account</span>
                            @else
                                <span class="status-badge inactive">Inactive Account</span>
                            @endif
                        </div>
                    </div>

                    {{-- Form body --}}
                    <div class="profile-form-body">
                        <form id="profileUpdateForm" action="{{ route('profile.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <input type="file" name="profile_photo" id="profile_photo" class="d-none" accept="image/*">

                            <div class="section-label">Personal Information</div>

                            <div class="form-row-custom">
                                {{-- Full Name --}}
                                <div class="field-group">
                                    <label>Full Name</label>
                                    <div class="field-input-wrap">
                                        <i class="fas fa-user field-icon"></i>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                            placeholder="Your full name">
                                    </div>
                                    @error('name')
                                        <small class="text-danger" style="font-size:0.75rem;">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Contact Number --}}
                                <div class="field-group">
                                    <label>Contact Number</label>
                                    <div class="field-input-wrap">
                                        <i class="fas fa-phone field-icon"></i>
                                        <input type="text" name="contact_number"
                                            value="{{ old('contact_number', $user->contact_number) }}"
                                            placeholder="e.g. 09XX-XXX-XXXX">
                                    </div>
                                    @error('contact_number')
                                        <small class="text-danger" style="font-size:0.75rem;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Email (read-only) --}}
                            <div class="form-row-custom single">
                                <div class="field-group">
                                    <label>Email Address</label>
                                    <div class="field-input-wrap">
                                        <i class="fas fa-envelope field-icon"></i>
                                        <input type="email" value="{{ $user->email }}" readonly>
                                    </div>
                                    <p class="field-hint">
                                        <i class="fab fa-google" style="color:#4285f4; font-size:11px;"></i>
                                    </p>
                                </div>
                            </div>

                            <hr class="form-divider">

                            {{-- Save button --}}
                            <div class="d-flex align-items-center" style="gap: 12px;">
                                <button type="submit" class="btn-save-profile">
                                    <i class="fas fa-save"></i> Save Profile Details
                                </button>

                                @if (session('status') === 'profile-updated')
                                    <span class="save-success" id="statusMessage">
                                        <i class="fas fa-check-circle"></i> Changes Saved!
                                    </span>
                                    <script>
                                        setTimeout(() => {
                                            const el = document.getElementById('statusMessage');
                                            if (el) el.style.display = 'none';
                                        }, 3000);
                                    </script>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Security + Help --}}
            <div class="col-lg-4 mb-4">

                {{-- Security Card --}}
                <div class="security-card">
                    <div class="security-card-header">
                        <i class="fas fa-shield-alt"></i>
                        <h6>Security Settings</h6>
                    </div>
                    <div class="security-card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                {{-- Admin Help --}}
                <div class="help-card">
                    <h6><i class="fas fa-info-circle"></i> Admin Help</h6>
                    <p>
                        Deletion of account and role modifications are restricted to the
                        <strong>User Management</strong> module. If you need to close this account,
                        please coordinate with the Admin.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Photo preview script --}}
    <script>
        document.getElementById('profile_photo').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('profilePreview');
                let placeholder = document.getElementById('profilePreviewPlaceholder');
                if (preview) {
                    preview.src = e.target.result;
                } else if (placeholder) {
                    const img = document.createElement('img');
                    img.id = 'profilePreview';
                    img.src = e.target.result;
                    img.className = 'avatar-img';
                    img.style.cssText =
                        'width:96px;height:96px;border-radius:50%;border:4px solid #fff;box-shadow:0 4px 20px rgba(0,0,0,0.15);object-fit:cover;';
                    placeholder.parentNode.replaceChild(img, placeholder);
                }
            };
            reader.readAsDataURL(file);
        });
    </script>
@endsection
