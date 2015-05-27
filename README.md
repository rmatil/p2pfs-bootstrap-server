# This is the repo of the bootstrap contacting server

## Architecture
For a rough architectural overview of this server see [this link](https://bitbucket.org/rmatil/comsys_bootstrap_server/wiki/Home)

## REST

#### Available Routes

* `GET /ip-addresses` Returns all current registered IP addresses
* `POST /ip-addresses/new` Stores a new address-port-pair on the server. Requires a form field `address` as well as a field `port`. Sets a ttl.
* `POST /ip-addresses/remove` Removes a certain address-port-pair from the list and removes expired pairs (in respect to ttl)
* `POST /keepalive` Updates the ttl on the specified address-port pair. Not definetly necessary as it would introduce a central authority

#### Authentication

To send request to the above described routes, you must provide the `GET` parameter `token` with the value `tabequals4`.     
E.g. `GET /ip-addresses?token=tabequals4`

#### Keepalive

Time to lives (ttl) for address port pairs can be updated on route `/keepalive` as specified above. 
_Note_: Not definetly necessary as this would introduce a central authority. See also the [wiki](https://bitbucket.org/rmatil/comsys_bootstrap_server/wiki/Home)
This removal occurs only on the following routes: 

* `/keepalive` 
* `/ip-addresses/remove`
* `/ip-addresses/new`

#### Examples

Using `curl`:

* Getting all addresses:

    

```
#!bash

    $ curl http://188.226.178.35/ip-addresses\?token\=tabequals4
    {"addresses":[{"address":"192.168.122.1","port":"4000","ttl":"17.05.2015 09:28:59"}]}
```

    

* Removal of an address:


    

```
#!bash

    $ curl -H "Content-Type: application/x-www-form-urlencoded" -X POST -d 'address=192.168.122.1&port=4000' 'http://188.226.178.35/ip-addresses/remove?token=tabequals4' 
    {"addresses":[]}
```

    