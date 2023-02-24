<?php

namespace App\Repositories\Eloquent;

use App\Models\Provider;

class ProviderRepository
{
    protected $model;

    public function __construct(Provider $model)
    {
        $this->model = $model;
    }

    /**
     * @throws \Exception
     */
    public function getIdByName(string $name): int
    {
        $provider = $this->model->select('id')->where('name', $name)->first();

        if (!$provider) {
            //@@TODO: create a custom exception, change on Handler
            throw new \Exception('Provider not found');
        }

        return $provider->id;
    }
}
