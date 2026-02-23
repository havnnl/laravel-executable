<?php

declare(strict_types=1);

use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;

return [

    /**
     * --------------------------------------------------------------------------
     * Model Serialization
     * --------------------------------------------------------------------------
     *
     * When executables are queued, this controls whether model relations
     * should be included during serialization. Set to false to reduce
     * serialization overhead if relations aren't needed in your jobs.
     *
     * @see SerializesAndRestoresModelIdentifiers::getSerializedPropertyValue()
     *
     * default: true (this is the default behavior for laravel)
     */
    'serialize_models_with_relations' => true,
];
