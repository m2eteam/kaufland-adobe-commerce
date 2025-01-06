<?php

namespace M2E\Kaufland\Model\ActiveRecord;

abstract class AbstractBuilder extends \M2E\Kaufland\Model\AbstractModel
{
    protected AbstractModel $model;
    protected array $rawData;

    /**
     * @param AbstractModel $model
     * @param array $rawData
     *
     * @return AbstractModel
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function build($model, array $rawData)
    {
        if (empty($rawData)) {
            return $model;
        }

        $this->model = $model;
        $this->rawData = $rawData;

        $preparedData = $this->prepareData();
        $this->model->addData($preparedData);

        $this->model->save();

        return $this->model;
    }

    /**
     * @return array
     */
    abstract protected function prepareData();

    /**
     * @return array
     */
    abstract public function getDefaultData();

    public function getModel()
    {
        return $this->model;
    }
}
