<?php

namespace frontend\modules\supervisor\components\supervisor;

use yii\base\Component;
use Zend\XmlRpc\Client as XmlRpcClient;
use Zend\XmlRpc\Client\Exception\HttpException;
use Zend\XmlRpc\Client\Exception\FaultException;
use Zend\Http\Client\Adapter\Exception\RuntimeException;
use frontend\modules\supervisor\components\supervisor\exceptions\ConnectionException;
use frontend\modules\supervisor\components\supervisor\exceptions\SupervisorException;
use frontend\modules\supervisor\components\supervisor\exceptions\AuthenticationException;

/**
 * Class Connection
 *
 * @package frontend\modules\supervisor\components\supervisor
 */
class Connection extends Component implements ConnectionInterface
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var XmlRpcClient
     */
    private $_connection;

    /**
     * Connection constructor.
     *
     * @param XmlRpcClient $client
     * @param array        $config
     */
    public function __construct(XmlRpcClient $client, array $config = [])
    {
        parent::__construct($config);

        $this->_connection = $client;

        $this->_initConnection();

        $this->checkConnection();
    }

    /**
     * @return XmlRpcClient
     */
    private function _initConnection()
    {
        return $this->_connection->getHttpClient()->setAuth(
            $this->user, $this->password
        );
    }

    /**
     * @return XmlRpcClient
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws ConnectionException
     * @throws SupervisorException
     */
    public function callMethod($method, array $params = [])
    {
        try {
            return $this->_connection->call($method, $params);
        } catch (RuntimeException $error) {
            throw new ConnectionException(
                'Unable to connect to supervisor XML RPC server.'
            );
        } catch (HttpException $error) {
            throw new AuthenticationException(
                'Authentication failed. Check user name and password.'
            );
        } catch (FaultException $error) {

            $methodName = isset($error->getTrace()[0]['args'][0])
                ? $error->getTrace()[0]['args'][0] : 'Unknown';

            throw new SupervisorException(
                'Method: ' . $methodName . ' was not found in supervisor RPC API.'
            );
        }
    }

    public function checkConnection()
    {
        return (int)$this->callMethod('supervisor.getAPIVersion');
    }
}