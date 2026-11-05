<?php

namespace Plugins\FollowUp\Http\Requests;

use Plugins\FollowUp\Models\FollowUp;

/**
 * The update shares the same rules as the creation.
 */
class UpdateFollowUpRequest extends StoreFollowUpRequest
{
    /**
     * Return to the listing with the edit modal open when the update fails.
     */
    protected function getRedirectUrl(): string
    {
        if ($this->input('modal') === 'edit') {
            $followUp = $this->route('followUp');

            if ($followUp instanceof FollowUp) {
                return route('admin.follow-ups.index', ['edit' => $followUp->getKey()]);
            }
        }

        return parent::getRedirectUrl();
    }
}
