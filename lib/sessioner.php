<?php
/**
 * Created by PhpStorm.
 * User: werd
 * Date: 17.02.16
 * Time: 13:22
 */

namespace OCA\Owncollab_Chart;


use OCP\ISession;

class Sessioner implements ISession
{

    const SESSION_STARTED = TRUE;
    const SESSION_NOT_STARTED = FALSE;

    // The state of the session
    private $sessionState = self::SESSION_NOT_STARTED;

    public function __construct(){
        if ( $this->sessionState == self::SESSION_NOT_STARTED ) {
            $this->sessionState = session_start();
        }
    }

    /**
     * Set a value in the session
     *
     * @param string $key
     * @param mixed $value
     * @since 6.0.0
     */
    public function set($key, $value){
        $_SESSION[$key] = $value;
    }

    /**
     * Get a value from the session
     *
     * @param string $key
     * @return mixed should return null if $key does not exist
     * @since 6.0.0
     */
    public function get($key){
        if(isset($_SESSION[$key])) return $_SESSION[$key];
        else return false;
    }

    /**
     * Check if a named key exists in the session
     *
     * @param string $key
     * @return bool
     * @since 6.0.0
     */
    public function exists($key){
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a $key/$value pair from the session
     *
     * @param string $key
     * @since 6.0.0
     */
    public function remove($key){
        if(isset($_SESSION[$key])) unset( $_SESSION[$key] );
    }

    /**
     * Reset and recreate the session
     * @since 6.0.0
     */
    public function clear(){
        unset( $_SESSION );
    }

    /**
     * Close the session and release the lock
     * @since 7.0.0
     */
    public function close(){
        session_destroy();
        unset( $_SESSION );
    }

}