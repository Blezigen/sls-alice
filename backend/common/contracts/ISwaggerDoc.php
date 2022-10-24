<?php

namespace common\contracts;

use OpenApi\Annotations\OpenApi;

interface ISwaggerDoc
{
    /**
     * @return mixed
     */
    public static function __docAttributeIgnore();

    /**
     * @return mixed|array[]
     */
    public static function __docAttributeExample();

    /**
     * @return mixed
     */
    public static function __makeDocumentationEntity();

    /**
     * @return mixed
     */
    public function __docs(OpenApi $openApi);
}
