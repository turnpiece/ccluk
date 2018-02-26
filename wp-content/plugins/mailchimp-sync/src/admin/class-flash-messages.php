<?php

namespace MC4WP\Sync\Admin;

class FlashMessages {

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $stack;

    /**
     * FlashMessages constructor.
     *
     * @param string $name
     */
    public function __construct( $name ) {
        $this->name = $name;
    }

    /**
     * Load stack from database.
     */
    private function load() {
        if( is_array( $this->stack ) ) {
            return;
        }

        $option = get_option( $this->name, array() );
        $option = is_array( $option ) ? $option : array();
        $this->stack = $option;
    }

    /**
     * Save stack to database
     */
    private function save() {
        update_option( $this->name, $this->stack );
    }

    /**
     * Adds a flash message for type.
     *
     * @param string $type
     * @param string $message
     */
    public function add( $type, $message ) {
        $this->load();
        $this->stack[] = array(
            'type' => $type,
            'message' => $message
        );
        $this->save();
    }

    /**
     * Clear all messages
     */
    public function clear() {
        $this->stack = array();
        $this->save();
    }

    /**
     * Get and clear messages of a given type
     *
     * @param string
     * @return array
     */
    public function get( $type ) {
        $this->load();
        $messages = array();

        foreach( $this->stack as $key => $message ) {
            if( $message['type'] != $type ) {
                continue;
            }

            $messages[] = $message['message'];
            unset( $this->stack[ $key ] );
        }

        $this->save();
        return $messages;
    }

    /**
     * Return and clear all messages
     *
     * @return array
     */
    public function all() {
        $this->load();
        $messages = $this->stack;
        $this->stack = array();
        $this->save();
        return array_map( function( $m ) { return $m['message']; }, $messages );
    }

    /**
     * Has flash messages for a given type?
     *
     * @param string $type
     *
     * @return boolean
     */
    public function has( $type ) {
        $this->load();

        foreach( $this->stack as $message ) {
            if( $message['type'] == $type ) {
                return true;
            }
        }

        return false;
    }

}
