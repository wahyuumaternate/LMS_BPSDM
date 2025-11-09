<!-- OPD Table -->
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="15%">Kode OPD</th>
                <th width="30%">Nama OPD</th>
                <th width="15%">Kepala OPD</th>
                <th width="15%">Kontak</th>
                <th width="15%" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($opds as $key => $opd)
                <tr>
                    <td>{{ ($opds->currentPage() - 1) * $opds->perPage() + $key + 1 }}</td>
                    <td>{{ $opd->kode_opd }}</td>
                    <td>{{ $opd->nama_opd }}</td>
                    <td>{{ $opd->nama_kepala ?? '-' }}</td>
                    <td>
                        @if($opd->no_telepon)
                            <small><i class="bi bi-telephone"></i> {{ $opd->no_telepon }}</small><br>
                        @endif
                        @if($opd->email)
                            <small><i class="bi bi-envelope"></i> {{ $opd->email }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info text-white show-opd-btn" data-id="{{ $opd->id }}" title="Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning text-white edit-opd-btn" data-id="{{ $opd->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-opd-btn" 
                                data-id="{{ $opd->id }}" 
                                data-name="{{ $opd->nama_opd }}" 
                                title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        @if(request('search'))
                            <p>Tidak ada OPD yang cocok dengan pencarian "{{ request('search') }}"</p>
                            <button class="btn btn-outline-primary" id="clearSearchFromEmpty">Tampilkan Semua OPD</button>
                        @else
                            <p>Tidak ada data OPD</p>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($opds->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $opds->links() }}
    </div>
@endif

<script>
    $(document).ready(function() {
        // Clear search from empty result
        $('#clearSearchFromEmpty').on('click', function() {
            $('#searchInput').val('');
            loadOpdTable('');
        });
    });
</script>