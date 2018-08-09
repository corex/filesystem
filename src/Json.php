<?php

namespace CoRex\Filesystem;

use CoRex\Helpers\Traits\DataTrait;

class Json
{
    use DataTrait;

    private $filename;
    private $keyOrder = [];

    /**
     * Constructor.
     *
     * @param string $filename
     * @param array $keyOrder Order of keys in json.
     * @throws \Exception
     */
    public function __construct($filename, array $keyOrder = [])
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
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Exist.
     *
     * @return boolean
     */
    public function exist()
    {
        return file_exists($this->getFilename());
    }

    /**
     * Set key-order for json-file.
     *
     * @param array $keyOrder
     */
    public function setKeyOrder(array $keyOrder)
    {
        $this->keyOrder = $keyOrder;
    }

    /**
     * Save.
     */
    public function save()
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