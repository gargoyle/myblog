# myblog
This is the application that powers my blog at paulcourt.co.uk. It was very much an exercise in learning about 
Event Sourcing and CQRS - and as my first attempt at such, I expect there to be a few bits that more experienced
CQRS'ers my find strange.

If that is the case, then I am happy for you to open issues or pull requests in the name of improving my understanding
of these concepts.

## Basic structure
### `/web`
This is where you are going to find all the front end code and the main `index.php` which bootstraps the app
and sets up the controllers and DI container, etc.

### `/views`
The twig based templates used for all page rendering.

### `/Pmc/Blog`
This contains the main appliction DI container, controllers and core event sourcing code. The main components 
are a `MessageBus` (allows any number of listeners to subscribe to events/messages), a `CommandBus` (Only allows one 
listener to subscribe per command), the `EventStore` abstraction and a `GigaFactory` (A factoy of factories)

### `/Pmc/Database`
Basic MySQL databse abstraction layer.

### `/Pmc/EsModules`
One goal I wanted to achive with the setup was to have the core event-sourcing setup separated from any "domain" 
logic. So I implemented the idea of *modules* which would be self contained blocks of domain logic.

In this case I have `User` module to handle all the aspects of user registration, authentication, etc. and 
an `Article` module to handle all the logic related to creating and displaying the actual blog posts.

The idea being that each module would only know about the core event store components and not each other - making
the modules re-usable in different applications.

