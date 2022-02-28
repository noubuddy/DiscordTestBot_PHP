<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;
use React\Http\Browser;

class RunBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $loop = Factory::create();

        $commands = array('!joke');

        $browser = new Browser($loop);

        $discord = new Discord([
            'token' => env('DISCORD_TOKEN'),
            'loop' => $loop,
        ]);

        $discord->on('ready', function (Discord $discord) {
            echo "Bot is ready!", PHP_EOL;

            // Listen for messages.
            $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
                echo "{$message->author->username}: {$message->content}", PHP_EOL;
                if(!$message->author->bot && $message->content != "!joke") {
                    $message->reply($message->author->username . ": " . $message->content);
                }
            });
        });

        $discord->on('message', function (Message $message, Discord $discord) use ($browser) {
            if (strtolower($message->content) == '!joke') {
                $browser->get('https://api.chucknorris.io/jokes/random')->then(function (ResponseInterface $response) use ($message) {
                    $joke = json_decode($response->getBody())->value;
                    echo "{$joke}", PHP_EOL;
                    $message->reply($joke);
                });
            }
        });

        $discord->run();
        return 0;
    }
}
