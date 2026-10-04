<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DiscordDeploy extends Command
{
    protected $signature = 'discord:deploy';
    protected $description = 'Daftarkan slash command ke server Discord';

    public function handle(): int
    {
        $cfg = config('services.discord');

        $res = Http::withToken($cfg['token'], 'Bot')->put(
            "https://discord.com/api/v10/applications/{$cfg['client_id']}/guilds/{$cfg['guild_id']}/commands",
            [
                [
                    'name' => 'register',
                    'description' => 'Daftarkan akun baru',
                    'type' => 1,
                ]
            ]
        );

        if ($res->successful()) {
            $this->info('Slash command berhasil didaftarkan.');
            return self::SUCCESS;
        }

        $this->error("Gagal ({$res->status()}): " . $res->body());
        return self::FAILURE;
    }
}