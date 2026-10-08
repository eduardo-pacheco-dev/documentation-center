<?php

namespace App\Enums;

enum TaskConstraint: string
{
    case AsSoonAsPossible = 'asap';
    case AsLateAsPossible = 'alap';
    case MustStartOn = 'must_start_on';
    case MustFinishOn = 'must_finish_on';
    case StartNoEarlierThan = 'start_no_earlier_than';
    case StartNoLaterThan = 'start_no_later_than';
    case FinishNoEarlierThan = 'finish_no_earlier_than';
    case FinishNoLaterThan = 'finish_no_later_than';

    /**
     * Whether the constraint pins the task to a single date.
     */
    public function isRigid(): bool
    {
        return match ($this) {
            self::MustStartOn, self::MustFinishOn => true,
            default => false,
        };
    }

    /**
     * Whether the constraint only applies during the backward pass.
     */
    public function isLateLimit(): bool
    {
        return match ($this) {
            self::AsLateAsPossible,
            self::StartNoLaterThan,
            self::FinishNoLaterThan => true,
            default => false,
        };
    }

    /**
     * The human readable label shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::AsSoonAsPossible => 'O mais cedo possível',
            self::AsLateAsPossible => 'O mais tarde possível',
            self::MustStartOn => 'Deve iniciar em',
            self::MustFinishOn => 'Deve terminar em',
            self::StartNoEarlierThan => 'Início não antes de',
            self::StartNoLaterThan => 'Início não depois de',
            self::FinishNoEarlierThan => 'Término não antes de',
            self::FinishNoLaterThan => 'Término não depois de',
        };
    }
}
