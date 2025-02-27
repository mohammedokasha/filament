<?php

namespace Filament\Support\Actions;

use Filament\Support\Actions\Concerns\CanBeHidden;
use Filament\Support\Actions\Concerns\HasColor;
use Filament\Support\Actions\Concerns\HasIcon;
use Filament\Support\Actions\Concerns\HasLabel;
use Filament\Support\Actions\Concerns\HasTooltip;
use Filament\Support\Actions\Concerns\InteractsWithRecord;
use Filament\Support\Actions\Contracts\Groupable;
use Filament\Support\Actions\Contracts\HasRecord;
use Filament\Support\Components\ViewComponent;

class ActionGroup extends ViewComponent
{
    use CanBeHidden, InteractsWithRecord {
        CanBeHidden::isHidden as baseIsHidden;
        InteractsWithRecord::parseAuthorizationArguments insteadof CanBeHidden;
    }
    use HasIcon;
    use HasTooltip;
    use HasLabel;
    use HasColor;

    protected string $evaluationIdentifier = 'group';

    protected string $viewIdentifier = 'group';

    public function __construct(
        protected array $actions,
    ) {
    }

    public static function make(array $actions): static
    {
        return app(static::class, ['actions' => $actions]);
    }

    public function getLabel(): ?string
    {
        return $this->evaluate($this->label);
    }

    public function getActions(): array
    {
        return collect($this->actions)
            ->mapWithKeys(function (Action | Groupable | HasRecord $action): array {
                $action->record($this->getRecord());

                return [$action->getName() => $action->grouped()];
            })
            ->toArray();
    }

    public function isHidden(): bool
    {
        $condition = $this->baseIsHidden();

        if ($condition) {
            return true;
        }

        foreach ($this->getActions() as $action) {
            if (! $action->isHidden()) {
                continue;
            }

            return false;
        }

        return true;
    }
}
