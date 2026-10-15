<?php

namespace App\Filament\Concerns;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Limits a Filament resource to super admins (staff and team management).
 */
trait RequiresSuperAdmin
{
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->isSuperAdmin()) {
            return Response::deny();
        }

        return parent::getAuthorizationResponse($action, $record);
    }
}
