# This is the repo of the bootstrap contacting server

## Available REST routes

* `GET /ip-addresses` Returns all current registered IP addresses
* `POST /ip-addresses/new` Stores a new address-port-pair on the server. Requires a form field `address` as well as a field `port`
* `POST /ip-addresses/remove` Removes a certain address-port-pair from the list

## Authentication

To send request to the above described routes, you must provide the `GET` parameter `token` with the value `tabequals4`.     
E.g. `GET /ip-addresses?token=tabequals4`