@php
    $contact = $contact ?? null;
    $uid = $contact ? 'contact-'.$contact->getKey() : 'contact-new';
    $isSubmitted = old('modal') === ($contact ? 'contact-edit' : 'contact-create')
        && (int) old('contact_id') === ($contact?->getKey() ?? 0);
    $value = fn (string $field, mixed $default = null) => $isSubmitted ? old($field, $default) : $default;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="{{ $uid }}-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
        <input
            type="text"
            name="name"
            id="{{ $uid }}-name"
            value="{{ $value('name', $contact?->name) }}"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @if ($isSubmitted)
            @error('name', 'contact')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
            @enderror
        @endif
    </div>

    <div>
        <label for="{{ $uid }}-position" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cargo</label>
        <input
            type="text"
            name="position"
            id="{{ $uid }}-position"
            value="{{ $value('position', $contact?->position) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @if ($isSubmitted)
            @error('position', 'contact')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
            @enderror
        @endif
    </div>

    <div>
        <label for="{{ $uid }}-phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
        <input
            type="text"
            name="phone"
            id="{{ $uid }}-phone"
            maxlength="30"
            value="{{ $value('phone', $contact?->phone) }}"
            placeholder="(00) 00000-0000"
            inputmode="tel"
            data-mask="phone"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @if ($isSubmitted)
            @error('phone', 'contact')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
            @enderror
        @endif
    </div>

    <div class="sm:col-span-2">
        <label for="{{ $uid }}-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail</label>
        <input
            type="email"
            name="email"
            id="{{ $uid }}-email"
            value="{{ $value('email', $contact?->email) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @if ($isSubmitted)
            @error('email', 'contact')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
            @enderror
        @endif
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input
                type="checkbox"
                name="is_primary"
                value="1"
                @checked($value('is_primary', $contact?->is_primary))
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
            >
            Definir como contato principal
        </label>
        @if ($isSubmitted)
            @error('is_primary', 'contact')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
            @enderror
        @endif
    </div>
</div>
