<?php

namespace Spatie\WebhookClient\Storage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class EloquentWebhookCallStorage implements WebhookCallStorage
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @param string $class
     * @throws \RuntimeException
     */
    public function __construct(string $class)
    {
        if (!is_subclass_of($class, Model::class)) {
            throw new \RuntimeException(sprintf(
                'Given class [%s] must be subclass of [%s]',
                $class,
                Model::class
            ));
        }

        if (!is_subclass_of($class, WebhookCall::class)) {
            throw new \RuntimeException(sprintf(
                'Given class [%s] must be subclass of [%s]',
                $class,
                WebhookCall::class
            ));
        }

        $this->model = $class;
    }

    /**
     * Store given webhook call.
     *
     * @param WebhookConfig $config
     * @param Request $request
     * @return WebhookCall
     */
    public function storeWebhookCall(WebhookConfig $config, Request $request): WebhookCall
    {
        /** @var Model|WebhookCall $model */
        $model = new $this->model;

        $model->fill([
            'name' => $config->name,
            'payload' => $request->input(),
        ]);

        $model->save();

        return $model;
    }

    /**
     * Retrieve a webhook by given id.
     *
     * @param string $id
     * @return WebhookCall|Model
     */
    public function retrieveWebhookCall(string $id): WebhookCall
    {
        /** @var Model $model */
        $model = new $this->model;

        try {
            return $model->newQuery()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new \OutOfBoundsException(sprintf('Given webhook call does not exist in storage [%s]', $id));
        }
    }

    /**
     * Delete given webhook from storage.
     *
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function deleteWebhookCall(string $id): bool
    {
        return $this->retrieveWebhookCall($id)->delete();
    }
}
