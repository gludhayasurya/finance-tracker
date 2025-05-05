<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

abstract class BaseModel extends Model implements AuditableContract
{
    use Auditable;

    public function transformAudit(array $data): array
    {
        // Add shared logic (override in child if needed)
        return $data;
    }
}

