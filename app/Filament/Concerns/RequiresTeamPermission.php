<?php

namespace App\Filament\Concerns;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Limits a Filament resource to staff whose team has the resource's
 * permission (see App\Models\Team::PERMISSIONS). Super admins pass.
 */
trait RequiresTeamPermission
{
    abstract public static function teamPermission(): string;

    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->hasPermission(static::teamPermission())) {
            return Response::deny();
        }

        return parent::getAuthorizationResponse($action, $record);
    }
}
