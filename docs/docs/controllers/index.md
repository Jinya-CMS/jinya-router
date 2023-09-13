# Controllers

Controllers are the core classes in Jinya Router. When the router handles a request or generates the routing table it
will look through a defined directory for all controllers it can find.

## Defining controllers

Defining a controller is simple. Any class that has the `Controller` attribute qualifies as controller. Apart from that, 
Jinya Router provides an abstract base controller which offers a few benefits. More on that later.

