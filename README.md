# This is the repo of the bootstrap contacting server

## Available REST routes

* `GET /ip-addresses` Returns all current registered IP addresses
* `POST /ip-addresses/new` Stores a new address on the server. Requires a form field `address` with the IP address in it
* `PUT /ip-addresses` Pings all current registered IP addresses and removes all, which do not respond