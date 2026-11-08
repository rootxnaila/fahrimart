@extends('layouts.app')

@section('content')
<!-- SweetAlert2 CDN untuk Pop-up Success -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Kustomisasi Warna & Border sesuai Desain */
    .btn-green {
        background-color: #4ade80;
        color: white;
        border-radius: 6px;
        font-weight: 500;
        padding: 8px 16px;
        border: none;
    }
    .btn-green:hover {
        background-color: #22c55e;
        color: white;
    }
    .btn-outline-green {
        background-color: white;
        color: #22c55e;
        border: 1px solid #22c55e;
        border-radius: 6px;
        font-weight: 500;
        padding: 8px 16px;
    }
    .btn-outline-green:hover {
        background-color: #f0fdf4;
        color: #22c55e;
    }
    
    /* Search Bar */
    .search-wrapper {
        position: relative;
        width: 320px;
    }
    .search-wrapper input {
        padding-left: 40px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .search-wrapper input:focus {
        border-color: #4ade80;
        box-shadow: 0 0 0 0.2rem rgba(74, 222, 128, 0.25);
        outline: none;
    }
    .search-wrapper svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    /* Pembungkus Utama Card */
    .main-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        padding: 24px;
    }

    /* Pembungkus Garis Tabel (Kunci Kerapian) */
    .table-wrapper {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* Styling Tabel */
    .table {
        margin-bottom: 0;
    }
    .table thead th {
        background-color: #ffffff;
        color: #0f172a;
        font-weight: 500;
        padding: 16px 24px;
        border-bottom: 1px solid #e2e8f0;
    }
    .table tbody td {
        padding: 16px 24px;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #e2e8f0;
    }
    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badge Status */
    .badge-active {
        background-color: #dcfce7;
        color: #16a34a;
        border-radius: 20px;
        padding: 6px 24px;
        font-weight: 500;
        font-size: 0.875rem;
        display: inline-block;
        text-align: center;
        min-width: 80px;
    }
    .badge-inactive {
        background-color: #f1f5f9;
        color: #64748b;
        border-radius: 20px;
        padding: 6px 24px;
        font-weight: 500;
        font-size: 0.875rem;
        display: inline-block;
        text-align: center;
        min-width: 80px;
    }

    /* Icons */
    .action-icon {
        color: #64748b;
        cursor: pointer;
        transition: color 0.2s;
        background: none;
        border: none;
        padding: 4px;
    }
    .action-icon:hover { color: #0f172a; }
    .action-icon.delete:hover { color: #ef4444; }
</style>

<div class="mb-4">
    <h2 class="h4 mb-1 text-dark fw-bold">List User</h2>
    <p class="text-muted small">Kelola dan pantau data user</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="main-container">
    <!-- Top Bar: Search & Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="search-wrapper">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" class="form-control" placeholder="Cari nama user" autocomplete="off">
        </div>
        <button type="button" class="btn-green" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
            Tambah User
        </button>
    </div>

    <!-- Table Wrapper -->
    <div class="table-wrapper">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                    <tr>
                        <td class="fw-bold text-dark">USR{{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->nama }}</td>
                        <td class="text-capitalize">{{ $user->role }}</td>
                        <td>
                            @if($user->status === 'active')
                                <span class="badge-active">Active</span>
                            @else
                                <span class="badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="action-icon me-2" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id_user }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            </button>
                            <form action="{{ route('master.user.destroy', $user->id_user) }}" method="POST" class="d-inline" id="delete-form-{{ $user->id_user }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="action-icon delete" onclick="confirmDelete({{ $user->id_user }})">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit User -->
                    <div class="modal fade" id="editUserModal{{ $user->id_user }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0" style="border-radius: 12px;">
                                <div class="modal-header border-0 pb-0 mt-2 mx-2">
                                    <h5 class="modal-title fw-bold">Edit User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('master.user.update', $user->id_user) }}" method="POST" autocomplete="off">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body text-start px-4">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Email</label>
                                            <input type="email" class="form-control" name="email" value="{{ $user->email }}" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Username</label>
                                            <input type="text" class="form-control" name="nama" value="{{ $user->nama }}" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Role</label>
                                            <select class="form-select" name="role" required>
                                                <option value="pemilik" {{ $user->role == 'pemilik' ? 'selected' : '' }}>Pemilik</option>
                                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="kasir" {{ $user->role == 'kasir' ? 'selected' : '' }}>Cashier</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label text-muted small mb-1">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $user->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-between gap-3 mb-2">
                                            <button type="button" class="btn-outline-green w-50" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn-green w-50">Simpan</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Footer -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <span class="text-muted small">Showing 1 to {{ count($users) }} entries</span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small cursor-pointer" style="cursor: pointer;"><i class="fas fa-chevron-left me-1"></i> Prev</span>
            <span class="text-dark small fw-medium cursor-pointer" style="cursor: pointer;">Next <i class="fas fa-chevron-right ms-1"></i></span>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="tambahUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0 mt-2 mx-2">
                <h5 class="modal-title fw-bold">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.user.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="modal-body text-start px-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="ex : user@gmail.com" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Username</label>
                        <input type="text" class="form-control" name="nama" placeholder="Ketik di sini" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="" disabled selected>Pilih Role</option>
                            <option value="pemilik">Pemilik</option>
                            <option value="admin">Admin</option>
                            <option value="kasir">Cashier</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small mb-1">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="" disabled selected>Pilih Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between gap-3 mb-2">
                        <button type="button" class="btn-outline-green w-50" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-green w-50">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script SweetAlert2 -->
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Your request has submitted successfully',
            confirmButtonText: 'OK',
            confirmButtonColor: '#4ade80',
            customClass: { popup: 'rounded-4' }
        });
    @endif

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus User?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>
@endsection