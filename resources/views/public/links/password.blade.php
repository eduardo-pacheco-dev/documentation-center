<x-layouts.public title="{{ $shortLink->title }}">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-lg font-semibold text-gray-900">{{ $shortLink->title }}</h1>
        @if ($shortLink->description)
            <p class="mt-1 text-sm text-gray-500">{{ $shortLink->description }}</p>
        @endif

        <p class="mt-4 text-sm text-gray-600">Este link é protegido por senha. Informe a senha para continuar.</p>

        <form method="POST" action="{{ route('public.short-links.unlock', $shortLink->code) }}" class="mt-4 space-y-3">
            @csrf

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autofocus
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
            >
                Continuar
            </button>
        </form>
    </div>
</x-layouts.public>