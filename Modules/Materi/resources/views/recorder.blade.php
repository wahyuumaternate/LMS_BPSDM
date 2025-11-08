@extends('layouts.main')

@section('title', 'Atur Urutan Materi')
@section('page-title', 'Atur Urutan Materi')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Atur Urutan Materi: {{ $modul->nama_modul }}</h5>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($materis->isEmpty())
                        <div class="alert alert-info">
                            Tidak ada materi untuk diurutkan.
                        </div>
                    @else
                        <p>Anda dapat menyesuaikan urutan materi dengan menggunakan drag & drop atau mengubah nomor urutan
                            secara manual.</p>

                        <form id="reorderForm" action="{{ route('materi.reorder.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="modul_id" value="{{ $modul->id }}">

                            <div class="table-responsive">
                                <table class="table table-hover" id="materiTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 70px">Urutan</th>
                                            <th>Judul Materi</th>
                                            <th>Tipe</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="sortable">
                                        @foreach ($materis as $materi)
                                            <tr data-id="{{ $materi->id }}" class="cursor-move">
                                                <td>
                                                    <input type="number" class="form-control form-control-sm urutan-input"
                                                        name="materis[{{ $loop->index }}][urutan]"
                                                        value="{{ $materi->urutan }}" min="1">
                                                    <input type="hidden" name="materis[{{ $loop->index }}][id]"
                                                        value="{{ $materi->id }}">
                                                </td>
                                                <td>
                                                    <span class="handle" style="cursor: move;"><i
                                                            class="bi bi-grip-vertical"></i></span>
                                                    {{ $materi->judul_materi }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-secondary">{{ ucfirst($materi->tipe_konten) }}</span>
                                                </td>
                                                <td>
                                                    @if ($materi->published_at)
                                                        <span class="badge bg-success">Dipublikasikan</span>
                                                    @else
                                                        <span class="badge bg-warning">Draft</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-between">
                                <a href="{{ route('materi.index', ['modul_id' => $modul->id]) }}"
                                    class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .cursor-move {
            cursor: move;
        }

        .sortable-ghost {
            background-color: #f8f9fa;
            opacity: 0.8;
        }

        .handle {
            margin-right: 8px;
            color: #adb5bd;
        }
    </style>
@endpush

@push('scripts')
    <!-- Sortable.js -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sortable
            const el = document.querySelector('.sortable');
            const sortable = new Sortable(el, {
                handle: '.handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function() {
                    // Update order inputs after sorting
                    updateOrderInputs();
                }
            });

            // Update order inputs based on current DOM order
            function updateOrderInputs() {
                const rows = document.querySelectorAll('#materiTable tbody tr');
                rows.forEach((row, index) => {
                    // Update urutan input value
                    row.querySelector('.urutan-input').value = index + 1;

                    // Update input names to maintain array indexes
                    const idInput = row.querySelector('input[name^="materis"][name$="[id]"]');
                    const urutanInput = row.querySelector('.urutan-input');

                    idInput.name = `materis[${index}][id]`;
                    urutanInput.name = `materis[${index}][urutan]`;
                });
            }

            // Handle manual order input changes
            const orderInputs = document.querySelectorAll('.urutan-input');
            orderInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const currentVal = parseInt(this.value);
                    if (currentVal < 1) {
                        this.value = 1;
                    }

                    // Reorder DOM elements based on manual input
                    reorderRows();
                });
            });

            // Reorder rows based on urutan input values
            function reorderRows() {
                const tbody = document.querySelector('#materiTable tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                // Sort rows based on urutan input values
                rows.sort((a, b) => {
                    const aValue = parseInt(a.querySelector('.urutan-input').value);
                    const bValue = parseInt(b.querySelector('.urutan-input').value);
                    return aValue - bValue;
                });

                // Clear and re-append rows in the new order
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));

                // Update order inputs with sequential numbers
                updateOrderInputs();
            }

            // Submit form handling
            document.getElementById('reorderForm').addEventListener('submit', function(e) {
                // Make sure input names are correct before submitting
                updateOrderInputs();
            });
        });
    </script>
@endpush
