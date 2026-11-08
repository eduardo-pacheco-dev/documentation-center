<?php

namespace Plugins\Nokia\Http\Requests;

/**
 * Validate the update of an existing Nokia site.
 *
 * The update shares the same rules as the creation, but ignores the site being
 * updated when checking the uniqueness of the OC number.
 */
class UpdateNokiaSiteRequest extends StoreNokiaSiteRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::fieldRules($this->route('site'));
    }
}
