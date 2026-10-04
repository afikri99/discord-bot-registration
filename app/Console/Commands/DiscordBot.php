<?php

namespace App\Console\Commands;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Intents;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;

class DiscordBot extends Command
{
    protected $signature = 'discord:run';
    protected $description = 'Jalankan bot Discord';

    public function handle(): void
    {
        $cfg = config('services.discord');

        $discord = new Discord([
            'token' => $cfg['token'],
            'intents' => Intents::GUILDS,
        ]);

        // HARUS non-blocking (jangan pakai Http:: milik Laravel di sini)
        $browser = (new Browser())->withTimeout(10)->withRejectErrorResponse(false);

        $discord->on('init', function (Discord $discord) use ($cfg, $browser) {
            $this->info("Bot online sebagai {$discord->user->username}");

            $discord->listenCommand('register', function (Interaction $interaction) use ($cfg, $browser) {

                // Batasi hanya di 1 channel
                if ($interaction->channel_id !== $cfg['register_channel_id']) {
                    return $interaction->respondWithMessage(
                        MessageBuilder::new()->setContent("❌ Command ini hanya bisa dipakai di <#{$cfg['register_channel_id']}>."),
                        true
                    );
                }

                // Form popup supaya password tidak tampil di chat
                $interaction->showModal(
                    'Registrasi Akun',
                    'register_modal',
                    [
                        ActionRow::new()->addComponent(
                            TextInput::new('Username', TextInput::STYLE_SHORT, 'username')->setRequired(true)
                        ),
                        ActionRow::new()->addComponent(
                            TextInput::new('Password', TextInput::STYLE_SHORT, 'password')
                                ->setRequired(true)->setMinLength(8)
                        ),
                    ],
                    function (Interaction $interaction, Collection $components) use ($cfg, $browser) {
                        $fields = $this->extractFields($components);
                        if (empty($fields)) {
                            $fields = $this->extractFields($interaction->data->components ?? []);
                        }
                        $username = $fields['username'] ?? null;
                        $password = $fields['password'] ?? null;

                        if (!$username || !$password) {
                            echo "Field form tidak terbaca\n";
                            return $interaction->respondWithMessage(
                                MessageBuilder::new()->setContent('⚠️ Form tidak terbaca, coba lagi.'),
                                true
                            );
                        }
                        $discordUserId = $interaction->user->id;

                        $interaction->acknowledgeWithResponse(true)->then(function () use ($interaction, $cfg, $browser, $username, $password, $discordUserId) {
                            $payload = [
                                'email' => "{$discordUserId}@discord.local",
                                'username' => $username,
                                'password' => $password,
                                'password_confirmation' => $password,
                                'discorduserid' => $discordUserId,
                            ];

                            $browser->post(
                                $cfg['register_api_url'],
                                ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                                json_encode($payload)
                            )->then(
                                    function (ResponseInterface $res) use ($interaction, $username) {
                                        $data = json_decode((string) $res->getBody(), true);
                                        $ok = $res->getStatusCode() >= 200 && $res->getStatusCode() < 300;

                                        if ($ok) {
                                            $text = "✅ Registrasi berhasil untuk **{$username}**.";
                                        } else {
                                            $msg = isset($data['errors'])
                                                ? implode(', ', array_merge(...array_values($data['errors'])))
                                                : ($data['message'] ?? 'HTTP ' . $res->getStatusCode());
                                            $text = "❌ Registrasi gagal: {$msg}";
                                        }

                                        $interaction->updateOriginalResponse(MessageBuilder::new()->setContent($text));
                                    },
                                    function (\Throwable $e) use ($interaction) {
                                        Log::error('Register API error: ' . $e->getMessage());
                                        $interaction->updateOriginalResponse(
                                            MessageBuilder::new()->setContent('⚠️ Tidak bisa menghubungi server API. Coba lagi nanti.')
                                        );
                                    }
                                );
                        })->otherwise(function (\Throwable $e) {
                            echo "ACK ERROR: " . $e->getMessage() . "\n";
                        });
                    }
                );
            });
        });

        $discord->run();
    }
    private function extractFields($node, array &$out = []): array
    {
        if (is_array($node) || $node instanceof \Traversable) {
            foreach ($node as $item) {
                $this->extractFields($item, $out);
            }
            return $out;
        }

        if (is_object($node)) {
            $id = $node->custom_id ?? null;
            $val = $node->value ?? null;
            if ($id !== null && $val !== null) {
                $out[$id] = $val;
            }
            if (!empty($node->components)) {
                $this->extractFields($node->components, $out);
            }
            if (!empty($node->component)) {
                $this->extractFields($node->component, $out);
            }
        }

        return $out;
    }
}