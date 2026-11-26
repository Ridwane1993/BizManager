<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Délai de paiement par défaut (en jours)
    |--------------------------------------------------------------------------
    |
    | Utilisé pour calculer la date d'échéance lorsque aucune date n'est
    | explicitement saisie lors de l'émission d'une facture.
    |
    */

    'default_due_days' => (int) env('INVOICE_DEFAULT_DUE_DAYS', 30),

];
