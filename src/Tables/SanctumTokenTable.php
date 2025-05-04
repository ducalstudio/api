<?php

namespace Ducal\Api\Tables;

use Ducal\Api\Models\PersonalAccessToken;
use Ducal\Table\Abstracts\TableAbstract;
use Ducal\Table\Actions\DeleteAction;
use Ducal\Table\BulkActions\DeleteBulkAction;
use Ducal\Table\Columns\Column;
use Ducal\Table\Columns\CreatedAtColumn;
use Ducal\Table\Columns\DateTimeColumn;
use Ducal\Table\Columns\IdColumn;
use Ducal\Table\Columns\NameColumn;
use Ducal\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class SanctumTokenTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->setView('packages/api::table')
            ->model(PersonalAccessToken::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('api.sanctum-token.create'))
            ->addAction(DeleteAction::make()->route('api.sanctum-token.destroy'))
            ->addColumns([
                IdColumn::make(),
                NameColumn::make(),
                Column::make('abilities')
                    ->label(trans('packages/api::sanctum-token.abilities')),
                DateTimeColumn::make('last_used_at')
                    ->label(trans('packages/api::sanctum-token.last_used_at')),
                CreatedAtColumn::make(),
            ])
            ->addBulkAction(DeleteBulkAction::make())
            ->queryUsing(fn (Builder $query) => $query->select([
                'id',
                'name',
                'abilities',
                'last_used_at',
                'created_at',
            ]));
    }
}
