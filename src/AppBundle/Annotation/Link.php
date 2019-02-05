<?php

namespace AppBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class Link
 * @Annotation
 * @Target("CLASS")
 */
class Link
{
    /**
     * @Required
     */
    public $name;

    /**
     * @Required
     */
    public $route;

    public $params = [];
}
