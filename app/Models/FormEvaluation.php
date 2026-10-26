<?php

namespace App\Models;

use Database\Factories\FormEvaluationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'form_id',
    'form_response_id',
    'user_id',
    'status',
    'notes',
    'changes',
])]
class FormEvaluation extends Model
{
    /** @use HasFactory<FormEvaluationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    /**
     * The form the evaluation belongs to.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * The response the evaluation refers to, when applicable.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(FormResponse::class, 'form_response_id');
    }

    /**
     * The user that evaluated the form.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
