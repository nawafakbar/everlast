@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-8 border-b border-gray-200 pb-4 mt-2">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Edit Assignment</h2>
            <p class="text-gray-500 text-xs mt-1">Update task and fee details for this event.</p>
        </div>
        <a href="{{ route('admin.bookings.show', $assignment->booking_id) }}" class="text-gray-500 hover:text-black transition-colors text-xs font-medium uppercase tracking-wider">
            <i class="fas fa-arrow-left mr-2"></i> Back to Team
        </a>
    </div>

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-sm flex items-center shadow-sm max-w-2xl">
            <i class="fas fa-exclamation-triangle mr-3 text-lg"></i>
            <p class="text-xs font-bold uppercase tracking-wider">{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-sm shadow-sm p-8 max-w-2xl">
        <form action="{{ route('admin.assignments.update', $assignment->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-6">
                <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Select Freelancer <span class="text-red-500">*</span></label>
                <select name="user_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors cursor-pointer">
                    @foreach($freelancers as $freelancer)
                        <option value="{{ $freelancer->id }}" {{ $assignment->user_id == $freelancer->id ? 'selected' : '' }}>
                            {{ $freelancer->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[9px] text-gray-400 mt-2 uppercase tracking-wide">Warning: Changing the freelancer will trigger a schedule conflict check.</p>
            </div>

            <div class="mb-6">
                <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Task / Role <span class="text-red-500">*</span></label>
                <input type="text" name="task" value="{{ old('task', $assignment->task) }}" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
            </div>

            <div class="mb-8">
                <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Fee (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="fee" value="{{ old('fee', $assignment->fee) }}" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
            </div>

            <div class="flex justify-end gap-4 border-t border-gray-100 pt-6">
                <button type="submit" class="bg-black text-white px-8 py-3 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-md">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection