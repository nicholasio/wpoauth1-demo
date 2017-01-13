# wpoauth1-demo
Simple WPOAuth1 Server demo.

## Instructions
1. Run `composer install`
2. Create the application in `Users -> Applications` in you WordPress OAuth1 server
3. Set the variables `$consumer_key`, `$consumer_secret`, `$base_urk` and `$callback_url`
4. `php -S localhost:8000` (this will be the `$callback_url`)
5. Open `localhost:8000` in your browser