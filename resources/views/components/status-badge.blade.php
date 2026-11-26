@props([
    'status',
    'type' => 'sale',
])

@php
    $badges = [
        'sale' => [
            'completed' => ['class' => 'bg-success', 'label' => 'Terminée'],
            'pending' => ['class' => 'bg-warning text-dark', 'label' => 'En attente'],
            'cancelled' => ['class' => 'bg-danger', 'label' => 'Annulée'],
        ],
        'purchase' => [
            'received' => ['class' => 'bg-success', 'label' => 'Reçu'],
            'pending' => ['class' => 'bg-warning text-dark', 'label' => 'En attente'],
            'cancelled' => ['class' => 'bg-danger', 'label' => 'Annulé'],
        ],
        'invoice' => [
            'paid' => ['class' => 'bg-success', 'label' => 'Payée'],
            'partial' => ['class' => 'bg-info text-dark', 'label' => 'Partiellement payée'],
            'unpaid' => ['class' => 'bg-warning text-dark', 'label' => 'Non payée'],
        ],
    ];

    $badge = $badges[$type][$status] ?? [
        'class' => 'bg-secondary',
        'label' => $status,
    ];
@endphp

<span class="badge {{ $badge['class'] }}">
    {{ $badge['label'] }}
</span>
