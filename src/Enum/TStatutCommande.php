<?php

namespace App\Enum;

enum TStatutCommande: string
{
    case CONFIRMED = 'Confirmée';
    case EN_ATTENTE = 'En attente de confirmation';
    case ANNULEE = 'Annulée';
}