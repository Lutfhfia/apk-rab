<div id="profileModal" class="fixed inset-0 bg-black/60 z-50 items-center justify-center hidden backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button onclick="document.getElementById('profileModal').style.display='none'" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Profil Saya
        </h3>

        @if($errors->any() && old('form_context') === 'profile')
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
            <ul class="text-sm text-red-600 list-disc list-inside">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" id="profileForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="form_context" value="profile">
            
            <div class="flex flex-col items-center mb-6">
                <div class="w-[100px] h-[100px] rounded-full overflow-hidden border-3 border-emerald-500 mb-3 relative group" id="avatarContainer">
                    @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover" id="currentAvatar">
                    @else
                    <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-4xl font-bold" id="currentAvatarText">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if(Auth::user()->avatar)
                    <button type="button" onclick="openImageLightbox('{{ asset('storage/' . Auth::user()->avatar) }}')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat Foto
                    </button>
                    @endif
                    <button type="button" onclick="document.getElementById('avatarInput').click()" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-600 px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Ubah Foto
                    </button>
                </div>
                <input type="file" id="avatarInput" class="hidden" accept="image/png, image/jpeg, image/jpg, image/webp">
                <input type="hidden" name="avatar_data" id="avatarData">
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Nomor WhatsApp (Aktif)</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', Auth::user()->phone_number) }}" placeholder="Contoh: 08123456789" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    <p class="text-[10px] text-gray-400 mt-1">Digunakan untuk menerima notifikasi persetujuan RAB secara langsung via WhatsApp.</p>
                </div>



                <div class="flex items-center justify-end space-x-3 mt-6 pt-5">
                    <button type="button" onclick="document.getElementById('profileModal').style.display='none'" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-bold transition">Batal</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Profil
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Crop Modal (Higher Z-Index) --}}
<div id="cropModal" class="fixed inset-0 bg-black/80 z-[60] items-center justify-center hidden">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Potong Foto Profil</h3>
        <div class="w-full h-64 bg-gray-100 relative overflow-hidden">
            <img id="imageToCrop" class="max-w-full hidden">
        </div>
        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" id="cancelCrop" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-bold transition">Batal</button>
            <button type="button" id="applyCrop" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition">Terapkan</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let cropper = null;
        const avatarInput = document.getElementById('avatarInput');
        const imageToCrop = document.getElementById('imageToCrop');
        const cropModal = document.getElementById('cropModal');
        const cancelCrop = document.getElementById('cancelCrop');
        const applyCrop = document.getElementById('applyCrop');
        const avatarDataInput = document.getElementById('avatarData');

        if(avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        imageToCrop.src = event.target.result;
                        imageToCrop.classList.remove('hidden');
                        cropModal.style.display = 'flex';
                        
                        if (cropper) { cropper.destroy(); }
                        
                        cropper = new Cropper(imageToCrop, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                    };
                    reader.readAsDataURL(files[0]);
                }
            });
        }

        if(cancelCrop) {
            cancelCrop.addEventListener('click', function() {
                cropModal.style.display = 'none';
                avatarInput.value = '';
                if (cropper) { cropper.destroy(); cropper = null; }
            });
        }

        if(applyCrop) {
            applyCrop.addEventListener('click', function() {
                if (!cropper) return;
                
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                });

                const base64data = canvas.toDataURL('image/jpeg', 0.8);
                avatarDataInput.value = base64data;
                
                // Update UI Preview
                const container = document.getElementById('avatarContainer');
                if(container) {
                    container.innerHTML = `<img src="${base64data}" class="w-full h-full object-cover" id="currentAvatar">`;
                }
                
                cropModal.style.display = 'none';
                if (cropper) { cropper.destroy(); cropper = null; }
            });
        }

        // Auto-open modal if there are validation errors
        @if($errors->any() && old('form_context') === 'profile')
        document.getElementById('profileModal').style.display = 'flex';
        @endif
    });
</script>
