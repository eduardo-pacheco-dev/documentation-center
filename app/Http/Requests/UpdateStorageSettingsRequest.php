<?php

namespace App\Http\Requests;

use App\Models\StorageSetting;
use App\Settings\StorageSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStorageSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Normalize the checkbox value.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'aws_use_path_style' => $this->boolean('aws_use_path_style'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $settings = StorageSettings::current();
        $provider = (string) $this->input('storage_provider');

        $isAws = $provider === StorageSetting::PROVIDER_AWS;
        $isAzure = $provider === StorageSetting::PROVIDER_AZURE;
        $isCloudflare = $provider === StorageSetting::PROVIDER_CLOUDFLARE;

        return [
            'storage_provider' => ['required', Rule::in(StorageSetting::providers())],

            'aws_key' => [Rule::requiredIf($isAws), 'nullable', 'string', 'max:500'],
            'aws_secret' => [
                Rule::requiredIf($isAws && blank($settings->aws_secret)),
                'nullable',
                'string',
                'max:1000',
            ],
            'aws_region' => [Rule::requiredIf($isAws), 'nullable', 'string', 'max:100'],
            'aws_bucket' => [Rule::requiredIf($isAws), 'nullable', 'string', 'max:255'],
            'aws_endpoint' => ['nullable', 'url', 'max:500'],
            'aws_url' => ['nullable', 'url', 'max:500'],
            'aws_use_path_style' => ['boolean'],

            'azure_account_name' => [Rule::requiredIf($isAzure), 'nullable', 'string', 'max:255'],
            'azure_account_key' => [
                Rule::requiredIf($isAzure && blank($settings->azure_account_key)),
                'nullable',
                'string',
                'max:1000',
            ],
            'azure_container' => [Rule::requiredIf($isAzure), 'nullable', 'string', 'max:255'],
            'azure_endpoint_suffix' => ['nullable', 'string', 'max:255'],
            'azure_url' => ['nullable', 'url', 'max:500'],

            'cloudflare_account_id' => [Rule::requiredIf($isCloudflare), 'nullable', 'string', 'max:255'],
            'cloudflare_access_key_id' => [Rule::requiredIf($isCloudflare), 'nullable', 'string', 'max:500'],
            'cloudflare_secret_access_key' => [
                Rule::requiredIf($isCloudflare && blank($settings->cloudflare_secret_access_key)),
                'nullable',
                'string',
                'max:1000',
            ],
            'cloudflare_bucket' => [Rule::requiredIf($isCloudflare), 'nullable', 'string', 'max:255'],
            'cloudflare_public_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    /**
     * Get the validation messages in Brazilian Portuguese.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'storage_provider.required' => 'Selecione o local de armazenamento.',
            'storage_provider.in' => 'Selecione um local de armazenamento válido.',
            'aws_region.required' => 'Informe a região da AWS.',
            'aws_bucket.required' => 'Informe o bucket da AWS.',
            'aws_key.required' => 'Informe o Access Key ID da AWS.',
            'aws_secret.required' => 'Informe a chave secreta da AWS.',
            'aws_endpoint.url' => 'Informe uma URL válida para o endpoint da AWS.',
            'aws_url.url' => 'Informe uma URL pública válida para a AWS.',
            'azure_account_name.required' => 'Informe o nome da conta do Azure.',
            'azure_account_key.required' => 'Informe a chave da conta do Azure.',
            'azure_container.required' => 'Informe o container do Azure.',
            'azure_url.url' => 'Informe uma URL pública válida para o Azure.',
            'cloudflare_account_id.required' => 'Informe o ID da conta da Cloudflare.',
            'cloudflare_access_key_id.required' => 'Informe o Access Key ID da Cloudflare.',
            'cloudflare_secret_access_key.required' => 'Informe a Secret Access Key da Cloudflare.',
            'cloudflare_bucket.required' => 'Informe o bucket da Cloudflare.',
            'cloudflare_public_url.url' => 'Informe uma URL pública válida para a Cloudflare.',
        ];
    }
}
