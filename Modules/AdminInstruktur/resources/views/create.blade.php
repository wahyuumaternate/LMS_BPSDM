@extends('layouts.main')

@section('title', 'Tambah Admin/Instruktur')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tambah Admin/Instruktur Baru</h5>
                        <a href="{{ route('admin.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Account Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Informasi Akun</h6>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" 
                                        class="form-control @error('username') is-invalid @enderror" 
                                        id="username" 
                                        name="username" 
                                        value="{{ old('username') }}" 
                                        required>
                                    @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        id="email" 
                                        name="email" 
                                        value="{{ old('email') }}" 
                                        required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                        class="form-control @error('password') is-invalid @enderror" 
                                        id="password" 
                                        name="password" 
                                        required>
                                    <small class="text-muted">Minimal 8 karakter</small>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role" 
                                        name="role" 
                                        required>
                                        <option value="">Pilih Role</option>
                                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                        <option value="instruktur" {{ old('role') == 'instruktur' ? 'selected' : '' }}>Instruktur</option>
                                    </select>
                                    @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="row mb-4 mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Informasi Pribadi</h6>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" 
                                        class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                        id="nama_lengkap" 
                                        name="nama_lengkap" 
                                        value="{{ old('nama_lengkap') }}" 
                                        required>
                                    @error('nama_lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="nip" class="form-label">NIP</label>
                                    <input type="text" 
                                        class="form-control @error('nip') is-invalid @enderror" 
                                        id="nip" 
                                        name="nip" 
                                        value="{{ old('nip') }}">
                                    @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="gelar_depan" class="form-label">Gelar Depan</label>
                                    <input type="text" 
                                        class="form-control @error('gelar_depan') is-invalid @enderror" 
                                        id="gelar_depan" 
                                        name="gelar_depan" 
                                        value="{{ old('gelar_depan') }}"
                                        placeholder="contoh: Dr., Ir.">
                                    @error('gelar_depan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="gelar_belakang" class="form-label">Gelar Belakang</label>
                                    <input type="text" 
                                        class="form-control @error('gelar_belakang') is-invalid @enderror" 
                                        id="gelar_belakang" 
                                        name="gelar_belakang" 
                                        value="{{ old('gelar_belakang') }}"
                                        placeholder="contoh: S.Kom., M.T.">
                                    @error('gelar_belakang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bidang_keahlian" class="form-label">Bidang Keahlian</label>
                                <textarea class="form-control @error('bidang_keahlian') is-invalid @enderror" 
                                    id="bidang_keahlian" 
                                    name="bidang_keahlian" 
                                    rows="3">{{ old('bidang_keahlian') }}</textarea>
                                @error('bidang_keahlian')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contact Information -->
                            <div class="row mb-4 mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Informasi Kontak</h6>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="no_telepon" class="form-label">No. Telepon</label>
                                    <input type="text" 
                                        class="form-control @error('no_telepon') is-invalid @enderror" 
                                        id="no_telepon" 
                                        name="no_telepon" 
                                        value="{{ old('no_telepon') }}">
                                    @error('no_telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email_verified_at" class="form-label">Email Verified At</label>
                                    <input type="datetime-local" 
                                        class="form-control @error('email_verified_at') is-invalid @enderror" 
                                        id="email_verified_at" 
                                        name="email_verified_at" 
                                        value="{{ old('email_verified_at') }}">
                                    <small class="text-muted">Kosongkan jika email belum diverifikasi</small>
                                    @error('email_verified_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                    id="alamat" 
                                    name="alamat" 
                                    rows="3">{{ old('alamat') }}</textarea>
                                @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Photo Profile -->
                            <div class="row mb-4 mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Foto Profil</h6>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="foto_profil" class="form-label">Upload Foto Profil</label>
                                <input type="file" 
                                    class="form-control @error('foto_profil') is-invalid @enderror" 
                                    id="foto_profil" 
                                    name="foto_profil"
                                    accept="image/jpeg,image/png,image/jpg"
                                    onchange="previewImage(event)">
                                <small class="text-muted">Format: JPEG, PNG, JPG. Maksimal 2MB</small>
                                @error('foto_profil')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <img id="preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; display: none;">
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                    <a href="{{ route('admin.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function previewImage(event) {
        const preview = document.getElementById('preview');
        const file = event.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush