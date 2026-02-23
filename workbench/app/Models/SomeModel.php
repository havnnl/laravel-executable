<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SomeModel extends Model
{
    protected $guarded = [];

    /**
     * @return HasMany<SomeOtherModel, $this>
     */
    public function someOtherModels(): HasMany
    {
        return $this->hasMany(SomeOtherModel::class);
    }
}
