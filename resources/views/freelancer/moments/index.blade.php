@extends('layouts.admin')

@section('content')
<div class="mb-8 border-b border-gray-200 pb-4 mt-2 flex justify-between items-end">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 tracking-tight">My Moments</h2>
        <p class="text-gray-500 text-xs mt-1">Manage and showcase your best photography or videography moments.</p>
    </div>
    <a href="{{ route('freelancer.moments.create') }}" class="bg-black text-white px-6 py-2.5 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-sm">
        <i class="fas fa-plus mr-2"></i> Add New Moment
    </a>
</div>

@if (session('success'))
    <div class="mb-6 p-4 bg-green-50 text-green-700 text-xs font-bold tracking-wider uppercase border border-green-200 rounded-sm flex items-center shadow-sm">
        <i class="fas fa-check-circle text-lg mr-3"></i> 
        {{ session('success') }}
    </div>
@endif

<div class="bg-white border border-gray-200 rounded-sm overflow-hidden mb-4 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-[10px] text-gray-500 uppercase tracking-widest">
                    <th class="px-6 py-4 font-bold">Cover</th>
                    <th class="px-6 py-4 font-bold">Moment Title & Event</th>
                    <th class="px-6 py-4 font-bold">Date</th>
                    <th class="px-6 py-4 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-xs divide-y divide-gray-100 text-gray-700">
                @forelse ($moments as $moment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <img src="{{ $moment->cover_image }}" alt="Cover" class="w-16 h-10 object-cover rounded-sm border border-gray-200" referrerpolicy="no-referrer">
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-900 text-sm mb-1">{{ $moment->title }}</p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">{{ $moment->client_name }}</p>
                            <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 bg-gray-100 text-gray-600 rounded-sm uppercase tracking-widest">{{ $moment->category }}</span>
                        </td>
                        <td class="px-6 py-4 font-medium">
                            {{ \Carbon\Carbon::parse($moment->event_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end space-x-4 items-center">
                                <button type="button" 
                                    onclick="openModal({{ json_encode($moment) }})" 
                                    class="text-gray-400 hover:text-blue-500 transition-colors" title="View Detail">
                                    <i class="fas fa-eye text-base"></i>
                                </button>
                                
                                <a href="{{ route('freelancer.moments.edit', $moment->id) }}" class="text-gray-400 hover:text-black transition-colors" title="Edit Moment">
                                    <i class="fas fa-edit text-base"></i>
                                </a>

                                <form action="{{ route('freelancer.moments.destroy', $moment->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this moment?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Delete Moment">
                                        <i class="fas fa-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 text-xs italic">
                            You haven't uploaded any moments yet. Let's create one!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="px-2">
    {{ $moments->links() }}
</div>

<div id="detailModal" class="fixed inset-0 z-[100] hidden bg-black/80 backdrop-blur-sm flex justify-center items-center p-4 sm:p-6 opacity-0 transition-opacity duration-300">
    <div class="bg-white w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-sm relative transform scale-95 transition-transform duration-300" id="modalContainer">
        
        <button onclick="closeModal()" class="absolute top-4 right-4 bg-white/50 hover:bg-white rounded-full p-2 text-gray-900 z-10 transition-colors shadow-sm">
            <i class="fas fa-times text-xl px-1"></i>
        </button>

        <div class="p-8">
            <div class="text-center mb-8 border-b border-gray-100 pb-8">
                <p id="modalCategory" class="text-[10px] font-bold tracking-[0.2em] text-[#C9A66B] uppercase mb-4"></p>
                <h2 id="modalTitle" class="text-xl md:text-2xl font-serif-custom text-gray-900 mb-2"></h2>
                <p id="modalClient" class="text-xs font-bold tracking-widest uppercase text-gray-500 mb-2"></p>
                <p id="modalDate" class="text-[10px] tracking-widest text-gray-400 uppercase italic"></p>
            </div>

            <img id="modalCover" src="" class="w-full h-auto object-cover rounded-sm mb-8 shadow-sm" referrerpolicy="no-referrer">
            
            <p id="modalQuote" class="text-center text-sm font-serif-custom italic text-gray-600 mb-8 px-4"></p>

            <h4 class="text-xs font-bold tracking-[0.2em] text-gray-900 uppercase border-b border-gray-100 pb-2 mb-6 text-center">Detail Gallery</h4>
            <div id="modalGallery" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('detailModal');
    const modalContainer = document.getElementById('modalContainer');

    function openModal(moment) {
        // Isi teks
        document.getElementById('modalCategory').textContent = moment.category;
        document.getElementById('modalTitle').textContent = moment.title;
        document.getElementById('modalClient').textContent = moment.client_name;
        
        // Format Tanggal (Simple)
        const dateObj = new Date(moment.event_date);
        document.getElementById('modalDate').textContent = dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
        
        document.getElementById('modalQuote').textContent = moment.quote ? `"${moment.quote}"` : '';
        document.getElementById('modalCover').src = moment.cover_image;

        // Render Gallery Images
        const galleryContainer = document.getElementById('modalGallery');
        galleryContainer.innerHTML = ''; // Kosongkan dulu
        
        if (moment.gallery_links && moment.gallery_links.length > 0) {
            moment.gallery_links.forEach(link => {
                const img = document.createElement('img');
                img.src = link;
                img.setAttribute('referrerpolicy', 'no-referrer');
                img.className = 'w-full h-64 object-cover rounded-sm border border-gray-100';
                galleryContainer.appendChild(img);
            });
        } else {
            galleryContainer.innerHTML = '<p class="text-center text-xs text-gray-400 italic col-span-full">No detail gallery provided.</p>';
        }

        // Tampilkan Modal
        modal.classList.remove('hidden');
        // Sedikit delay untuk efek transisi opacity
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContainer.classList.remove('scale-95');
        }, 10);
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContainer.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); // Tunggu animasi selesai baru di-hide
    }

    // Tutup modal kalau user klik area hitam di luar box
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
</script>
@endsection