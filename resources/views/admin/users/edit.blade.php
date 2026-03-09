@extends('layouts.admin')

@section('content')
    <div class="mb-8 border-b border-gray-200 pb-4 mt-2 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Edit User Role</h2>
            <p class="text-gray-500 text-xs mt-1">Manage system access privileges for {{ $user->name }}.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-xs text-gray-500 hover:text-black transition-colors uppercase tracking-wider font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-sm max-w-xl mb-10">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Full Name (Readonly)</label>
                    <input type="text" value="{{ $user->name }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-sm text-sm text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Email Address (Readonly)</label>
                    <input type="email" value="{{ $user->email }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-sm text-sm text-gray-500 cursor-not-allowed">
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <label for="role" class="block text-xs font-medium text-gray-900 uppercase tracking-wider mb-2">Account Role</label>
                    <select name="role" id="role" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-sm text-gray-800">
                        <option value="customer" {{ $user->role === 'customer' ? 'selected' : '' }}>Customer (Can book packages)</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin (Full system access)</option>
                        <option value="freelancer" {{ $user->role === 'freelancer' ? 'selected' : '' }}>Freelance (Everlast)</option>
                    </select>
                    <p class="text-[10px] text-gray-400 mt-2 uppercase">Warning: Granting admin access allows the user to modify system data.</p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-3 text-xs font-medium uppercase tracking-wider rounded-sm hover:bg-gray-800 transition-colors">
                    Update Role
                </button>
            </div>
        </form>
    </div>
@endsection