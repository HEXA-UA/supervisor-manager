<?php

namespace frontend\modules\supervisor\components\supervisor;

/**
 * Interface ConnectionInterface
 *
 * @package frontend\modules\supervisor\components\supervisor
 */
interface ConnectionInterface
{
    /**
     * @return mixed
     */
    public function getConnection();

    /**
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    public function callMethod($method, array $params = []);
}