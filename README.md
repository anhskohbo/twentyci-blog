## Blog TEST

## Installation

Use docker, following this instruction: https://laravel.com/docs/8.x/sail

Install depencencies.

```
$ composer install
```

## Setup

After setup complete, you should config the `.env` file matching with your system.

Run database migrate:

```
$ php artisan migrate
```

Create an admin account:

```
$ php artisan user:create
```

That's it. Happy review.

## Core features

[x] Everyone could see list of posts and check their detail

[x] People could register for new account and login/logout to the system

[x] Registered users could CRUD their posts.

[x] Posts’ body understand markdown syntax and could render properly

[x] Admin could see a list of created posts

[x] Admin could publish or unpublish created posts

## Optional features

[x] Only published posts would be display in public listing page

[x] Admin could see highlighting unpublished posts in list of all posts

[x] Admin could filter/order posts by date or status

[x] Admin could schedule post publishing. E.g найти работу I want publish this post automatically in tomorrow 9AM


## Very Very Very Optional features

[x] 100% Unit Tested (but not 100%)

[x] Clean commit messages
