<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'surname', 'email', 'password', 'theme'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The user's full name (name plus surname).
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim($this->name.' '.($this->surname ?? '')),
        );
    }

    /**
     * The files owned by the user.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * The backups requested by the user.
     *
     * @return HasMany<Backup, $this>
     */
    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    /**
     * The folders owned by the user.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * The ERBs registered by the user.
     */
    public function erbs(): HasMany
    {
        return $this->hasMany(Erb::class);
    }

    /**
     * The data-collection forms created by the user.
     *
     * @return HasMany<Form, $this>
     */
    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    /**
     * The projects created by the user.
     *
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * The clients registered by the user.
     *
     * @return HasMany<Client, $this>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * The catalog items registered by the user.
     *
     * @return HasMany<CatalogItem, $this>
     */
    public function catalogItems(): HasMany
    {
        return $this->hasMany(CatalogItem::class);
    }

    /**
     * The work orders created by the user.
     *
     * @return HasMany<WorkOrder, $this>
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * The task lists created by the user.
     *
     * @return HasMany<TodoList, $this>
     */
    public function todoLists(): HasMany
    {
        return $this->hasMany(TodoList::class);
    }

    /**
     * The tasks created by the user.
     *
     * @return HasMany<TodoTask, $this>
     */
    public function todoTasks(): HasMany
    {
        return $this->hasMany(TodoTask::class);
    }

    /**
     * The radio links created by the user.
     *
     * @return HasMany<RadioLink, $this>
     */
    public function radioLinks(): HasMany
    {
        return $this->hasMany(RadioLink::class);
    }

    /**
     * The colaboradores registered by the user.
     *
     * @return HasMany<Colaborador, $this>
     */
    public function colaboradores(): HasMany
    {
        return $this->hasMany(Colaborador::class);
    }

    /**
     * The agenda calendars owned by the user.
     *
     * @return HasMany<AgendaCalendar, $this>
     */
    public function agendaCalendars(): HasMany
    {
        return $this->hasMany(AgendaCalendar::class);
    }

    /**
     * The agenda calendars shared with the user.
     *
     * @return BelongsToMany<AgendaCalendar, $this>
     */
    public function sharedAgendaCalendars(): BelongsToMany
    {
        return $this->belongsToMany(AgendaCalendar::class, 'agenda_calendar_shares')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * The agenda events organized by the user.
     *
     * @return HasMany<AgendaEvent, $this>
     */
    public function agendaEvents(): HasMany
    {
        return $this->hasMany(AgendaEvent::class);
    }

    /**
     * The agenda invitations received by the user.
     *
     * @return HasMany<AgendaEventAttendee, $this>
     */
    public function agendaAttendances(): HasMany
    {
        return $this->hasMany(AgendaEventAttendee::class);
    }

    /**
     * The project memberships held by the user.
     *
     * @return HasMany<ProjectMember, $this>
     */
    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * The projects the user can open, owned or shared.
     *
     * @return BelongsToMany<Project, $this>
     */
    public function accessibleProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')->withPivot('role')->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
