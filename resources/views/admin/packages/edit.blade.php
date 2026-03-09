@extends('layouts.admin')

@section('content')
    <div class="mb-8 border-b border-gray-200 pb-4 mt-2 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Edit Package</h2>
            <p class="text-gray-500 text-xs mt-1">Update package details for {{ $package->name }}.</p>
        </div>
        <a href="{{ route('admin.packages.index') }}" class="text-xs text-gray-500 hover:text-black transition-colors uppercase tracking-wider font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-sm max-w-2xl">
        <form action="{{ route('admin.packages.update', $package->id) }}" method="POST" enctype="multipart/form-data" class="p-8">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Package Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $package->name) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="category" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Category</label>
                        <select name="category" id="category" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700">
                            @foreach(['All In', 'Wedding', 'Prewedding', 'Akad', 'Engagement', 'Other'] as $cat)
                                <option value="{{ $cat }}" {{ $package->category == $cat ? 'selected' : '' }}>{{ $cat }} Package</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="price" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Price (Rp)</label>
                        <input type="number" name="price" id="price" value="{{ old('price', $package->price) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm">
                    </div>
                </div>

                <div>
                    <label for="duration_hours" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Duration Hours</label>
                    <input type="text" name="duration_hours" id="duration_hours" value="{{ old('duration_hours', $package->duration_hours) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm">
                </div>

                <div>
                    <label for="total_locations" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Duration Hours</label>
                    <input type="text" name="total_locations" id="total_locations" value="{{ old('total_locations', $package->total_locations) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm">
                </div>

                <div>
                    <label for="description" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" id="description" rows="4" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm">{{ old('description', $package->description) }}</textarea>
                </div>

                <div>
                    <label for="thumbnail_path" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Thumbnail Image (Optional)</label>
                    @if($package->thumbnail_path)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $package->thumbnail_path) }}" alt="Thumbnail" class="h-20 w-32 object-cover border border-gray-200 rounded-sm">
                        </div>
                    @endif
                    <input type="file" name="thumbnail_path" id="thumbnail_path" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition-colors border border-gray-200 p-1">
                    <p class="text-[10px] text-gray-400 mt-2 uppercase tracking-wide">Leave blank to keep current image. Max: 2MB.</p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-6 py-3 text-xs font-medium uppercase tracking-wider rounded-sm hover:bg-gray-800 transition-colors">
                    Update Package
                </button>
            </div>
        </form>
    </div>
@endsection