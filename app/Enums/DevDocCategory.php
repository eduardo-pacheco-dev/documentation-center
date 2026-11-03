<?php

namespace App\Enums;

enum DevDocCategory: string
{
    case GettingStarted = 'getting-started';
    case Architecture = 'architecture';
    case Database = 'database';
    case Api = 'api';
    case Frontend = 'frontend';
    case Testing = 'testing';
    case Deployment = 'deployment';
    case Conventions = 'conventions';

    /**
     * The label shown in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::GettingStarted => 'Primeiros passos',
            self::Architecture => 'Arquitetura',
            self::Database => 'Banco de dados',
            self::Api => 'API',
            self::Frontend => 'Frontend',
            self::Testing => 'Testes',
            self::Deployment => 'Deploy',
            self::Conventions => 'Convenções',
        };
    }
}
