{{-- Composant d'icône papicons — usage unique dans toute l'app.
     <x-icon name="wallet" class="w-5 h-5" variant="outline" />

     L'icône est rendue via un sprite SVG (public/icons/papicons.svg) généré
     depuis la bibliothèque papicons. Aucune dépendance JS par icône : un seul
     <use> léger, idéal mobile-first.
--}}
@props([
    'name'    => 'circle',
    'class'   => 'w-5 h-5',
    'variant' => 'outline', // outline | solid | duotone
    'label'   => null,
])

@php
    $sprite = asset('icons/papicons.svg');
    $href = $sprite . '#icon-' . $name;
@endphp

<svg {{ $attributes->merge(['class' => 'inline-block shrink-0 ' . $class, 'aria-hidden' => $label ? 'false' : 'true']) }}
     fill="none"
     viewBox="0 0 24 24"
     stroke="currentColor"
     stroke-width="1.5"
     @if($label) role="img" aria-label="{{ $label }}" @endif
>
    <use href="{{ $href }}" />
</svg>
