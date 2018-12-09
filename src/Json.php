<?php

declare(strict_types=1);

namespace CoRex\Filesystem;

use CoRex\Helpers\Traits\DataTrait;

class Json
{
    use DataTrait;

    /** @var string */
    private $filename;

    /** @var string[] */
    private $keyOrder = [];

    /**
     * Json.
     *
     * @param string $filename
     * @param string[] $keyOrder Order of keys in json.
     */
    public function __construct(string $filename, array $keyOrder = [])
    {
        $this->filename = $filename;
        $this->setKeyOrder($keyOrder);
        $this->setArray(File::getJson($this->filename));
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Exist.
     *
     * @return bool
     */
    public function exist(): bool
    {
        return file_exists($this->getFilename());
    }

    /**
     * Set key-order for json-file.
     *
     * @param string[] $keyOrder
     */
    public function setKeyOrder(array $keyOrder): void
    {
        $this->keyOrder = $keyOrder;
    }

    /**
     * Save.
     */
    public function save(): void
    {
        $result = [];
        $data = $this->all();

        // Loop through key-order and set keys.
        if (count($this->keyOrder) > 0) {
            foreach ($this->keyOrder as $key) {
                if (array_key_exists($key, $data)) {
                    $result[$key] = $data[$key];
                }
            }
        }

        // Loop through data and set rest.
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        File::putJson($this->filename, $result);
    }
}