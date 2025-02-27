<?php

namespace Filament\Pages\Actions;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Model;

class CreateAction extends Action
{
    use CanCustomizeProcess;

    protected bool | Closure $isCreateAnotherDisabled = false;

    public static function make(string $name = 'create'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('filament-support::actions/create.single.label', ['label' => $this->getModelLabel()]));

        $this->modalHeading(fn (): string => __('filament-support::actions/create.single.modal.heading', ['label' => $this->getModelLabel()]));

        $this->modalButton(__('filament-support::actions/create.single.modal.actions.create.label'));

        $this->extraModalActions(function (): array {
            return $this->isCreateAnotherDisabled() ? [] : [
                $this->makeExtraModalAction('createAnother', ['another' => true])
                    ->label(__('filament-support::actions/create.single.modal.actions.create_another.label')),
            ];
        });

        $this->successNotificationMessage(__('filament-support::actions/create.single.messages.created'));

        $this->button();

        $this->action(function (array $arguments, ComponentContainer $form): void {
            $model = $this->getModel();

            $record = $this->process(fn (array $data): Model => $model::create($data));

            $form->model($record)->saveRelationships();

            if ($arguments['another'] ?? false) {
                // Ensure that the form record is anonymized so that relationships aren't loaded.
                $form->model($model);

                $form->fill();

                $this->sendSuccessNotification();
                $this->callAfter();
                $this->hold();

                return;
            }

            $this->success();
        });
    }

    public function disableCreateAnother(bool | Closure $condition = true): static
    {
        $this->isCreateAnotherDisabled = $condition;

        return $this;
    }

    public function isCreateAnotherDisabled(): bool
    {
        return $this->evaluate($this->isCreateAnotherDisabled);
    }
}
