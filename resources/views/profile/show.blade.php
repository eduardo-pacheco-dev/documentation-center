<x-admin-layout>
    <x-slot name="title">Perfil</x-slot>

    <div class="mx-auto max-w-2xl">
        <div class="mb-6">
            <h1 class="text-xl font-semibold">Perfil</h1>
            <p class="mt-1 text-sm text-gray-600">Informações do seu perfil.</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff&size=64"
                        alt="{{ $user->name }}"
                        class="h-16 w-16 rounded-full"
                    >
                    <div>
                        <h2 class="text-lg font-medium">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
