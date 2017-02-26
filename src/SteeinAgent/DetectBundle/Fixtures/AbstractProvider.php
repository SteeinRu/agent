<?php
namespace SteeinAgent\DetectBundle\Fixtures;


abstract class AbstractProvider
{
    /**
     * Данные установлены.
     *
     * @var array
     */
    protected $data;

    /**
     * Возвращает набор данных.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }
}