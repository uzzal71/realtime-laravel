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

Open your application terminal
```
composer require laravel/ui
php artisan ui bootstrap --auth
```

You can choose Vuejs, Reactjs Or Others.

## 9. Compiling Some Required Components Using NPM

Open your application terminal
```
npm install
npm run dev
```

## 10. Exploring the Way as Laravel Mix Works in Laravel

Open resources/views/layouts/app.blade.php file

Before closed head tag

```
<!-- Styles -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
@stack("styles")
```

Before closed body tag

```
<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
@stack("scripts")
```

Now resources/views/home.blaade.php copy and paste welcome.blade.php file
```
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Welcome</div>

                <div class="card-body">
                   This is a realtime application
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

```

# Section 3: Configuring Laravel to Handle Events and Messages on Realtime

## 11. Getting Ready to Use Pusher as the Realtime Service on Laravel
Goto [Pusher](https://pusher.com/) this site create an account and create application.
Then pusher give application configure necessary resource.

Goto config/broadcasting.php file
```
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS' => true,
    ],
]
```

Now oepn .env file & updated by pusher key
```
PUSHER_APP_ID=pusher_app_id
PUSHER_APP_KEY=pusher_app_key
PUSHER_APP_SECRET=pusher_app_secret
PUSHER_APP_CLUSTER=us2
```

Again .env file changed
```
BROADCAST_DRIVER=pusher
```

Install php-pusher
```
composer require pusher/pusher-php-server
```

This Time Using Version: ^4.1

Finally
Now goto config/app.php file and enabled 'BroadcastServiceProvider'. by it is comments
```
App\Providers\BroadcastServiceProvider::class,
```

## 12. Installing and Preparing Laravel Echo to Broadcast Messages

Open your application terminal
```
npm install --save-dev laravel-echo pusher-js
```
This time using version:
laravel-echo: "^1.12.1"
pusher-js: "^5.1.1"

Now open resources/js/bootstrap.js file 
```
import Echo from 'laravel-echo'

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true
});
```

By default bootstrap.js comments this code.

Open your application termial
```
npm run dev
```

Compile bootstrap.js file

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

Finally goto app/Providers/EventServiceProvider.php and define Both Listener

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

Now Open resources/js/app.js file and updated
```
require('./bootstrap');

Echo.channel("notifications")
    .listen('UserSessionChanged', (e) => {
        const notificationElement = document.getElementById("notification");

        notificationElement.innerText = e.message;
        notificationElement.classList.remove('invisible');
        notificationElement.classList.remove('alert-success');
        notificationElement.classList.remove('alert-danger');

        notificationElement.classList.add('alert-' + e.type);
    });
```

## 17. Broadcasting the Event Only to Authenticated Users


Open app/Events/UserSessionChanged.php and updated
```
public function broadcastOn()
{
    Log::debug($this->message);
    Log::debug($this->type);
    
    return new PrivateChannel('notifications');
}
```

Open resources/js/app.js file and updated
```
require('./bootstrap');

Echo.private("notifications")
    .listen('UserSessionChanged', (e) => {
        const notificationElement = document.getElementById("notification");

        notificationElement.innerText = e.message;
        notificationElement.classList.remove('invisible');
        notificationElement.classList.remove('alert-success');
        notificationElement.classList.remove('alert-danger');

        notificationElement.classList.add('alert-' + e.type);
    });
```

Define chaneels.php notifications route
```
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('notifications', function ($user) {
    return $user != null;
});
```

Open your application terminal
```
npm run dev
php artisan serve
```

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


# Section 6: Creating a Realtime Game with Laravel Echo

## 23. Adding the Visual Components of the Realtime Game

## 24. Creating the Events of the Game to Broadcast

## 25. Creating a Command to Broadcast Game Events on Realtime

## 26. Showing Events to Players in Realtime with Laravel Echo


# Section 7: Creating a Chat Room Using Realtime Messages with Laravel Echo

## 27. Creating Visual Components to Send Messages in Realtime

First create a ChatController.php file inside app/Http/Controller and open newly crated ChatController

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showChat()
    {
        return view('chat.show');
    }
}
```

Now, goto routes/web.php file
```
Route::get('/chat', 'ChatController@showChat')->name('chat.show');
```

Create show.blade.php in resource/views/chat and open show.blade.php file

```
@extends('layouts.app')

@push('styles')
<style type="text/css">
    
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Chat</div>
                <div class="card-body">
                   <div class="row p-2">
                        <div class="col-10">
                            <div class="row">
                                <div class="col-12 border rounded-lg p-3">
                                    <ul
                                        id="messages"
                                        class="list-unstyled overflow-auto"
                                        style="height: 45vh"
                                    >
                                        <li>Test 1: Hi</li>
                                        <li>Test 2: Hello</li>
                                    </ul>
                                </div>
                            </div>
                            {{-- Message Send Start --}}
                            <form>
                                <div class="row py-3">
                                    <div class="col-10">
                                        <input id="message" class="form-control" type="text">
                                    </div>
                                    <div class="col-2">
                                        <button id="send" type="submit" class="btn btn-primary btn-block">Send</button>
                                    </div>
                                </div>
                            </form>
                            {{-- Message Send End --}}
                        </div> {{-- End Col-10 --}}
                        <div class="col-2">
                            <p><strong>Online Now</strong></p>
                            <ul
                                id="users"
                                class="list-unstyled overflow-auto text-info"
                                style="height: 45vh"
                            >
                                <li>Test 1</li>
                                <li>Test 2</li>
                            </ul>
                        </div> {{-- End Col-2 --}}
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    
</script>
@endpush
```

## 28. Managing the List of Connected Users in Realtime

Open resources/views/chat/show.blade.php

```
<ul
    id="users"
    class="list-unstyled overflow-auto text-info"
    style="height: 45vh"
>

</ul>
```

```
@push('scripts')
<script>
     const usersElement = document.getElementById('users');

     Echo.join('chat')
        .here((users) => {
                users.forEach((user, index) => {
                    let element = document.createElement('li');
                    element.setAttribute('id', user.id);
                    element.innerText = user.name;
                    usersElement.appendChild(element);
                });
            })
        .joining((user) => {
            let element = document.createElement('li');
            element.setAttribute('id', user.id);
            element.innerText = user.name;
            usersElement.appendChild(element);
        })
        .leaving((user) => {
            const element = document.getElementById(user.id);
            element.parentNode.removeChild(element);
        })
</script>
@endpush
```

Open routes/chaneels.php file

```
Broadcast::channel('chat', function ($user) {
    if ($user != null) {
        return ['id' => $user->id, 'name' => $user->name];
    }
});

```

## 29. Creating an Event on Sending Messages in the Chat

Now create a event

```
php artisan make:event MessageSent
```

Now Open app/Events/MessageSent.php

```
<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable; 
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        Log::debug("{$this->user->name}: {$this->message}");
        
        return new PresenceChannel('chat');
    }
}
```

## 30. Broadcasting the Event Created When Sending a Message

Now, Open routes/web.php file

```
Route::post('/chat/message', 'ChatController@messageReceived')->name('chat.message');
```

Now Open ChatController.php file

```
use App\Events\MessageSent;
.........
public function messageReceived(Request $request)
{
    $rules = [
        'message' => 'required',
    ];

    $request->validate($rules);

    broadcast(new MessageSent($request->user(), $request->message));

    return response()->json('Message broadcast');
}
```

Now Open resources/views/chat/show.blade.php

```
<script>
    const messageElement = document.getElementById('message');
    const sendElement = document.getElementById('send');

    sendElement.addEventListener('click', (e) => {
        e.preventDefault();
        window.axios.post('/chat/message', {
            message: messageElement.value,
        });
        messageElement.value = '';
    });
</script>
```

Send message and check log. then checking done remove Log function.

```
public function broadcastOn()
{
    // Log::debug("{$this->user->name}: {$this->message}");
    
    return new PresenceChannel('chat');
}
```

## 31. Showing the Broadcasted Messages to All Users

Goto to resources/views/chat/show.blade.php file

```
<ul
    id="messages"
    class="list-unstyled overflow-auto"
    style="height: 45vh"
>
</ul>
```

```
......
.listen('MessageSent', (e) => {
    let element = document.createElement('li');
    element.innerText = e.user.name + ': ' + e.message;
    messagesElement.appendChild(element);
});
```

# Section 8: Allowing to Send Private Events in Realtime with Laravel Echo

## 32. Adding Components to Allow Events Between Users

Open resources/views/chat/show.blade.php


```
@extends('layouts.app')

@push('styles')
<style type="text/css">
    #users > li {
        cursor: pointer;
    }
</style>
@endpush
```


```
<script>
     const usersElement = document.getElementById('users');
     const messagesElement = document.getElementById('messages');

     Echo.join('chat')
        .here((users) => {
                users.forEach((user, index) => {
                    element.setAttribute('id', user.id);
                    element.setAttribute('onclick', 'greetUser("' + user.id +'")');
            })
        .joining((user) => {
            element.setAttribute('id', user.id);
            element.setAttribute('onclick', 'greetUser("' + user.id +'")');
        })
</script>
```

added javascript function

```
<script>
    function greetUser(id)
    {
        window.axios.post('/chat/greet/' + id);
    }
</script>
```

Open routes/web.php file
```
Route::post('/chat/greet/{user}', 'ChatController@greetReceived')->name('chat.greet');
```

Open app/Http/Controller/ChatController.php
```
public function greetReceived(Request $request, User $user)
{
    return "Greeting {$user->name} from {$request->user()->name}";
}
```

## 33. Creating and Broadcasting an Event Using a Private Channel

Create a event and open it
```
php artisan make:event GreetingSent
```

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

class GreetingSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user;

    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        Log::debug($this->message);

        return new PrivateChannel("chat.greet.{$this->user->id}");
    }
}
```

Now open routes/chaneels.php
```
Broadcast::channel('chat.greet.{receiver}', function ($user, $receiver) {
    return (int) $user->id === (int) $receiver;
});
```

Now open app/Http/Controller/ChatController.php
```
public function greetReceived(Request $request, User $user)
{
    broadcast(new GreetingSent($user, "{$request->user()->name} greeted you"));
    broadcast(new GreetingSent($request->user(), "You greeted {$user->name}"));
    
    return "Greeting {$user->name} from {$request->user()->name}";
}
```

## 34. Showing the Private Events Only to the Receiver and Sender


# Section 9: Adding, Configuring and Using Your Own WebSockets Server

## 35. Creating a New Laravel Project for The WebSockets Server

## 36. Adding Laravel WebSockets to The Project

## 37. Configuring an Application in the Laravel WebSockets Server

## 38. Using Your Own WebSockets Server from your Laravel Project

## 39. Checking the Laravel WebSockets Statistics


# Section 10: Deploying Laravel WebSockets on a Server

## 40. Cloning and Deploying the Laravel WebSockets Project

```
# Step 01
-------------------------------------------------------
cd /var/www
sudo git clone "" ws.domain.com
cd ws.domain.com
sudo chown -R www-data storage/
sudo chown -R www-data bootstrap/cache/
sudo nano .env

# Step 02
---------------------------------------------------------
# (#copy your project .env file & paste this nano .env#)
# .env file APP_URL changed

APP_URL=https://ws.domain.com
Then .env file save

# Step 03
---------------------------------------------------------
sudo touch database/database.sqlite

# Step 04
---------------------------------------------------------
sudo composer install --no-dev

# Step 05
---------------------------------------------------------
sudo php artisan migrate
> yes
```

## 41. Using Supervisor to Execute the WebSockets Server

```
# Step 01
------------------------------------------------
> sudo php artisan websockets:serve
> exit ctrl + c
> sudo apt install supervisor
> cd /etc/supervisor
> ll
> cd config.d
> ll
> sudo nano ws.domain.com.conf

# Step 02
-------------------------------------------------
[program:ws_domain]
command=/usr/bin/php /var/www/ws.domain.com/artisan websockets:serve
autostart=true
autorestart=true

# Step 03
--------------------------------------------------
> sudo supervisorctl update
> sudo supervisorctl start ws_domain
> cd /var/www/ws.domain.com/
> sudo php artisan websockets:serve
-> sudo supervisorctl status
```

## 42. Configuring a Reversed Proxy for the WebSockets Server

```
# Step 01
----------------------------------------------
> cd /etc/nginx/sites-available/
> ll
> sudo nano ws.domain.com

# Step 02 Update this file
-----------------------------------------------
server {
        listen        80;
        listen        [::]:80;

        server_name ws.domain.com;

        location /app {
                proxy_pass             http://127.0.0.1:6001;
                proxy_read_timeout     60;
                proxy_connect_timeout  60;
                proxy_redirect         off;

                # Allow the use of websockets
                proxy_http_version 1.1;
                proxy_set_header Upgrade $http_upgrade;
                proxy_set_header Connection 'upgrade';
                proxy_set_header Host $host;
                proxy_cache_bypass $http_upgrade;
        }
}

# Step 03
--------------------------------------------------------
> sudo ln -s /etc/nginx/sites-available/ws.domain.com /etc/nginx/sites-enabled/
> ll ../sites-enabled/
> sudo nginx -t
> sudo systemctl reload nginx.service

# Step 04
---------------------------------------------------------
Goto you browser: ws.domain.com
Result Not Found and show nginx server
```

## 43. Using the New Server in the Laravel Realtime Application

Goto config/broadcasting.php
```
'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host'    => 'ws.domain.com',
                'port'    => 80,
                'useTLS' => true,
                'scheme' => 'http'
            ],
        ]
```

Then Goto resources/js/bootstrap.js
```
import Echo from 'laravel-echo'

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    wsHost: 'ws.domain.com',
    wsPort: 80,
    encrypted: false,
    disableStats: true
});

```

Open you application termial
```
npm run dev
php artisan serve
```

# Section 11: Securing the Connections to the Laravel WebSockets Server

## 44. Accepting HTTP Connections for the Laravel WebSockets Project

# Step 01
--------------------------------------------------
sudo nano /etc/nginx/sites-available/ws.domain.com

# Step 02
```
server {
        listen        80;
        listen        [::]:80;

        server_name ws.supersecuredomain.com;
        
        root /var/www/ws.domain.com/public;
        
        index index.php;

        location / {
                try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
                include snippets/fastcgi-php.conf;

                fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        }

        location /app {
                proxy_pass             http://127.0.0.1:6001;
                proxy_read_timeout     60;
                proxy_connect_timeout  60;
                proxy_redirect         off;

                # Allow the use of websockets
                proxy_http_version 1.1;
                proxy_set_header Upgrade $http_upgrade;
                proxy_set_header Connection 'upgrade';
                proxy_set_header Host $host;
                proxy_cache_bypass $http_upgrade;
        }
}
```

save this file

# Step 03
```
> suto nginx -t
> sudo systemctl reload nginx.service
```

## 45. Generating SSL Certificates for the Laravel WebSockets Server

## 46. Establishing Secure Connections to the Laravel WebSockets Server


# Section 12: Conclusions and Recommendations

## 47. The Essence of the Realtime Application with Laravel

## 48. Bonus Class

## 49. Pending Topics

### Pending Topics

1. Adding, Configuring and Using Your Own WebSockets Server

    1. Creating a New Laravel Project for the WebSockets Server

    2. Adding Laravel WebSockets to the New Project

    3. Configuring an Application on the WebSockets Server with Laravel

    4. Using Your Own WebSockets Server from the Laravel Project

    5. Viewing Laravel WebSockets Statistics