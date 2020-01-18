<?php


namespace Main\Models;


use PDO;

class BaseModel
{
    protected $connection;


    public function __construct(array $attributes =[])
    {
        $this->connection = new PDO("pgsql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}", $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        $this->appendAttributes($attributes);
    }

    public function newInstance(array $attributes = [])
    {
        return new static((array) $attributes);
    }

    public function appendAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

}