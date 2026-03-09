@extends('layouts.admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 border-b border-gray-200 pb-4 mt-2 gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Users Database</h2>
            <p class="text-gray-500 text-xs mt-1">Manage clients, role, and system access.</p>
        </div>
        
        <form action="{{ route('admin.users.index') }}" method="GET" class="relative w-full sm:w-64">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email..." 
                   class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-sm focus:outline-none focus:ring-1 focus:ring-black focus:border-black text-sm transition-colors">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
        </form>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-sm text-sm font-medium">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <form id="bulkDeleteForm" action="{{ route('admin.users.bulkDelete') }}" method="POST" class="hidden">
        @csrf
        </form>
        <div>
        <div id="bulkActionContainer" class="hidden bg-gray-50 border border-gray-200 p-3 mb-4 rounded-sm flex items-center justify-between transition-all">
            <span class="text-xs font-semibold text-gray-700"><span id="selectedCount">0</span> users selected</span>
            <button type="button" onclick="confirmBulkDelete()" class="bg-red-500 text-white px-4 py-1.5 text-[10px] font-medium uppercase tracking-wider rounded-sm hover:bg-red-600 transition-colors">
                <i class="fas fa-trash mr-1"></i> Delete Selected
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm overflow-hidden mb-4">
            <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" id="selectAll" form="bulkDeleteForm" class="w-3 h-3 text-black bg-gray-100 border-gray-300 rounded-sm focus:ring-black focus:ring-1 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 font-medium">Name & Email</th>
                        <th class="px-6 py-4 font-medium">Role</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="ids[]" value="{{ $user->id }}"  form="bulkDeleteForm" class="row-checkbox w-3 h-3 text-black bg-gray-100 border-gray-300 rounded-sm focus:ring-black focus:ring-1 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->role === 'admin')
                                    <span class="px-2 py-1 bg-black text-white text-[10px] font-semibold uppercase tracking-wider rounded-sm">Admin</span>
                                @elseif($user->role === 'customer')
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-semibold uppercase tracking-wider rounded-sm">Client</span>
                                @elseif($user->role === 'freelancer')
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-semibold uppercase tracking-wider rounded-sm">Freelance</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end space-x-3">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="text-gray-400 hover:text-black transition-colors" title="Edit Role">
                                        <i class="fas fa-user-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus user ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs italic">
                                {{ $search ? 'No users found matching your search.' : 'No users registered yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <script>
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionContainer = document.getElementById('bulkActionContainer');
        const selectedCountText = document.getElementById('selectedCount');
        const bulkForm = document.getElementById('bulkDeleteForm');

        function updateBulkActionUI() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            selectedCountText.innerText = checkedCount;
            
            if (checkedCount > 0) {
                bulkActionContainer.classList.remove('hidden');
            } else {
                bulkActionContainer.classList.add('hidden');
                selectAll.checked = false;
            }
        }

        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActionUI();
        });

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActionUI);
        });

        function confirmBulkDelete() {
            if (confirm('Are you sure you want to delete all selected users? This action cannot be undone.')) {
                bulkForm.submit();
            }
        }
    </script>
@endsection