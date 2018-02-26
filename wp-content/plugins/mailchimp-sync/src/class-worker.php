<?php


namespace MC4WP\Sync;

use Error;
use MC4WP_Queue as Queue;

class Worker {

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var UserHandler
     */
    private $user_handler;

    /**
     * Worker constructor.
     *
     * @param Queue      $queue
     * @param UserHandler $user_handler
     */
    public function __construct( Queue $queue, UserHandler $user_handler ) {
        $this->queue = $queue;
        $this->user_handler = $user_handler;
    }

    /**
     * Add hooks
     */
    public function add_hooks() {
        add_action( 'mailchimp_user_sync_run', array( $this, 'work' ) );
    }

    /**
     * Put in work!
     */
    public function work() {

        // We'll use this to keep track of what we've done
        $done = array();

        while( ( $job = $this->queue->get() ) ) {

            // get type & then unset it because we're using the rest as method parameters
            $method = $job->data['type'] . '_user';
            unset( $job->data['type'] );

            // don't perform the same job more than once
            if( ! in_array( $job->data, $done ) ) {

                // do the actual work
                try {
                    $success = call_user_func_array( array( $this->user_handler, $method ), $job->data );
                } catch( Error $e ) {
                    $message = sprintf( 'User Sync: Failed to process background job. %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine() );
                    $this->get_log()->error( $message );
                }

                // keep track of what we've done
                $done[] = $job->data;
            }

            // remove job from queue & force save for long-lived processes
            $this->queue->delete( $job );
            $this->queue->save();
        }
    }

    /**
     * @return \MC4WP_Debug_Log
     */
    private function get_log() {
        return mc4wp('log');
    }

}
