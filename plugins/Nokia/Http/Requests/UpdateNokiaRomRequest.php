<?php

namespace Plugins\Nokia\Http\Requests;

/**
 * Validate the update of an existing ROM.
 *
 * The update shares the same rules as the creation, ignoring the current ROM
 * when checking the e-mail uniqueness.
 */
class UpdateNokiaRomRequest extends StoreNokiaRomRequest {}
