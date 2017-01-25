<?php

/**
 * Linna App
 *
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2017, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */

namespace App\DomainObjects;

use Linna\DataMapper\DomainObjectAbstract;
use Linna\Auth\Password;

/**
 * User
 *
 */
class User extends DomainObjectAbstract
{
    /**
     * @var string $name User name
     */
    public $name;

    /**
     * @var string $description User description
     */
    public $description;

    /**
     * @var string $password User hashed password
     */
    public $password;

    /**
     * @var int $active It say if user is active or not
     */
    public $active = 0;

    /**
     * @var string $created User creation date
     */
    public $created;

    /**
     * @var string $last_update Last user update
     */
    public $lastUpdate;
    
    /**
     *
     * @var object $passwordUtility Password class for manage password
     */
    private $passwordUtility;
    
    /**
     * Constructor
     * Do type conversion because PDO doesn't return any original type from db :(
     * 
     * @param Password $password
     */
    public function __construct(Password $password)
    {
        $this->passwordUtility = $password;
        
        //set required type
        settype($this->objectId, 'integer');
        settype($this->active, 'integer');
    }

    /**
     * Set new user password without do any check
     *
     * @param string $newPassword
     *
     */
    public function setPassword(string $newPassword)
    {
        //hash provided password
        $this->password = $this->passwordUtility->hash($newPassword);
    }

    /**
     * Change user password only after check old password
     *
     * @param string $newPassword New user password
     * @param string $oldPassword Old user password
     *
     * @return bool
     */
    public function chagePassword(string $newPassword, string $oldPassword) : bool
    {
        //verfy password match
        if ($this->passwordUtility->verify($oldPassword, $this->password)) {
            
            //if match set new password
            $this->password = $this->passwordUtility->hash($newPassword);

            return true;
        }

        return false;
    }
}
