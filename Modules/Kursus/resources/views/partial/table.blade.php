<table class="table table-hover">
    <thead>
        <tr>
            <th scope="col">No</th>
            <th scope="col">Judul Kursus</th>
            <th scope="col">Instruktur</th>
            <th scope="col">Kategori</th>
            <th scope="col">Status</th>
            <th scope="col">Peserta</th>
            <th scope="col">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($kursus as $item)
            <tr>
                <th scope="row">{{ $kursus->firstItem() + $loop->index }}</th>
                <td>{{ $item->judul }}</td>
                <td>{{ $item->adminInstruktur->nama_lengkap }}</td>
                <td>{{ $item->kategori->nama_kategori }}</td>
                <td>
                    @switch($item->status)
                        @case('draft')
                            <span class="badge bg-warning">{{ strtoupper($item->status) }}</span>
                        @break

                        @case('aktif')
                            <span class="badge bg-success">{{ strtoupper($item->status) }}</span>
                        @break

                        @case('nonaktif')
                            <span class="badge bg-danger">{{ strtoupper($item->status) }}</span>
                        @break

                        @case('selesai')
                            <span class="badge bg-primary">{{ strtoupper($item->status) }}</span>
                        @break

                        @default
                    @endswitch
                </td>
                <td>{{ $item->peserta->count() }}</td>
                <td class="d-flex gap-1">
                    <a href={{ route("course.show", $item->id) }} class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                    <a href={{ route("course.edit", $item->id) }} class="btn btn-sm btn-success"><i class="bi bi-pencil-fill"></i></a>
                    <button id="btn-delete" class="btn btn-sm btn-danger" data-id="{{ $item->id }}"
                        data-judul="{{ $item->judul }}">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan={{ 7 }} class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $kursus->links('pagination::bootstrap-5') }}
