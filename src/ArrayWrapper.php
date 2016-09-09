<?php
    /**
     * Created by PhpStorm.
     * User: fenikkusu
     * Date: 9/8/16
     * Time: 9:23 PM
     */

    namespace TwistersFury\Helpers\Arrays;

    /**
     * Class ArrayWrapper. Used For Creating Object Oriented Like Access To Arrays.
     *
     * Usage:
     *
     * $config = new ArrayWrapper(['something' => ['else' => 'value']);
     * $config->getSomething()->getElse();
     * $config['something']['else'];
     *
     * @package TwistersFury\Helpers\Arrays
     * @author Phoenix Osiris <phoenix@twistersfury.com>
     */
    class ArrayWrapper implements \ArrayAccess {
        /**
         * @var array
         */
        protected $arrayData = [];

        protected $classData = NULL;

        public function __construct(array $arrayData) {
            $this->mergeConfig($arrayData);
        }

        public function mergeConfig(array $arrayData) {
            $this->arrayData = array_merge($this->arrayData, $arrayData);

            return $this;
        }

        public function __call($methodName, $methodArgs) {
            if (!isset($methodArgs[0])) {
                $methodArgs[0] = NULL;
            }

            $propertyType = $propertyName = $this->prepareKey($methodName);
            $typePosition = strpos($propertyType, '_');
            if ($typePosition) {
                $propertyName = substr($propertyName, $typePosition + 1);
                $propertyType = substr($propertyType, 0, $typePosition);
            }

            switch($propertyType) {
                case 'get':
                    return $this->getProperty($propertyName, $methodArgs[0]);
                case 'set':
                    return $this->setProperty($propertyName, $methodArgs[0]);
                case 'is':
                case 'can':
                    return (bool) $this->getProperty($propertyName, $methodArgs[0]);
                case 'has':
                    return $this->hasProperty($propertyName);
            }

            throw new \RuntimeException('Invalid Method: ' . $methodName);
        }

        public function hasProperty($propertyName) {
            return array_key_exists($this->prepareKey($propertyName), $this->arrayData);
        }

        public function getProperty($propertyName, $defaultValue = NULL) {
            $propertyName = $this->prepareKey($propertyName);
            if (!$this->hasProperty($propertyName)) {
                return $defaultValue;
            }

            if (isset($this->classData[$propertyName])) {
                return $this->classData[$propertyName];
            }

            $classValue = $this->arrayData[$propertyName];
            if (is_array($classValue)) {
                $classValue = new static($classValue);
            }

            $this->classData[$propertyName] = $classValue;

            return $classValue;
        }

        public function setProperty($propertyName, $propertyValue) {
            $this->removeProperty($propertyName)->arrayData[$this->prepareKey($propertyName)] = $propertyValue;

            return $this;
        }

        public function removeProperty($propertyName) {
            $propertyName = $this->prepareKey($propertyName);
            unset($this->arrayData[$propertyName]);
            unset($this->classData[$propertyName]);

            return $this;
        }

        protected function prepareKey($propertyName) {
            //NOTE: Additional str_replace Is For Humbug/Testing Reasons.
            return str_replace('+', '_', strtolower(trim(preg_replace('#([A-Z]|[0-9]+)#', "+$1", $propertyName), '+')));
        }

        public function offsetExists($offset) {
            return $this->hasProperty($offset);
        }

        public function offsetGet($offset) {
            return $this->getProperty($offset);
        }

        public function offsetSet($offset, $value) {
            $this->setProperty($offset, $value);
        }

        public function offsetUnset($offset) {
            return $this->removeProperty($offset);
        }
    }