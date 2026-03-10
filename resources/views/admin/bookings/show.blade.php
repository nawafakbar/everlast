@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-8 border-b border-gray-200 pb-4 mt-2">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Team Assignment</h2>
            <p class="text-gray-500 text-xs mt-1">Manage freelancer team for: <span class="font-bold">{{ $booking->user->name }} & {{ $booking->partner_name }}</span></p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class=" ms-3 text-gray-500 hover:text-black transition-colors text-xs font-medium uppercase tracking-wider">
            <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
        </a>
    </div>

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-sm flex items-center shadow-sm">
            <i class="fas fa-exclamation-triangle mr-3 text-lg"></i>
            <p class="text-xs font-bold uppercase tracking-wider">{{ session('error') }}</p>
        </div>
    @endif
    
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 mb-6 rounded-sm flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-lg"></i>
            <p class="text-xs font-bold uppercase tracking-wider">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-sm shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">Assigned Team</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-[10px] text-gray-500 uppercase tracking-widest border-b border-gray-200">
                            <th class="px-4 py-3 font-bold">Freelancer</th>
                            <th class="px-4 py-3 font-bold">Task</th>
                            <th class="px-4 py-3 font-bold">Fee</th>
                            <th class="px-4 py-3 font-bold text-center">Status</th>
                            <th class="px-4 py-3 font-bold text-right">Actions</th> 
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-gray-100">
                        @forelse($booking->assignments as $assign)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $assign->user->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $assign->task }}</td>
                            <td class="px-4 py-3 text-gray-600 font-medium">Rp {{ number_format($assign->fee, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($assign->status == 'pending')
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-sm text-[9px] font-bold uppercase tracking-wider inline-block min-w-[70px]">Pending</span>
                                @elseif($assign->status == 'accepted')
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-sm text-[9px] font-bold uppercase tracking-wider inline-block min-w-[70px]">Accepted</span>
                                @elseif($assign->status == 'completed')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-sm text-[9px] font-bold uppercase tracking-wider inline-block min-w-[70px]">Completed</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-sm text-[9px] font-bold uppercase tracking-wider inline-block min-w-[70px]">Rejected</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end space-x-3 items-center">
                                    <a href="{{ route('admin.assignments.edit', $assign->id) }}" class="text-gray-400 hover:text-black transition-colors" title="Edit Assignment">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.assignments.destroy', $assign->id) }}" method="POST" onsubmit="return confirm('Hapus freelancer ini dari tim acara?');" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Remove from Team">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-xs italic">
                                Belum ada tim yang ditugaskan untuk acara ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm shadow-sm p-6 h-fit">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">Assign Freelancer</h3>
            
            <form action="{{ route('admin.bookings.assign', $booking->id) }}" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Select Freelancer <span class="text-red-500">*</span></label>
                    <select name="user_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors cursor-pointer">
                        <option value="" disabled selected>-- Choose Talent --</option>
                        @foreach($freelancers as $freelancer)
                            <option value="{{ $freelancer->id }}">{{ $freelancer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Task / Role <span class="text-red-500">*</span></label>
                    <input type="text" name="task" required placeholder="e.g., Lead Photographer" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
                </div>

                <div class="mb-8">
                    <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Fee (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="fee" required placeholder="500000" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
                </div>

                <button type="submit" class="w-full bg-black text-white px-4 py-3 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-sm flex justify-center items-center">
                    <i class="fas fa-plus mr-2"></i> Assign to Event
                </button>
            </form>
        </div>

    </div>
@endsection