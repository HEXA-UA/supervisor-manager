# Supervisor Manager

[![Build Status](https://travis-ci.org/HEXA-UA/supervisor-manager.svg?branch=master)](https://travis-ci.org/HEXA-UA/supervisor-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/supervisor-manager/yii2-supervisor-manager.svg)](https://packagist.org/packages/supervisor-manager/yii2-supervisor-manager)
[![Latest Stable Version](https://img.shields.io/packagist/v/supervisor-manager/yii2-supervisor-manager.svg)](https://packagist.org/packages/supervisor-manager/yii2-supervisor-manager)
[![License](https://img.shields.io/packagist/l/supervisor-manager/yii2-supervisor-manager.svg)](https://packagist.org/packages/supervisor-manager/yii2-supervisor-manager)

Provides a graphical interface to the [supervisor](http://supervisord.org/) process manager. This package is written to work with the Yii2 framework.
To use this package you should have already installed supervisor on your system.

##Installation
Simply add to your composer.json
```
"supervisor-manager/yii2-supervisor-manager": "dev-master",
```
And add new module to your application config:
```
...
'modules' => [
  'supervisor' => [
    'class'    => 'supervisormanager\Module',
    'authData' => [
        'user'     => 'supervisor_user',
        'password' => 'supervisor_pass',
        'url'      => 'http://127.0.0.1:9001/RPC2' // Set by default
    ]
  ]
]
...
```
##Example of process list
![](http://image.prntscr.com/image/f06ca8a673de44118c1305e2f1deb849.png)
