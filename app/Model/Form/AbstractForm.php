<?php
namespace App\Model\Form;

use App\Model\WithProperties;
use App\Util\Std;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractForm
{
    use WithProperties;
    
    private $fields;
    private $hasErrors;
    private $errors;
    
    public function getState()
    {
        return [
            'hasErrors' => $this->hasErrors,
            'formErrors' => $this->errors,
            'formValues' => $this->toArray(),
        ];
    }
    
    public function setError($field, $message)
    {
        if (!in_array($field, $this->getPropertyNames())) {
            throw new \Exception("No such field $field in " . get_class($this));
        }
        
        $this->errors[$field] = $message;
        $this->hasErrors = true;
    }
    
    public function hasErrors()
    {
        return $this->hasErrors;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getError($field)
    {
        if (!in_array($field, $this->getPropertyNames())) {
            throw new \Exception("No such field $field in " . get_class($this));
        }
        
        return $this->errors[$field];
    }
    
    /**
     * Calls defineProperties(), along with initializing the errors array.
     *
     * @param array $fields
     */
    protected function defineFields(array $fields)
    {
        $this->hasErrors = false;
        $this->fields = $fields;
        
        // Initialize to an array with empty string (no error) for each field.
        $init = array_fill_keys($fields, '');
        $this->errors = $init;
        $this->defineProperties($fields);
        $this->init($init);
    }
    
    /**
     * Ensure all fields are submitted. More specific validation should be
     * defined in subclassed public validate() method.
     *
     * @param array $params
     * @throws \Exception
     */
    protected function validate(array $params)
    {
        if (!$this->hasAllFields($params)) {
            throw new \Exception('Missing form parameters.');
        }
    }
    
    protected function hasAllFields(array $params)
    {
        return Std::arrayKeysExist($this->fields, $params);
    }
}
