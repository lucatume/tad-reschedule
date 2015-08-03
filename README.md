# tad-reschedule
Easy cron event rescheduling

## Code example
Reschedule the `my_hook` hook each 10 minutes until `my_option` option is truthy and pass a know set of arguments to each called funciton:

    tad_reschedule( 'my_hook' )
        ->while( get_option('my_option', false) )
        ->each( 600 )
        ->with_args( array('one', 23, 'foo') ); 

or reschedule the `my_hook` hook but do not pass any argument to the called functions

    tad_reschedule( 'my_hook' )
        ->until( get_option('my_option', false) )
        ->each( 600 );

The function allows for the same flexibility to be used to conditionally hook into actions:

    tad_reschedule( 'my_callback' )
        ->until( get_option('my_option', false) )
        ->each( 'shutdown' )
        ->priority( 99 );
        
## Installation
Download the zip file and copy the folder into the WordPress plugin folder.  

### Composer installation
The plugin is meant to be pulled into a project using [Composer](https://getcomposer.org/):

    composer require lucatume/tad-reschedule:~1.0
       
## Cron Rescheduling usage
Calling the function just with the hook name will be fine and will work as a shorthand for custom time rescheduling not requiring the registration of any cron schedule.
 
    tad_reschedule('my_hook')->each(519);
    
Using the optional methods to specify parameters allows to set custom condition for the re-scheduling to happen

### until
This parameter can be a "truthy" or "falsy" value or a callable; in the second case the function should return a "falsy" or "truthy" value.  
If the value returned is "truthy" then the re-scheduling will happen; the condition will default to `true` making the reschedule hapen every time.
    
    function my_condition(){
        return get_option('my_option', 0) > 1; 
    }
    
    tad_reschedule('my_hook)->until( 'my_condition');
    
or

    function my_condition(){
        return get_option('my_option', 0) > 1; 
    }
    
    tad_reschedule('my_hook)->until(get_option('my_option', 0) > 1);
   
### each
This methods expects a interval (in seconds) or a callable returning an int value; will default to `600` (ten minutes).

    tad_reschedule('my_hook')->each(300);
    
or
     
    tad_reschedule('my_hook')->each( get_option('my_reschedule_interval'));
    
or
    
    function my_interval(){
       return 100 + get_option('my_reschedule_interval', 300); 
    }
    
    tad_reschedule('my_hook')->each('my_interval');
    
### with_args
This method expects an array of arguments that will be passed to the scheduled action or a callable returnin an array; will default to no arguments.

    tad_reschedule('my_hook')->with_args(array(1,2,3);
    
or
     
    tad_reschedule('my_hook')->each(array(get_option('my_reschedule_args'), 2, 'foo'));
    
or
    
    function my_reschedule_args(){
       return array(get_option('my_reschedule_args', 'foo'), 2, 3);
    }
    
    tad_reschedule('my_hook')->each('my_reschedule_args');
    
## Action Hooking usage
The function chain will allow for conditional re-hooking into a set action, the methods are the same used to reschedule cron jobs but with different methods.
An example usage:

    tad_reschedule( array( 'Cache_Builder', 'build_post_cache' ) )
        ->each( 'post_updated' )
        ->until( !function_exists('super_cache_init') )
        ->with_args( 3 )
        ->priority( 999 );

### Hook name
In place of initiating the function chain using an action hook name the argument should be a callable.

### until
This method works exactly the same as for scheduled actions: if a "truthy" value or a callable returning a "truthy" value than the function will be hooked to the action.

### each
When using the function chain to re-hook a function to an action than this will be the action hook name; defaults to `shutdown`.

### with_args
When using the function chain to re-hook a function to an action than this will be the number of args the called function will be passed.

### priority
When using the function chain to re-hook a function to an action than this will be the priority the function will be given in the hook.

