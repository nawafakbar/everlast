@extends('layouts.admin')

@section('content')
<div class="mb-8 border-b border-gray-200 pb-4 mt-2 flex justify-between items-center">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Create New Moment</h2>
        <p class="text-gray-500 text-xs mt-1">Publish a new portfolio using Google Drive image links.</p>
    </div>
    <a href="{{ route('freelancer.moments.index') }}" class="text-xs text-gray-500 hover:text-black transition-colors uppercase tracking-wider font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-sm max-w-4xl mb-10 shadow-sm">
    
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 mx-8 mt-8 rounded-sm">
            <p class="text-xs font-bold mb-2 uppercase tracking-wider"><i class="fas fa-exclamation-triangle mr-2"></i> Please check the following errors:</p>
            <ul class="list-disc list-inside text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('freelancer.moments.store') }}" method="POST" class="p-8">
        @csrf
        
        <div class="space-y-8">
            
            <div>
                <h3 class="text-[10px] font-bold text-gray-900 uppercase tracking-[0.2em] border-b border-gray-100 pb-2 mb-6">Main Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="cover_image" class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Cover Image (GDrive Direct Link) <span class="text-red-500">*</span></label>
                        <input type="url" name="cover_image" id="cover_image" value="{{ old('cover_image') }}" required placeholder="https://drive.google.com/uc?id=YOUR_FILE_ID" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
                        <p class="text-[9px] text-gray-400 mt-2 uppercase tracking-wide">Must be a direct link (e.g., drive.google.com/uc?id=...). Ensure the GDrive file is set to "Anyone with the link can view".</p>
                    </div>

                    <div>
                        <label for="category" class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Category <span class="text-red-500">*</span></label>
                        <input type="text" name="category" id="category" value="{{ old('category') }}" required placeholder="e.g., PHOTOGRAPHY : TYING THE KNOT" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
                    </div>

                    <div>
                        <label for="event_date" class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Event Date <span class="text-red-500">*</span></label>
                        <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs text-gray-700 transition-colors cursor-pointer">
                    </div>

                    <div class="md:col-span-2">
                        <label for="title" class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Main Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g., A HOLY DAY, A ROYAL TOUCH, A LOVE BEYOND WORDS" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
                    </div>

                    <div class="md:col-span-2">
                        <label for="client_name" class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Client / Event Name <span class="text-red-500">*</span></label>
                        <input type="text" name="client_name" id="client_name" value="{{ old('client_name') }}" required placeholder="e.g., DAVIN & VANESSA WEDDING BY TONNY" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
                    </div>

                    <div class="md:col-span-2">
                        <label for="quote" class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Footer Quote</label>
                        <input type="text" name="quote" id="quote" value="{{ old('quote') }}" placeholder="e.g., GUIDED BY GRACE, FUELED BY FAITH" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <h3 class="text-[10px] font-bold text-gray-900 uppercase tracking-[0.2em] border-b border-gray-100 pb-2 mb-6">Detail Gallery</h3>
                
                <div>
                    <label for="gallery_links" class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Gallery Images (GDrive Links)</label>
                    <textarea name="gallery_links" id="gallery_links" rows="6" placeholder="Paste GDrive direct links here.&#10;One link per line.&#10;Example:&#10;https://drive.google.com/uc?id=FILE_ID_1&#10;https://drive.google.com/uc?id=FILE_ID_2" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors leading-relaxed">{{ old('gallery_links') }}</textarea>
                    <p class="text-[9px] text-gray-400 mt-2 uppercase tracking-wide">Press 'Enter' to separate each image link. These images will appear below the main cover in the public gallery.</p>
                </div>
            </div>

        </div>

        <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end">
            <button type="submit" class="bg-black text-white px-8 py-3 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-md">
                Publish Moment
            </button>
        </div>
    </form>
</div>
@endsection