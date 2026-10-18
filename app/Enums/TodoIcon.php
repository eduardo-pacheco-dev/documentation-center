<?php

namespace App\Enums;

enum TodoIcon: string
{
    case CheckCircle = 'check-circle';
    case ClipboardDocumentList = 'clipboard-document-list';
    case Star = 'star';
    case Flag = 'flag';
    case Briefcase = 'briefcase';
    case BuildingOffice = 'building-office';
    case Home = 'home';
    case User = 'user';
    case Users = 'users';
    case Cube = 'cube';
    case Folder = 'folder';
    case DocumentText = 'document-text';
    case Phone = 'phone';
    case MapPin = 'map-pin';
    case CalendarDays = 'calendar-days';
    case WrenchScrewdriver = 'wrench-screwdriver';
    case ArrowPath = 'arrow-path';
    case Signal = 'signal';

    /**
     * Get the human readable label for the icon.
     */
    public function label(): string
    {
        return match ($this) {
            self::CheckCircle => 'Lista',
            self::ClipboardDocumentList => 'Tarefas',
            self::Star => 'Favoritos',
            self::Flag => 'Prioridades',
            self::Briefcase => 'Trabalho',
            self::BuildingOffice => 'Clientes',
            self::Home => 'Casa',
            self::User => 'Pessoal',
            self::Users => 'Equipe',
            self::Cube => 'Produtos',
            self::Folder => 'Projetos',
            self::DocumentText => 'Documentos',
            self::Phone => 'Contatos',
            self::MapPin => 'Visitas',
            self::CalendarDays => 'Agenda',
            self::WrenchScrewdriver => 'Manutenção',
            self::ArrowPath => 'Rotinas',
            self::Signal => 'Rede',
        };
    }
}
