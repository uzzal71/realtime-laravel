# realtime-laravel
Laravel &amp; Realtime: Build Several Realtime Apps with Laravel

# Section 1: Introduction

## 1. About the instructor and what you will learn

## 2. About the course and what you will achieve

## 3. Understanding messages broadcasting on realtime

## 4. How to Ask Questions

## 5. The development environment to use

# Section 2: Starting with the Laravel Structure to Create Realtime Applications with Laravel

## 6. Obtaining and Preparing the Laravel Structure Using Composer
```
composer create-project laravel/laravel RealtimeLaravel
```

Create database like "realtime_laravel"

Database connection changed .env file
```
DB_DATABASE=realtime_laravel
```
Database migration
```
php artisan migrate
```

## 7. The source code of the course

## 8. Adding Laravel UI and Generating Some Useful Components

## 9. Compiling Some Required Components Using NPM

## 10. Exploring the Way as Laravel Mix Works in Laravel

```
composer require laravel/ui
php artisan ui bootstrap --auth
```

# Section 3: Configuring Laravel to Handle Events and Messages on Realtime

## 11. Getting Ready to Use Pusher as the Realtime Service on Laravel

## 12. Installing and Preparing Laravel Echo to Broadcast Messages

# Section 4: Creating Your First Realtime Notifications System with Laravel

## 13. Adding a Generic Component to Show Notifications in Laravel
Goto resources/views/layouts/app.blade.php
```
<main class="py-4">
    <div id="notification" class="alert mx-3 invisible"></div>
    @yield('content')
</main>
```
## 14. Creating an Event to Notify Users’ Session Changes

Goto your application terminal
```
php artisan make:event UserSessionChanged
```
Now goto app/Events/UserSessionChanged.php file and updated
```
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserSessionChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $type)
    {
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        Log::debug($this->message);
        Log::debug($this->type);
        
        return new Channel('notifications');
    }
}
```

## 15. Using Laravel Listeners to Broadcast Changes on Users’ Session
Goto your application terminal
```
php artisan make:listener BroadcastUserLoginNotification
```

Open app/Listeners/BroadcastUserLoginNotification.php file and updated

```
<?php

namespace App\Listeners;

use App\Events\UserSessionChanged;
use Illuminate\Auth\Events\Login;

class BroadcastUserLoginNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void 
     */
    public function handle(Login $event)
    {
        broadcast(new UserSessionChanged("{$event->user->name} is online", 'success'));
    }
}

```

Another Listener create again goto your application terminal
```
php artisan make:listener BroadcastUserLogoutNotification
```

Open app/Listeners/BroadcastUserLogoutNotification.php file and updated

```
<?php

namespace App\Listeners;

use App\Events\UserSessionChanged;
use Illuminate\Auth\Events\Logout;

class BroadcastUserLogoutNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        broadcast(new UserSessionChanged("{$event->user->name} is offline", 'danger'));
    }
}
```

Finaly goto app/Providers/EventServiceProvider.php and define Both Listener

```
<?php

namespace App\Providers;

use App\Listeners\BroadcastUserLoginNotification;
use App\Listeners\BroadcastUserLogoutNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [
            BroadcastUserLoginNotification::class
        ],

        Logout::class => [
            BroadcastUserLogoutNotification::class
        ],
    ];
}

```

## 16. Showing the Notification on Realtime Using Laravel Echo

## 17. Broadcasting the Event Only to Authenticated Users


# Section 5: Creating a Realtime API with Laravel

## 18. Implementing the Actions Over a Resource to Broadcast

```
php artisan make:controller Api\UesrController -r -m User
```

Open Api\UesrController.php file

Display a listing of the resource
```
public function index()
{
    return User::all();
}
```

Store a newly created resource in storage
```
public function store(Request $request)
{
    $data = $request->all();
    $data['password'] = bcrypt($request->password);

    return User::create($data);
}
```

Display the specified resource
```
public function show(User $user)
{
    return $user;
}
```

Update the specified resource in storage
```
public function update(Request $request, User $user)
{
    $data = $request->all();
    $data['password'] = bcrypt($request->password);

    $user->fill($data);
    $user->save();

    return $user;
}
```

Remove the specified resource from storage
```
public function destroy(User $user)
{
    $user->delete();

    return $user;
}
```

Goto API Routes routes/api.php
```
Route::apiResource('users', 'Api/UesrController');
```

Testing Api With Postman - url example
```
# get all users
{uri}/api/users

# get single user
{uri}/api/users/id

# save user
{uri}/api/users

# delete user
{uri}/api/users/id
```

## 19. Showing the List of User to Manipulate It on Realtime

Now open routes/web.php file
```
Route::get('/users', function () {
	return view('users.showAll');
})->name('users.all');
```

Create resources/users/showAll.blade.php file and open it
```
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Users</div>

                <div class="card-body">
                   <ul id="users">
                       
                   </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.axios.get('/api/users')
    .then((response) => {
        const usersElement = document.getElementById('users');
        let users = response.data;

        users.forEach((user, index) => {
            let element = document.createElement('li');

            element.setAttribute('id', user.id);
            element.innerText = user.name;

            usersElement.appendChild(element);
        });
    });
</script>
@endpush
```

## 20. Creating the Events to Indicate Changes on Users

Now, created three Events file
1. UserCreated.php
2. UserUpdated.php
3. UserDeleted.php

1. app/Events/UserCreated.php files
```
<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {       
        return new Channel('users');
    }
}
```
2. app/Events/UserUpdated.php files
```
<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {        
        return new Channel('users');
    }
}
```
3. app/Events/UserDeleted.php files
```
<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Log;

class UserDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {        
        return new Channel('users');
    }
}
```

## 21. Broadcasting the Events Over Users on Realtime

Now Open app/User.php model file
```
/**
 * The event map for the model.
 *
 * Allows for object-based events for native Eloquent events.
 *
 * @var array
 */
protected $dispatchesEvents = [
    'created' => UserCreated::class,
    'updated' => UserUpdated::class,
    'deleted' => UserDeleted::class
];
```

Test Events Log
1. app/Events/UserCreated.php files
```
public function broadcastOn()
{  
    Log::debug("Created {$this->user->name}");         
    return new Channel('users');
}
```

2. app/Events/UserUpdated.php files
```
public function broadcastOn()
{  
    Log::debug("Updated {$this->user->name}");      
    return new Channel('users');
}
```

3. app/Events/UserDeleted.php files
```
public function broadcastOn()
{   
    Log::debug("Deleted {$this->user->name}");         
    return new Channel('users');
}
```

## 22. Showing the Changes on the Users’ List on Realtime

Now Open resources/users/showAll.blade.php
```

@push('scripts')
<script>
    window.axios.get('/api/users')
    .then((response) => {
        const usersElement = document.getElementById('users');
        let users = response.data;

        users.forEach((user, index) => {
            let element = document.createElement('li');

            element.setAttribute('id', user.id);
            element.innerText = user.name;

            usersElement.appendChild(element);
        });
    });
</script>

<script>
    Echo.channel('users')
    .listen('UserCreated', (e) => {
        const usersElement = document.getElementById('users');

        let element = document.createElement('li');
        element.setAttribute('id', e.user.id);
        element.innerText = e.user.name;

        usersElement.appendChild(element);
    })

    .listen('UserUpdated', (e) => {
        const element = document.getElementById(e.user.id);
        element.innerText = e.user.name;
    })

    .listen('UserDeleted', (e) => {
        const element = document.getElementById(e.user.id);
        element.parentNode.removeChild(element);
    })
</script>
@endpush
```
