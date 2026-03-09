@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-8 border-b border-gray-200 pb-4 mt-2">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Packages</h2>
            <p class="text-gray-500 text-xs mt-1">Manage photography and videography packages.</p>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="bg-black text-white px-4 py-2 text-xs font-medium uppercase tracking-wider rounded-sm hover:bg-gray-800 transition-colors">
            + New Package
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <form action="{{ route('admin.packages.index') }}" method="GET" class="relative w-full sm:w-64">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search packages..." class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-sm focus:outline-none focus:ring-1 focus:ring-black text-sm">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
        </form>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-sm text-sm font-medium"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</div>
    @endif

    <form id="bulkDeleteForm" action="{{ route('admin.packages.bulkDelete') }}" method="POST" class="hidden">
        @csrf
    </form>
    <div>
        <div id="bulkActionContainer" class="hidden bg-gray-50 border border-gray-200 p-3 mb-4 rounded-sm flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-700"><span id="selectedCount">0</span> selected</span>
            <button type="button" onclick="confirmBulkDelete()" class="bg-red-500 text-white px-4 py-1.5 text-[10px] font-medium uppercase tracking-wider rounded-sm hover:bg-red-600">
                <i class="fas fa-trash mr-1"></i> Delete Selected
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm overflow-hidden mb-4">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-10"><input type="checkbox" id="selectAll" form="bulkDeleteForm" class="w-3.5 h-3.5 text-black bg-gray-100 border-gray-300 rounded-sm cursor-pointer"></th>
                        <th class="px-6 py-4 font-medium">Name</th>
                        <th class="px-6 py-4 font-medium">Category</th>
                        <th class="px-6 py-4 font-medium">Price</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($packages as $package)
                        <tr class="hover:bg-gray-50 transition-colors group cursor-pointer" onclick="openModal(this)" data-name="{{ $package->name }}" data-category="{{ $package->category }}" data-price="Rp {{ number_format($package->price, 0, ',', '.') }}" data-desc="{{ $package->description }}" data-duration="{{ $package->duration_hours }}" data-locations="{{ $package->total_locations }}" data-image="{{ $package->thumbnail_path ? asset('storage/' . $package->thumbnail_path) : '' }}">
                            <td class="px-6 py-4" onclick="event.stopPropagation()">
                                <input type="checkbox" name="ids[]" value="{{ $package->id }}" form="bulkDeleteForm" class="row-checkbox w-3.5 h-3.5 text-black bg-gray-100 border-gray-300 rounded-sm cursor-pointer">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $package->name }}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-sm">{{ $package->category }}</span></td>
                            <td class="px-6 py-4 text-gray-600">Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right" onclick="event.stopPropagation()">
                                <div class="flex justify-end space-x-3">
                                    <a href="{{ route('admin.packages.edit', $package->id) }}" class="text-gray-400 hover:text-black transition-colors" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Hapus paket ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs italic">No packages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>

    <div class="mt-4">{{ $packages->links() }}</div>

    <div id="packageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-sm shadow-2xl w-full max-w-lg p-8 relative transform transition-all">
            <button onclick="closeModal()" class="absolute top-5 right-5 text-gray-400 hover:text-black transition-colors">
                <i class="fas fa-times fa-lg"></i>
            </button>
            
            <div class="mb-4 border-b border-gray-100 pb-4">
                <span id="modalCategory" class="px-2 py-1 bg-gray-100 text-gray-500 text-[10px] uppercase tracking-wider rounded-sm mb-3 inline-block"></span>
                <h3 id="modalName" class="text-2xl font-semibold text-gray-900 tracking-tight leading-none mb-2"></h3>
                <p id="modalPrice" class="text-lg font-light text-black"></p>
            </div>

            <div class="flex space-x-8 mb-6 bg-gray-50 p-3 rounded-sm border border-gray-100">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Duration</p>
                    <p class="text-sm font-medium text-gray-800"><i class="far fa-clock mr-1 text-gray-400"></i> <span id="modalDuration"></span> Hours</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Locations</p>
                    <p class="text-sm font-medium text-gray-800"><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> <span id="modalLocations"></span> Spot(s)</p>
                </div>
            </div>

            <div id="modalImageContainer" class="mb-6 hidden">
                <img id="modalImage" src="" alt="Package Thumbnail" class="w-full h-48 object-cover rounded-sm border border-gray-200">
            </div>

            <div>
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-2">Description / Inclusions</p>
                <p id="modalDescription" class="text-sm text-gray-700 leading-relaxed whitespace-pre-line"></p>
            </div>
        </div>
    </div>
    
    <script>
        function openModal(row) {
            document.getElementById('modalName').innerText = row.dataset.name;
            document.getElementById('modalCategory').innerText = row.dataset.category + ' Package';
            document.getElementById('modalPrice').innerText = row.dataset.price;
            document.getElementById('modalDescription').innerText = row.dataset.desc;
            
            // Tangkap dan tampilkan data durasi & lokasi
            document.getElementById('modalDuration').innerText = row.dataset.duration;
            document.getElementById('modalLocations').innerText = row.dataset.locations;

            const imgContainer = document.getElementById('modalImageContainer');
            const img = document.getElementById('modalImage');
            
            if (row.dataset.image) {
                img.src = row.dataset.image;
                imgContainer.classList.remove('hidden');
            } else {
                img.src = '';
                imgContainer.classList.add('hidden');
            }

            const modal = document.getElementById('packageModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('packageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('packageModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        // Copy-paste javascript checkbox (updateBulkActionUI) persis seperti di index users tadi ke sini
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionContainer = document.getElementById('bulkActionContainer');
        const selectedCountText = document.getElementById('selectedCount');
        const bulkForm = document.getElementById('bulkDeleteForm');

        function updateBulkActionUI() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            selectedCountText.innerText = checkedCount;
            if (checkedCount > 0) { bulkActionContainer.classList.remove('hidden'); } 
            else { bulkActionContainer.classList.add('hidden'); selectAll.checked = false; }
        }

        selectAll.addEventListener('change', function() { rowCheckboxes.forEach(cb => cb.checked = this.checked); updateBulkActionUI(); });
        rowCheckboxes.forEach(cb => { cb.addEventListener('change', updateBulkActionUI); });
        function confirmBulkDelete() { if (confirm('Delete selected packages?')) { bulkForm.submit(); } }
    </script>
@endsection