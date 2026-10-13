<?php

namespace Database\Seeders;

use App\Enums\ShortLinkType;
use App\Models\Document;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShortLinkSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The links seeded for every user.
     *
     * @var list<array{title: string, type: ShortLinkType, state: string, files: int}>
     */
    private const LINKS = [
        ['title' => 'Envio de contratos', 'type' => ShortLinkType::Upload, 'state' => 'active', 'files' => 3],
        ['title' => 'Documentação técnica', 'type' => ShortLinkType::Download, 'state' => 'active', 'files' => 3],
        ['title' => 'Fotos da vistoria', 'type' => ShortLinkType::Upload, 'state' => 'active', 'files' => 2],
        ['title' => 'Planilha orçamentária', 'type' => ShortLinkType::Download, 'state' => 'limited', 'files' => 2],
        ['title' => 'Certidões negativas', 'type' => ShortLinkType::Download, 'state' => 'password', 'files' => 1],
        ['title' => 'Relatório final', 'type' => ShortLinkType::Download, 'state' => 'expired', 'files' => 1],
        ['title' => 'Termo de encerramento', 'type' => ShortLinkType::Upload, 'state' => 'inactive', 'files' => 0],
    ];

    /**
     * The documents attached to or received through the seeded links.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const DOCUMENTS = [
        ['Contrato social.pdf', 'application/pdf'],
        ['Proposta comercial.pdf', 'application/pdf'],
        ['Planilha orçamentária.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ['Memorial descritivo.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ['Cronograma de obras.pdf', 'application/pdf'],
        ['Lista de materiais.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ['Laudo técnico.pdf', 'application/pdf'],
        ['Termo de recebimento.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        foreach ($users as $owner) {
            foreach (self::LINKS as $definition) {
                $this->seedLink($owner, $definition);
            }
        }
    }

    /**
     * Seed one link owned by the given user.
     *
     * @param  array{title: string, type: ShortLinkType, state: string, files: int}  $definition
     */
    private function seedLink(User $owner, array $definition): void
    {
        $shortLink = ShortLink::firstOrCreate(
            [
                'user_id' => $owner->getKey(),
                'title' => $definition['title'],
            ],
            [
                'code' => ShortLink::createCode(),
                'type' => $definition['type'],
                'description' => ucfirst(fake('pt_BR')->sentence(12)),
                'is_active' => $definition['state'] !== 'inactive',
                'expires_at' => $this->expiresAt($definition['state']),
                'max_uses' => $definition['state'] === 'limited' ? 10 : null,
                'used_count' => $this->usedCount($definition['state']),
                'password' => $definition['state'] === 'password' ? Hash::make('segredo123') : null,
            ],
        );

        if ($shortLink->wasRecentlyCreated) {
            $this->seedDocuments($shortLink, $definition['files']);
        }
    }

    /**
     * Resolve the expiry timestamp for the given link state.
     */
    private function expiresAt(string $state): ?Carbon
    {
        return match ($state) {
            'expired' => now()->subDays(3),
            'active' => now()->addDays(30),
            default => null,
        };
    }

    /**
     * Resolve the access count for the given link state.
     */
    private function usedCount(string $state): int
    {
        return match ($state) {
            'inactive' => 0,
            'limited' => 4,
            'expired' => 12,
            default => random_int(0, 18),
        };
    }

    /**
     * Seed the documents attached to or received through the given link.
     */
    private function seedDocuments(ShortLink $shortLink, int $count): void
    {
        $documents = [];

        for ($index = 1; $index <= $count; $index++) {
            [$originalName, $mimeType] = self::DOCUMENTS[($index - 1) % count(self::DOCUMENTS)];
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);

            $documents[] = Document::create([
                'user_id' => $shortLink->user_id,
                'uploaded_via_short_link_id' => $shortLink->type === ShortLinkType::Upload ? $shortLink->getKey() : null,
                'original_name' => $originalName,
                'path' => 'documents/'.Str::uuid().'.'.$extension,
                'disk' => 'local',
                'mime_type' => $mimeType,
                'size' => random_int(20_000, 4_000_000),
            ]);
        }

        if ($shortLink->type === ShortLinkType::Download && $documents !== []) {
            $shortLink->documents()->attach(collect($documents)->pluck('id')->all());
        }
    }
}
