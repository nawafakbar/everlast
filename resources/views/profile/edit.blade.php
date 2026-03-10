@extends(Auth::user()->role === 'freelancer' ? 'layouts.freelancer' : 'layouts.customer')

@section('content')
<div class="max-w-4xl">
    
    <div class="mb-8 border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">My Profil</h2>
        <p class="text-xs text-gray-500">Manage your profile information to control, protect, and secure your account.
</p>
    </div>

    @if (session('error_profile'))
        <div class="mb-6 p-4 bg-red-50 text-red-700 text-xs font-medium border border-red-200 rounded-sm flex items-center shadow-sm">
            <i class="fas fa-exclamation-circle mr-2"></i> 
            {{ session('error_profile') }}
        </div>
    @endif
    @if (session('status') === 'profile-updated')
        <div class="mb-6 p-4 bg-green-50 text-green-700 text-xs font-medium border border-green-200 rounded-md">
            <i class="fas fa-check-circle mr-2"></i> Profil has been updated.
        </div>
    @endif

    <div class="bg-white p-8 rounded-md shadow-sm border border-gray-100">
        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col-reverse md:flex-row gap-12">
            @csrf
            @method('patch')

            <div class="flex-1 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label class="sm:w-1/3 text-xs text-gray-700 font-semibold mb-2 sm:mb-0">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="flex-1 border border-gray-300 rounded-sm px-4 py-2 text-sm focus:ring-1 focus:ring-black outline-none transition-shadow">
                </div>
                @error('name') <p class="text-red-500 text-[10px] sm:pl-[33%]">{{ $message }}</p> @enderror

                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label class="sm:w-1/3 text-xs text-gray-700 font-semibold mb-2 sm:mb-0">Email</label>
                    <input type="email" name="email" value="{{ Auth::user()->email }}" readonly title="Email tidak dapat diubah" 
                           class="flex-1 border border-gray-200 bg-gray-50 text-gray-500 rounded-sm px-4 py-2 text-sm outline-none cursor-not-allowed">
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center">
                    <label class="sm:w-1/3 text-xs text-gray-700 font-semibold mb-2 sm:mb-0">No.Tlp</label>
                    <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" placeholder="Contoh: 081234567890" class="flex-1 border border-gray-300 rounded-sm px-4 py-2 text-sm focus:ring-1 focus:ring-black outline-none transition-shadow">
                </div>
                @error('phone') <p class="text-red-500 text-[10px] sm:pl-[33%]">{{ $message }}</p> @enderror

                <div class="pt-6 sm:pl-[33%]">
                    <button type="submit" class="bg-black text-white px-8 py-3 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-800 transition-colors shadow-md">
                        Save
                    </button>
                </div>
            </div>

            <div class="md:w-1/3 flex flex-col items-center border-l-0 md:border-l border-gray-100 pl-0 md:pl-8 justify-center">
                <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-gray-100 mb-6 shadow-sm">
                    <img id="avatarPreview" src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random' }}" class="w-full h-full object-cover">
                </div>
                
                <input type="file" id="avatarInput" name="avatar" class="hidden" accept="image/jpeg, image/png, image/jpg" onchange="previewImage(event)">
                
                <button type="button" onclick="document.getElementById('avatarInput').click()" class="border border-gray-300 text-gray-800 px-6 py-2 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-50 transition-colors">
                    Choose Image
                </button>
                
                <p class="text-[10px] text-gray-400 mt-4 text-center leading-relaxed">Max. 2 MB<br>Format: JPEG, PNG</p>
                @error('avatar') <p class="text-red-500 text-[10px] mt-2 text-center">{{ $message }}</p> @enderror
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('avatarPreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection