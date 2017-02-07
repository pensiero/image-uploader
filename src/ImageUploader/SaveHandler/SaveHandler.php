<?php declare(strict_types=1);
namespace ImageUploader\SaveHandler;

class SaveHandler
{
    // default image format
    const FORMAT_DEFAULT = 'jpg';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $blob;

    /**
     * ID of the image
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * ID of the image
     *
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getBlob(): string
    {
        return $this->blob;
    }

    /**
     * Generate a random string as unique id
     */
    protected function generateId()
    {
        // get hash and integer part from the generated uniq id
        list($hash, $integerPart) = explode('.', uniqid('', true));

        // reverse the hash
        $this->id = strrev($hash) . '-' . $integerPart;
    }

    /**
     * Attach the passed params to the id in order to generate the filename
     *
     * @param string $id
     * @param array $params
     *
     * @return string
     */
    protected function generateFilename($id, $params = []): string
    {
        $paramsString = '';

        if (!empty($params)) {

            // replace null values with zero
            $params = array_map(function($param) {
                return empty($param) ? 0 : $param;
            }, $params);

            $paramsString = '_' . implode($params, '_');
        }

        // concatenate id, params and extension
        return $id . $paramsString . '.' . self::FORMAT_DEFAULT;
    }

    /**
     * Image is considered "original" if there are only empty params
     *
     * @param $params
     *
     * @return bool
     */
    protected function imageIsOriginal($params): bool
    {
        return empty(array_filter($params, function($param) {
            return !empty($param);
        }));
    }
}