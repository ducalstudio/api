<?php

namespace Ducal\Api\Models;

use Ducal\Base\Contracts\BaseModel;
use Ducal\Base\Models\Concerns\HasBaseEloquentBuilder;
use Ducal\Base\Models\Concerns\HasMetadata;
use Ducal\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken implements BaseModel
{
    use HasMetadata;
    use HasUuidsOrIntegerIds;
    use HasBaseEloquentBuilder;
}
