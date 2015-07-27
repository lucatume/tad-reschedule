# tad-reschedule
Easy cron event rescheduling

## Code example
Reschedule the `my_hook` hook each 10 minutes until `my_option` option is truthy and pass a know set of arguments to each called funciton:

    reschedule( 'my_hook' )
        ->while( get_option('my_option', false) )
        ->each( 600 )
        ->with_args( array('one', 23, 'foo') ); 

or reschedule the `my_hook` hook but do not pass any argument to the called functions

    reschedule( 'my_hook' )
        ->until( get_option('my_option', false) )
        ->each( 600 )
        ->with_no_args();
        
This is a work in progress.
