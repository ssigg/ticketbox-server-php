# Ticketbox server

[![Build Status](https://travis-ci.org/ssigg/ticketbox-server-php.svg?branch=master)](https://travis-ci.org/ssigg/ticketbox-server-php) [![Code Climate](https://codeclimate.com/github/ssigg/ticketbox-server-php/badges/gpa.svg)](https://codeclimate.com/github/ssigg/ticketbox-server-php) [![Test Coverage](https://codeclimate.com/github/ssigg/ticketbox-server-php/badges/coverage.svg)](https://codeclimate.com/github/ssigg/ticketbox-server-php/coverage)

## Web client
Use this server together with the web client available at https://github.com/ssigg/ticketbox-client-angularjs

## Available endpoints
* `/customer`: Endpoint for customer interface available to all visitors. Currently, they can only reserve seats but they cannot purchase any tickets.
* `/boxoffice`: Endpoint for the box office or ticket agencies. Currently, they can sell hardware tickets and mark the seats as sold.
* `/admin`: Administration interface endpoint.


## Installation
* Copy `admin/config_sample` to `admin/config` and adjust the values
* Copy `boxoffice/config_sample` to `boxoffice/config` and adjust the values
* Copy `admin/config_sample` to `admin/config` and adjust the values
* If you decide to use a server based database, set up the database
