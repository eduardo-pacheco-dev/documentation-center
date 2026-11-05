<form
    id="armazenamento"
    method="POST"
    action="{{ route('settings.storage.update') }}"
    class="{{ $cardClass }} scroll-mt-6"
    x-data="{ provider: @js(old('storage_provider', $storageSettings->storage_provider)) }"
>
    @csrf
    @method('PUT')

    <div class="{{ $headerClass }}">
        <span class="{{ $tileClass }}">
            <x-icon name="server" class="h-5 w-5" />
        </span>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="{{ $titleClass }}">Armazenamento</h2>
                <x-ui.badge tone="indigo">Plugins</x-ui.badge>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Escolha onde os arquivos enviados pelos plugins serão armazenados. As credenciais ficam criptografadas no banco.
            </p>
        </div>
    </div>

    <div class="{{ $bodyClass }}">
        <fieldset>
            <legend class="{{ $labelClass }}">Provedor de armazenamento</legend>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Novos arquivos são enviados para o provedor selecionado. Arquivos já gravados permanecem no provedor anterior e precisam ser migrados manualmente ao trocar de provedor.
            </p>

            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['value' => 'local', 'label' => 'Local', 'description' => 'No servidor da aplicação', 'icon' => 'folder'],
                    ['value' => 'aws', 'label' => 'AWS S3', 'description' => 'Amazon Web Services', 'icon' => 'cube'],
                    ['value' => 'azure', 'label' => 'Azure Blob', 'description' => 'Microsoft Azure', 'icon' => 'squares-2x2'],
                    ['value' => 'cloudflare', 'label' => 'Cloudflare R2', 'description' => 'Compatível com S3', 'icon' => 'arrow-path'],
                ] as $provider)
                    <label class="cursor-pointer">
                        <input type="radio" name="storage_provider" value="{{ $provider['value'] }}" x-model="provider" class="sr-only">

                        <span
                            class="flex h-full flex-col rounded-2xl border p-4 transition"
                            x-bind:class="provider === '{{ $provider['value'] }}'
                                ? 'border-indigo-500 bg-indigo-50/50 dark:border-indigo-400 dark:bg-indigo-500/10'
                                : 'border-gray-200 hover:border-indigo-300 dark:border-white/10 dark:hover:border-indigo-500/40'"
                        >
                            <span class="flex items-center justify-between">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                    <x-icon :name="$provider['icon']" class="h-4 w-4" />
                                </span>

                                @if ($storageConfigured[$provider['value']] ?? false)
                                    <x-ui.badge tone="green">Configurado</x-ui.badge>
                                @endif
                            </span>

                            <span class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $provider['label'] }}</span>
                            <span class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $provider['description'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            @error('storage_provider')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </fieldset>

        <div x-show="provider === 'local'" x-cloak class="mt-5 rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-3 dark:border-white/15 dark:bg-[#171716]">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Os arquivos ficam no disco privado da aplicação, em <code class="rounded bg-gray-100 px-1 py-0.5 text-[11px] dark:bg-white/10">storage/app/plugins</code>, e são servidos somente por rotas autenticadas.
            </p>
        </div>

        <div x-show="provider === 'aws'" x-cloak class="mt-5 rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Amazon S3</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Informe as credenciais do bucket. A permissão de leitura e escrita é necessária.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="aws_key" class="{{ $labelClass }}">Access Key ID</label>
                    <input id="aws_key" name="aws_key" type="text" value="{{ old('aws_key', $storageSettings->aws_key) }}" class="{{ $inputClass }}">
                    @error('aws_key')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="aws_secret" class="{{ $labelClass }}">
                        Secret Access Key
                        @if (filled($storageSettings->aws_secret))<x-ui.badge tone="green" class="ml-1">Configurada</x-ui.badge>@endif
                    </label>
                    <input id="aws_secret" name="aws_secret" type="password" autocomplete="new-password" placeholder="{{ filled($storageSettings->aws_secret) ? 'Deixe em branco para manter' : '' }}" class="{{ $inputClass }}">
                    @error('aws_secret')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="aws_region" class="{{ $labelClass }}">Região</label>
                    <input id="aws_region" name="aws_region" type="text" value="{{ old('aws_region', $storageSettings->aws_region ?: 'us-east-1') }}" placeholder="us-east-1" class="{{ $inputClass }}">
                    @error('aws_region')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="aws_bucket" class="{{ $labelClass }}">Bucket</label>
                    <input id="aws_bucket" name="aws_bucket" type="text" value="{{ old('aws_bucket', $storageSettings->aws_bucket) }}" class="{{ $inputClass }}">
                    @error('aws_bucket')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="aws_endpoint" class="{{ $labelClass }}">Endpoint <span class="font-normal text-gray-400">(opcional)</span></label>
                    <input id="aws_endpoint" name="aws_endpoint" type="url" value="{{ old('aws_endpoint', $storageSettings->aws_endpoint) }}" placeholder="https://..." class="{{ $inputClass }}">
                    @error('aws_endpoint')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="aws_url" class="{{ $labelClass }}">URL pública <span class="font-normal text-gray-400">(opcional)</span></label>
                    <input id="aws_url" name="aws_url" type="url" value="{{ old('aws_url', $storageSettings->aws_url) }}" placeholder="https://..." class="{{ $inputClass }}">
                    @error('aws_url')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-2 sm:col-span-2">
                    <input type="hidden" name="aws_use_path_style" value="0">
                    <input type="checkbox" name="aws_use_path_style" value="1" @checked(old('aws_use_path_style', $storageSettings->aws_use_path_style)) class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500/30 dark:border-white/20 dark:bg-white/[0.03] dark:text-indigo-400">
                    <span class="text-sm text-gray-700 dark:text-gray-200">Usar endpoint no estilo path-style</span>
                </label>
            </div>
        </div>

        <div x-show="provider === 'azure'" x-cloak class="mt-5 rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Azure Blob Storage</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">A conta precisa de acesso ao container informado.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="azure_account_name" class="{{ $labelClass }}">Nome da conta</label>
                    <input id="azure_account_name" name="azure_account_name" type="text" value="{{ old('azure_account_name', $storageSettings->azure_account_name) }}" class="{{ $inputClass }}">
                    @error('azure_account_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="azure_account_key" class="{{ $labelClass }}">
                        Chave da conta
                        @if (filled($storageSettings->azure_account_key))<x-ui.badge tone="green" class="ml-1">Configurada</x-ui.badge>@endif
                    </label>
                    <input id="azure_account_key" name="azure_account_key" type="password" autocomplete="new-password" placeholder="{{ filled($storageSettings->azure_account_key) ? 'Deixe em branco para manter' : '' }}" class="{{ $inputClass }}">
                    @error('azure_account_key')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="azure_container" class="{{ $labelClass }}">Container</label>
                    <input id="azure_container" name="azure_container" type="text" value="{{ old('azure_container', $storageSettings->azure_container) }}" class="{{ $inputClass }}">
                    @error('azure_container')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="azure_endpoint_suffix" class="{{ $labelClass }}">Sufixo do endpoint</label>
                    <input id="azure_endpoint_suffix" name="azure_endpoint_suffix" type="text" value="{{ old('azure_endpoint_suffix', $storageSettings->azure_endpoint_suffix ?: 'core.windows.net') }}" placeholder="core.windows.net" class="{{ $inputClass }}">
                    @error('azure_endpoint_suffix')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="azure_url" class="{{ $labelClass }}">URL pública <span class="font-normal text-gray-400">(opcional)</span></label>
                    <input id="azure_url" name="azure_url" type="url" value="{{ old('azure_url', $storageSettings->azure_url) }}" placeholder="https://..." class="{{ $inputClass }}">
                    @error('azure_url')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div x-show="provider === 'cloudflare'" x-cloak class="mt-5 rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Cloudflare R2</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                O endpoint será montado como <code class="rounded bg-gray-100 px-1 py-0.5 text-[11px] dark:bg-white/10">https://&lt;account_id&gt;.r2.cloudflarestorage.com</code>.
            </p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="cloudflare_account_id" class="{{ $labelClass }}">Account ID</label>
                    <input id="cloudflare_account_id" name="cloudflare_account_id" type="text" value="{{ old('cloudflare_account_id', $storageSettings->cloudflare_account_id) }}" class="{{ $inputClass }}">
                    @error('cloudflare_account_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="cloudflare_access_key_id" class="{{ $labelClass }}">Access Key ID</label>
                    <input id="cloudflare_access_key_id" name="cloudflare_access_key_id" type="text" value="{{ old('cloudflare_access_key_id', $storageSettings->cloudflare_access_key_id) }}" class="{{ $inputClass }}">
                    @error('cloudflare_access_key_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="cloudflare_secret_access_key" class="{{ $labelClass }}">
                        Secret Access Key
                        @if (filled($storageSettings->cloudflare_secret_access_key))<x-ui.badge tone="green" class="ml-1">Configurada</x-ui.badge>@endif
                    </label>
                    <input id="cloudflare_secret_access_key" name="cloudflare_secret_access_key" type="password" autocomplete="new-password" placeholder="{{ filled($storageSettings->cloudflare_secret_access_key) ? 'Deixe em branco para manter' : '' }}" class="{{ $inputClass }}">
                    @error('cloudflare_secret_access_key')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="cloudflare_bucket" class="{{ $labelClass }}">Bucket</label>
                    <input id="cloudflare_bucket" name="cloudflare_bucket" type="text" value="{{ old('cloudflare_bucket', $storageSettings->cloudflare_bucket) }}" class="{{ $inputClass }}">
                    @error('cloudflare_bucket')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="cloudflare_public_url" class="{{ $labelClass }}">URL pública <span class="font-normal text-gray-400">(opcional)</span></label>
                    <input id="cloudflare_public_url" name="cloudflare_public_url" type="url" value="{{ old('cloudflare_public_url', $storageSettings->cloudflare_public_url) }}" placeholder="https://..." class="{{ $inputClass }}">
                    @error('cloudflare_public_url')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <x-ui.action type="submit" icon="check">
                Salvar armazenamento
            </x-ui.action>

            <p class="text-xs text-gray-400 dark:text-gray-500">Todas as credenciais do provedor são armazenadas criptografadas no banco.</p>
        </div>
    </div>
</form>
